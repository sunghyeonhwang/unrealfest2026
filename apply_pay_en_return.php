<?php
/* Unreal Fest Seoul 2026 — 해외(영문) INICIS 승인 콜백 (apply_pay_en_return.php)
 * apply_pay_en.php(INIStdPay) returnUrl. 국내 apply_pay_return.php 기반 + 영문 메일 + 영문 완료페이지.
 * 승인 → UPDATE(status=10) → 정원 재확인 → 쿠폰+1 → QR → 영문 완료메일 → ticket-en-complete.php. PHP 7.0.
 */
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";
require_once "../unrealfest2025/inisis_pc/libs/HttpClient.php";
require_once "../unrealfest2025/inisis_pc/libs/properties.php";
include_once "../common.php";
if (is_file(__DIR__.'/_dodo_mail.php')) require_once __DIR__.'/_dodo_mail.php';   // ufs_dodo_confirm_mail (영문)
if (is_file(__DIR__.'/_resend.php'))    require_once __DIR__.'/_resend.php';      // ufs_resend_send

$mid = "MOIepiclou"; $signKey = "Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09";   // 국내와 동일(해외카드 가맹)

function back_en($msg){
  echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"></head><body style="background:#09090b;color:#e4e4e7;font-family:system-ui,sans-serif;text-align:center;padding:60px 24px">'
     . '<h2 style="color:#fff">Payment not completed</h2><p style="color:#a1a1aa">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p>'
     . '<p><a href="ticket-en.php" style="color:#00C1D5">← Back to registration</a></p></body></html>'; exit;
}
function R($k){ return isset($_REQUEST[$k]) ? $_REQUEST[$k] : ''; }

$util = new INIStdPayUtil();
$prop = new properties();

// 결제창 1차 인증 결과
if (strcmp("0000", R("resultCode")) !== 0) {
  back_en("Your payment was cancelled or failed. You have not been charged.");
}

// 주문 식별: merchantData(apply_no)
$apply_no = R("merchantData") ? R("merchantData") : (isset($_SESSION["final_idx"]) ? $_SESSION["final_idx"] : '');
$apply_no = preg_replace('/[^0-9]/', '', $apply_no);
if ($apply_no === '') { back_en("Order information not found."); }

$prev = sql_fetch("select * from cb_unreal_2026_event2_apply where apply_no = '".intval($apply_no)."'");
if (!$prev) { back_en("Registration not found."); }
if ($prev['apply_temp_yn'] === 'N') {   // 이미 처리됨(중복 콜백)
  header("Location: ticket-en-complete.php?apply_no=".intval($apply_no)); exit;
}

// ── 서버-투-서버 승인 요청 ──
$timestamp = $util->getTimestamp();
$authToken = R("authToken");
$idc_name  = R("idc_name");
$authUrl   = $prop->getAuthUrl($idc_name);

$signParam    = array("authToken"=>$authToken, "timestamp"=>$timestamp);
$signature    = $util->makeSignature($signParam);
$veriParam    = array("authToken"=>$authToken, "signKey"=>$signKey, "timestamp"=>$timestamp);
$verification = $util->makeSignature($veriParam);

$authMap = array(
  "mid"=>$mid, "authToken"=>$authToken, "signature"=>$signature,
  "verification"=>$verification, "timestamp"=>$timestamp, "charset"=>"UTF-8", "format"=>"JSON"
);

$http = new HttpClient();
if (!$http->processHTTP($authUrl, $authMap)) { back_en("A communication error occurred during payment approval."); }
$authResultString = $http->body;

@sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".intval($apply_no)."','".str_replace("'","`",$authResultString)."',now())");
$rm = json_decode($authResultString, true);

if (!isset($rm["resultCode"]) || $rm["resultCode"] !== "0000") {
  back_en("Payment approval failed. ".(isset($rm["resultMsg"])?$rm["resultMsg"]:''));
}

// ── 승인 성공 → 확정 UPDATE ──
$apply_password = md5(str_replace("'","\\'",$prev['apply_user_email']));
function rmv($rm,$k){ return isset($rm[$k]) ? sql_real_escape_string($rm[$k]) : ''; }
$sql = "UPDATE cb_unreal_2026_event2_apply SET
  apply_password='".sql_real_escape_string($apply_password)."',
  free_yn='N', apply_pay_status=10,
  pay_resultCode='".rmv($rm,'resultCode')."',
  pay_resultMsg='".rmv($rm,'resultMsg')."',
  pay_tid='".rmv($rm,'tid')."',
  pay_moid='".rmv($rm,'MOID')."',
  pay_totprice='".rmv($rm,'TotPrice')."',
  pay_goodname='".rmv($rm,'goodName')."',
  pay_appldate='".rmv($rm,'applDate')."',
  pay_appltime='".rmv($rm,'applTime')."',
  pay_applnum='".rmv($rm,'applNum')."',
  pay_paymethod='".rmv($rm,'payMethod')."',
  pay_result_map='".str_replace("'","`",sql_real_escape_string($authResultString))."',
  pay_complete='Y', apply_temp_yn='N'
  WHERE apply_no='".intval($apply_no)."'";
sql_query($sql);

// ── 트랙 정원 재확인 (동시 결제 초과 방지) — 선착순 apply_no 기준 ──
$over_track = '';
$ot_tracks = explode(',', isset($prev['apply_track']) ? $prev['apply_track'] : '');
foreach ($ot_tracks as $ot) {
  $ot = trim($ot); if ($ot === '') continue;
  $ote = sql_real_escape_string($ot);
  $cap = sql_fetch("select date1 from 2026_event_ticket where name='".$ote."'");
  if (!$cap) continue;
  $capN = (int)$cap['date1']; if ($capN <= 0) continue;
  $rk = sql_fetch("select count(*) c from cb_unreal_2026_event2_apply where apply_temp_yn='N' and apply_pay_status<>0 and apply_track like '%".$ote."%' and apply_no <= ".intval($apply_no));
  if ($rk && (int)$rk['c'] > $capN) { $over_track = $ot; break; }
}
if ($over_track !== '') {
  require_once __DIR__ . '/_refund.php';
  $rf_tid = isset($rm['tid']) ? $rm['tid'] : (isset($prev['pay_tid']) ? $prev['pay_tid'] : '');
  $rf_pm  = isset($rm['payMethod']) ? $rm['payMethod'] : 'Card';
  @ufs_inicis_refund($rf_tid, $rf_pm, 'Track full - auto cancel');
  sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0, pay_complete='N', refund_msg='Track full - auto cancel', refund_date='".date('Y-m-d H:i:s')."' WHERE apply_no='".intval($apply_no)."'");
  back_en("Sorry, the track you selected sold out during checkout, so your payment was automatically refunded. Please register again with another track.");
}

// ── 쿠폰 사용횟수 +1 (카드 확정 성공 시 1회) ──
if (!empty($prev['apply_coupon_code'])) {
  @sql_query("UPDATE cb_unreal_2026_coupon SET cp_used=cp_used+1 WHERE cp_code='".sql_real_escape_string($prev['apply_coupon_code'])."'");
}

// ── QR 생성 ──
@mkdir(__DIR__."/qrdata", 0755);
if (file_exists("../unrealfest2025/phpqrcode/qrlib.php")) {
  include_once "../unrealfest2025/phpqrcode/qrlib.php";
  $png = __DIR__."/qrdata/".$apply_no.".png";
  $jpg = __DIR__."/qrdata/".$apply_no.".jpg";
  QRcode::png($apply_password, $png, 0, 7, 2);
  if (file_exists($png) && function_exists('imagecreatefrompng')) {
    $p = imagecreatefrompng($png);
    if ($p) { $j = imagecreatetruecolor(imagesx($p), imagesy($p)); imagecopy($j,$p,0,0,0,0,imagesx($p),imagesy($p)); imagejpeg($j,$jpg,100); imagedestroy($p); imagedestroy($j); }
  }
}

// ── 영문 완료 메일 (QR 포함, 비차단) ──
$row2 = sql_fetch("select * from cb_unreal_2026_event2_apply where apply_no = '".intval($apply_no)."'");
if ($row2 && function_exists('ufs_dodo_confirm_mail') && function_exists('ufs_resend_send')) {
  $amt = isset($rm['TotPrice']) ? ('&#8361;'.number_format((int)$rm['TotPrice']).' KRW') : '';
  $m = ufs_dodo_confirm_mail($row2, $amt);
  @ufs_resend_send($row2['apply_user_email'], $m['subject'], $m['html'], '', $m['text']);
}

// ── 전환 추적(광고 수신동의 시, 비차단) ──
if (is_file(__DIR__.'/_kakao_capi.php')) { require_once __DIR__.'/_kakao_capi.php'; @ufs_kakao_capi_send($prev); }
if (is_file(__DIR__.'/_meta_capi.php'))  { require_once __DIR__.'/_meta_capi.php';  @ufs_meta_capi_send($prev); }

header("Location: ticket-en-complete.php?apply_no=".intval($apply_no));
exit;
