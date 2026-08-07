<?php
/* Unreal Fest Seoul 2026 — 온라인 라이브 시청 (live.php)
 * 이메일 등록확인 게이트(등록 완료자) → Day1/Day2 · 트랙 4채널 YouTube 라이브 시청. 설정=cb_unreal_2026_config(라이브 설정 admin).
 * 관리자(mb_level>=10)는 게이트 우회. noindex. PHP 7.0 호환.
 */
include_once "../common.php";
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

function lv_get($k){ $r=@sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='".sql_real_escape_string($k)."'"); return $r?$r['cfg_val']:''; }
// 라이브 활성 = 수동 토글 ON 또는 예약 기간(live_start~end) 내 (서버 시각 기준)
$__la_manual = (lv_get('live_active') === '1');
$__ls = lv_get('live_start'); $__le = lv_get('live_end'); $__lnow = date('Y-m-d H:i');
$live_active = $__la_manual || ($__ls !== '' && $__le !== '' && $__lnow >= $__ls && $__lnow <= $__le);
$live_notice = lv_get('live_notice');

// 관리자 여부(공개측 동일 세션)
$is_adm = (isset($member['mb_id']) && $member['mb_id']!=='' && (
    ((int)(isset($member['mb_level'])?$member['mb_level']:0) >= 10) ||
    (isset($config['cf_admin']) && $member['mb_id']===$config['cf_admin'])
));

// ── 이메일 게이트 ──
if (isset($_GET['logout'])) { unset($_SESSION['ufs_live_ok'], $_SESSION['ufs_live_name']); header('Location: live.php'); exit; }
$gate_err = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['live_email'])) {
    $em = trim($_POST['live_email']);
    $phd = preg_replace('/[^0-9]/', '', isset($_POST['live_phone']) ? $_POST['live_phone'] : '');
    if ($em === '' || !filter_var($em, FILTER_VALIDATE_EMAIL)) { $gate_err = '올바른 이메일을 입력해 주세요.'; }
    else if (strlen($phd) < 8) { $gate_err = '전화번호를 정확히 입력해 주세요.'; }
    else {
        // 이메일 + 전화번호(뒷 8자리·앞자리0 보정) 둘 다 일치하는 등록 확인
        $row = sql_fetch("SELECT apply_user_name, apply_user_phone, apply_user_email FROM cb_unreal_2026_event2_apply WHERE apply_user_email='".sql_real_escape_string($em)."' AND apply_user_phone LIKE '%".sql_real_escape_string(substr($phd,-8))."%' AND apply_pay_status<>0 AND apply_temp_yn='N' ORDER BY apply_no DESC LIMIT 1");
        if ($row) { $_SESSION['ufs_live_ok']=1; $_SESSION['ufs_live_name']=$row['apply_user_name']; $_SESSION['ufs_live_phone']=$row['apply_user_phone']; $_SESSION['ufs_live_email']=$row['apply_user_email']; header('Location: live.php'); exit; }
        $gate_err = '등록 정보를 찾을 수 없습니다. 등록에 사용하신 이메일과 전화번호를 확인해 주세요.';
    }
}
$verified = (!empty($_SESSION['ufs_live_ok']) || $is_adm);
$viewer = !empty($_SESSION['ufs_live_name']) ? $_SESSION['ufs_live_name'] : ($is_adm ? '관리자' : '');

// ── 채널 정의 ──
$DAYS = array('1'=>'8월 20일', '2'=>'8월 21일');
$DAYSUB = array('1'=>'Day 1 · 목', '2'=>'Day 2 · 금');
$TRK  = array(
  '1'=>array('1'=>'게임: 프로그래밍','2'=>'게임: 아트','3'=>'미디어 & 엔터','4'=>'공통'),
  '2'=>array('1'=>'게임: 프로그래밍','2'=>'게임: 아트','3'=>'미디어 & 엔터','4'=>'제조 및 시뮬'),
);
$TRKCOL = array('1'=>'#307FE2','2'=>'#FF8F1C','3'=>'#FA4616','4'=>'#DD0AB2');
// 기본 Day = 오늘(8/20→1, 8/21→2), 그 외 1
$today = date('Y-m-d');
$defDay = ($today==='2026-08-21') ? '2' : '1';
$day = (isset($_GET['d']) && isset($DAYS[$_GET['d']])) ? $_GET['d'] : $defDay;
$trk_explicit = (isset($_GET['t']) && isset($TRK[$day][$_GET['t']]));
$trk = $trk_explicit ? $_GET['t'] : '1';
$ytid = lv_get('live_yt_d'.$day.'t'.$trk);
// 기본 진입(트랙 미지정)인데 해당 채널이 비어 있고 라이브 활성이면 → 스트림 있는 첫 채널로 자동 이동
if (!$trk_explicit && $live_active && $ytid === '') {
    $__order = array();
    foreach (array_keys($TRK[$day]) as $tk) $__order[] = array($day, $tk);
    $__od2 = ($day==='1') ? '2' : '1';
    foreach (array_keys($TRK[$__od2]) as $tk) $__order[] = array($__od2, $tk);
    foreach ($__order as $o) { $yy = lv_get('live_yt_d'.$o[0].'t'.$o[1]); if ($yy !== '') { $day=$o[0]; $trk=$o[1]; $ytid=$yy; break; } }
}
$curtrk = $TRK[$day][$trk];
$curcol = $TRKCOL[$trk];
$cur_label = $DAYS[$day].' · '.$curtrk;
// 이전/다음 트랙(현재 Day 내 순환)
$__tk = array_keys($TRK[$day]); $__pos = array_search($trk, $__tk, true);
$prev_t = $__tk[($__pos - 1 + count($__tk)) % count($__tk)];
$next_t = $__tk[($__pos + 1) % count($__tk)];

// ── CGChat(2025 동일 방식) 채팅 URL 구성 — 채널별 room, griff/griff2/griff3 분산 ──
$chIndex = array('d1t1'=>1,'d1t2'=>2,'d1t3'=>3,'d1t4'=>4,'d2t1'=>5,'d2t2'=>6,'d2t3'=>7,'d2t4'=>8);
$__ci = isset($chIndex['d'.$day.'t'.$trk]) ? $chIndex['d'.$day.'t'.$trk] : 1;
$__sub = array('griff','griff2','griff3');
$chat_base = 'https://'.$__sub[($__ci-1)%3].'.cgchat.kr/chat?sk=griff&no=griffroom2026_'.$__ci;
$__ui   = rawurlencode('{"btnPopupChat":"0","btnEmoji":"1"}');
$__view = rawurlencode('{"sendBtn":"333333","bgColor":"333333","msgViewType":"0","chatOneLine":"0","isChatHistory":"1","sysMsgColor":"7e7e7e","chatTime":"1"}');
$__my   = rawurlencode('{"nkColor":"7e7e7e","msgColor":"000000"}');
$__ctrl = rawurlencode('{"banWord":"***나쁜말***","autoLink":"1","maxLength":"0","msgInterval":"0","mobileFocus":"0"}');
$chat_src = $chat_base.'&ui='.$__ui.'&view='.$__view.'&my='.$__my.'&control='.$__ctrl.'&tg=https://unrealsummit16.cafe24.com/og/tg.svg';
if ($is_adm) {
    $chat_src .= '&lv=3&id='.rawurlencode('언리얼페스트').'&nk=admin';
} else {
    $__vemail = !empty($_SESSION['ufs_live_email']) ? $_SESSION['ufs_live_email'] : '';
    $__vphone = preg_replace('/[^0-9]/', '', !empty($_SESSION['ufs_live_phone']) ? $_SESSION['ufs_live_phone'] : '');
    $__nk = (function_exists('mb_substr') ? mb_substr($viewer,0,12,'UTF-8') : substr($viewer,0,12)).'('.substr($__vphone,-4).')';
    $chat_src .= '&lv=2&id='.rawurlencode($__vemail).'&nk='.rawurlencode($__nk);
}
$has_stream = ($live_active && $ytid !== '');
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>온라인 라이브 — Unreal Fest Seoul 2026</title>
<style>
:root{--bg:#08080a;--panel:#0e0e12;--panel2:#0b0b0e;--line:#1e1e25;--line2:#2a2a33;--teal:#00C1D5;--text:#eaeaef;--muted:#8b8b96;--accent:<?= $curcol ?>}
*{box-sizing:border-box}
html,body{margin:0}
body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;-webkit-font-smoothing:antialiased;
  background-image:radial-gradient(900px 500px at 78% -8%, rgba(0,193,213,.10), transparent 60%),radial-gradient(700px 400px at 0% 0%, rgba(48,127,226,.06), transparent 55%)}
a{color:inherit;text-decoration:none}
.blink{animation:blink 1.25s steps(1,end) infinite}@keyframes blink{50%{opacity:.28}}
.dot{display:inline-block;width:8px;height:8px;border-radius:50%;vertical-align:middle}

/* top bar */
.lv-top{position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:14px;
  padding:13px clamp(16px,4vw,40px);background:rgba(8,8,10,.72);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
.lv-logo{display:flex;align-items:center;gap:14px;min-width:0}
.lv-toplogo{height:24px;width:auto;display:block}
.lv-user{display:flex;align-items:center;gap:12px;font-size:13px;color:var(--muted);white-space:nowrap}
.lv-user b{color:var(--text);font-weight:700}
.lv-out{padding:6px 13px;border:1px solid var(--line2);border-radius:999px;font-size:12px;color:var(--muted);transition:.15s}
.lv-out:hover{border-color:var(--teal);color:var(--teal)}

.lv-wrap{max-width:1680px;margin:0 auto;padding:clamp(16px,2.4vw,30px) clamp(14px,4vw,40px) 56px}

/* channel bar */
.lv-chan{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:18px}
.lv-tracks{display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex:1;min-width:260px}
.lv-tk{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border:1px solid var(--line);border-radius:11px;background:var(--panel);font-size:13px;font-weight:700;color:var(--muted);transition:.15s}
.lv-tk .dot{width:9px;height:9px;box-shadow:0 0 0 3px rgba(255,255,255,.03)}
.lv-tk:hover{border-color:var(--line2);color:var(--text)}
.lv-tk.on{color:#fff;background:transparent;box-shadow:none}
.lv-nowb{margin-left:3px;padding:2px 8px;font-size:10px;font-weight:800;letter-spacing:.02em;background:var(--tkc);color:#fff;line-height:1.55}
.lv-nav{margin-left:auto;display:flex;gap:6px}
.lv-nav a{width:38px;height:38px;display:grid;place-items:center;border:1px solid var(--line);border-radius:10px;background:var(--panel);color:var(--muted);transition:.15s}
.lv-nav a:hover{border-color:var(--teal);color:var(--teal)}

/* unified player */
.lv-player{display:flex;align-items:stretch;border:1px solid var(--line);border-radius:16px;overflow:hidden;background:#000;box-shadow:0 40px 90px -30px rgba(0,0,0,.75),0 0 0 1px rgba(0,193,213,.05)}
.lv-video{flex:1 1 auto;min-width:0;display:flex;flex-direction:column;background:#000}
.lv-frame{position:relative;width:100%;padding-top:56.25%;background:radial-gradient(120% 120% at 50% 30%, #131318, #000)}
.lv-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.lv-frame:fullscreen{padding-top:0;height:100%}
.lv-frame:-webkit-full-screen{padding-top:0;height:100%}
.lv-hold{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;text-align:center;padding:24px}
.lv-hold .ic{width:64px;height:64px;border-radius:50%;display:grid;place-items:center;border:1px solid var(--line2);background:#0c0c10;color:var(--accent)}
.lv-hold h3{margin:0;font-size:20px;font-weight:900;color:#fff;letter-spacing:-.01em}
.lv-hold p{margin:0;font-size:13px;color:var(--muted);line-height:1.7}
/* control bar */
.lv-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 16px;background:rgba(12,12,16,.92);border-top:1px solid var(--line)}
.lv-now{display:flex;align-items:center;gap:10px;min-width:0}
.lv-now .tag{display:inline-flex;align-items:center;gap:6px;padding:4px 9px;border-radius:6px;font-size:10px;font-weight:900;letter-spacing:.1em;background:rgba(239,68,68,.16);color:#ff6b6b}
.lv-now .lab{font-size:14px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lv-now .lab i{font-style:normal;color:var(--accent)}
.lv-ctrl{display:flex;gap:8px;flex-shrink:0}
.lv-btn{display:inline-flex;align-items:center;gap:7px;padding:8px 13px;border:1px solid var(--line2);border-radius:9px;background:#141418;color:#cfcfd6;font-size:12px;font-weight:700;cursor:pointer;transition:.15s}
.lv-btn:hover{border-color:var(--teal);color:#fff}
.lv-btn svg{width:15px;height:15px}
/* chat */
.lv-chat{flex:0 0 380px;display:flex;flex-direction:column;border-left:1px solid var(--line);background:var(--panel2)}
.lv-chat-h{display:flex;align-items:center;gap:8px;padding:13px 15px;font-size:13px;font-weight:800;color:#fff;border-bottom:1px solid var(--line)}
.lv-chat-h .dot{width:7px;height:7px;background:#ff6b6b}
.lv-chat iframe{flex:1;width:100%;border:0;min-height:0;background:#111}
.hidechat .lv-chat{display:none}

/* footer */
.lv-foot{margin-top:28px;padding-top:22px;border-top:1px solid var(--line);display:flex;justify-content:center;align-items:center}
.lv-foot img{height:22px;width:auto;opacity:.6;transition:opacity .15s}
.lv-foot a:hover img{opacity:1}
.lv-notice{margin:0 0 16px;font-size:13px;color:#cfd0d6;background:linear-gradient(180deg,rgba(0,193,213,.06),transparent);border:1px solid rgba(0,193,213,.22);border-radius:12px;padding:12px 16px;display:flex;gap:10px;align-items:flex-start}
.lv-notice .dot{width:7px;height:7px;background:var(--teal);margin-top:7px;flex:none}

/* gate */
.lv-gate{min-height:calc(100vh - 54px);display:flex;align-items:center;justify-content:center;padding:24px}
.lv-gcard{width:100%;max-width:440px;background:linear-gradient(180deg,rgba(255,255,255,.03),transparent),var(--panel);border:1px solid var(--line);border-radius:20px;padding:38px 34px;box-shadow:0 40px 90px -40px rgba(0,0,0,.8)}
.lv-glogo{width:200px;max-width:64%;height:auto;display:block;margin-bottom:22px}
.lv-gcard h1{font-size:23px;font-weight:900;color:#fff;margin:0 0 8px;letter-spacing:-.01em}
.lv-gcard p{font-size:13.5px;color:var(--muted);margin:0 0 20px;line-height:1.7}
.lv-gcard input{width:100%;padding:14px 16px;background:#08080b;border:1px solid var(--line2);border-radius:11px;color:#fff;font-size:15px;margin-bottom:12px;transition:.15s}
.lv-gcard input:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,193,213,.12)}
.lv-gcard button{width:100%;padding:15px;background:var(--teal);color:#00232a;border:0;border-radius:11px;font-size:15px;font-weight:900;cursor:pointer;transition:.15s;letter-spacing:.01em}
.lv-gcard button:hover{filter:brightness(1.06)}
.lv-err{color:#ff8a8a;font-size:13px;margin-bottom:12px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);padding:9px 12px;border-radius:9px}

@media (max-width:900px){
  .lv-player{flex-direction:column}
  .lv-chat{flex-basis:auto;border-left:0;border-top:1px solid var(--line);height:52vh}
  .lv-nav{display:none}
}
/* 영상·버튼·폼 라운드 제거(각진 스타일). 원형 표시점(dot/ic)은 유지 */
.lv-player,.lv-frame,.lv-chat,.lv-chat iframe,.lv-btn,.lv-tk,.lv-nav a,.lv-out,.lv-notice,.lv-nowb,.lv-gcard,.lv-gcard input,.lv-gcard button,.lv-err{border-radius:0}
</style>
</head>
<body class="<?= $has_stream ? '' : '' ?>">

<header class="lv-top">
  <div class="lv-logo">
    <a href="index.php" aria-label="Unreal Fest Seoul 2026"><img class="lv-toplogo" src="white_logo.svg" alt="Unreal Fest Seoul 2026"></a>
  </div>
  <?php if ($verified): ?>
  <div class="lv-user"><span><b><?= e($viewer) ?></b>님</span><a class="lv-out" href="live.php?logout=1">로그아웃</a></div>
  <?php endif; ?>
</header>

<?php if (!$verified): ?>
  <div class="lv-gate">
    <div class="lv-gcard">
      <img class="lv-glogo" src="white_logo.svg" alt="Unreal Fest Seoul 2026">
      <h1>온라인 라이브 시청</h1>
      <p>참가 등록에 사용하신 <b style="color:#cfd0d6">이메일과 전화번호</b>를 입력하시면 실시간 세션 시청 화면으로 입장합니다.</p>
      <?php if ($gate_err): ?><div class="lv-err"><?= e($gate_err) ?></div><?php endif; ?>
      <form method="post">
        <input type="email" name="live_email" autocapitalize="off" autocomplete="off" placeholder="이메일 입력" required autofocus>
        <input type="text" name="live_phone" inputmode="numeric" autocomplete="off" placeholder="전화번호 입력" required>
        <button type="submit">입장하기 →</button>
      </form>
      <p style="font-size:12px;color:#6b6b76;margin:16px 0 0;line-height:1.7">등록 확인이 안 되면 사무국으로 문의해 주세요.<br>02-326-3701 · info@epiclounge.co.kr</p>
    </div>
  </div>
<?php else: ?>
  <div class="lv-wrap" id="lvWrap">

    <!-- 채널 바 -->
    <div class="lv-chan">
      <div class="lv-tracks">
        <?php foreach ($TRK[$day] as $tk=>$tl): $c=$TRKCOL[$tk]; ?>
          <a class="lv-tk <?= (string)$trk===(string)$tk?'on':'' ?>" style="--tkc:<?= $c ?>" href="live.php?d=<?= $day ?>&t=<?= $tk ?>"><span class="dot" style="background:<?= $c ?>"></span><?= e($tl) ?><?php if ((string)$trk===(string)$tk): ?><span class="lv-nowb">시청 중</span><?php endif; ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($live_notice !== ''): ?><div class="lv-notice"><span class="dot"></span><span><?= e($live_notice) ?></span></div><?php endif; ?>

    <!-- 플레이어 -->
    <div class="lv-player">
      <div class="lv-video">
        <div class="lv-frame" id="lvFrame">
          <?php if ($has_stream): ?>
            <iframe src="https://www.youtube.com/embed/<?= e($ytid) ?>?rel=0&autoplay=1&modestbranding=1" title="<?= e($cur_label) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe>
          <?php else: ?>
            <div class="lv-hold">
              <div class="ic"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
              <h3>곧 시작합니다</h3>
              <p><b style="color:<?= $curcol ?>"><?= e($curtrk) ?></b> 채널의 라이브가 아직 시작되지 않았습니다.<br>세션 시간에 맞춰 다시 접속하거나 다른 채널을 확인해 주세요.</p>
            </div>
          <?php endif; ?>
        </div>
        <div class="lv-bar">
          <div class="lv-now">
            <span class="lab"><?= e($DAYS[$day]) ?> · <i><?= e($curtrk) ?></i></span>
          </div>
          <div class="lv-ctrl">
            <?php if ($has_stream): ?>
            <button type="button" class="lv-btn" id="chBtn" onclick="ufsToggleChat()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span id="chTxt">채팅 숨기기</span></button>
            <?php endif; ?>
            <button type="button" class="lv-btn" onclick="ufsFS()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>전체화면</button>
          </div>
        </div>
      </div>
      <?php if ($has_stream): ?>
      <aside class="lv-chat" id="lvChat">
        <div class="lv-chat-h">실시간 채팅</div>
        <iframe src="<?= e($chat_src) ?>" title="라이브 채팅" allow="clipboard-write"></iframe>
      </aside>
      <?php endif; ?>
    </div>

    <footer class="lv-foot">
      <a href="https://epiclounge.co.kr" target="_blank" rel="noopener"><img src="https://epiclounge.co.kr/resource/images/common/foot_logo.svg" alt="EPIC LOUNGE"></a>
    </footer>
  </div>

  <script>
  function ufsToggleChat(){
    var w=document.getElementById('lvWrap'), t=document.getElementById('chTxt');
    if(!w) return;
    var hid=w.classList.toggle('hidechat');
    if(t) t.textContent = hid ? '채팅 보이기' : '채팅 숨기기';
  }
  function ufsFS(){
    var el=document.getElementById('lvFrame'); if(!el) return;
    var fsEl=document.fullscreenElement||document.webkitFullscreenElement;
    if(fsEl){ (document.exitFullscreen||document.webkitExitFullscreen).call(document); }
    else { (el.requestFullscreen||el.webkitRequestFullscreen).call(el); }
  }
  </script>
<?php endif; ?>
</body></html>
