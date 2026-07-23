<?php
/* Unreal Fest Seoul 2026 — Resend 웹훅 수신 (_resend_webhook.php) [M6 도달이력]
 * Resend 이벤트(email.delivered/bounced/opened 등)로 cb_unreal_2026_speaker_code.sc_status 갱신.
 * Svix 서명 검증(HTTP_SVIX_ID/TIMESTAMP/SIGNATURE + whsec_ 시크릿). 시크릿 미설정 시 처리 스킵(200).
 * 등록: Resend 대시보드 Webhooks → https://epiclounge.co.kr/unrealfest2026/_resend_webhook.php
 * PHP 7.0. noindex/no-render. sc_msg_id로 매칭.
 */
include_once "../common.php";
require_once __DIR__ . '/_invite_apply.php';                          // ufs_invite_schema + sql_*
if (is_file(__DIR__ . '/_secret_resend.php')) require_once __DIR__ . '/_secret_resend.php';

$body   = file_get_contents('php://input');
$secret = defined('UFS_RESEND_WEBHOOK_SECRET') ? UFS_RESEND_WEBHOOK_SECRET : '';

// ── 서명 검증(Svix) ──
if ($secret === '') { http_response_code(200); echo 'ok (no secret configured)'; exit; } // 설치 확인용 — 처리 안 함

$svix_id  = isset($_SERVER['HTTP_SVIX_ID'])        ? $_SERVER['HTTP_SVIX_ID']        : '';
$svix_ts  = isset($_SERVER['HTTP_SVIX_TIMESTAMP']) ? $_SERVER['HTTP_SVIX_TIMESTAMP'] : '';
$svix_sig = isset($_SERVER['HTTP_SVIX_SIGNATURE']) ? $_SERVER['HTTP_SVIX_SIGNATURE'] : '';
$verified = false;
if ($svix_id !== '' && $svix_ts !== '' && $svix_sig !== '') {
    $us = strpos($secret, '_');
    $key = base64_decode($us !== false ? substr($secret, $us + 1) : $secret);   // whsec_XXXX → XXXX(base64)
    $signed   = $svix_id . '.' . $svix_ts . '.' . $body;
    $expected = base64_encode(hash_hmac('sha256', $signed, $key, true));
    foreach (explode(' ', $svix_sig) as $sig) {                                   // "v1,<b64> v1,<b64>"
        $p = explode(',', $sig, 2);
        if (count($p) === 2 && hash_equals($p[1], $expected)) { $verified = true; break; }
    }
}
if (!$verified) { http_response_code(401); echo 'invalid signature'; exit; }

// ── 이벤트 처리 ──
$evt = json_decode($body, true);
$type = (is_array($evt) && isset($evt['type'])) ? $evt['type'] : '';
$email_id = '';
if (is_array($evt) && isset($evt['data'])) {
    if (isset($evt['data']['email_id'])) $email_id = $evt['data']['email_id'];
    elseif (isset($evt['data']['id']))   $email_id = $evt['data']['id'];
}
$map = array(
    'email.sent'=>'sent', 'email.delivered'=>'delivered', 'email.delivery_delayed'=>'delayed',
    'email.bounced'=>'bounced', 'email.complained'=>'complained',
    'email.opened'=>'opened', 'email.clicked'=>'clicked',
);
if ($email_id !== '' && isset($map[$type])) {
    ufs_invite_schema();
    $st  = $map[$type];
    $eid = sql_real_escape_string($email_id);
    // 상태 우선순위: 진행단계는 뒤로 못 감(늦게 온 delivered가 opened를 덮지 않음). bounced/complained는 최종(항상 반영).
    $rank = array('sent'=>1,'delayed'=>2,'delivered'=>3,'opened'=>4,'clicked'=>5,'bounced'=>9,'complained'=>9);
    $cur = sql_fetch("SELECT sc_status FROM cb_unreal_2026_speaker_code WHERE sc_msg_id='".$eid."' LIMIT 1");
    $curst = $cur ? (string)$cur['sc_status'] : '';
    $apply = true;
    if ($curst !== '' && isset($rank[$curst]) && isset($rank[$st]) && $rank[$curst] < 9 && $rank[$st] < $rank[$curst]) $apply = false;
    if ($apply) sql_query("UPDATE cb_unreal_2026_speaker_code SET sc_status='".$st."', sc_status_at=now() WHERE sc_msg_id='".$eid."'");
}
http_response_code(200);
echo 'ok';
