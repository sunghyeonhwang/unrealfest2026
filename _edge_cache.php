<?php
/* Unreal Fest Seoul 2026 — 엣지 캐시 헤더 헬퍼 (_edge_cache.php)
 *
 * [엣지 TTL 5분→1시간, 2026-08-17]
 *   원본이 부하에 따라 503 을 낸다 — 8/11 얼리버드 마감 피크에 순방문자 1,061명·요청 38,449건에
 *   503 이 10.57% 였고 5시간 지속됐다. 캐시 HIT 은 원본에 아예 가지 않으므로, 원본 왕복 횟수를
 *   줄이는 것이 가장 직접적인 완화책이다. 5분 TTL 로는 콜로별 방문 간격이 TTL 보다 길어
 *   적중률이 13% 대에 그쳤다. 신선도는 관리자 저장 시 자동 퍼지(_cf_purge.php)가 보장한다.
 *   ⚠️ SFTP 로 파일을 직접 올렸을 때는 관리자 '캐시 비우기'를 눌러야 즉시 반영된다.
 * 공개·비개인화 페이지(schedule/session 등)만 Cloudflare 엣지에 캐시되도록 응답 헤더를 교체한다.
 *
 * ★ 원본 주도(fail-safe) 설계: 이 함수를 호출하지 않는 페이지는 기존 no-cache 헤더 그대로라
 *   CF 캐시룰 표현식이 넓어도 절대 캐시되지 않는다. (ticket/apply/live/myticket/adm 등 보호)
 *
 * 헤더 전송 직전(header_register_callback)에 수행 — 렌더 도중 발생한 setcookie 까지 확실히 제거:
 *   1) Set-Cookie 전량 제거   ← 세션쿠키(PHPSESSID)가 캐시에 섞여 전원 공유되는 사고 방지(필수)
 *   2) gnuboard 기본 no-cache(Pragma/Expires/Cache-Control) 제거 → public 캐시 헤더 부여
 *
 * 캐시 제외 조건(하나라도 해당하면 기존 헤더 유지 = 캐시 안 됨):
 *   - GET 이 아님(POST 등)
 *   - 프리뷰 모드(ufs_is_preview) : 가림세션·연장가격 노출 → _pricing.php 가 이미 no-store 전송
 *   - 관리자 로그인 세션
 * PHP 7.0 호환.
 */
if (!function_exists('ufs_edge_cache')) {
function ufs_edge_cache($edge_ttl = 300, $browser_ttl = 60) {
    if (PHP_SAPI === 'cli') return false;
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') return false;
    if (headers_sent()) return false;
    // 프리뷰 응답은 절대 캐시 금지(공개 화면에 미공개 내용이 박히는 사고 방지)
    if (function_exists('ufs_is_preview') && ufs_is_preview()) return false;
    // 관리자 로그인 상태도 제외(내용은 동일하나 보수적으로)
    if (!empty($GLOBALS['member']['mb_id'])) return false;

    // 세션 락 조기 해제 — 이 페이지들은 $_SESSION 미사용. 동시접속 시 세션파일 락 경합 완화.
    if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) { @session_write_close(); }

    $edge_ttl    = (int)$edge_ttl;
    $browser_ttl = (int)$browser_ttl;

    if (function_exists('header_register_callback')) {
        header_register_callback(function () use ($edge_ttl, $browser_ttl) {
            header_remove('Set-Cookie');
            header_remove('Pragma');
            header_remove('Expires');
            header('Cache-Control: public, max-age=' . $browser_ttl . ', s-maxage=' . $edge_ttl);
            header('CDN-Cache-Control: public, max-age=' . $edge_ttl);
            header('X-UFS-Edge: cacheable');
        });
        return true;
    }

    // 폴백(header_register_callback 미지원): 즉시 교체
    header_remove('Set-Cookie'); header_remove('Pragma'); header_remove('Expires');
    header('Cache-Control: public, max-age=' . $browser_ttl . ', s-maxage=' . $edge_ttl);
    header('CDN-Cache-Control: public, max-age=' . $edge_ttl);
    header('X-UFS-Edge: cacheable-fallback');
    return true;
}
}
