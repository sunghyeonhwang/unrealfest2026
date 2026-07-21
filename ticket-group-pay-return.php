<?php
/* Unreal Fest Seoul 2026 — 단체 카드결제 승인 콜백 (ticket-group-pay-return.php) [Phase 3]
 * INICIS 승인 → cb_unreal_2026_group pay_status='paid' + 쿠폰 사용횟수 증가 → 완료 페이지.
 * PHP 7.0 호환.
 */
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";
require_once "../unrealfest2025/inisis_pc/libs/HttpClient.php";
require_once "../unrealfest2025/inisis_pc/libs/properties.php";
include_once "../common.php";
require_once __DIR__ . "/_sms.php";   // 결제완료 LMS

$mid="MOIepiclou"; $signKey="Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09";

function gback_alert($msg){ echo '<script>alert("'.str_replace(array('"',"\n"),array('\\"',' '),$msg).'");location.href="ticket-group.php";</script>'; exit; }
function R($k){ return isset($_REQUEST[$k]) ? $_REQUEST[$k] : ''; }

$util = new INIStdPayUtil();
$prop = new properties();

if (strcmp("0000", R("resultCode")) !== 0) { gback_alert("결제가 취소되었거나 실패했습니다. ".R("resultMsg")); }

$grp_no = preg_replace('/[^0-9]/', '', R("merchantData"));
if ($grp_no === '') { gback_alert("주문 정보를 찾을 수 없습니다."); }
$g = sql_fetch("SELECT * FROM cb_unreal_2026_group WHERE grp_no=".intval($grp_no));
if (!$g) { gback_alert("등록 정보를 찾을 수 없습니다."); }
if ($g['pay_status'] === 'paid') { header("Location: ticket-group-complete.php?g=".intval($grp_no)."&t=".rawurlencode($g['grp_code'])); exit; }

// 서버-투-서버 승인
$timestamp = $util->getTimestamp();
$authToken = R("authToken");
$authUrl   = $prop->getAuthUrl(R("idc_name"));
$sig = $util->makeSignature(array("authToken"=>$authToken, "timestamp"=>$timestamp));
$ver = $util->makeSignature(array("authToken"=>$authToken, "signKey"=>$signKey, "timestamp"=>$timestamp));
$map = array("mid"=>$mid,"authToken"=>$authToken,"signature"=>$sig,"verification"=>$ver,"timestamp"=>$timestamp,"charset"=>"UTF-8","format"=>"JSON");

$http = new HttpClient();
if (!$http->processHTTP($authUrl, $map)) { gback_alert("결제 승인 통신 오류가 발생했습니다."); }
$resStr = $http->body;
@sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".intval($grp_no)."','[GROUP] ".str_replace("'","`",$resStr)."',now())");
$rm = json_decode($resStr, true);
if (!isset($rm["resultCode"]) || $rm["resultCode"] !== "0000") { gback_alert("결제 승인에 실패했습니다. ".(isset($rm["resultMsg"])?$rm["resultMsg"]:'')); }

// 금액 검증
$tot = isset($rm['TotPrice']) ? (int)$rm['TotPrice'] : 0;
if ($tot !== (int)$g['total_amount']) { gback_alert("결제 금액이 일치하지 않습니다. 사무국으로 문의해 주세요."); }

function grmv($rm,$k){ return isset($rm[$k]) ? sql_real_escape_string($rm[$k]) : ''; }
sql_query("UPDATE cb_unreal_2026_group SET pay_status='paid', pay_tid='".grmv($rm,'tid')."', pay_applnum='".grmv($rm,'applNum')."', paid_at=now() WHERE grp_no=".intval($grp_no));
if ($g['coupon_code'] !== '' && (int)$g['discount_pct'] > 0) { // 실제 할인 적용건만 사용횟수 증가(유령증가 방지)
    @sql_query("UPDATE cb_unreal_2026_coupon SET cp_used=cp_used+1 WHERE cp_code='".sql_real_escape_string($g['coupon_code'])."'");
}

// 등록현황(apply) 반영 + QR
require_once __DIR__ . '/_group_apply.php';
@ufs_group_reflect($grp_no);

// ③ 카드 결제완료 → 대표자 결제 확인 LMS
$lms = "[언리얼 페스트 서울 2026] 단체 등록 결제가 완료되었습니다.\n".
       "접수번호: ".$g['grp_code']." · ".(int)$g['headcount']."명\n".
       "결제금액: ".number_format((int)$g['total_amount'])."원\n".
       "감사합니다. 문의: 02-326-3701";
@ufs_send_text_sms($g['rep_name'], $g['rep_phone'], '언리얼 페스트 서울 2026', $lms, 'group-card-done');

// ④ 등록자 전원에게 개인 QR 첨부 MMS (무통장 입금확인과 동일 정책)
if (function_exists('ufs_send_group_qr_mms')) {
    $ms = sql_query("SELECT * FROM cb_unreal_2026_group_member WHERE grp_no=".intval($grp_no));
    if ($ms) { while ($m = $ms->fetch_assoc()) {
        if (trim($m['phone']) === '') continue;
        if ((int)$m['apply_no'] > 0) @ufs_send_group_qr_mms($m['name'], $m['phone'], $m['apply_no'], $m['ticket'], $g['rep_company']);
    }}
}

header("Location: ticket-group-complete.php?g=".intval($grp_no)."&t=".rawurlencode($g['grp_code']));
exit;
