<?php
/* 토스 결제 데모 — 실패 복귀 (paments/fail.php) · PG 심사용. PHP 7.0 호환. */
function fx_e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$code = isset($_GET['code']) ? $_GET['code'] : '';
$msg  = isset($_GET['message']) ? $_GET['message'] : '결제가 취소되었거나 실패했습니다.';
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title>결제 실패 · Unreal Fest Seoul 2026</title>
<style>
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
    background:radial-gradient(900px 500px at 78% -8%,rgba(0,193,213,.12),transparent 60%),#08080a;
    color:#eaeaef;font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;word-break:keep-all;box-sizing:border-box}
  .b{max-width:460px;text-align:center}
  .k{font-size:12px;font-weight:800;letter-spacing:.14em;color:#00FFC8;margin-bottom:14px}
  h1{margin:0 0 14px;font-size:23px;font-weight:800}
  p{margin:0;font-size:14.5px;line-height:1.85;color:#a9a9b8}
  .c{color:#6e6e80;font-size:12.5px;margin-top:10px}
  a{display:inline-block;margin-top:28px;background:#00C1D5;color:#06060a;text-decoration:none;padding:13px 26px;border-radius:8px;font-weight:800;font-size:14.5px}
</style>
</head>
<body>
<div class="b">
  <div class="k">UNREAL FEST SEOUL 2026</div>
  <h1>결제에 실패했습니다</h1>
  <p><?= fx_e($msg) ?></p>
  <?php if ($code !== ''): ?><p class="c">오류 코드: <?= fx_e($code) ?></p><?php endif; ?>
  <a href="/v3/unrealfest2026/paments/index.php">행사 홈으로</a>
</div>
</body>
</html>
