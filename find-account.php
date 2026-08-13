<?php
/* Unreal Fest Seoul 2026 — 등록정보(이메일/전화) 찾기 (find-account.php)
 * live.php 게이트에서 진입. 공개 셀프조회. 이름+(전화 또는 이메일) → 나머지 안내.
 * 확정 등록(apply_temp_yn='N' AND apply_pay_status<>0)만 대상. noindex. PHP 7.0 호환.
 */
include_once "../common.php";
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
function fp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }

$mode = (isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'phone') ? 'phone' : 'email';
$err = ''; $result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = fp('f_name');
    $ne = sql_real_escape_string($name);
    if ($mode === 'email') {
        // 이름 + 전화 → 이메일
        $phone = preg_replace('/[^0-9]/', '', fp('f_phone'));
        if ($name === '' || $phone === '') {
            $err = '이름과 전화번호를 모두 입력해 주세요.';
        } else {
            $pe = sql_real_escape_string($phone);
            $result = sql_fetch(
                "SELECT apply_user_name nm, apply_user_email em, apply_user_phone ph, apply_product_name pn, apply_reg_datetime dt
                 FROM cb_unreal_2026_event2_apply
                 WHERE TRIM(apply_user_name)='$ne'
                   AND REPLACE(REPLACE(apply_user_phone,'-',''),' ','')='$pe'
                   AND apply_temp_yn='N' AND apply_pay_status<>0
                 ORDER BY apply_no DESC LIMIT 1");
            if (!$result) $err = '일치하는 등록 정보를 찾을 수 없습니다. 이름과 전화번호를 확인해 주세요.';
        }
    } else {
        // 이름 + 이메일 → 전화
        $email = fp('f_email');
        if ($name === '' || $email === '') {
            $err = '이름과 이메일을 모두 입력해 주세요.';
        } else {
            $ee = sql_real_escape_string($email);
            $result = sql_fetch(
                "SELECT apply_user_name nm, apply_user_email em, apply_user_phone ph, apply_product_name pn, apply_reg_datetime dt
                 FROM cb_unreal_2026_event2_apply
                 WHERE TRIM(apply_user_name)='$ne' AND apply_user_email='$ee'
                   AND apply_temp_yn='N' AND apply_pay_status<>0
                 ORDER BY apply_no DESC LIMIT 1");
            if (!$result) $err = '일치하는 등록 정보를 찾을 수 없습니다. 이름과 이메일을 확인해 주세요.';
        }
    }
}

// 전화 표기(11자리면 하이픈)
function fa_phone($p){ $d = preg_replace('/[^0-9]/','',(string)$p);
    if (strlen($d)===11) return substr($d,0,3).'-'.substr($d,3,4).'-'.substr($d,7);
    if (strlen($d)===10) return substr($d,0,3).'-'.substr($d,3,3).'-'.substr($d,6);
    return $p; }
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>등록정보 찾기 — Unreal Fest Seoul 2026</title>
<style>
:root{--bg:#08080a;--panel:#0e0e12;--line:#1e1e25;--line2:#2a2a33;--teal:#00C1D5;--text:#eaeaef;--muted:#8b8b96}
*{box-sizing:border-box}html,body{margin:0}
body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;
  -webkit-font-smoothing:antialiased;word-break:keep-all;min-height:100vh;
  background-image:radial-gradient(900px 500px at 78% -8%,rgba(0,193,213,.10),transparent 60%),radial-gradient(700px 400px at 0% 0%,rgba(48,127,226,.06),transparent 55%)}
a{color:inherit;text-decoration:none}
.fa-top{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px clamp(16px,4vw,40px);border-bottom:1px solid var(--line);background:rgba(8,8,10,.72)}
.fa-top img{height:24px;width:auto;display:block}
.fa-back{padding:6px 13px;border:1px solid var(--line2);font-size:12px;color:var(--muted);transition:.15s}
.fa-back:hover{border-color:var(--teal);color:var(--teal)}
.fa-wrap{min-height:calc(100vh - 54px);display:flex;align-items:center;justify-content:center;padding:24px}
.fa-card{width:100%;max-width:440px;background:linear-gradient(180deg,rgba(255,255,255,.03),transparent),var(--panel);border:1px solid var(--line);padding:34px 30px;box-shadow:0 40px 90px -40px rgba(0,0,0,.8)}
.fa-card h1{font-size:22px;font-weight:900;color:#fff;margin:0 0 6px;letter-spacing:-.01em}
.fa-card .lead{font-size:13.5px;color:var(--muted);margin:0 0 20px;line-height:1.7}
.fa-tabs{display:flex;gap:0;margin-bottom:20px;border:1px solid var(--line2)}
.fa-tabs a{flex:1;text-align:center;padding:11px 8px;font-size:13.5px;font-weight:700;color:var(--muted);transition:.15s}
.fa-tabs a.on{background:var(--teal);color:#00232a}
.fa-card label.fl{display:block;font-size:12px;color:var(--muted);margin:0 0 6px;font-weight:600}
.fa-card input{width:100%;padding:14px 16px;background:#08080b;border:1px solid var(--line2);color:#fff;font-size:15px;margin-bottom:14px;transition:.15s}
.fa-card input:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,193,213,.12)}
.fa-card button{width:100%;padding:15px;background:var(--teal);color:#00232a;border:0;font-size:15px;font-weight:900;cursor:pointer;transition:.15s}
.fa-card button:hover{filter:brightness(1.06)}
.fa-err{color:#ff8a8a;font-size:13px;margin-bottom:14px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);padding:10px 12px;line-height:1.6}
.fa-res{border:1px solid var(--teal);background:rgba(0,193,213,.06);padding:20px;margin-bottom:18px}
.fa-res .rk{font-size:12px;color:var(--muted);margin-bottom:4px}
.fa-res .rv{font-size:20px;font-weight:900;color:#fff;word-break:break-all;letter-spacing:.01em}
.fa-res .rmeta{margin-top:14px;padding-top:12px;border-top:1px solid var(--line2);font-size:12.5px;color:var(--muted);line-height:1.8}
.fa-res .rmeta b{color:var(--text);font-weight:700}
.fa-cta{display:block;width:100%;text-align:center;padding:14px;background:var(--teal);color:#00232a;font-size:15px;font-weight:900;margin-bottom:10px}
.fa-note{font-size:11.5px;color:#6b6b76;margin:16px 0 0;line-height:1.7}
</style>
</head>
<body>
<header class="fa-top">
  <a href="index.php" aria-label="Unreal Fest Seoul 2026"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026"></a>
  <a class="fa-back" href="live.php">← 라이브 시청</a>
</header>

<div class="fa-wrap">
  <div class="fa-card">
    <?php if ($result): ?>
      <h1>등록 정보를 찾았습니다</h1>
      <p class="lead"><b style="color:#cfd0d6"><?= e($result['nm']) ?></b>님의 등록 정보입니다.</p>
      <div class="fa-res">
        <?php if ($mode === 'email'): ?>
          <div class="rk">등록 이메일</div>
          <div class="rv"><?= e($result['em']) ?></div>
        <?php else: ?>
          <div class="rk">등록 전화번호</div>
          <div class="rv"><?= e(fa_phone($result['ph'])) ?></div>
        <?php endif; ?>
        <div class="rmeta">
          티켓: <b><?= e($result['pn'] !== '' ? $result['pn'] : '-') ?></b><br>
          이메일: <b><?= e($result['em']) ?></b> · 전화: <b><?= e(fa_phone($result['ph'])) ?></b>
        </div>
      </div>
      <a class="fa-cta" href="live.php">이 정보로 라이브 시청하기 →</a>
      <a class="fa-cta" style="background:transparent;color:var(--muted);border:1px solid var(--line2)" href="find-account.php?mode=<?= $mode ?>">다시 찾기</a>
    <?php else: ?>
      <h1>등록정보 찾기</h1>
      <p class="lead">등록 시 입력한 <b style="color:#cfd0d6">이름</b>과 <b style="color:#cfd0d6"><?= $mode==='email'?'전화번호':'이메일' ?></b>를 입력하시면 <?= $mode==='email'?'등록 이메일':'등록 전화번호' ?>을(를) 안내해 드립니다.</p>
      <div class="fa-tabs">
        <a href="find-account.php?mode=email" class="<?= $mode==='email'?'on':'' ?>">이메일 찾기</a>
        <a href="find-account.php?mode=phone" class="<?= $mode==='phone'?'on':'' ?>">전화번호 찾기</a>
      </div>
      <?php if ($err): ?><div class="fa-err"><?= e($err) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="mode" value="<?= e($mode) ?>">
        <label class="fl">이름</label>
        <input type="text" name="f_name" value="<?= e(fp('f_name')) ?>" placeholder="등록 시 이름" required autofocus>
        <?php if ($mode === 'email'): ?>
          <label class="fl">전화번호</label>
          <input type="text" name="f_phone" inputmode="numeric" value="<?= e(fp('f_phone')) ?>" placeholder="01012345678" required>
        <?php else: ?>
          <label class="fl">이메일</label>
          <input type="email" name="f_email" autocapitalize="off" value="<?= e(fp('f_email')) ?>" placeholder="등록 시 이메일" required>
        <?php endif; ?>
        <button type="submit">찾기</button>
      </form>
      <p class="fa-note">등록 정보가 조회되지 않으면 사무국으로 문의해 주세요.<br>info@epiclounge.co.kr</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
