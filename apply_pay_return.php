<?php
/* Unreal Fest Seoul 2026 — INICIS 승인 콜백 (apply_pay_return.php)
 * 포팅: 2025 inisis_pc/INIstdpay_pc_return.php
 * 승인 → cb_unreal_2026_event2_apply UPDATE → QR → ticket-complete.php
 * PHP 7.0 호환.
 */
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";
require_once "../unrealfest2025/inisis_pc/libs/HttpClient.php";
require_once "../unrealfest2025/inisis_pc/libs/properties.php";
include_once "../common.php";
require_once __DIR__ . "/_sms.php";   // QR MMS / 가상계좌 안내 SMS

$INICIS_TEST = false;
if ($INICIS_TEST) { $mid="INIpayTest"; $signKey="SU5JTElURV9UUklQTEVERVNfS0VZU1RS"; }
else { $mid="MOIepiclou"; $signKey="Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09"; }

function back_alert($msg){
  echo '<script>alert("'.str_replace(array('"',"\n"),array('\\"',' '),$msg).'");history.back(-1);</script>'; exit;
}
function R($k){ return isset($_REQUEST[$k]) ? $_REQUEST[$k] : ''; }

$util = new INIStdPayUtil();
$prop = new properties();

// 인증결과 (결제창에서 1차 인증)
if (strcmp("0000", R("resultCode")) !== 0) {
  back_alert("결제가 취소되었거나 실패했습니다. ".R("resultMsg"));
}

// 주문 식별: merchantData(apply_no) 우선, 세션 폴백
$apply_no = R("merchantData") ? R("merchantData") : (isset($_SESSION["final_idx"]) ? $_SESSION["final_idx"] : '');
$apply_no = preg_replace('/[^0-9]/', '', $apply_no);
if ($apply_no === '') { back_alert("주문 정보를 찾을 수 없습니다."); }

$prev = sql_fetch("select * from cb_unreal_2026_event2_apply where apply_no = '".intval($apply_no)."'");
if (!$prev) { back_alert("등록 정보를 찾을 수 없습니다."); }
if ($prev['apply_temp_yn'] === 'N') { // 이미 처리된 건 (중복 콜백)
  header("Location: ticket-complete.php?k=".rawurlencode(base64_encode($apply_no))); exit;
}

// ── 서버-투-서버 승인 요청 ──
$timestamp = $util->getTimestamp();
$authToken = R("authToken");
$idc_name  = R("idc_name");
$authUrl   = $prop->getAuthUrl($idc_name);

$signParam = array("authToken"=>$authToken, "timestamp"=>$timestamp);
$signature = $util->makeSignature($signParam);
$veriParam = array("authToken"=>$authToken, "signKey"=>$signKey, "timestamp"=>$timestamp);
$verification = $util->makeSignature($veriParam);

$authMap = array(
  "mid"=>$mid, "authToken"=>$authToken, "signature"=>$signature,
  "verification"=>$verification, "timestamp"=>$timestamp, "charset"=>"UTF-8", "format"=>"JSON"
);

$http = new HttpClient();
if (!$http->processHTTP($authUrl, $authMap)) { back_alert("결제 승인 통신 오류가 발생했습니다."); }
$authResultString = $http->body;

@sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".intval($apply_no)."','".str_replace("'","`",$authResultString)."',now())");
$rm = json_decode($authResultString, true);

if (!isset($rm["resultCode"]) || $rm["resultCode"] !== "0000") {
  back_alert("결제 승인에 실패했습니다. ".(isset($rm["resultMsg"])?$rm["resultMsg"]:''));
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
  pay_vact_num='".rmv($rm,'VACT_Num')."',
  pay_vact_bankcode='".rmv($rm,'VACT_BankCode')."',
  pay_vact_date='".rmv($rm,'VACT_Date')."',
  pay_result_map='".str_replace("'","`",sql_real_escape_string($authResultString))."',
  pay_complete='Y', apply_temp_yn='N'
  WHERE apply_no='".intval($apply_no)."'";
sql_query($sql);

// ── 트랙 정원 재확인 (동시 결제로 인한 초과 방지) ──
//   선착순 = apply_no(결제 시작 순) 기준. 내 건 포함 확정 등록수가 정원 초과면 자동 취소.
//   결제 직전 체크(apply_pay)만으로는 동시 결제 시 같은 잔여석에 복수 통과 가능 → 여기서 최종 차단.
$over_track = '';
$ot_tracks = explode(',', isset($prev['apply_track']) ? $prev['apply_track'] : '');
foreach ($ot_tracks as $ot) {
  $ot = trim($ot);
  if ($ot === '') continue;
  $ote = sql_real_escape_string($ot);
  $cap = sql_fetch("select date1 from 2026_event_ticket where name='".$ote."'");
  if (!$cap) continue;                       // 정원 미설정 트랙(온라인 등) = 무제한
  $capN = (int)$cap['date1'];
  if ($capN <= 0) continue;                  // 0 = 정원 관리 안 함(결제 전 단계에서 이미 차단됨)
  $rk = sql_fetch("select count(*) c from cb_unreal_2026_event2_apply where apply_temp_yn='N' and apply_pay_status<>0 and apply_track like '%".$ote."%' and apply_no <= ".intval($apply_no));
  if ($rk && (int)$rk['c'] > $capN) { $over_track = $ot; break; }
}
if ($over_track !== '') {
  $is_vbank = (isset($rm["payMethod"]) && $rm["payMethod"] === "VBank");
  if (!$is_vbank) {                          // 카드/간편결제: 실제 결제됨 → 자동 환불
    require_once __DIR__ . '/_refund.php';
    $rf_tid = isset($rm['tid']) ? $rm['tid'] : (isset($prev['pay_tid']) ? $prev['pay_tid'] : '');
    $rf_pm  = isset($rm['payMethod']) ? $rm['payMethod'] : 'Card';
    @ufs_inicis_refund($rf_tid, $rf_pm, '트랙 정원 초과 자동취소');
  }
  sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0, pay_complete='N', refund_msg='트랙 정원 초과 자동취소', refund_date='".date('Y-m-d H:i:s')."' WHERE apply_no='".intval($apply_no)."'");
  back_alert("죄송합니다. 선택하신 트랙이 동시 접수로 마감되어 결제가 자동 취소되었습니다. 결제하신 금액은 환불 처리되며, 다른 트랙으로 다시 등록해 주세요.");
}

// ── 가상계좌: 입금대기 ──
if (isset($rm["payMethod"]) && $rm["payMethod"] === "VBank") {
  sql_query("UPDATE cb_unreal_2026_event2_apply SET pay_complete='N', apply_pay_status=1 WHERE apply_no='".intval($apply_no)."'");
  $_SESSION["VBANK_NUM"] = isset($rm["VACT_Num"]) ? $rm["VACT_Num"] : '';
  $_SESSION["VBANK_AMOUNT"] = isset($rm["TotPrice"]) ? $rm["TotPrice"] : '';
  // 가상계좌 입금안내 SMS (운영 전환 시 발송)
  ufs_send_vbank_sms(
    $prev['apply_user_name'], $prev['apply_user_phone'],
    $_SESSION["VBANK_NUM"],
    ($_SESSION["VBANK_AMOUNT"] !== '' ? $_SESSION["VBANK_AMOUNT"] : $prev['apply_product_price']),
    $prev['apply_product_code']
  );
  header("Location: ticket-complete.php?vbank=1&k=".rawurlencode(base64_encode($apply_no))); exit;
}

// ── 카드 등 즉시완료: QR 생성 ──
@mkdir(__DIR__."/qrdata", 0755);
if (file_exists("../unrealfest2025/phpqrcode/qrlib.php")) {
  include_once "../unrealfest2025/phpqrcode/qrlib.php";
  $png = __DIR__."/qrdata/".$apply_no.".png";
  $jpg = __DIR__."/qrdata/".$apply_no.".jpg";
  QRcode::png($apply_password, $png, 0, 7, 2);
  if (file_exists($png) && function_exists('imagecreatefrompng')) {
    $p = imagecreatefrompng($png);
    if ($p) {
      $j = imagecreatetruecolor(imagesx($p), imagesy($p));
      imagecopy($j, $p, 0,0,0,0, imagesx($p), imagesy($p));
      imagejpeg($j, $jpg, 100);
      imagedestroy($p); imagedestroy($j);
    }
  }
}

// 카드 등 즉시완료 → QR jpg 첨부 MMS 발송 (운영 전환 시 발송; QR 파일 생성 후 호출)
ufs_send_qr_mms($prev['apply_user_name'], $prev['apply_user_phone'], $apply_no, $prev['apply_product_code']);

header("Location: ticket-complete.php?k=".rawurlencode(base64_encode($apply_no)));
exit;
