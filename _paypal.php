<?php
/* Unreal Fest Seoul 2026 — PayPal (Orders API v2) 헬퍼 (_paypal.php)
 * 외국인(영문) 등록 해외카드 결제. 주문생성 → 승인 → 캡처 → 웹훅검증 → 환불. USD.
 * 시크릿(_secret_paypal.php)에서 키/베이스URL/모드 로드. PHP 7.0 호환.
 */
require_once __DIR__ . '/_secret_paypal.php';

if (!function_exists('ufs_pp_base')) { function ufs_pp_base(){ return defined('UFS_PAYPAL_API_BASE') ? UFS_PAYPAL_API_BASE : 'https://api-m.sandbox.paypal.com'; } }
if (!function_exists('ufs_pp_mode')) { function ufs_pp_mode(){ return defined('UFS_PAYPAL_MODE') ? UFS_PAYPAL_MODE : 'sandbox'; } }

/* 티켓코드 → USD 가격(문자열). */
if (!function_exists('ufs_pp_price')) {
function ufs_pp_price($ticket_code) {
    if ($ticket_code === 'NORMAL_ALL') return defined('UFS_PAYPAL_PRICE_ALL') ? UFS_PAYPAL_PRICE_ALL : '89.00';
    if ($ticket_code === 'NORMAL_20' || $ticket_code === 'NORMAL_21') return defined('UFS_PAYPAL_PRICE_DAY') ? UFS_PAYPAL_PRICE_DAY : '45.00';
    return '';
}}
if (!function_exists('ufs_pp_currency')) { function ufs_pp_currency(){ return defined('UFS_PAYPAL_CURRENCY') ? UFS_PAYPAL_CURRENCY : 'USD'; } }

/* OAuth2 access token (client_credentials). 요청당 1회 캐시. */
if (!function_exists('ufs_pp_token')) {
function ufs_pp_token() {
    static $tok = null;
    if ($tok !== null) return $tok;
    $cid = defined('UFS_PAYPAL_CLIENT_ID') ? UFS_PAYPAL_CLIENT_ID : '';
    $sec = defined('UFS_PAYPAL_SECRET') ? UFS_PAYPAL_SECRET : '';
    if ($cid === '' || $sec === '') return ($tok = '');
    $ch = curl_init(ufs_pp_base() . '/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $cid . ':' . $sec);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $raw = curl_exec($ch); curl_close($ch);
    $j = json_decode((string)$raw, true);
    return ($tok = (isset($j['access_token']) ? $j['access_token'] : ''));
}}

/* 공통 API 호출 (Bearer). 반환: array('ok','code','json','raw') */
if (!function_exists('ufs_pp_api')) {
function ufs_pp_api($method, $path, $body = null) {
    $token = ufs_pp_token();
    if ($token === '') return array('ok'=>false, 'code'=>0, 'json'=>null, 'raw'=>'no token');
    $ch = curl_init(ufs_pp_base() . $path);
    $headers = array('Authorization: Bearer ' . $token, 'Accept: application/json');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $raw = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode((string)$raw, true);
    return array('ok'=>($code>=200 && $code<300), 'code'=>$code, 'json'=>(is_array($json)?$json:null), 'raw'=>(string)$raw);
}}

/* 주문 생성(CAPTURE). 반환: array('ok','order_id','approve_url','msg')
 * custom_id=apply_no 로 웹훅/캡처에서 등록건 식별. */
if (!function_exists('ufs_pp_create_order')) {
function ufs_pp_create_order($ticket_code, $email, $name, $return_url, $cancel_url, $apply_no, $amount_override = null) {
    $price = ($amount_override !== null && $amount_override !== '') ? (string)$amount_override : ufs_pp_price($ticket_code);
    if ($price === '') return array('ok'=>false, 'order_id'=>'', 'approve_url'=>'', 'msg'=>'no price for '.$ticket_code);
    $body = array(
        'intent' => 'CAPTURE',
        'purchase_units' => array(array(
            'custom_id'   => (string)$apply_no,
            'description' => 'Unreal Fest Seoul 2026 ('.$ticket_code.')',
            'amount'      => array('currency_code'=>ufs_pp_currency(), 'value'=>$price),
        )),
        'payment_source' => array('paypal' => array('experience_context' => array(
            'brand_name' => 'Unreal Fest Seoul 2026',
            'shipping_preference' => 'NO_SHIPPING',
            'user_action' => 'PAY_NOW',
            'return_url' => $return_url,
            'cancel_url' => $cancel_url,
        ))),
    );
    $r = ufs_pp_api('POST', '/v2/checkout/orders', $body);
    if ($r['ok'] && isset($r['json']['id'])) {
        $approve = '';
        foreach ((isset($r['json']['links'])?$r['json']['links']:array()) as $lk) {
            if (isset($lk['rel']) && ($lk['rel']==='payer-action' || $lk['rel']==='approve')) { $approve = $lk['href']; break; }
        }
        return array('ok'=>true, 'order_id'=>$r['json']['id'], 'approve_url'=>$approve, 'msg'=>'');
    }
    $msg = isset($r['json']['message']) ? $r['json']['message'] : ('HTTP '.$r['code'].' '.substr($r['raw'],0,200));
    return array('ok'=>false, 'order_id'=>'', 'approve_url'=>'', 'msg'=>$msg);
}}

/* 주문 캡처(결제 확정). 반환: array('ok','status','capture_id','amount','msg') */
if (!function_exists('ufs_pp_capture_order')) {
function ufs_pp_capture_order($order_id) {
    $order_id = preg_replace('/[^A-Za-z0-9\-]/', '', (string)$order_id);
    if ($order_id === '') return array('ok'=>false, 'msg'=>'no order id');
    $r = ufs_pp_api('POST', '/v2/checkout/orders/'.$order_id.'/capture', new stdClass());   // 빈 객체 {} (배열 []이면 schema 오류)
    // 이미 캡처된 주문 재캡처(422) → get 으로 상태 확인
    if (!$r['ok'] && $r['code']===422) {
        $g = ufs_pp_get_order($order_id);
        if ($g && isset($g['status']) && $g['status']==='COMPLETED') $r = array('ok'=>true, 'json'=>$g);
    }
    if (!empty($r['ok']) && isset($r['json'])) {
        $j = $r['json'];
        $status = isset($j['status']) ? $j['status'] : '';
        $cap_id=''; $amt='';
        if (isset($j['purchase_units'][0]['payments']['captures'][0])) {
            $c = $j['purchase_units'][0]['payments']['captures'][0];
            $cap_id = isset($c['id']) ? $c['id'] : '';
            $amt = isset($c['amount']['value']) ? $c['amount']['value'] : '';
            if ($status==='') $status = isset($c['status']) ? $c['status'] : '';
        }
        return array('ok'=>($status==='COMPLETED'), 'status'=>$status, 'capture_id'=>$cap_id, 'amount'=>$amt, 'msg'=>'');
    }
    $msg = isset($r['json']['message']) ? $r['json']['message'] : ('HTTP '.(isset($r['code'])?$r['code']:'?'));
    return array('ok'=>false, 'status'=>'', 'capture_id'=>'', 'amount'=>'', 'msg'=>$msg);
}}

if (!function_exists('ufs_pp_get_order')) {
function ufs_pp_get_order($order_id) {
    $order_id = preg_replace('/[^A-Za-z0-9\-]/', '', (string)$order_id);
    if ($order_id === '') return null;
    $r = ufs_pp_api('GET', '/v2/checkout/orders/'.$order_id, null);
    return $r['ok'] ? $r['json'] : null;
}}

/* 전액 환불. capture_id 기준. 반환 shape = inicis/dodo 호환(ok/already/msg). */
if (!function_exists('ufs_pp_refund')) {
function ufs_pp_refund($capture_id, $reason = '', $apply_no = 0) {
    $capture_id = trim((string)$capture_id);
    if ($capture_id === '') return array('ok'=>false, 'msg'=>'캡처 ID가 없습니다.');
    $body = ($reason !== '') ? array('note_to_payer'=>substr($reason,0,255)) : new stdClass();
    $r = ufs_pp_api('POST', '/v2/payments/captures/'.$capture_id.'/refund', $body);
    if ($apply_no > 0 && function_exists('sql_query')) {
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".(int)$apply_no."','[PAYPAL REFUND cap=".str_replace("'","`",$capture_id)."] HTTP ".$r['code']." ".str_replace("'","`",substr((string)$r['raw'],0,300))."',now())");
    }
    if ($r['ok']) {
        $st = isset($r['json']['status']) ? strtolower($r['json']['status']) : '';
        return array('ok'=>true, 'status'=>$st, 'refund_id'=>(isset($r['json']['id'])?$r['json']['id']:''), 'raw'=>$r['raw']);
    }
    $msg = isset($r['json']['message']) ? $r['json']['message'] : ('HTTP '.$r['code']);
    $already = (stripos((string)$r['raw'],'ALREADY') !== false);
    return array('ok'=>false, 'already'=>$already, 'msg'=>$msg, 'raw'=>$r['raw']);
}}

/* 웹훅 서명검증 — PayPal verify-webhook-signature API. 반환 bool. webhook_id 미설정 시 false. */
if (!function_exists('ufs_pp_verify_webhook')) {
function ufs_pp_verify_webhook($raw_body, $headers) {
    $wid = defined('UFS_PAYPAL_WEBHOOK_ID') ? UFS_PAYPAL_WEBHOOK_ID : '';
    if ($wid === '') return false;
    $h = function($k) use ($headers){ $k=strtoupper($k); return isset($headers[$k]) ? $headers[$k] : ''; };
    $evt = json_decode($raw_body, true);
    if (!is_array($evt)) return false;
    $body = array(
        'auth_algo'         => $h('PAYPAL-AUTH-ALGO'),
        'cert_url'          => $h('PAYPAL-CERT-URL'),
        'transmission_id'   => $h('PAYPAL-TRANSMISSION-ID'),
        'transmission_sig'  => $h('PAYPAL-TRANSMISSION-SIG'),
        'transmission_time' => $h('PAYPAL-TRANSMISSION-TIME'),
        'webhook_id'        => $wid,
        'webhook_event'     => $evt,
    );
    $r = ufs_pp_api('POST', '/v1/notifications/verify-webhook-signature', $body);
    return ($r['ok'] && isset($r['json']['verification_status']) && $r['json']['verification_status']==='SUCCESS');
}}
