<?php
/* Unreal Fest Seoul 2026 — 다시보기(VOD) (replay.php)
 * 등록자 전용(이메일+전화 게이트). 관리자 2026_replay_config.php에서 공개 설정한 세션만 노출.
 * 세션 소스=cb_unreal_2026_agenda + cb_unreal_2026_replay(공개·YouTube). 재생=YouTube 임베드 모달. noindex. PHP 7.0.
 */
include_once "../common.php";
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

// 관리자 여부(공개측 세션)
$is_adm = (isset($member['mb_id']) && $member['mb_id']!=='' && (
    ((int)(isset($member['mb_level'])?$member['mb_level']:0) >= 10) ||
    (isset($config['cf_admin']) && $member['mb_id']===$config['cf_admin'])));

// ── 등록자 게이트 (live.php와 동일 방식, 세션키 ufs_vod_ok) ──
if (isset($_GET['logout'])) { unset($_SESSION['ufs_vod_ok'], $_SESSION['ufs_vod_name']); header('Location: replay.php'); exit; }
$gate_err = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['vod_email'])) {
    $em = trim($_POST['vod_email']);
    $phd = preg_replace('/[^0-9]/', '', isset($_POST['vod_phone']) ? $_POST['vod_phone'] : '');
    if ($em === '' || !filter_var($em, FILTER_VALIDATE_EMAIL)) { $gate_err = '올바른 이메일을 입력해 주세요.'; }
    else if (strlen($phd) < 8) { $gate_err = '전화번호를 정확히 입력해 주세요.'; }
    else {
        $row = sql_fetch("SELECT apply_user_name FROM cb_unreal_2026_event2_apply WHERE apply_user_email='".sql_real_escape_string($em)."' AND apply_user_phone LIKE '%".sql_real_escape_string(substr($phd,-8))."%' AND apply_pay_status<>0 AND apply_temp_yn='N' ORDER BY apply_no DESC LIMIT 1");
        if ($row) { $_SESSION['ufs_vod_ok']=1; $_SESSION['ufs_vod_name']=$row['apply_user_name']; header('Location: replay.php'); exit; }
        $gate_err = '등록 정보를 찾을 수 없습니다. 등록에 사용하신 이메일과 전화번호를 확인해 주세요.';
    }
}
$verified = (!empty($_SESSION['ufs_vod_ok']) || $is_adm);
$viewer = !empty($_SESSION['ufs_vod_name']) ? $_SESSION['ufs_vod_name'] : ($is_adm ? '관리자' : '');

// ── 공개 세션 로드 ──
$DAYS = array('1'=>'8월 20일(목)', '2'=>'8월 21일(금)');
$sessions = array();
if ($verified) {
    $rs = @sql_query("SELECT a.ag_no,a.ag_day,a.ag_track,a.ag_time,a.ag_title,a.ag_sp_name,a.ag_sp_company, r.rp_yt
        FROM cb_unreal_2026_replay r JOIN cb_unreal_2026_agenda a ON a.ag_no=r.rp_agno
        WHERE r.rp_public='Y' AND r.rp_yt<>'' AND a.ag_is_active='Y'
        ORDER BY a.ag_day ASC, a.ag_sort ASC, a.ag_no ASC");
    if ($rs) { while ($x=sql_fetch_array($rs)) $sessions[] = $x; }
}
// Day별 그룹
$byday = array('1'=>array(), '2'=>array());
foreach ($sessions as $s) { $d=((int)$s['ag_day']===2)?'2':'1'; $byday[$d][] = $s; }
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>다시보기 — Unreal Fest Seoul 2026</title>
<style>
:root{--bg:#08080a;--panel:#0e0e12;--line:#1e1e25;--line2:#2a2a33;--teal:#00C1D5;--text:#eaeaef;--muted:#8b8b96}
*{box-sizing:border-box}html,body{margin:0}
body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;-webkit-font-smoothing:antialiased;word-break:keep-all;
 background-image:radial-gradient(900px 500px at 78% -8%, rgba(0,193,213,.10), transparent 60%)}
a{color:inherit;text-decoration:none}
.rv-top{position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px clamp(16px,4vw,40px);background:rgba(8,8,10,.72);backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
.rv-toplogo{height:24px;width:auto;display:block}
.rv-user{display:flex;align-items:center;gap:12px;font-size:13px;color:var(--muted);white-space:nowrap}
.rv-user b{color:var(--text);font-weight:700}
.rv-out{padding:6px 13px;border:1px solid var(--line2);font-size:12px;color:var(--muted)}
.rv-out:hover{border-color:var(--teal);color:var(--teal)}
.rv-wrap{max-width:1280px;margin:0 auto;padding:clamp(18px,3vw,36px) clamp(14px,4vw,40px) 64px}
.rv-h1{font-size:clamp(24px,4vw,34px);font-weight:900;letter-spacing:-.01em;margin:0 0 6px}
.rv-sub{color:var(--muted);font-size:14px;margin:0 0 28px}
.rv-day{font-size:15px;font-weight:800;color:#fff;margin:26px 0 12px;padding-left:11px;border-left:3px solid var(--teal)}
.rv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.rv-card{border:1px solid var(--line);background:var(--panel);cursor:pointer;transition:.15s;overflow:hidden;display:flex;flex-direction:column}
.rv-card:hover{border-color:var(--teal);transform:translateY(-2px)}
.rv-thumb{position:relative;width:100%;padding-top:56.25%;background:#000 center/cover no-repeat}
.rv-thumb .play{position:absolute;inset:0;display:grid;place-items:center}
.rv-thumb .play svg{width:52px;height:52px;color:#fff;filter:drop-shadow(0 2px 8px rgba(0,0,0,.6));opacity:.92}
.rv-meta{padding:13px 15px}
.rv-trk{font-size:11px;font-weight:800;color:var(--teal);letter-spacing:.02em}
.rv-title{font-size:14.5px;font-weight:700;color:#fff;margin:5px 0 6px;line-height:1.4}
.rv-sp{font-size:12px;color:var(--muted)}
.rv-empty{border:1px dashed var(--line2);padding:48px 24px;text-align:center;color:var(--muted);font-size:14px;line-height:1.8}
/* gate */
.rv-gate{min-height:calc(100vh - 54px);display:flex;align-items:center;justify-content:center;padding:24px}
.rv-gcard{width:100%;max-width:440px;background:var(--panel);border:1px solid var(--line);padding:38px 34px}
.rv-glogo{width:200px;max-width:64%;height:auto;display:block;margin-bottom:22px}
.rv-gcard h1{font-size:23px;font-weight:900;color:#fff;margin:0 0 8px}
.rv-gcard p{font-size:13.5px;color:var(--muted);margin:0 0 20px;line-height:1.7}
.rv-gcard input{width:100%;padding:14px 16px;background:#08080b;border:1px solid var(--line2);color:#fff;font-size:15px;margin-bottom:12px}
.rv-gcard input:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,193,213,.12)}
.rv-gcard button{width:100%;padding:15px;background:var(--teal);color:#00232a;border:0;font-size:15px;font-weight:900;cursor:pointer}
.rv-err{color:#ff8a8a;font-size:13px;margin-bottom:12px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);padding:9px 12px}
/* modal */
.rv-mask{position:fixed;inset:0;z-index:60;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(4,4,6,.82)}
.rv-mask.on{display:flex}
.rv-modal{width:100%;max-width:960px;background:#000;border:1px solid var(--line2)}
.rv-mhd{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 16px;background:#0c0c10;border-bottom:1px solid var(--line)}
.rv-mhd .t{font-size:14px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.rv-mx{background:transparent;border:0;color:var(--muted);font-size:24px;cursor:pointer;line-height:1}
.rv-mx:hover{color:#fff}
.rv-frame{position:relative;width:100%;padding-top:56.25%;background:#000}
.rv-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
</style>
</head>
<body>
<header class="rv-top">
  <a href="index.php" aria-label="Unreal Fest Seoul 2026"><img class="rv-toplogo" src="white_logo.svg" alt="Unreal Fest Seoul 2026"></a>
  <?php if ($verified): ?><div class="rv-user"><span><b><?= e($viewer) ?></b>님</span><a class="rv-out" href="replay.php?logout=1">로그아웃</a></div><?php endif; ?>
</header>

<?php if (!$verified): ?>
  <div class="rv-gate">
    <div class="rv-gcard">
      <img class="rv-glogo" src="white_logo.svg" alt="Unreal Fest Seoul 2026">
      <h1>다시보기</h1>
      <p>참가 등록에 사용하신 <b style="color:#cfd0d6">이메일과 전화번호</b>를 입력하시면 세션 다시보기를 시청하실 수 있습니다.</p>
      <?php if ($gate_err): ?><div class="rv-err"><?= e($gate_err) ?></div><?php endif; ?>
      <form method="post">
        <input type="email" name="vod_email" autocapitalize="off" autocomplete="off" placeholder="이메일 입력" required autofocus>
        <input type="text" name="vod_phone" inputmode="numeric" autocomplete="off" placeholder="전화번호 입력" required>
        <button type="submit">입장하기 →</button>
      </form>
      <p style="font-size:12px;color:#6b6b76;margin:16px 0 0;line-height:1.7">등록 확인이 안 되면 사무국으로 문의해 주세요.<br>02-326-3701 · info@epiclounge.co.kr</p>
    </div>
  </div>
<?php else: ?>
  <div class="rv-wrap">
    <h1 class="rv-h1">세션 다시보기</h1>
    <p class="rv-sub">언리얼 페스트 서울 2026 세션을 다시 시청하실 수 있습니다. 카드를 클릭하면 재생됩니다.</p>

    <?php if (!$sessions): ?>
      <div class="rv-empty">다시보기가 준비 중입니다.<br>세션 영상이 준비되는 대로 순차적으로 공개됩니다.</div>
    <?php else: foreach (array('1','2') as $d): if (!$byday[$d]) continue; ?>
      <div class="rv-day"><?= e($DAYS[$d]) ?></div>
      <div class="rv-grid">
        <?php foreach ($byday[$d] as $s): $yt=e($s['rp_yt']); ?>
          <div class="rv-card" onclick="rvPlay('<?= $yt ?>','<?= e(addslashes($s['ag_title'])) ?>')">
            <div class="rv-thumb" style="background-image:url('https://i.ytimg.com/vi/<?= $yt ?>/hqdefault.jpg')">
              <div class="play"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="11" fill="rgba(0,0,0,.45)"/><path d="M10 8.5l6 3.5-6 3.5z" fill="#fff"/></svg></div>
            </div>
            <div class="rv-meta">
              <?php if ($s['ag_track']!==''): ?><div class="rv-trk"><?= e($s['ag_track']) ?></div><?php endif; ?>
              <div class="rv-title"><?= e($s['ag_title']) ?></div>
              <?php if ($s['ag_sp_name']!==''): ?><div class="rv-sp"><?= e($s['ag_sp_name']) ?><?= $s['ag_sp_company']!==''?' · '.e($s['ag_sp_company']):'' ?></div><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="rv-mask" id="rvMask" onclick="if(event.target===this)rvClose()">
    <div class="rv-modal">
      <div class="rv-mhd"><span class="t" id="rvTitle"></span><button type="button" class="rv-mx" onclick="rvClose()" aria-label="닫기">&times;</button></div>
      <div class="rv-frame"><iframe id="rvFrame" src="" title="다시보기" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe></div>
    </div>
  </div>
  <script>
  function rvPlay(id,title){
    document.getElementById('rvTitle').textContent=title||'';
    document.getElementById('rvFrame').src='https://www.youtube.com/embed/'+id+'?rel=0&autoplay=1&modestbranding=1';
    document.getElementById('rvMask').classList.add('on');
  }
  function rvClose(){
    document.getElementById('rvMask').classList.remove('on');
    document.getElementById('rvFrame').src='';
  }
  document.addEventListener('keydown',function(e){ if(e.key==='Escape') rvClose(); });
  </script>
<?php endif; ?>
</body></html>
