<?php
/* Unreal Fest Seoul 2026 — 단체 등록 카드 결제(INICIS 일괄) (ticket-group-pay.php) [Phase 3]
 * 저장된 그룹(grp_no+grp_code)의 총액으로 INICIS 결제 요청. PC=INIStdPay / 모바일=mobile.inicis.com.
 * 승인 콜백 = ticket-group-pay-return.php. PHP 7.0 호환.
 */
include_once "../common.php";
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";

$mid="MOIepiclou"; $signKey="Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09"; $jsUrl="https://stdpay.inicis.com/stdjs/INIStdPay.js";

function ev($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$grp_no = isset($_GET['g']) ? (int)$_GET['g'] : 0;
$tok    = isset($_GET['t']) ? trim($_GET['t']) : '';
$g = $grp_no ? sql_fetch("SELECT * FROM cb_unreal_2026_group WHERE grp_no=".$grp_no." LIMIT 1") : null;
if (!$g || $g['grp_code'] !== $tok) { exit('잘못된 접근입니다.'); }
if ($g['pay_status'] === 'paid') { header('Location: ticket-group-complete.php?g='.$grp_no.'&t='.rawurlencode($tok)); exit; }
if ($g['paymethod'] !== 'card') { exit('카드 결제 건이 아닙니다.'); }

$price      = (int)$g['total_amount'];
$buyername  = $g['rep_name']; $buyertel = $g['rep_phone']; $buyeremail = $g['rep_email'];
$goods      = '언리얼 페스트 서울 2026 단체등록 '.(int)$g['headcount'].'명';

$util       = new INIStdPayUtil();
$timestamp  = $util->getTimestamp();
$mKey       = $util->makeHash($signKey, "sha256");
$oid        = $mid."_".$timestamp;
$sign       = $util->makeSignature(array("oid"=>$oid, "price"=>$price, "timestamp"=>$timestamp));
$sign2      = $util->makeSignature(array("oid"=>$oid, "price"=>$price, "signKey"=>$signKey, "timestamp"=>$timestamp));
$base       = "https://".$_SERVER['HTTP_HOST']."/v3/unrealfest2026";
$returnUrl  = $base."/ticket-group-pay-return.php";
$closeUrl   = $base."/ticket-group.php";
$mNextUrl   = $base."/ticket-group-pay-return.php";

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
  <input type="hidden" name="P_OID" value="<?= ev($oid) ?>">
  <input type="hidden" name="P_AMT" value="<?= ev($price) ?>">
  <input type="hidden" name="P_GOODS" value="<?= ev($goods) ?>">
  <input type="hidden" name="P_UNAME" value="<?= ev($buyername) ?>">
  <input type="hidden" name="P_MOBILE" value="<?= ev($buyertel) ?>">
  <input type="hidden" name="P_EMAIL" value="<?= ev($buyeremail) ?>">
  <input type="hidden" name="P_NEXT_URL" value="<?= ev($mNextUrl) ?>">
  <input type="hidden" name="P_NOTI_URL" value="<?= ev($mNextUrl) ?>">
  <input type="hidden" name="P_CHARSET" value="utf8">
  <input type="hidden" name="P_RESERVED" value="below1000=Y&vbank_receipt=Y&centerCd=Y">
  <input type="hidden" name="merchantData" value="<?= ev($grp_no) ?>">
</form>
</body></html>
<?php else: ?>
<body onload="INIStdPay.pay('SendPayForm_id');">
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
  <input type="hidden" name="goodname" value="<?= ev($goods) ?>">
  <input type="hidden" name="buyername" value="<?= ev($buyername) ?>">
  <input type="hidden" name="buyertel" value="<?= ev($buyertel) ?>">
  <input type="hidden" name="buyeremail" value="<?= ev($buyeremail) ?>">
  <input type="hidden" name="gopaymethod" value="Card">
  <input type="hidden" name="acceptmethod" value="HPP(1):below1000:centerCd(Y):cardonly">
  <input type="hidden" name="merchantData" value="<?= ev($grp_no) ?>">
  <input type="hidden" name="returnUrl" value="<?= ev($returnUrl) ?>">
  <input type="hidden" name="closeUrl" value="<?= ev($closeUrl) ?>">
</form>
</body></html>
<?php endif; ?>
