<?php
/* Unreal Fest Seoul 2026 — Resend 이메일 발송 헬퍼 (_resend.php) [M6]
 * 키: _secret_resend.php(UFS_RESEND_API_KEY, git 제외). 발송 도메인: update.epiclounge.co.kr(인증됨).
 * ufs_resend_send($to, $subject, $html, $from='', $text='') -> array('ok'=>bool, 'id'|'error').
 * PHP 7.0 / curl. 카카오 CAPI(_kakao_capi.php)와 동일 curl 관용구.
 */
if (is_file(__DIR__ . '/_secret_resend.php')) require_once __DIR__ . '/_secret_resend.php';   // UFS_RESEND_API_KEY (없으면 아래 no_api_key로 graceful)

if (!defined('UFS_RESEND_FROM')) define('UFS_RESEND_FROM', 'Unreal Fest Seoul 2026 <noreply@update.epiclounge.co.kr>');
if (!defined('UFS_RESEND_REPLYTO')) define('UFS_RESEND_REPLYTO', 'info@epiclounge.co.kr');

if (!function_exists('ufs_resend_send')) {
function ufs_resend_send($to, $subject, $html, $from = '', $text = '') {
    if (!function_exists('curl_init')) return array('ok'=>false, 'error'=>'no_curl');
    if (!defined('UFS_RESEND_API_KEY') || UFS_RESEND_API_KEY === '') return array('ok'=>false, 'error'=>'no_api_key');
    $to = is_array($to) ? array_values(array_filter($to)) : array($to);
    if (!$to) return array('ok'=>false, 'error'=>'no_recipient');

    $payload = array(
        'from'     => ($from !== '' ? $from : UFS_RESEND_FROM),
        'to'       => $to,
        'subject'  => (string)$subject,
        'html'     => (string)$html,
        'reply_to' => UFS_RESEND_REPLYTO,
    );
    if ($text !== '') $payload['text'] = (string)$text;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);   // Bearer 키 전송 → MITM 방지 위해 TLS 인증서 검증
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . UFS_RESEND_API_KEY,
        'Content-Type: application/json',
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);

    if ($res === false) return array('ok'=>false, 'error'=>'curl: '.$cerr);
    $d = json_decode($res, true);
    if ($code >= 200 && $code < 300 && isset($d['id'])) return array('ok'=>true, 'id'=>$d['id']);
    return array('ok'=>false, 'error'=> (isset($d['message']) ? $d['message'] : ('HTTP '.$code)));
}
}
