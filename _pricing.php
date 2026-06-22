<?php
/* Unreal Fest Seoul 2026 — 가격/얼리버드 단일 소스 (_pricing.php)
 * 얼리버드(~2026-07-13 23:59 KST)와 정가를 날짜 기준 자동 전환. 환불 안내도 동일 기준.
 * require 처: apply_pay.php(실결제 금액) / _ticket_init.php(등록폼·myticket·sidebar) / index.php(표시가).
 * ⚠️ 가격 변경은 ufs_price_table() 한 곳만 수정. PHP 7.0 호환.
 */
if (!function_exists('ufs_is_earlybird')) {
function ufs_is_earlybird() { return time() <= strtotime('2026-07-13 23:59:59 +0900'); } // 7/14 0시부터 정가
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
    return ufs_is_earlybird()
        ? '얼리버드 티켓은 2026년 7월 13일 23:59까지 취소 및 환불이 가능하며, 7월 14일부터는 취소 및 환불이 불가합니다.'
        : '2026년 8월 18일 23:59까지 취소 및 환불이 가능합니다.';
}
}
/* ───────── 단체 할인 (관리자 설정, 정상가 기준 %) ───────── */
if (!function_exists('ufs_group_discount')) {
function ufs_group_discount() {            // 단체 할인율(%). 미설정 0, 0~50 클램프.
    $v = 0;
    if (function_exists('sql_fetch')) {
        $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='group_discount'");
        if ($r && isset($r['cfg_val']) && $r['cfg_val'] !== '') $v = (int)$r['cfg_val'];
    }
    if ($v < 0) $v = 0; if ($v > 50) $v = 50;
    return $v;
}
}
if (!function_exists('ufs_group_price')) {
function ufs_group_price($code) {          // 단체가 = 정상가 × (1 - 할인율), 100원 단위 반올림
    $base = ufs_ticket_orig($code);
    $d = ufs_group_discount();
    return (int)(round(($base * (100 - $d) / 100) / 100) * 100);
}
}
