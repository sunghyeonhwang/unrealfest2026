<?php
/* 토스 결제 데모 — 승인 처리 (paments/success.php) · PG 심사용
 * 결제위젯 successUrl 복귀: paymentKey/orderId/amount 를 받아 confirm API 로 승인한다.
 * 문서용 공개 테스트 키(샌드박스) — 실제 과금 없음. 심사 종료 후 paments/ 폴더째 삭제. PHP 7.0 호환.
 */
$TOSS_SECRET_KEY = 'test_gsk_docs_OaPz8L5KdmQXkzRz3y47BMw6';   // 토스 문서용 공개 테스트 시크릿 키

function sx_e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/* 승인 API 호출 — HTTP 스트림(POST JSON). 반환: array(http코드, 응답배열|null) */
function sx_confirm($secret, $paymentKey, $orderId, $amount) {
    $body = json_encode(array('paymentKey' => $paymentKey, 'orderId' => $orderId, 'amount' => $amount));
    $ctx = stream_context_create(array('http' => array(
        'method'  => 'POST',
        'header'  => "Authorization: Basic " . base64_encode($secret . ':') . "\r\n" .
                     "Content-Type: application/json\r\n",
        'content' => $body,
        'timeout' => 20,
        'ignore_errors' => true,   // 4xx/5xx 에서도 본문을 받는다
    )));
    $res = @file_get_contents('https://api.tosspayments.com/v1/payments/confirm', false, $ctx);
    $http = 0;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (preg_match('~^HTTP/\S+\s+(\d{3})~', $h, $m)) $http = (int)$m[1];
        }
    }
    return array($http, $res ? json_decode($res, true) : null);
}

$paymentKey = isset($_GET['paymentKey']) ? trim($_GET['paymentKey']) : '';
$orderId    = isset($_GET['orderId'])    ? trim($_GET['orderId'])    : '';
$amount     = isset($_GET['amount'])     ? (int)$_GET['amount']      : 0;

$ok = false; $err = ''; $pay = null;
if ($paymentKey === '' || $orderId === '' || $amount <= 0) {
    $err = '필수 파라미터가 없습니다.';
} else {
    list($http, $pay) = sx_confirm($TOSS_SECRET_KEY, $paymentKey, $orderId, $amount);
    if ($http === 200 && is_array($pay) && isset($pay['status']) && $pay['status'] === 'DONE') {
        $ok = true;
    } else {
        $err = (is_array($pay) && isset($pay['message'])) ? $pay['message'] : '승인에 실패했습니다. (HTTP ' . $http . ')';
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title><?= $ok ? '결제 완료' : '결제 실패' ?> · Unreal Fest Seoul 2026</title>
<style>
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
    background:radial-gradient(900px 500px at 78% -8%,rgba(0,193,213,.12),transparent 60%),#08080a;
    color:#eaeaef;font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;word-break:keep-all;box-sizing:border-box}
  .b{max-width:480px;width:100%}
  .k{font-size:12px;font-weight:800;letter-spacing:.14em;color:#00FFC8;margin-bottom:14px;text-align:center}
  h1{margin:0 0 18px;font-size:23px;font-weight:800;text-align:center}
  table{width:100%;border-collapse:collapse;background:#0e0f14;border:1px solid #27272a}
  td{padding:12px 16px;font-size:13.5px;border-bottom:1px solid #1c1c21}
  td:first-child{color:#71717a;width:38%;font-weight:700}
  tr:last-child td{border-bottom:0}
  .err{background:#0e0f14;border:1px solid #27272a;padding:18px;font-size:14px;color:#f4a6a6;line-height:1.7}
  a.home{display:block;margin-top:24px;background:#00C1D5;color:#06060a;text-decoration:none;text-align:center;
    padding:13px;border-radius:8px;font-weight:800;font-size:14.5px}
  .s{margin-top:18px;font-size:12px;color:#6e6e80;text-align:center;line-height:1.8}
</style>
</head>
<body>
<div class="b">
  <div class="k">UNREAL FEST SEOUL 2026</div>
<?php if ($ok): ?>
  <h1>결제가 완료되었습니다 ✓</h1>
  <table>
    <tr><td>주문번호</td><td><?= sx_e($pay['orderId']) ?></td></tr>
    <tr><td>상품명</td><td><?= sx_e(isset($pay['orderName']) ? $pay['orderName'] : '') ?></td></tr>
    <tr><td>결제수단</td><td><?= sx_e(isset($pay['method']) ? $pay['method'] : '카드') ?></td></tr>
    <tr><td>결제금액</td><td>₩<?= number_format((int)$pay['totalAmount']) ?></td></tr>
    <tr><td>승인시각</td><td><?= sx_e(isset($pay['approvedAt']) ? str_replace('T', ' ', substr($pay['approvedAt'], 0, 19)) : '') ?></td></tr>
  </table>
<?php else: ?>
  <h1>결제에 실패했습니다</h1>
  <div class="err"><?= sx_e($err) ?></div>
<?php endif; ?>
  <a class="home" href="/v3/unrealfest2026/paments/index.php">행사 홈으로</a>
  <div class="s">본 결제는 토스페이먼츠 테스트 환경에서 처리되어 실제로 과금되지 않습니다.</div>
</div>
</body>
</html>
