<?php
/* Unreal Fest Seoul 2026 — INICIS 모바일 표준결제 승인 콜백 (apply_pay_mobile_return.php)
 * 포팅: 2025 inisis/INImobile_mo_return.php → 2026 스타일(apply_pay_return.php 와 동일 컬럼/플로우).
 * 모바일 결제 승인 → cb_unreal_2026_event2_apply UPDATE(pay_* 컬럼) → QR → ticket-complete.php.
 * apply_no 식별: $_SESSION["final_idx"] (apply_pay.php 에서 INSERT 직후 저장).
 * PHP 7.0 호환.
 */
require_once "../unrealfest2025/inisis/libs/properties.php";   // 모바일 승인 URL(getAuthUrl)
include_once "../common.php";
require_once __DIR__ . "/_sms.php";                            // QR MMS / 가상계좌 안내 SMS

function mback_alert($msg){
  echo '<script>alert("'.str_replace(array('"',"\n"),array('\\"',' '),$msg).'");history.back(-1);</script>'; exit;
}
function MR($k){ return isset($_REQUEST[$k]) ? $_REQUEST[$k] : ''; }
function moc($out,$k){ return isset($out[$k]) ? sql_real_escape_string($out[$k]) : ''; }

$prop = new properties();

$P_STATUS = MR("P_STATUS");
$P_TID    = MR("P_TID");

// ── 주문 식별 (세션) ──
$apply_no = isset($_SESSION["final_idx"]) ? $_SESSION["final_idx"] : '';
$apply_no = preg_replace('/[^0-9]/', '', (string)$apply_no);
$prev = $apply_no !== '' ? sql_fetch("select * from cb_unreal_2026_event2_apply where apply_no = '".intval($apply_no)."'") : null;

// 취소/실패 시 복귀할 등록폼 (PC closeUrl 과 동일 — 본인인증 세션 유지된 채 폼으로 복귀, 입력 localStorage 복원)
$pcode = $prev ? $prev['apply_product_code'] : '';
if ($pcode === 'NORMAL_ALL')      { $backUrl = 'ticket-all.php'; }
else if ($pcode === 'NORMAL_21')  { $backUrl = 'ticket-day.php?d=2'; }
else if ($pcode === 'NORMAL_20')  { $backUrl = 'ticket-day.php'; }
else                              { $backUrl = 'ticket-all.php'; }

// 알림 후 등록폼으로 복귀(history.back 금지 — 모바일에선 결제창/재전송 문제 + 본인인증 초기화)
function mgo($url, $msg){
  echo '<script>alert("'.str_replace(array('"',"\n","\r"),array('\\"',' ',' '),$msg).'");location.replace("'.$url.'");</script>'; exit;
}

// ── 1차 인증 결과 (취소/실패 → 등록폼 복귀) ──
if ($P_STATUS !== "00") {
  mgo($backUrl, "결제가 취소되었습니다. 입력하신 정보로 다시 시도할 수 있습니다.");
}
if ($apply_no === '' || !$prev) {
  mgo($backUrl, "입력 정보가 만료되었습니다. 다시 등록해 주세요.");
}
if ($prev['apply_temp_yn'] === 'N') {            // 이미 처리된 건 (중복 콜백)
  header("Location: ticket-complete.php?k=".rawurlencode(base64_encode($apply_no))); exit;
}

// ── 서버-투-서버 승인 요청 (모바일) ──
$idc_name = MR("idc_name");
$authUrl  = $prop->getAuthUrl($idc_name);
if (MR("P_REQ_URL") !== '' && strcmp($authUrl, MR("P_REQ_URL")) != 0) {
  $authUrl = MR("P_REQ_URL");                    // 수신된 P_REQ_URL 우선 (2025 동일 동작)
}
$id_merchant = substr($P_TID, 10, 10);           // P_TID 내 MID 구분
$data = array('P_MID' => $id_merchant, 'P_TID' => $P_TID);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $authUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_POST, 1);
$response = curl_exec($ch);
if ($response === false) { mback_alert("결제 승인 통신 오류가 발생했습니다."); }
curl_close($ch);

@sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".intval($apply_no)."','".str_replace("'","`",$response)."',now())");

parse_str($response, $out);
$flag_status = isset($out["P_STATUS"]) ? $out["P_STATUS"] : $P_STATUS;
if ($flag_status !== "00") {
  mback_alert("결제 승인에 실패했습니다. ".(isset($out["P_RMESG1"]) ? $out["P_RMESG1"] : ''));
}

// ── 승인 성공 → 확정 UPDATE (PC 와 동일한 pay_* 컬럼 사용; 취소/환불이 pay_tid·pay_paymethod 참조) ──
$is_vbank = (isset($out['P_TYPE']) && $out['P_TYPE'] === 'VBANK');
$pay_method = $is_vbank ? 'VBank' : 'Card';
$auth_dt = isset($out['P_AUTH_DT']) ? $out['P_AUTH_DT'] : '';
$apply_password = md5(str_replace("'","\\'",$prev['apply_user_email']));

$sql = "UPDATE cb_unreal_2026_event2_apply SET
  apply_password='".sql_real_escape_string($apply_password)."',
  free_yn='N', apply_pay_status=10,
  pay_resultCode='".moc($out,'P_STATUS')."',
  pay_resultMsg='".moc($out,'P_RMESG1')."',
  pay_tid='".moc($out,'P_TID')."',
  pay_moid='".moc($out,'P_OID')."',
  pay_totprice='".moc($out,'P_AMT')."',
  pay_goodname='".sql_real_escape_string($prev['apply_product_name'])."',
  pay_appldate='".sql_real_escape_string(substr($auth_dt,0,8))."',
  pay_appltime='".sql_real_escape_string(substr($auth_dt,8,6))."',
  pay_applnum='".moc($out,'P_AUTH_NO')."',
  pay_paymethod='".sql_real_escape_string($pay_method)."',
  pay_vact_num='".moc($out,'P_VACT_NUM')."',
  pay_vact_bankcode='".moc($out,'P_VACT_BANK_CODE')."',
  pay_vact_date='".(moc($out,'P_VACT_DATE').moc($out,'P_VACT_TIME'))."',
  pay_result_map='".str_replace("'","`",sql_real_escape_string($response))."',
  pay_complete='Y', apply_temp_yn='N'
  WHERE apply_no='".intval($apply_no)."'";
sql_query($sql);

// ── 트랙 정원 재확인 (동시 결제 초과 방지) → 초과 시 자동환불 ──
$over_track = '';
$ot_tracks = explode(',', isset($prev['apply_track']) ? $prev['apply_track'] : '');
foreach ($ot_tracks as $ot) {
  $ot = trim($ot);
  if ($ot === '') continue;
  $ote = sql_real_escape_string($ot);
  $cap = sql_fetch("select date1 from 2026_event_ticket where name='".$ote."'");
  if (!$cap) continue;
  $capN = (int)$cap['date1'];
  if ($capN <= 0) continue;
  $rk = sql_fetch("select count(*) c from cb_unreal_2026_event2_apply where apply_temp_yn='N' and apply_pay_status<>0 and apply_track like '%".$ote."%' and apply_no <= ".intval($apply_no));
  if ($rk && (int)$rk['c'] > $capN) { $over_track = $ot; break; }
}
if ($over_track !== '') {
  if (!$is_vbank) {                              // 카드: 실제 결제됨 → 자동 환불
    require_once __DIR__ . '/_refund.php';
    @ufs_inicis_refund(isset($out['P_TID']) ? $out['P_TID'] : '', $pay_method, '트랙 정원 초과 자동취소');
  }
  sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0, pay_complete='N', refund_msg='트랙 정원 초과 자동취소', refund_date='".date('Y-m-d H:i:s')."' WHERE apply_no='".intval($apply_no)."'");
  mback_alert("죄송합니다. 선택하신 트랙이 동시 접수로 마감되어 결제가 자동 취소되었습니다. 결제하신 금액은 환불 처리되며, 다른 트랙으로 다시 등록해 주세요.");
}

// ── 가상계좌: 입금대기 ──
if ($is_vbank) {
  sql_query("UPDATE cb_unreal_2026_event2_apply SET pay_complete='N', apply_pay_status=1 WHERE apply_no='".intval($apply_no)."'");
  $_SESSION["VBANK_NUM"]    = isset($out["P_VACT_NUM"]) ? $out["P_VACT_NUM"] : '';
  $_SESSION["VBANK_AMOUNT"] = isset($out["P_CSHR_AMT"]) ? $out["P_CSHR_AMT"] : (isset($out["P_AMT"]) ? $out["P_AMT"] : '');
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

// 카드 즉시완료 → QR jpg 첨부 MMS 발송 (QR 파일 생성 후 호출)
ufs_send_qr_mms($prev['apply_user_name'], $prev['apply_user_phone'], $apply_no, $prev['apply_product_code']);

// 카카오 Conversion API(서버 전송) — 광고 수신동의 시 Purchase 전환 (비차단)
require_once __DIR__ . '/_kakao_capi.php';
@ufs_kakao_capi_send($prev);

header("Location: ticket-complete.php?k=".rawurlencode(base64_encode($apply_no)));
exit;
