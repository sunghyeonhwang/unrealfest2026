<?php
/* Unreal Fest Seoul 2026 — 초청(부분할인) 카드결제 승인 콜백 (ticket-invite-pay-return.php) [M3]
 * INICIS 승인 → 배치(oid) 홀드행 status 1→10 승급 + QR. 실패/취소 → 홀드 해제 + sc_used 원복.
 * 단체 결제 콜백(ticket-group-pay-return.php) 구조 재사용. PHP 7.0 호환.
 */
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";
require_once "../unrealfest2025/inisis_pc/libs/HttpClient.php";
require_once "../unrealfest2025/inisis_pc/libs/properties.php";
include_once "../common.php";
require_once __DIR__ . '/_invite_apply.php';

$mid="MOIepiclou"; $signKey="Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09";
function iback_alert($msg){ echo '<script>alert("'.str_replace(array('"',"\n"),array('\\"',' '),$msg).'");location.href="ticket-invite.php";</script>'; exit; }
function R($k){ return isset($_REQUEST[$k]) ? $_REQUEST[$k] : ''; }

$util = new INIStdPayUtil();
$prop = new properties();

$oid = trim(R("merchantData"));
$sm  = ufs_invite_oid_summary($oid);
if (!$sm || (int)$sm['cnt'] <= 0) { iback_alert("주문 정보를 찾을 수 없습니다."); }
$rep = sql_fetch("SELECT apply_no, apply_user_email, apply_password, apply_speaker_code FROM cb_unreal_2026_event2_apply WHERE apply_no=".(int)$sm['rep_no']." LIMIT 1");
if (!$rep) { iback_alert("등록 정보를 찾을 수 없습니다."); }
$code = $rep['apply_speaker_code'];

// 이미 결제완료(승급 10) → 완료 페이지
if ((int)$sm['st'] === 10) { header("Location: ticket-invite-complete.php?a=".(int)$rep['apply_no']."&t=".rawurlencode($rep['apply_password'])); exit; }

// 결제 취소/실패 → 홀드 해제 + sc_used 원복
if (strcmp("0000", R("resultCode")) !== 0) {
  ufs_invite_release_oid($oid, $code, (int)$sm['cnt']);
  iback_alert("결제가 취소되었거나 실패했습니다. ".R("resultMsg"));
}

// 서버-투-서버 승인
$timestamp = $util->getTimestamp();
$authToken = R("authToken");
$authUrl   = $prop->getAuthUrl(R("idc_name"));
$sig = $util->makeSignature(array("authToken"=>$authToken, "timestamp"=>$timestamp));
$ver = $util->makeSignature(array("authToken"=>$authToken, "signKey"=>$signKey, "timestamp"=>$timestamp));
$map = array("mid"=>$mid,"authToken"=>$authToken,"signature"=>$sig,"verification"=>$ver,"timestamp"=>$timestamp,"charset"=>"UTF-8","format"=>"JSON");

$http = new HttpClient();
if (!$http->processHTTP($authUrl, $map)) { ufs_invite_release_oid($oid, $code, (int)$sm['cnt']); iback_alert("결제 승인 통신 오류가 발생했습니다."); }
$resStr = $http->body;
@sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".(int)$rep['apply_no']."','[INVITE] ".str_replace("'","`",$resStr)."',now())");
$rm = json_decode($resStr, true);
if (!isset($rm["resultCode"]) || $rm["resultCode"] !== "0000") { ufs_invite_release_oid($oid, $code, (int)$sm['cnt']); iback_alert("결제 승인에 실패했습니다. ".(isset($rm["resultMsg"])?$rm["resultMsg"]:'')); }

// 금액 검증 (홀드 합계와 일치) — 불일치 시 금액이 이미 승인됐으므로 자동해제 금지(사무국 확인)
$tot = isset($rm['TotPrice']) ? (int)$rm['TotPrice'] : 0;
if ($tot !== (int)$sm['amt']) { iback_alert("결제 금액이 일치하지 않습니다. 사무국으로 문의해 주세요."); }

// 홀드 → 결제완료 승급 + QR
$n = ufs_invite_reflect_oid($oid);
if ($n <= 0) { iback_alert("등록 반영 중 오류가 발생했습니다. 사무국으로 문의해 주세요."); }

header("Location: ticket-invite-complete.php?a=".(int)$rep['apply_no']."&t=".rawurlencode($rep['apply_password']));
exit;
