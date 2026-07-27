<?php
/* Unreal Fest Seoul 2026 — Dodo Payments 웹훅 수신 (dodo_webhook.php)
 * 결제완료(payment.succeeded) 이벤트 → 서명검증(Standard Webhooks) → 등록 확정(_dodo_apply).
 * 엔드포인트 URL(Dodo 대시보드 Webhooks에 등록): https://epiclounge.co.kr/unrealfest2026/dodo_webhook.php
 * 응답은 항상 신속히 2xx. 미검증/미해당 이벤트는 무시. PHP 7.0.
 */
require __DIR__ . '/_ticket_init.php';          // common(DB), 출력 없음
require_once __DIR__ . '/_dodo.php';            // ufs_dodo_verify_webhook
require_once __DIR__ . '/_dodo_apply.php';      // ufs_dodo_finalize_apply

$raw = file_get_contents('php://input');
$wh_id  = isset($_SERVER['HTTP_WEBHOOK_ID']) ? $_SERVER['HTTP_WEBHOOK_ID'] : '';
$wh_ts  = isset($_SERVER['HTTP_WEBHOOK_TIMESTAMP']) ? $_SERVER['HTTP_WEBHOOK_TIMESTAMP'] : '';
$wh_sig = isset($_SERVER['HTTP_WEBHOOK_SIGNATURE']) ? $_SERVER['HTTP_WEBHOOK_SIGNATURE'] : '';

// 로그(진단용, 짧게) — 서버 파일에 append. 민감정보 최소.
$log = function($m) {
    @file_put_contents(__DIR__.'/dodo_webhook.log', date('Y-m-d H:i:s').' '.$m."\n", FILE_APPEND);
};

$secret_set = (defined('UFS_DODO_WEBHOOK_SECRET') && UFS_DODO_WEBHOOK_SECRET !== '');
if ($secret_set) {
    if (!ufs_dodo_verify_webhook($raw, $wh_id, $wh_ts, $wh_sig)) {
        http_response_code(401);
        $log('SIG_FAIL id='.$wh_id);
        echo 'invalid signature';
        exit;
    }
} else {
    // 시크릿 미설정 → 검증 불가. 확정은 완료페이지(API 재확인)에서 처리하므로 여기선 수신만 하고 무시.
    http_response_code(200);
    $log('NO_SECRET (skipped) id='.$wh_id);
    echo 'ok (unverified skipped)';
    exit;
}

$data = json_decode($raw, true);
$type = '';
if (is_array($data)) {
    $type = isset($data['type']) ? $data['type'] : (isset($data['event_type']) ? $data['event_type'] : '');
}
$payload = (is_array($data) && isset($data['data']) && is_array($data['data'])) ? $data['data'] : (is_array($data) ? $data : array());

// 결제 성공 이벤트만 확정
if (stripos($type, 'succeeded') !== false || stripos($type, 'payment.completed') !== false) {
    $meta = (isset($payload['metadata']) && is_array($payload['metadata'])) ? $payload['metadata'] : array();
    $apply_no = isset($meta['apply_no']) ? (int)$meta['apply_no'] : 0;
    $payment_id = isset($payload['payment_id']) ? $payload['payment_id'] : (isset($payload['id']) ? $payload['id'] : '');
    $amount = isset($payload['total_amount']) ? $payload['total_amount'] : (isset($payload['amount']) ? $payload['amount'] : null);
    if ($apply_no > 0) {
        $r = ufs_dodo_finalize_apply($apply_no, $payment_id, $amount);
        $log('FINALIZE apply_no='.$apply_no.' pid='.$payment_id.' -> '.(!empty($r['ok'])?(!empty($r['already'])?'already':'ok'):('fail:'.(isset($r['msg'])?$r['msg']:''))));
    } else {
        $log('NO_APPLY_NO type='.$type);
    }
}

http_response_code(200);
echo 'ok';
