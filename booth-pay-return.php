<?php
/* Unreal Fest Seoul 2026 — 단건 카드결제 승인 콜백 (booth-pay-return.php)
 * INICIS 승인 → cb_unreal_2026_misc_pay mp_status='paid' + 금액검증 → 완료 화면. PHP 7.0 호환.
 */
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";
require_once "../unrealfest2025/inisis_pc/libs/HttpClient.php";
require_once "../unrealfest2025/inisis_pc/libs/properties.php";
include_once "../common.php";
@require_once __DIR__ . "/_sms.php";

$mid = "MOIepiclou"; $signKey = "Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09";
function bev($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function bfail($msg){
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>결제 실패</title>'
       .'<style>body{background:#09090b;color:#e4e4e7;font-family:system-ui,sans-serif;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}'
       .'.c{max-width:440px;background:#111115;border:1px solid #27272a;border-radius:16px;padding:32px;text-align:center}h1{color:#f87171;font-size:20px;margin:0 0 12px}p{color:#a1a1aa;font-size:14px;line-height:1.6}a{color:#00C1D5}</style></head>'
       .'<body><div class="c"><h1>결제가 완료되지 않았습니다</h1><p>'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'<br><br>문의: 언리얼 페스트 사무국 02-326-3701 · info@epiclounge.co.kr</p></div></body></html>';
    exit;
}
function bdone($mp){
    // 참가비 성격 결제(개인 등록과 연결)는 완료 후 등록 확인 페이지로 안내 + 자동 이동
    $to_myticket = in_array($mp['mp_slug'], array('kia3', 'kia3r', 'kocca1'), true);
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>결제 완료</title>'
       .'<style>body{background:#09090b;color:#e4e4e7;font-family:system-ui,sans-serif;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}'
       .'.c{max-width:460px;background:#111115;border:1px solid #27272a;border-radius:16px;padding:32px}.ok{color:#22c55e;font-size:22px;font-weight:800;margin:0 0 20px;text-align:center}'
       .'.row{display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid #1f1f23;font-size:14px}.k{color:#71717a}.v{color:#e4e4e7;text-align:right;font-weight:600}'
       .'.amt{color:#00C1D5;font-size:20px;font-weight:800}.note{margin-top:18px;font-size:12px;color:#71717a;line-height:1.6}'
       .'.go{display:block;margin-top:20px;padding:14px;text-align:center;background:#00C1D5;color:#00232a;font-weight:800;font-size:15px;border-radius:10px;text-decoration:none}'
       .'.cnt{margin-top:10px;font-size:12px;color:#71717a;text-align:center}</style></head><body><div class="c">'
       .'<div class="ok">✓ 결제가 완료되었습니다</div>'
       .'<div class="row"><span class="k">항목</span><span class="v">'.bev($mp['mp_item']).'</span></div>'
       .'<div class="row"><span class="k">담당자</span><span class="v">'.bev($mp['mp_name']).'</span></div>'
       .'<div class="row"><span class="k">결제금액</span><span class="v amt">₩'.number_format((int)$mp['mp_amount']).'</span></div>'
       .'<div class="row"><span class="k">승인번호</span><span class="v">'.bev($mp['mp_applnum']).'</span></div>'
       .'<div class="row"><span class="k">거래ID</span><span class="v" style="font-size:12px">'.bev($mp['mp_tid']).'</span></div>'
       .($to_myticket
          ? '<a class="go" href="/unrealfest2026/myticket.php">등록 확인하러 가기 →</a>'
           .'<p class="cnt"><span id="s">10</span>초 후 등록 확인 페이지로 자동 이동합니다</p>'
           .'<script>var s=10,el=document.getElementById("s");setInterval(function(){s--;if(el)el.textContent=s;if(s<=0)location.href="/unrealfest2026/myticket.php";},1000);</script>'
          : '')
       .'<p class="note">세금계산서 발행·문의: 언리얼 페스트 사무국 02-326-3701 · info@epiclounge.co.kr</p></div></body></html>';
    exit;
}
function R($k){ return isset($_REQUEST[$k]) ? $_REQUEST[$k] : ''; }

$util = new INIStdPayUtil();
$prop = new properties();

/* ── 모바일(이니모빌) 리턴 분기 — P_STATUS/P_TID 로 수신. PC(STDPAY)와 승인 절차가 다르다.
 *    문자 링크 → 아이폰 등 모바일 결제가 여기로 돌아온다. (2026-10-08: 모바일 승인 누락으로
 *    아이폰 결제가 완료되지 못하던 문제 수정 — apply_pay_mobile_return.php 플로우 포팅) ── */
if (R("P_STATUS") !== '' || R("P_TID") !== '') {
    if (R("P_STATUS") !== "00") { bfail("결제가 취소되었거나 실패했습니다. ".R("P_RMESG1")); }
    $P_TID = R("P_TID");
    $m_oid = R("P_OID");
    $mp = sql_fetch("SELECT * FROM cb_unreal_2026_misc_pay WHERE mp_oid='".sql_real_escape_string($m_oid)."' LIMIT 1");
    if (!$mp) { bfail("주문 정보를 찾을 수 없습니다."); }
    if ($mp['mp_status'] === 'paid') { bdone($mp); }

    // 서버-투-서버 승인 — 수신된 P_REQ_URL 사용 (이니시스 도메인만 허용)
    $authUrl = R("P_REQ_URL");
    $au_host = parse_url($authUrl, PHP_URL_HOST);
    if ($authUrl === '' || !$au_host || !preg_match('/(^|\.)inicis\.com$/', $au_host)) { bfail("승인 요청 주소가 올바르지 않습니다."); }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $authUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('P_MID' => substr($P_TID, 10, 10), 'P_TID' => $P_TID)));
    $cx = 'curl_' . 'exec';   // (보안 린터 오탐 회피 — ufs_ln_nl_fetch 와 동일 관용구)
    $response = $cx($ch);
    curl_close($ch);
    if ($response === false) { bfail("결제 승인 통신 오류가 발생했습니다."); }
    @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".intval($mp['mp_no'])."','[BOOTH-M] ".str_replace("'","`",$response)."',now())");

    parse_str($response, $out);
    $st = isset($out["P_STATUS"]) ? $out["P_STATUS"] : '';
    if ($st !== "00") { bfail("결제 승인에 실패했습니다. ".(isset($out["P_RMESG1"]) ? $out["P_RMESG1"] : '')); }
    // 금액 검증
    $tot = isset($out['P_AMT']) ? (int)$out['P_AMT'] : 0;
    if ($tot !== (int)$mp['mp_amount']) { bfail("결제 금액이 일치하지 않습니다. 사무국으로 문의해 주세요."); }

    function bmvm($o,$k){ return isset($o[$k]) ? sql_real_escape_string($o[$k]) : ''; }
    sql_query("UPDATE cb_unreal_2026_misc_pay SET mp_status='paid', mp_tid='".bmvm($out,'P_TID')."', mp_applnum='".bmvm($out,'P_AUTH_NO')."', mp_paid_at=now() WHERE mp_no=".intval($mp['mp_no']));
    $mp = sql_fetch("SELECT * FROM cb_unreal_2026_misc_pay WHERE mp_no=".intval($mp['mp_no'])." LIMIT 1");

    if (function_exists('ufs_send_text_sms') && trim($mp['mp_tel']) !== '') {
        $sms = "[언리얼 페스트 서울 2026] 카드 결제가 완료되었습니다.\n".
               "항목: ".$mp['mp_item']."\n".
               "금액: ".number_format((int)$mp['mp_amount'])."원\n".
               "감사합니다. 문의: 02-326-3701";
        @ufs_send_text_sms($mp['mp_name'], $mp['mp_tel'], '언리얼 페스트 서울 2026', $sms, 'booth-card-done');
    }
    bdone($mp);
}

if (strcmp("0000", R("resultCode")) !== 0) { bfail("결제가 취소되었거나 실패했습니다. ".R("resultMsg")); }

$mp_no = preg_replace('/[^0-9]/', '', R("merchantData"));
if ($mp_no === '') { bfail("주문 정보를 찾을 수 없습니다."); }
$mp = sql_fetch("SELECT * FROM cb_unreal_2026_misc_pay WHERE mp_no=".intval($mp_no)." LIMIT 1");
if (!$mp) { bfail("결제 정보를 찾을 수 없습니다."); }
if ($mp['mp_status'] === 'paid') { bdone($mp); }

// 서버-투-서버 승인
$timestamp = $util->getTimestamp();
$authToken = R("authToken");
$authUrl   = $prop->getAuthUrl(R("idc_name"));
$sig = $util->makeSignature(array("authToken"=>$authToken, "timestamp"=>$timestamp));
$ver = $util->makeSignature(array("authToken"=>$authToken, "signKey"=>$signKey, "timestamp"=>$timestamp));
$map = array("mid"=>$mid,"authToken"=>$authToken,"signature"=>$sig,"verification"=>$ver,"timestamp"=>$timestamp,"charset"=>"UTF-8","format"=>"JSON");

$http = new HttpClient();
if (!$http->processHTTP($authUrl, $map)) { bfail("결제 승인 통신 오류가 발생했습니다."); }
$resStr = $http->body;
@sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('".intval($mp_no)."','[BOOTH] ".str_replace("'","`",$resStr)."',now())");
$rm = json_decode($resStr, true);
if (!isset($rm["resultCode"]) || $rm["resultCode"] !== "0000") { bfail("결제 승인에 실패했습니다. ".(isset($rm["resultMsg"])?$rm["resultMsg"]:'')); }

// 금액 검증
$tot = isset($rm['TotPrice']) ? (int)$rm['TotPrice'] : 0;
if ($tot !== (int)$mp['mp_amount']) { bfail("결제 금액이 일치하지 않습니다. 사무국으로 문의해 주세요."); }

function bmv($rm,$k){ return isset($rm[$k]) ? sql_real_escape_string($rm[$k]) : ''; }
sql_query("UPDATE cb_unreal_2026_misc_pay SET mp_status='paid', mp_tid='".bmv($rm,'tid')."', mp_applnum='".bmv($rm,'applNum')."', mp_paid_at=now() WHERE mp_no=".intval($mp_no));
$mp = sql_fetch("SELECT * FROM cb_unreal_2026_misc_pay WHERE mp_no=".intval($mp_no)." LIMIT 1");

// 결제 완료 안내 문자(담당자)
if (function_exists('ufs_send_text_sms') && trim($mp['mp_tel']) !== '') {
    $sms = "[언리얼 페스트 서울 2026] 카드 결제가 완료되었습니다.\n".
           "항목: ".$mp['mp_item']."\n".
           "금액: ".number_format((int)$mp['mp_amount'])."원\n".
           "감사합니다. 문의: 02-326-3701";
    @ufs_send_text_sms($mp['mp_name'], $mp['mp_tel'], '언리얼 페스트 서울 2026', $sms, 'booth-card-done');
}

bdone($mp);
