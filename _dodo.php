<?php
/* Unreal Fest Seoul 2026 — Dodo Payments (MoR) 헬퍼 (_dodo.php)
 * 외국인(영문) 등록 해외카드 결제. 상품매핑 · 체크아웃 생성 · 웹훅 서명검증(Standard Webhooks).
 * 시크릿(_secret_dodo.php)에서 키/베이스URL/모드 로드. PHP 7.0 호환.
 */
require_once __DIR__ . '/_secret_dodo.php';

if (!function_exists('ufs_dodo_key'))  { function ufs_dodo_key()  { return defined('UFS_DODO_API_KEY') ? UFS_DODO_API_KEY : ''; } }
if (!function_exists('ufs_dodo_base')) { function ufs_dodo_base() { return defined('UFS_DODO_API_BASE') ? UFS_DODO_API_BASE : 'https://test.dodopayments.com'; } }
if (!function_exists('ufs_dodo_mode')) { function ufs_dodo_mode() { return defined('UFS_DODO_MODE') ? UFS_DODO_MODE : 'test'; } }

/* 티켓코드 → Dodo product_id (모드별). live 상품 생성 후 live 배열 채우기. */
if (!function_exists('ufs_dodo_product_id')) {
function ufs_dodo_product_id($ticket_code) {
    $map = array(
        'test' => array(
            'NORMAL_ALL' => 'pdt_0Nk5M8f8lgaRSsSZqOHyK',
            'NORMAL_20'  => 'pdt_0Nk5MFgRC2nrfRzYjf2GC',
            'NORMAL_21'  => 'pdt_0Nk5MFjLiuDNmIdDZmFkM',
        ),
        'live' => array(
            'NORMAL_ALL' => '',   // live 상품 생성 후 채우기
            'NORMAL_20'  => '',
            'NORMAL_21'  => '',
        ),
    );
    $m = ufs_dodo_mode();
    return isset($map[$m][$ticket_code]) ? $map[$m][$ticket_code] : '';
}}

/* 공통 API 호출 (Bearer). 반환: array('ok'=>bool,'code'=>int,'json'=>array|null,'raw'=>string) */
if (!function_exists('ufs_dodo_api')) {
function ufs_dodo_api($method, $path, $body = null) {
    $ch = curl_init(ufs_dodo_base() . $path);
    $headers = array('Authorization: Bearer ' . ufs_dodo_key(), 'Accept: application/json');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $raw  = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode((string)$raw, true);
    return array('ok' => ($code >= 200 && $code < 300), 'code' => $code, 'json' => (is_array($json) ? $json : null), 'raw' => (string)$raw);
}}

/* 체크아웃 세션 생성. 반환: array('ok'=>bool,'url'=>string,'session_id'=>string,'msg'=>string)
 * $meta: 웹훅에서 등록건을 식별하기 위한 key-value (예: array('apply_no'=>'123')). */
if (!function_exists('ufs_dodo_create_checkout')) {
function ufs_dodo_create_checkout($ticket_code, $email, $name, $return_url, $meta = array()) {
    $pid = ufs_dodo_product_id($ticket_code);
    if ($pid === '') return array('ok' => false, 'url' => '', 'session_id' => '', 'msg' => 'no product mapping for ' . $ticket_code);
    $body = array(
        'product_cart' => array(array('product_id' => $pid, 'quantity' => 1)),
        'return_url'   => $return_url,
    );
    if ($email !== '' || $name !== '') {
        $body['customer'] = array('email' => $email, 'name' => ($name !== '' ? $name : $email));
    }
    if (!empty($meta)) {
        $m = array();
        foreach ($meta as $k => $v) $m[(string)$k] = (string)$v;
        $body['metadata'] = $m;
    }
    $r = ufs_dodo_api('POST', '/checkouts', $body);
    if ($r['ok'] && isset($r['json']['checkout_url'])) {
        return array('ok' => true, 'url' => $r['json']['checkout_url'], 'session_id' => (isset($r['json']['session_id']) ? $r['json']['session_id'] : ''), 'msg' => '');
    }
    $msg = isset($r['json']['message']) ? $r['json']['message'] : ('HTTP ' . $r['code'] . ' ' . substr($r['raw'], 0, 200));
    return array('ok' => false, 'url' => '', 'session_id' => '', 'msg' => $msg);
}}

/* 결제 단건 조회 (웹훅 보강/폴링용). GET /payments/{id} */
if (!function_exists('ufs_dodo_get_payment')) {
function ufs_dodo_get_payment($payment_id) {
    $payment_id = preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$payment_id);
    if ($payment_id === '') return null;
    $r = ufs_dodo_api('GET', '/payments/' . $payment_id, null);
    return $r['ok'] ? $r['json'] : null;
}}

/* 전액 환불. POST /refunds {payment_id, reason}. 반환 shape은 ufs_inicis_refund 과 호환(ok/already/msg).
 * Dodo는 30일 이내·원결제수단 환불. test 모드는 sandbox 실호출(실과금 없음). */
if (!function_exists('ufs_dodo_refund')) {
function ufs_dodo_refund($payment_id, $reason = '', $apply_no = 0) {
    $payment_id = trim((string)$payment_id);
    if ($payment_id === '') return array('ok'=>false, 'msg'=>'결제 ID가 없습니다.');
    $body = array('payment_id' => $payment_id);
    if ($reason !== '') $body['reason'] = $reason;
    $r = ufs_dodo_api('POST', '/refunds', $body);
    if ($apply_no > 0 && function_exists('sql_query')) {
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".(int)$apply_no."','[DODO REFUND pid=".str_replace("'","`",$payment_id)."] HTTP ".$r['code']." ".str_replace("'","`",substr((string)$r['raw'],0,300))."',now())");
    }
    if ($r['ok']) {
        $st = isset($r['json']['status']) ? strtolower($r['json']['status']) : '';
        return array('ok'=>true, 'status'=>$st, 'refund_id'=>(isset($r['json']['refund_id'])?$r['json']['refund_id']:''), 'raw'=>$r['raw']);
    }
    $msg = isset($r['json']['message']) ? $r['json']['message'] : ('HTTP '.$r['code']);
    $already = (stripos($msg,'already')!==false || stripos($msg,'refunded')!==false);
    return array('ok'=>false, 'already'=>$already, 'msg'=>$msg, 'raw'=>$r['raw']);
}}

/* Standard Webhooks 서명검증 (HMAC-SHA256). 헤더: webhook-id, webhook-timestamp, webhook-signature.
 * 서명대상 = "{id}.{timestamp}.{body}". 시크릿 whsec_<base64>. 반환 bool. */
if (!function_exists('ufs_dodo_verify_webhook')) {
function ufs_dodo_verify_webhook($raw_body, $wh_id, $wh_ts, $wh_sig) {
    $secret = defined('UFS_DODO_WEBHOOK_SECRET') ? UFS_DODO_WEBHOOK_SECRET : '';
    if ($secret === '' || $wh_id === '' || $wh_ts === '' || $wh_sig === '') return false;
    if (!ctype_digit((string)$wh_ts) || abs(time() - (int)$wh_ts) > 300) return false;   // 타임스탬프 허용오차 5분
    $key = $secret;
    if (strpos($secret, 'whsec_') === 0) {
        $decoded = base64_decode(substr($secret, 6), true);
        if ($decoded !== false) $key = $decoded;   // Svix 형식: base64 디코드한 원시 바이트가 서명키
    }
    $signed   = $wh_id . '.' . $wh_ts . '.' . $raw_body;
    $expected = base64_encode(hash_hmac('sha256', $signed, $key, true));
    foreach (explode(' ', trim($wh_sig)) as $part) {    // "v1,<sig> v1,<sig2>"
        $part = trim($part);
        if ($part === '') continue;
        $sig = (strpos($part, ',') !== false) ? substr($part, strpos($part, ',') + 1) : $part;
        $eq  = function_exists('hash_equals') ? hash_equals($expected, $sig) : ($expected === $sig);
        if ($eq) return true;
    }
    return false;
}}
