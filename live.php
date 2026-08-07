<?php
/* Unreal Fest Seoul 2026 — 온라인 라이브 시청 (live.php)
 * 이메일 등록확인 게이트(등록 완료자) → Day1/Day2 · 트랙 4채널 YouTube 라이브 시청. 설정=cb_unreal_2026_config(라이브 설정 admin).
 * 관리자(mb_level>=10)는 게이트 우회. noindex. PHP 7.0 호환.
 */
include_once "../common.php";
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

function lv_get($k){ $r=@sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='".sql_real_escape_string($k)."'"); return $r?$r['cfg_val']:''; }
$live_active = (lv_get('live_active') === '1');
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
    if ($em==='' || !filter_var($em, FILTER_VALIDATE_EMAIL)) { $gate_err = '올바른 이메일을 입력해 주세요.'; }
    else {
        $row = sql_fetch("SELECT apply_user_name FROM cb_unreal_2026_event2_apply WHERE apply_user_email='".sql_real_escape_string($em)."' AND apply_pay_status<>0 AND apply_temp_yn='N' ORDER BY apply_no DESC LIMIT 1");
        if ($row) { $_SESSION['ufs_live_ok']=1; $_SESSION['ufs_live_name']=$row['apply_user_name']; header('Location: live.php'); exit; }
        $gate_err = '등록 정보를 찾을 수 없습니다. 참가 등록에 사용하신 이메일을 입력해 주세요.';
    }
}
$verified = (!empty($_SESSION['ufs_live_ok']) || $is_adm);
$viewer = !empty($_SESSION['ufs_live_name']) ? $_SESSION['ufs_live_name'] : ($is_adm ? '관리자' : '');

// ── 채널 정의 ──
$DAYS = array('1'=>'8월 20일 (Day 1)', '2'=>'8월 21일 (Day 2)');
$TRK  = array(
  '1'=>array('1'=>'게임: 프로그래밍','2'=>'게임: 아트','3'=>'미디어 & 엔터','4'=>'공통'),
  '2'=>array('1'=>'게임: 프로그래밍','2'=>'게임: 아트','3'=>'미디어 & 엔터','4'=>'제조 및 시뮬'),
);
// 기본 Day = 오늘(8/20→1, 8/21→2), 그 외 1
$today = date('Y-m-d');
$defDay = ($today==='2026-08-21') ? '2' : '1';
$day = (isset($_GET['d']) && isset($DAYS[$_GET['d']])) ? $_GET['d'] : $defDay;
$trk = (isset($_GET['t']) && isset($TRK[$day][$_GET['t']])) ? $_GET['t'] : '1';
$ytid = lv_get('live_yt_d'.$day.'t'.$trk);
$cur_label = $DAYS[$day].' · '.$TRK[$day][$trk];
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>온라인 라이브 — Unreal Fest Seoul 2026</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#09090b;color:#e4e4e7;font-family:system-ui,-apple-system,'Apple SD Gothic Neo',sans-serif}
a{color:inherit}
.top{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 20px;border-bottom:1px solid #1f1f23;background:#0d0d10;position:sticky;top:0;z-index:10;flex-wrap:wrap}
.brand{font-size:13px;font-weight:800;letter-spacing:.06em;color:#00C1D5}
.live-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#ef4444;margin-right:6px;animation:blink 1.3s infinite}
@keyframes blink{50%{opacity:.3}}
.wrap{max-width:1400px;margin:0 auto;padding:18px 20px 60px}
.tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.tab{padding:8px 16px;border:1px solid #27272a;border-radius:8px;background:#111115;color:#a1a1aa;text-decoration:none;font-size:13px;font-weight:600}
.tab.on{background:rgba(0,193,213,.15);border-color:#00C1D5;color:#00C1D5}
.daytabs .tab{font-size:14px;font-weight:700}
.stage{display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap}
.video-col{flex:1;min-width:320px}
.ratio{position:relative;width:100%;padding-top:56.25%;background:#000;border-radius:12px;overflow:hidden;border:1px solid #1f1f23}
.ratio iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.holder{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;text-align:center;color:#a1a1aa}
.holder .big{font-size:20px;font-weight:800;color:#fff}
.chat-col{width:360px;max-width:100%}
.chat-col iframe{width:100%;height:560px;border:1px solid #1f1f23;border-radius:12px;background:#111}
.nowlabel{margin:14px 0 4px;font-size:16px;font-weight:800;color:#fff}
.notice{margin-top:10px;font-size:13px;color:#a1a1aa;background:#111115;border:1px solid #27272a;border-radius:8px;padding:10px 14px}
.gate{min-height:calc(100vh - 52px);display:flex;align-items:center;justify-content:center;padding:20px}
.gate-card{width:100%;max-width:420px;background:#111115;border:1px solid #27272a;border-radius:16px;padding:32px}
.gate-card h1{font-size:20px;color:#fff;margin:0 0 6px}
.gate-card p{font-size:13px;color:#a1a1aa;margin:0 0 18px;line-height:1.6}
.gate-card input{width:100%;padding:13px;background:#0b0b0e;border:1px solid #27272a;border-radius:8px;color:#fff;font-size:15px;margin-bottom:12px}
.gate-card button{width:100%;padding:14px;background:#00C1D5;color:#001b1f;border:0;border-radius:8px;font-size:16px;font-weight:800;cursor:pointer}
.err{color:#f87171;font-size:13px;margin-bottom:12px}
.muted{color:#71717a;font-size:12px}
.btnline{display:flex;gap:8px;align-items:center}
.chtoggle{padding:6px 12px;border:1px solid #27272a;border-radius:8px;background:#111115;color:#a1a1aa;font-size:12px;cursor:pointer}
</style>
</head>
<body>
<div class="top">
  <div class="brand"><?php if ($live_active): ?><span class="live-dot"></span>LIVE · <?php endif; ?>UNREAL FEST SEOUL 2026 · 온라인</div>
  <?php if ($verified): ?>
  <div class="btnline"><span class="muted"><?= e($viewer) ?>님</span> <a class="tab" style="padding:6px 12px" href="live.php?logout=1">로그아웃</a></div>
  <?php endif; ?>
</div>

<?php if (!$verified): ?>
  <div class="gate">
    <div class="gate-card">
      <h1>온라인 라이브 시청</h1>
      <p>참가 등록에 사용하신 <b>이메일</b>을 입력하시면 라이브 시청 페이지로 이동합니다.</p>
      <?php if ($gate_err): ?><div class="err"><?= e($gate_err) ?></div><?php endif; ?>
      <form method="post">
        <input type="email" name="live_email" placeholder="email@example.com" required autofocus>
        <button type="submit">입장하기</button>
      </form>
      <p class="muted" style="margin-top:14px">· 등록 확인이 안 되면 사무국(02-326-3701 · info@epiclounge.co.kr)으로 문의해 주세요.</p>
    </div>
  </div>
<?php else: ?>
  <div class="wrap">
    <!-- Day 탭 -->
    <div class="tabs daytabs">
      <?php foreach ($DAYS as $dk=>$dl): ?>
        <a class="tab <?= $day===$dk?'on':'' ?>" href="live.php?d=<?= $dk ?>&t=1"><?= e($dl) ?></a>
      <?php endforeach; ?>
    </div>
    <!-- 트랙 탭 -->
    <div class="tabs">
      <?php foreach ($TRK[$day] as $tk=>$tl): ?>
        <a class="tab <?= $trk===$tk?'on':'' ?>" href="live.php?d=<?= $day ?>&t=<?= $tk ?>"><?= e($tl) ?></a>
      <?php endforeach; ?>
    </div>

    <div class="nowlabel"><?php if ($live_active && $ytid!==''): ?><span class="live-dot"></span><?php endif; ?><?= e($cur_label) ?></div>

    <div class="stage">
      <div class="video-col">
        <div class="ratio">
          <?php if ($live_active && $ytid !== ''): ?>
            <iframe src="https://www.youtube.com/embed/<?= e($ytid) ?>?rel=0&autoplay=1" title="<?= e($cur_label) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe>
          <?php else: ?>
            <div class="holder">
              <div class="big">⏳ 곧 시작합니다</div>
              <div>이 채널의 라이브가 아직 시작되지 않았습니다.<br>세션 시간에 맞춰 다시 접속해 주세요.</div>
            </div>
          <?php endif; ?>
        </div>
        <?php if ($live_notice !== ''): ?><div class="notice"><?= e($live_notice) ?></div><?php endif; ?>
        <?php if ($live_active && $ytid !== ''): ?>
        <div style="margin-top:10px"><button type="button" class="chtoggle" onclick="var c=document.getElementById('chatcol');c.style.display=(c.style.display==='none')?'block':'none';">채팅 켜기/끄기</button></div>
        <?php endif; ?>
      </div>
      <?php if ($live_active && $ytid !== ''): ?>
      <div class="chat-col" id="chatcol">
        <iframe src="https://www.youtube.com/live_chat?v=<?= e($ytid) ?>&embed_domain=<?= e($_SERVER['HTTP_HOST']) ?>" title="라이브 채팅"></iframe>
      </div>
      <?php endif; ?>
    </div>

    <p class="muted" style="margin-top:22px">· 상단 <b>Day</b>·<b>트랙</b> 탭으로 채널을 이동하세요. · 온라인 중계 제외 세션은 송출되지 않습니다. · 문의: 사무국 02-326-3701</p>
  </div>
<?php endif; ?>
</body></html>
