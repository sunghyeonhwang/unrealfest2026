<?php
/* Unreal Fest Seoul 2026 — PayPal 웹훅 수신 (paypal_webhook.php)
 * PAYMENT.CAPTURE.COMPLETED → 서명검증 → 등록 확정(백업). 완료는 원칙적으로 return 캡처에서 처리됨.
 * 엔드포인트(대시보드 Webhooks 등록): https://epiclounge.co.kr/unrealfest2026/paypal_webhook.php
 * 항상 신속히 2xx. PHP 7.0.
 */
require __DIR__ . '/_ticket_init.php';          // common(DB), 출력 없음
require_once __DIR__ . '/_paypal.php';          // ufs_pp_verify_webhook
require_once __DIR__ . '/_paypal_apply.php';    // ufs_paypal_finalize_apply

$raw = file_get_contents('php://input');
$headers = array();
foreach ($_SERVER as $k=>$v) {
    if (strpos($k, 'HTTP_') === 0) $headers[str_replace('_','-',substr($k,5))] = $v;   // HTTP_PAYPAL_TRANSMISSION_ID → PAYPAL-TRANSMISSION-ID
}
$log = function($m){ @file_put_contents(__DIR__.'/paypal_webhook.log', date('Y-m-d H:i:s').' '.$m."\n", FILE_APPEND); };

$secret_set = (defined('UFS_PAYPAL_WEBHOOK_ID') && UFS_PAYPAL_WEBHOOK_ID !== '');
if ($secret_set) {
    if (!ufs_pp_verify_webhook($raw, $headers)) {
        http_response_code(401);
        $log('SIG_FAIL');
        echo 'invalid signature';
        exit;
    }
} else {
    // Webhook ID 미설정 → 검증 불가. 확정은 return 캡처에서 하므로 수신만 하고 무시.
    http_response_code(200);
    $log('NO_WEBHOOK_ID (skipped)');
    echo 'ok (unverified skipped)';
    exit;
}

$evt = json_decode($raw, true);
$type = (is_array($evt) && isset($evt['event_type'])) ? $evt['event_type'] : '';
$res  = (is_array($evt) && isset($evt['resource']) && is_array($evt['resource'])) ? $evt['resource'] : array();

if ($type === 'PAYMENT.CAPTURE.COMPLETED') {
    $capture_id = isset($res['id']) ? $res['id'] : '';
    $apply_no   = isset($res['custom_id']) ? (int)$res['custom_id'] : 0;   // 주문 생성 시 custom_id=apply_no
    $usd        = isset($res['amount']['value']) ? $res['amount']['value'] : '';
    if ($apply_no > 0) {
        $r = ufs_paypal_finalize_apply($apply_no, $capture_id, $usd);
        $log('FINALIZE apply_no='.$apply_no.' cap='.$capture_id.' -> '.(!empty($r['ok'])?(!empty($r['already'])?'already':'ok'):('fail:'.(isset($r['msg'])?$r['msg']:''))));
    } else {
        $log('NO_APPLY_NO type='.$type);
    }
}

http_response_code(200);
echo 'ok';
