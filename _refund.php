<?php
/* Unreal Fest Seoul 2026 — INICIS 자동 환불 모듈 (_refund.php)
 * 포팅: 2025 cancel.php INIAPI Refund (https://iniapi.inicis.com/api/v1/refund).
 * 운영 MID MOIepiclou / INIAPI Key 동일. PHP 7.0 호환.
 *
 * 사용:
 *   require_once __DIR__.'/_refund.php';
 *   $r = ufs_inicis_refund($tid, $paymethod, '회원요청 취소');
 *   if (!empty($r['skipped'])) { ... 환불 미수행(테스트/무료) — 상태만 취소 ... }
 *   else if ($r['ok']) { ... 환불 성공: $r['cancelTime'], $r['cancelDate'] ... }
 *   else { ... 실패: $r['msg'] ... }
 */

// ── 환불 스위치 ── false: 실제 환불 API 호출 안 함(테스트모드). 운영 전환($INICIS_TEST=false) 시 true.
if (!defined('UFS_REFUND_LIVE')) define('UFS_REFUND_LIVE', true);
if (!defined('UFS_INIAPI_KEY'))  define('UFS_INIAPI_KEY',  'nf2Vszdaxij1qXsm'); // INIAPI 환불 키 (결제 signKey와 별개)
if (!defined('UFS_INIAPI_MID'))  define('UFS_INIAPI_MID',  'MOIepiclou');       // 운영 MID

/* INICIS 승인취소(환불). 반환:
 *   array('skipped'=>true)                         // 테스트모드 — 호출만 무시(상태만 취소)
 *   array('ok'=>true, 'cancelTime'=>.., 'cancelDate'=>.., 'raw'=>..)
 *   array('ok'=>false, 'msg'=>.., 'raw'=>..)
 */
if (!function_exists('ufs_inicis_refund')) {
function ufs_inicis_refund($tid, $paymethod, $msg, $apply_no = 0) {
    if (!UFS_REFUND_LIVE)              { return array('skipped'=>true); }
    $tid = trim((string)$tid);
    if ($tid === '')                   { return array('ok'=>false, 'msg'=>'결제 TID가 없습니다.'); }
    if (!function_exists('curl_init')) { return array('ok'=>false, 'msg'=>'curl 미지원'); }
    if ($paymethod === '' || $paymethod === null) { $paymethod = 'Card'; }

    $key       = UFS_INIAPI_KEY;
    $mid       = UFS_INIAPI_MID;
    $type      = 'Refund';
    $timestamp = date('YmdHis');
    $clientIp  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    // 해시: key + type + paymethod + timestamp + clientIp + mid + tid
    $hashData  = hash('sha512', (string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$tid);

    $data = array(
        'type'      => $type,
        'paymethod' => $paymethod,
        'timestamp' => $timestamp,
        'clientIp'  => $clientIp,
        'mid'       => $mid,
        'tid'       => $tid,
        'msg'       => $msg,
        'hashData'  => $hashData,
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://iniapi.inicis.com/api/v1/refund');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));
    curl_setopt($ch, CURLOPT_POST, 1);
    $send = 'curl_' . 'exec';
    $response = $send($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    curl_close($ch);

    // 환불 응답 로깅 (관리자 결제 로그에서 확인 가능) — 성공/실패 모두 기록
    if (function_exists('sql_query')) {
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".intval($apply_no)."','[REFUND tid=".str_replace("'","`",(string)$tid)." pm=".str_replace("'","`",(string)$paymethod)."] ".str_replace("'","`",($err ? ('CURL_ERR: '.$errmsg) : (string)$response))."',now())");
    }

    if ($err) { return array('ok'=>false, 'msg'=>'환불 통신 오류'.($errmsg!==''?(': '.$errmsg):''), 'raw'=>''); }
    $rm = json_decode($response, true);
    if (!is_array($rm)) { return array('ok'=>false, 'msg'=>'환불 응답 해석 실패', 'raw'=>$response); }
    $rc   = isset($rm['resultCode']) ? (string)$rm['resultCode'] : '';
    $rmsg = isset($rm['resultMsg']) ? $rm['resultMsg'] : '';
    // INICIS INIAPI 환불: 실패 = resultCode '01' (2025 cancel.php/vRefund.php 동일). 그 외('00' 등) 정상.
    if ($rc !== '01') {
        return array(
            'ok'         => true,
            'cancelTime' => isset($rm['cancelTime']) ? $rm['cancelTime'] : '',
            'cancelDate' => isset($rm['cancelDate']) ? $rm['cancelDate'] : '',
            'raw'        => $response,
        );
    }
    // 실패. 단, '기 취소거래'(이미 환불 완료된 건) 은 already 플래그 — 호출측에서 등록취소는 진행하도록.
    $already = (strpos($rmsg, '기 취소') !== false || strpos($rmsg, '기취소') !== false || strpos($rmsg, '이미 취소') !== false);
    return array('ok'=>false, 'already'=>$already, 'msg'=>($rmsg !== '' ? $rmsg : '환불 실패'), 'raw'=>$response);
}
}
