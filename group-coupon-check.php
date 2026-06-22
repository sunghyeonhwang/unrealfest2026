<?php
/* Unreal Fest Seoul 2026 — 단체 쿠폰 검증 (group-coupon-check.php)
 * ?code=XXX → JSON {ok, percent, code, msg}. 사용중지/만료/한도초과 검증(증가는 결제완료 시=Phase3).
 */
include_once "../common.php";
header('Content-Type: application/json; charset=utf-8');
$code = isset($_GET['code']) ? strtoupper(trim($_GET['code'])) : '';
if ($code === '') { echo json_encode(array('ok'=>false,'msg'=>'쿠폰 코드를 입력해 주세요.')); exit; }
$ce = sql_real_escape_string($code);
$r = @sql_fetch("SELECT * FROM cb_unreal_2026_coupon WHERE cp_code='$ce' LIMIT 1");
if (!$r) { echo json_encode(array('ok'=>false,'msg'=>'유효하지 않은 쿠폰입니다.')); exit; }
if ($r['cp_active'] !== 'Y') { echo json_encode(array('ok'=>false,'msg'=>'사용할 수 없는 쿠폰입니다.')); exit; }
if (!empty($r['cp_expire']) && $r['cp_expire'] !== '0000-00-00' && $r['cp_expire'] < date('Y-m-d')) {
    echo json_encode(array('ok'=>false,'msg'=>'만료된 쿠폰입니다.')); exit;
}
if ((int)$r['cp_max'] > 0 && (int)$r['cp_used'] >= (int)$r['cp_max']) {
    echo json_encode(array('ok'=>false,'msg'=>'사용 한도가 초과된 쿠폰입니다.')); exit;
}
echo json_encode(array('ok'=>true, 'percent'=>(int)$r['cp_percent'], 'code'=>$code, 'msg'=>(int)$r['cp_percent'].'% 할인 쿠폰이 적용되었습니다.'));
