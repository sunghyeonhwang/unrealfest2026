<?php
/* Unreal Fest Seoul 2026 — 뉴스레터 수신거부 (newsletter_unsub.php)
 *
 * 뉴스레터 본문의 {{UNSUBSCRIBE_URL}} 자리에 들어가는 주소다.
 * 주소마다 서명(s)이 달라 남의 주소를 대신 해지할 수 없다.
 *
 *  GET  — 해지 처리 후 안내 화면
 *  POST — 지메일·야후의 원클릭 수신거부(List-Unsubscribe-Post). 화면 없이 200만 응답.
 *
 * 해지된 주소는 cb_unreal_2026_mail_optout 에 남고, 이후 뉴스레터 대상에서 빠진다.
 * (결제·QR 등 거래 안내 메일은 여기 영향을 받지 않는다)
 */
include_once "../common.php";
require_once __DIR__ . '/_live_notify.php';

$email = isset($_REQUEST['e']) ? trim($_REQUEST['e']) : '';
$sig   = isset($_REQUEST['s']) ? trim($_REQUEST['s']) : '';
$ok    = ($email !== '' && $sig !== '' && hash_equals(ufs_ln_unsub_sig($email), $sig));

if ($ok) {
    ufs_ln_optout_table();
    $e  = sql_real_escape_string(strtolower($email));
    $ip = sql_real_escape_string(substr(isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''), 0, 45));
    @sql_query("INSERT INTO cb_unreal_2026_mail_optout (mo_email, mo_at, mo_ip) VALUES ('$e', now(), '$ip')
                ON DUPLICATE KEY UPDATE mo_at = now(), mo_ip = '$ip'");
}

// 원클릭 수신거부는 화면이 필요 없다
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code($ok ? 200 : 400);
    header('Content-Type: text/plain; charset=utf-8');
    echo $ok ? 'unsubscribed' : 'invalid';
    exit;
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
$mask = '';
if ($email !== '' && strpos($email, '@') !== false) {
    list($lp, $dm) = explode('@', $email, 2);
    $mask = substr($lp, 0, 2) . str_repeat('*', max(1, strlen($lp) - 2)) . '@' . $dm;
}
?><!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>뉴스레터 수신거부 · Unreal Fest Seoul 2026</title>
<style>
  body{margin:0;background:#0b0b0f;color:#e8e8ef;font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif;
       display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;box-sizing:border-box}
  .b{max-width:460px;width:100%;background:#14141b;border-radius:16px;padding:40px 32px;text-align:center}
  .k{font-size:12px;font-weight:700;letter-spacing:.14em;color:#00FFC8;margin-bottom:14px}
  h1{margin:0 0 14px;font-size:22px;font-weight:700;word-break:keep-all;line-height:1.4}
  p{margin:0;font-size:14.5px;line-height:1.8;color:#b9b9c6;word-break:keep-all}
  .em{display:inline-block;margin-top:16px;padding:8px 14px;background:#1d1d27;border-radius:8px;font-size:13px;color:#8a8a9c}
  a.h{display:inline-block;margin-top:26px;color:#00FFC8;font-size:13.5px;text-decoration:none;font-weight:700}
</style>
</head>
<body>
  <div class="b">
    <div class="k">UNREAL FEST SEOUL 2026</div>
<?php if ($ok): ?>
    <h1>수신거부가 완료되었습니다</h1>
    <p>앞으로 언리얼 페스트 뉴스레터를 보내지 않습니다.<br>그동안 함께해 주셔서 감사합니다.</p>
    <?php if ($mask !== ''): ?><div class="em"><?= htmlspecialchars($mask, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <p style="margin-top:22px;font-size:12.5px;color:#6e6e80">등록 확인·결제 영수증 등 <b>신청하신 내용에 대한 안내</b>는 계속 발송됩니다.</p>
<?php else: ?>
    <h1>처리하지 못했습니다</h1>
    <p>수신거부 주소가 올바르지 않거나 만료되었습니다.<br>
       메일의 수신거부 링크를 다시 눌러 주세요.</p>
    <p style="margin-top:18px;font-size:12.5px;color:#6e6e80">계속 문제가 있으면 <a href="mailto:info@epiclounge.co.kr" style="color:#8a8a9c">info@epiclounge.co.kr</a> 로 알려 주세요.</p>
<?php endif; ?>
    <a class="h" href="https://epiclounge.co.kr/unrealfest2026/">언리얼 페스트 서울 2026 →</a>
  </div>
</body>
</html>
