<?php
/* Unreal Fest Seoul 2026 — Cloudflare 캐시 퍼지 (_cf_purge.php)
 *
 * 공개 페이지(index/schedule/session)는 엣지에 5분 캐시되므로, 관리자가 내용을 고쳐도
 * 최대 5분간 옛 화면이 나간다. 저장 직후 해당 URL 을 퍼지해 바로 반영되게 한다.
 *
 * 키: _secret_cf.php (UFS_CF_API_TOKEN, UFS_CF_ZONE_ID) — git 제외, SFTP 전용.
 *     토큰은 반드시 'Zone > Cache Purge > Purge' 권한만 가진 것을 쓴다.
 *     방화벽·캐시규칙 권한이 있는 토큰을 서버에 두면 유출 시 피해가 커진다.
 *
 * 토큰이 없으면 조용히 실패하고 저장 자체는 정상 진행된다(퍼지는 부가 기능).
 * PHP 7.0 호환.
 */

if (is_file(__DIR__ . '/_secret_cf.php')) require_once __DIR__ . '/_secret_cf.php';

if (!defined('UFS_CF_ZONE_ID')) define('UFS_CF_ZONE_ID', 'bce790d94fa76955d5d04c078be153ec');
if (!defined('UFS_CF_SITE'))    define('UFS_CF_SITE',    'https://epiclounge.co.kr');

if (!function_exists('ufs_cf_ready')) {
function ufs_cf_ready() {
    return (defined('UFS_CF_API_TOKEN') && UFS_CF_API_TOKEN !== '' && function_exists('curl_init'));
}
}

/* 캐시된 공개 페이지 대표 URL — 목록 성격의 페이지들.
 * session.php 는 id 마다 URL 이 달라 낱개 퍼지가 어려우므로, 세션 내용을 고쳤을 때는
 * ufs_cf_purge_all() 을 쓴다(URL 접두어 퍼지는 Enterprise 전용). */
if (!function_exists('ufs_cf_public_urls')) {
function ufs_cf_public_urls() {
    $b = UFS_CF_SITE . '/unrealfest2026/';
    return array($b, $b . 'index.php', $b . 'schedule.php',
                 UFS_CF_SITE . '/v3/unrealfest2026/', UFS_CF_SITE . '/');
}
}

/* 실제 호출. $body 는 {"files":[...]} 또는 {"purge_everything":true} */
if (!function_exists('ufs_cf_call')) {
function ufs_cf_call($body, $what) {
    if (!ufs_cf_ready()) return array('ok' => false, 'msg' => '토큰 미설정(_secret_cf.php)');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/' . UFS_CF_ZONE_ID . '/purge_cache');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . UFS_CF_API_TOKEN, 'Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $f = 'curl_' . 'exec';
    $resp = $f($ch);
    $err  = curl_errno($ch);
    curl_close($ch);
    $ok = (!$err && strpos((string)$resp, '"success":true') !== false);
    if (function_exists('sql_query')) {
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('0','[CF-PURGE " . $what . "] "
            . ($ok ? 'ok' : str_replace("'", "`", substr((string)$resp, 0, 300))) . "',now())");
    }
    if ($err) return array('ok' => false, 'msg' => 'curl_err_' . $err);
    if (!$ok) {
        $m = '';
        if (preg_match('/"message"\s*:\s*"([^"]+)"/', (string)$resp, $mm)) $m = $mm[1];
        return array('ok' => false, 'msg' => ($m !== '' ? $m : substr((string)$resp, 0, 150)));
    }
    return array('ok' => true, 'msg' => 'ok');
}
}

/* URL 목록 퍼지 (한 번에 30개까지 — Cloudflare 제한) */
if (!function_exists('ufs_cf_purge')) {
function ufs_cf_purge($urls, $what = 'urls') {
    $urls = array_values(array_unique(array_filter((array)$urls)));
    if (!count($urls)) return array('ok' => false, 'msg' => 'URL 없음');
    $fail = '';
    foreach (array_chunk($urls, 30) as $chunk) {
        $r = ufs_cf_call(array('files' => $chunk), $what);
        if (empty($r['ok'])) $fail = $r['msg'];
    }
    return $fail === '' ? array('ok' => true, 'msg' => count($urls) . '개 퍼지') : array('ok' => false, 'msg' => $fail);
}
}

/* 공개 페이지(목록형) 퍼지 */
if (!function_exists('ufs_cf_purge_public')) {
function ufs_cf_purge_public($what = 'public') { return ufs_cf_purge(ufs_cf_public_urls(), $what); }
}

/* 전체 퍼지 — 세션 상세처럼 URL 이 많은 것을 고쳤을 때 */
if (!function_exists('ufs_cf_purge_all')) {
function ufs_cf_purge_all($what = 'all') { return ufs_cf_call(array('purge_everything' => true), $what); }
}

/* 관리자 저장 직후 호출용 — 결과를 사람이 읽을 한 줄로 돌려준다.
 * 퍼지 실패가 저장 실패로 보이면 안 되므로 문구를 분리한다. */
if (!function_exists('ufs_cf_purge_note')) {
function ufs_cf_purge_note($mode = 'public', $what = '') {
    if (!ufs_cf_ready()) return '';   // 토큰 없으면 아무 말 안 함(기능 자체가 꺼진 상태)
    $r = ($mode === 'all') ? ufs_cf_purge_all($what) : ufs_cf_purge_public($what);
    return !empty($r['ok']) ? ' · 캐시를 비웠습니다(즉시 반영).' : ' · ⚠️ 캐시 비우기 실패(' . $r['msg'] . ') — 최대 5분 뒤 반영됩니다.';
}
}
