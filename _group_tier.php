<?php
/* Unreal Fest Seoul 2026 — 단체 티어 할인 헬퍼 (_group_tier.php)
 * 등록 인원(headcount) 구간별 할인율(%). 정상가 기준.
 *   5~9인=10% / 10~19인=20% / 20~29인=30% / 30~39인=40% / 40인↑=50% / 5인 미만=0%.
 * PHP 7.0.
 */
if (!function_exists('ufs_group_tier_pct')) {
function ufs_group_tier_pct($n) {
    $n = (int)$n;
    if ($n >= 40) return 50;
    if ($n >= 30) return 40;
    if ($n >= 20) return 30;
    if ($n >= 10) return 20;
    if ($n >= 5)  return 10;
    return 0;
}
}
/* JS 전달용 구간표 [[하한인원, 할인율], ...] 내림차순 */
if (!function_exists('ufs_group_tier_table')) {
function ufs_group_tier_table() {
    return array(array(40,50),array(30,40),array(20,30),array(10,20),array(5,10));
}
}
