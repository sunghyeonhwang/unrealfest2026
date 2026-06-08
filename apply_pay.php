<?php
/* Unreal Fest Seoul 2026 — 오프라인 등록 INSERT + INICIS 결제요청 (apply_pay.php)
 * 포팅: 2025 _applicaiton_pay_ajax.php(INSERT) + application_step2.php(INICIS)
 * PHP 7.0 호환. 세션/DB는 www/common.php(= ../../common.php) 공유.
 */
include_once "../common.php";                 // sql_query/sql_fetch/sql_real_escape_string + 세션
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";

// ── INICIS 모드 (검증 후 false 로 전환) ──
$INICIS_TEST = true;
if ($INICIS_TEST) {
    $mid     = "INIpayTest";
    $signKey = "SU5JTElURV9UUklQTEVERVNfS0VZU1RS";
    $jsUrl   = "https://stgstdpay.inicis.com/stdjs/INIStdPay.js";
} else {
    $mid     = "MOIepiclou";
    $signKey = "Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09";
    $jsUrl   = "https://stdpay.inicis.com/stdjs/INIStdPay.js";
}

// ── 입력값 (CI/DI/이름은 POST 우선, 세션 폴백) ──
function pp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }
$apply_ci    = pp('apply_ci')   ? pp('apply_ci')   : (isset($_SESSION['CI']) ? $_SESSION['CI'] : '');
$apply_di    = pp('apply_di')   ? pp('apply_di')   : (isset($_SESSION['DI']) ? $_SESSION['DI'] : '');
$apply_user_name = pp('apply_user_name') ? pp('apply_user_name') : (isset($_SESSION['RSLT_NAME']) ? $_SESSION['RSLT_NAME'] : '');
$apply_user_email   = pp('apply_user_email');
$apply_user_phone   = pp('apply_user_phone');
$apply_user_company = pp('apply_user_company');
$apply_user_depart  = pp('apply_user_depart');
$apply_user_job     = pp('apply_user_job');
$apply_user_grade   = pp('apply_user_grade');
$apply_user_ex1     = pp('apply_user_ex1');
$apply_product_code = pp('apply_product_code');
$apply_product_name = pp('apply_product_name');
$apply_product_price= pp('apply_product_price');
$apply_track        = pp('apply_track');
$apply_tshirt       = pp('tshirt');
$apply_event_agree  = (pp('agree_mkt') !== '' ) ? '1' : '0';

// 상품 화이트리스트 (서버측 금액 재검증 — 위변조 방지)
$PRODUCTS = array(
  'NORMAL_ALL' => array('name'=>'양일권',       'price'=>120000),
  'NORMAL_20'  => array('name'=>'Day 1 단일권', 'price'=>60000),
  'NORMAL_21'  => array('name'=>'Day 2 단일권', 'price'=>60000),
);
if (!isset($PRODUCTS[$apply_product_code])) { exit('잘못된 상품입니다.'); }
$apply_product_name  = $PRODUCTS[$apply_product_code]['name'];
$apply_product_price = (string)$PRODUCTS[$apply_product_code]['price'];

// ── 검증 ──
if ($apply_ci === '')          { exit('<script>alert("본인인증을 먼저 진행해주세요.");history.back();</script>'); }
if ($apply_user_email === '' || $apply_user_phone === '') { exit('<script>alert("이메일/연락처를 입력해주세요.");history.back();</script>'); }

// ── 중복 등록 체크 (확정건 기준) ──
$ci_esc = sql_real_escape_string($apply_ci);
$em_esc = sql_real_escape_string($apply_user_email);
$ph_esc = sql_real_escape_string($apply_user_phone);
$dup = sql_fetch("select count(*) as cnt from cb_unreal_2026_event2_apply where apply_ci = '$ci_esc' and apply_temp_yn = 'N'");
if ($dup && $dup['cnt'] > 0) { exit('<script>alert("이미 등록된 본인인증 정보입니다. 등록 확인 페이지에서 확인해주세요.");location.href="myticket.php";</script>'); }
$dup = sql_fetch("select count(*) as cnt from cb_unreal_2026_event2_apply where apply_user_email = '$em_esc' and apply_user_phone = '$ph_esc' and apply_temp_yn = 'N'");
if ($dup && $dup['cnt'] > 0) { exit('<script>alert("이미 등록된 이메일/연락처입니다.");location.href="myticket.php";</script>'); }

// ── INSERT (임시: apply_temp_yn=Y) ──
$apply_password = md5(str_replace("'","\\'",$apply_user_email));
$sql = "INSERT INTO cb_unreal_2026_event2_apply
  (apply_user_name, apply_user_email, apply_user_phone, apply_user_job, apply_user_company,
   apply_user_depart, apply_user_grade, apply_user_ex1, apply_product_code, apply_product_name,
   apply_product_price, apply_tshirt, apply_track, apply_user_event_agree, apply_password,
   apply_ci, apply_di, apply_temp_yn, apply_reg_datetime)
  VALUES (
   '".sql_real_escape_string(strip_tags($apply_user_name))."',
   '".sql_real_escape_string(strip_tags($apply_user_email))."',
   '".sql_real_escape_string(strip_tags($apply_user_phone))."',
   '".sql_real_escape_string(strip_tags($apply_user_job))."',
   '".sql_real_escape_string(strip_tags($apply_user_company))."',
   '".sql_real_escape_string(strip_tags($apply_user_depart))."',
   '".sql_real_escape_string(strip_tags($apply_user_grade))."',
   '".sql_real_escape_string(strip_tags($apply_user_ex1))."',
   '".sql_real_escape_string(strip_tags($apply_product_code))."',
   '".sql_real_escape_string(strip_tags($apply_product_name))."',
   '".sql_real_escape_string(strip_tags($apply_product_price))."',
   '".sql_real_escape_string(strip_tags($apply_tshirt))."',
   '".sql_real_escape_string(strip_tags($apply_track))."',
   '".sql_real_escape_string($apply_event_agree)."',
   '".sql_real_escape_string($apply_password)."',
   '".sql_real_escape_string(strip_tags($apply_ci))."',
   '".sql_real_escape_string(strip_tags($apply_di))."',
   'Y', now())";
sql_query($sql);
$row = sql_query("SELECT LAST_INSERT_ID() as idx")->fetch_array();
$apply_no = $row['idx'];
$_SESSION["final_idx"] = $apply_no;

// ── INICIS 결제 요청 전문 ──
$util       = new INIStdPayUtil();
$timestamp  = $util->getTimestamp();
$mKey       = $util->makeHash($signKey, "sha256");
$oid        = $mid . "_" . $timestamp;
$price      = $apply_product_price;
$signParam  = array("oid"=>$oid, "price"=>$price, "timestamp"=>$timestamp);
$sign       = $util->makeSignature($signParam);
$veriParam  = array("oid"=>$oid, "price"=>$price, "signKey"=>$signKey, "timestamp"=>$timestamp);
$sign2      = $util->makeSignature($veriParam);
$base       = "https://".$_SERVER['HTTP_HOST']."/v3/unrealfest2026";
$returnUrl  = $base."/apply_pay_return.php";
$closeUrl   = $base."/ticket.php";
function ev($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ko"><head><meta charset="utf-8">
<title>결제 진행 중 — Unreal Fest Seoul 2026</title>
<script language="javascript" type="text/javascript" src="<?= $jsUrl ?>" charset="UTF-8"></script>
<style>body{background:#09090b;color:#fff;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}</style>
</head><body onload="INIStdPay.pay('SendPayForm_id');">
<div>결제 화면으로 이동 중입니다...</div>
<form name="SendPayForm_id" id="SendPayForm_id" method="post" style="display:none">
  <input type="hidden" name="version" value="1.0">
  <input type="hidden" name="mid" value="<?= ev($mid) ?>">
  <input type="hidden" name="oid" value="<?= ev($oid) ?>">
  <input type="hidden" name="price" value="<?= ev($price) ?>">
  <input type="hidden" name="timestamp" value="<?= ev($timestamp) ?>">
  <input type="hidden" name="use_chkfake" value="Y">
  <input type="hidden" name="signature" value="<?= ev($sign) ?>">
  <input type="hidden" name="verification" value="<?= ev($sign2) ?>">
  <input type="hidden" name="mKey" value="<?= ev($mKey) ?>">
  <input type="hidden" name="currency" value="WON">
  <input type="hidden" name="goodname" value="<?= ev('언리얼 페스트 서울 2026 '.$apply_product_name) ?>">
  <input type="hidden" name="buyername" value="<?= ev($apply_user_name) ?>">
  <input type="hidden" name="buyertel" value="<?= ev($apply_user_phone) ?>">
  <input type="hidden" name="buyeremail" value="<?= ev($apply_user_email) ?>">
  <input type="hidden" name="gopaymethod" value="Card:Directbank:vbank">
  <input type="hidden" name="acceptmethod" value="HPP(1):below1000:centerCd(Y)">
  <input type="hidden" name="merchantData" value="<?= ev($apply_no) ?>">
  <input type="hidden" name="returnUrl" value="<?= ev($returnUrl) ?>">
  <input type="hidden" name="closeUrl" value="<?= ev($closeUrl) ?>">
</form>
</body></html>
