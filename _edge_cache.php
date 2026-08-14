<?php
/* Unreal Fest Seoul 2026 — 엣지 캐시 헤더 헬퍼 (_edge_cache.php)
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
