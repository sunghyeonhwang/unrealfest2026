<?php
/* Unreal Fest Seoul 2026 — 영문 쿠폰 검증 AJAX (coupon_en_check.php)
 * ticket-en.php 쿠폰 'Apply' → {ok, percent} 반환(할인율 미리보기용).
 * 실제 할인/무료 확정·정원·사용횟수는 apply_pay_en.php에서 재검증(권위). PHP 7.0.
 */
require __DIR__ . '/_ticket_init.php';   // common + _coupon(ufs_coupon_check)
header('Content-Type: application/json; charset=utf-8');
$code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
if (!function_exists('ufs_coupon_check')) { echo json_encode(array('ok'=>false, 'percent'=>0)); exit; }
$ck = ufs_coupon_check($code);
echo json_encode(array('ok'=>!empty($ck['ok']), 'percent'=>(int)$ck['percent']));
