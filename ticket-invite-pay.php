<?php
/* Unreal Fest Seoul 2026 — 초청(부분할인) 카드 결제 요청 (ticket-invite-pay.php) [M3]
 * 홀드 배치(?o=oid, status 1)의 합계로 INICIS 결제 요청. 승인 콜백 = ticket-invite-pay-return.php.
 * 단체 결제(ticket-group-pay.php) 구조 재사용. PHP 7.0 호환.
 */
include_once "../common.php";
require_once __DIR__ . '/_invite_apply.php';
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";

$mid="MOIepiclou"; $signKey="Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09"; $jsUrl="https://stdpay.inicis.com/stdjs/INIStdPay.js";
function ev($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$oid = isset($_GET['o']) ? trim($_GET['o']) : '';
$sm  = ufs_invite_oid_summary($oid);
if (!$sm || (int)$sm['cnt'] <= 0) { exit('결제 대상을 찾을 수 없습니다.'); }
$rep = sql_fetch("SELECT apply_no, apply_user_name, apply_user_email, apply_user_phone, apply_password FROM cb_unreal_2026_event2_apply WHERE apply_no=".(int)$sm['rep_no']." LIMIT 1");
if (!$rep) { exit('등록 정보를 찾을 수 없습니다.'); }
// 이미 결제완료(승급 10) → 완료 페이지로
if ((int)$sm['st'] === 10) { header('Location: ticket-invite-complete.php?a='.(int)$rep['apply_no'].'&t='.rawurlencode($rep['apply_password'])); exit; }

$price      = (int)$sm['amt'];
if ($price <= 0) { exit('결제 금액이 올바르지 않습니다. 사무국으로 문의해 주세요.'); }
$buyername  = $rep['apply_user_name']; $buyertel = $rep['apply_user_phone']; $buyeremail = $rep['apply_user_email'];
$goods      = '언리얼 페스트 서울 2026 초청등록 '.(int)$sm['cnt'].'명';

$util       = new INIStdPayUtil();
$timestamp  = $util->getTimestamp();
$mKey       = $util->makeHash($signKey, "sha256");
$poid       = $mid."_".$timestamp;
$sign       = $util->makeSignature(array("oid"=>$poid, "price"=>$price, "timestamp"=>$timestamp));
$sign2      = $util->makeSignature(array("oid"=>$poid, "price"=>$price, "signKey"=>$signKey, "timestamp"=>$timestamp));
$base       = "https://".$_SERVER['HTTP_HOST']."/v3/unrealfest2026";
$returnUrl  = $base."/ticket-invite-pay-return.php";
$closeUrl   = $base."/ticket-invite.php";
$mNextUrl   = $base."/ticket-invite-pay-return.php";

$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$is_mobile = preg_match('/(android|iphone|ipad|ipod|mobile)/i', $ua);
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>결제 진행 중 — Unreal Fest Seoul 2026</title>
<?php if (!$is_mobile): ?>
<script language="javascript" type="text/javascript" src="<?= $jsUrl ?>" charset="UTF-8"></script>
<?php endif; ?>
<style>body{background:#09090b;color:#fff;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}</style>
</head>
<?php if ($is_mobile): ?>
<body onload="document.getElementById('SendPayForm_mobile').submit();">
<div>결제 화면으로 이동 중입니다...</div>
<form name="SendPayForm_mobile" id="SendPayForm_mobile" method="post" action="https://mobile.inicis.com/smart/payment/" accept-charset="euc-kr" style="display:none">
  <input type="hidden" name="P_INI_PAYMENT" value="CARD">
  <input type="hidden" name="P_MID" value="<?= ev($mid) ?>">
  <input type="hidden" name="P_OID" value="<?= ev($poid) ?>">
  <input type="hidden" name="P_AMT" value="<?= ev($price) ?>">
  <input type="hidden" name="P_GOODS" value="<?= ev($goods) ?>">
  <input type="hidden" name="P_UNAME" value="<?= ev($buyername) ?>">
  <input type="hidden" name="P_MOBILE" value="<?= ev($buyertel) ?>">
  <input type="hidden" name="P_EMAIL" value="<?= ev($buyeremail) ?>">
  <input type="hidden" name="P_NEXT_URL" value="<?= ev($mNextUrl) ?>">
  <input type="hidden" name="P_NOTI_URL" value="<?= ev($mNextUrl) ?>">
  <input type="hidden" name="P_CHARSET" value="utf8">
  <input type="hidden" name="P_RESERVED" value="below1000=Y&vbank_receipt=Y&centerCd=Y">
  <input type="hidden" name="merchantData" value="<?= ev($oid) ?>">
</form>
</body></html>
<?php else: ?>
<body onload="INIStdPay.pay('SendPayForm_id');">
<div>결제 화면으로 이동 중입니다...</div>
<form name="SendPayForm_id" id="SendPayForm_id" method="post" style="display:none">
  <input type="hidden" name="version" value="1.0">
  <input type="hidden" name="mid" value="<?= ev($mid) ?>">
  <input type="hidden" name="oid" value="<?= ev($poid) ?>">
  <input type="hidden" name="price" value="<?= ev($price) ?>">
  <input type="hidden" name="timestamp" value="<?= ev($timestamp) ?>">
  <input type="hidden" name="use_chkfake" value="Y">
  <input type="hidden" name="signature" value="<?= ev($sign) ?>">
  <input type="hidden" name="verification" value="<?= ev($sign2) ?>">
  <input type="hidden" name="mKey" value="<?= ev($mKey) ?>">
  <input type="hidden" name="currency" value="WON">
  <input type="hidden" name="goodname" value="<?= ev($goods) ?>">
  <input type="hidden" name="buyername" value="<?= ev($buyername) ?>">
  <input type="hidden" name="buyertel" value="<?= ev($buyertel) ?>">
  <input type="hidden" name="buyeremail" value="<?= ev($buyeremail) ?>">
  <input type="hidden" name="gopaymethod" value="Card">
  <input type="hidden" name="acceptmethod" value="HPP(1):below1000:centerCd(Y):cardonly">
  <input type="hidden" name="merchantData" value="<?= ev($oid) ?>">
  <input type="hidden" name="returnUrl" value="<?= ev($returnUrl) ?>">
  <input type="hidden" name="closeUrl" value="<?= ev($closeUrl) ?>">
</form>
</body></html>
<?php endif; ?>
