<?php
/* Unreal Fest Seoul 2026 — 가격/얼리버드 단일 소스 (_pricing.php)
 * 얼리버드와 정가를 날짜 기준 자동 전환. 환불 안내도 동일 기준.
 * require 처: apply_pay.php(실결제 금액) / _ticket_init.php(등록폼·myticket·sidebar) / index.php(표시가).
 * ⚠️ 가격 변경은 ufs_price_table() 한 곳만 수정. PHP 7.0 호환.
 *
 * ───────── 얼리버드 연장(전체 세션 공개 기념) 전환 ─────────
 * 연장 시 "전체 세션 공개 기념 얼리버드 연장"(마감 7/27) 상태. 관리자 페이지로 on/off 제어.
 *   - ufs_extend_mode() : 관리자 설정값. 'off'(기본)=비활성 · 'sched'=자정 자동 · 'on'=즉시.
 *       저장 위치: cb_unreal_2026_config[eb_extend] (adm/2026_earlybird_config.php).
 *   - ?ufs_preview=KEY  : 라이브에 영향 없이 "연장 후 화면"을 실제 URL로 미리보기(쿠키로 이동 유지, no-store/noindex).
 *   - ufs_extended()    : 연장 상태 여부(프리뷰이거나, 관리자 설정에 따라).
 * off 인 동안 일반 방문자에게는 어떤 흔적도 노출되지 않는다(마케팅 필수 요건). CF는 이 HTML을 캐시하지 않음(DYNAMIC). */
// 연장 모드 — 관리자 페이지(adm/2026_earlybird_config.php)가 cb_unreal_2026_config[eb_extend] 에 저장.
if (!function_exists('ufs_extend_mode')) {
function ufs_extend_mode() {                                          // 'off' | 'sched' | 'on'
    static $m = null;
    if ($m !== null) return $m;
    $m = 'off';
    if (function_exists('sql_fetch')) {
        $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='eb_extend'");
        if ($r && isset($r['cfg_val'])) {
            $v = trim((string)$r['cfg_val']);
            if ($v === 'on' || $v === 'sched' || $v === 'off') $m = $v;
        }
    }
    return $m;
}
}
if (!function_exists('ufs_extend_start_ts')) {
function ufs_extend_start_ts() { return strtotime('2026-07-14 00:00:00 +0900'); }  // 'sched' 자동전환 시각(KST 고정)
}
if (!function_exists('ufs_preview_key')) {
function ufs_preview_key() { return 'ufseb2707x9f3a'; }               // 프리뷰 시크릿(비공개 링크용)
}
if (!function_exists('ufs_is_preview')) {
function ufs_is_preview() {                                           // 프리뷰 모드 여부(파라미터/쿠키)
    $key = ufs_preview_key();
    if (isset($_GET['ufs_preview'])) {
        $v = $_GET['ufs_preview'];
        if ($v === $key)  { if (!headers_sent()) setcookie('ufs_preview', $key, 0, '/'); return true; }
        if ($v === 'off') { if (!headers_sent()) setcookie('ufs_preview', '', time() - 3600, '/'); return false; }
    }
    return (isset($_COOKIE['ufs_preview']) && $_COOKIE['ufs_preview'] === $key);
}
}
if (!function_exists('ufs_extended')) {
function ufs_extended() {                                             // 연장 상태(프리뷰 or 관리자 설정)
    if (ufs_is_preview()) return true;
    $m = ufs_extend_mode();
    if ($m === 'on')    return true;                                 // 즉시 활성
    if ($m === 'sched') return (time() >= ufs_extend_start_ts());    // 자정 자동
    return false;                                                    // off
}
}
if (!defined('UFS_PROMO_BOOT')) {                                     // 프리뷰 응답은 캐시/색인 금지(누수 방지)
    define('UFS_PROMO_BOOT', 1);
    if (function_exists('ufs_is_preview') && ufs_is_preview() && !headers_sent()) {
        header('Cache-Control: no-store, private');
        header('X-Robots-Tag: noindex, nofollow');
    }
}
if (!function_exists('ufs_is_earlybird')) {
function ufs_is_earlybird() { return time() <= ufs_earlybird_end_ts(); }
}
if (!function_exists('ufs_earlybird_end_ts')) {
function ufs_earlybird_end_ts() {                                    // 얼리버드 마감 시각: 연장 시 7/27, 아니면 7/13
    return ufs_extended()
        ? strtotime('2026-07-27 23:59:59 +0900')
        : strtotime('2026-07-13 23:59:59 +0900');
}
}
/* ───────── 프로모 표기 헬퍼 (연장 상태에 따라 문구 전환) ───────── */
if (!function_exists('ufs_promo_is_ext')) {
function ufs_promo_is_ext() { return ufs_is_earlybird() && ufs_extended(); }  // 연장 프로모 활성
}
if (!function_exists('ufs_promo_hero_line')) {
function ufs_promo_hero_line() { return ufs_promo_is_ext() ? '전체 세션 공개 기념, 마지막 할인 혜택 !' : ''; }
}
if (!function_exists('ufs_promo_countdown_label')) {
function ufs_promo_countdown_label() { return ufs_promo_is_ext() ? '전체 세션 공개 기념 얼리버드 연장' : '얼리버드 할인 종료까지'; }
}
if (!function_exists('ufs_promo_card_badge')) {
function ufs_promo_card_badge() { return ufs_promo_is_ext() ? '전체 세션 공개 기념, 마지막 할인 혜택 !' : '얼리버드 50% 할인'; }
}
if (!function_exists('ufs_promo_card_note')) {   // index 티켓카드 가격 아래 줄
function ufs_promo_card_note() { return ufs_promo_is_ext() ? '마지막 할인 혜택 ! 50% 할인 (~7/27 마감)' : '얼리버드 할인 (~7/13 마감)'; }
}
if (!function_exists('ufs_promo_ticket_note')) { // 구매 페이지(ticket-all/day) 카드 소문구
function ufs_promo_ticket_note() { return ufs_promo_is_ext() ? '마지막 할인 혜택 ! 50% 할인 (~7/27 마감)' : '얼리버드 50% 할인'; }
}
if (!function_exists('ufs_refund_eb_label')) {   // FAQ/문구용 얼리버드 환불 마감일 라벨
function ufs_refund_eb_label() { return ufs_extended() ? '7월 27일' : '7월 13일'; }
}
if (!function_exists('ufs_price_table')) {
function ufs_price_table() {
    return array(
        'NORMAL_ALL' => array('eb'=>60000,  'reg'=>120000),  // 양일권
        'NORMAL_20'  => array('eb'=>30000,  'reg'=>60000),   // 1일권(8/20)
        'NORMAL_21'  => array('eb'=>30000,  'reg'=>60000),   // 1일권(8/21)
    );
}
}
if (!function_exists('ufs_ticket_price')) {
function ufs_ticket_price($code) {            // 현재 결제 금액(얼리버드/정가 자동)
    $t = ufs_price_table();
    if (!isset($t[$code])) return 0;
    return ufs_is_earlybird() ? (int)$t[$code]['eb'] : (int)$t[$code]['reg'];
}
}
if (!function_exists('ufs_ticket_orig')) {
function ufs_ticket_orig($code) {             // 정가(취소선 표기용)
    $t = ufs_price_table();
    return isset($t[$code]) ? (int)$t[$code]['reg'] : 0;
}
}
if (!function_exists('ufs_refund_notice')) {
function ufs_refund_notice() {
    if (!ufs_is_earlybird()) return '2026년 8월 18일 23:59까지 취소 및 환불이 가능합니다.';
    return ufs_extended()
        ? '얼리버드 티켓은 2026년 7월 27일 23:59까지 취소 및 환불이 가능하며, 7월 28일부터는 취소 및 환불이 불가합니다.'
        : '얼리버드 티켓은 2026년 7월 13일 23:59까지 취소 및 환불이 가능하며, 7월 14일부터는 취소 및 환불이 불가합니다.';
}
}
/* ───────── 단체 할인 (관리자 설정 — 모드 스위치) ─────────
 * cb_unreal_2026_config.group_discount 값이 "모드 스위치":
 *   0~50 = 일괄 모드 : 모든 단체가 이 할인율(%). 쿠폰은 완전히 무시.
 *   100  = 쿠폰 모드 : 일괄 할인 없음. 각 단체는 자기 쿠폰%로 개별 할인(쿠폰 없으면 정상가).
 *   (51~99 는 미사용 — 50 으로 스냅)
 * 최종 유효 할인율은 ufs_group_effective_discount($couponPct) 한 곳에서 결정. PHP 7.0 호환. */
if (!function_exists('ufs_group_discount_raw')) {
function ufs_group_discount_raw() {        // 원시 설정값(모드 스위치). 0~50 또는 100.
    $v = 0;
    if (function_exists('sql_fetch')) {
        $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='group_discount'");
        if ($r && isset($r['cfg_val']) && $r['cfg_val'] !== '') $v = (int)$r['cfg_val'];
    }
    if ($v < 0) $v = 0;
    if ($v >= 100) return 100;             // 쿠폰 모드 sentinel
    if ($v > 50) $v = 50;                   // 51~99 → 50 스냅
    return $v;
}
}
if (!function_exists('ufs_group_coupon_mode')) {
function ufs_group_coupon_mode() {         // 쿠폰 모드 여부(전역 100)
    return ufs_group_discount_raw() >= 100;
}
}
if (!function_exists('ufs_group_flat_discount')) {
function ufs_group_flat_discount() {       // 일괄 할인율(%). 쿠폰 모드면 0.
    return ufs_group_coupon_mode() ? 0 : ufs_group_discount_raw();
}
}
if (!function_exists('ufs_group_discount')) {
function ufs_group_discount() {            // (호환) 표시용 일괄 할인율 = flat. 쿠폰 모드면 0.
    return ufs_group_flat_discount();
}
}
if (!function_exists('ufs_group_effective_discount')) {
function ufs_group_effective_discount($coupon_pct) { // 최종 유효 할인율(%) — 단일 결정점
    if (ufs_group_coupon_mode()) {         // 쿠폰 모드: 쿠폰%만 적용
        $c = (int)$coupon_pct; if ($c < 0) $c = 0; if ($c > 100) $c = 100; return $c;
    }
    return ufs_group_flat_discount();      // 일괄 모드: 쿠폰 무시, 일괄 할인만
}
}
if (!function_exists('ufs_group_price')) {
function ufs_group_price($code) {          // 일괄 단체가 = 정상가 × (1 - 일괄할인율), 100원 단위 반올림
    $base = ufs_ticket_orig($code);
    $d = ufs_group_flat_discount();        // 쿠폰 모드면 0 → 정상가 (100% 무료 버그 방지)
    return (int)(round(($base * (100 - $d) / 100) / 100) * 100);
}
}
