<?php
/* Unreal Fest Seoul 2026 — 오프라인 등록 INSERT + INICIS 결제요청 (apply_pay.php)
 * 포팅: 2025 _applicaiton_pay_ajax.php(INSERT) + application_step2.php(INICIS)
 * PHP 7.0 호환. 세션/DB는 www/common.php(= ../../common.php) 공유.
 */
include_once "../common.php";                 // sql_query/sql_fetch/sql_real_escape_string + 세션
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";
require_once __DIR__ . "/_pricing.php";   // 가격 단일 소스(얼리버드/정가 자동 전환)

// ── INICIS 모드 (검증 후 false 로 전환) ──
$INICIS_TEST = false;
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
$payment            = pp('payment');   // Card | kakaopay | naverpay | tosspay (결제수단 라디오)

// 상품 화이트리스트 (서버측 금액 재검증 — 위변조 방지)
// ※ 금액은 _pricing.php(ufs_ticket_price)에서 얼리버드/정가 자동 전환. 가격 변경은 거기 한 곳만.
$PRODUCTS = array(
  'NORMAL_ALL' => array('name'=>'언리얼 페스트 서울 2026 양일권(8월 20일~21일)'),
  'NORMAL_20'  => array('name'=>'언리얼 페스트 서울 2026 1일권(8월 20일)'),
  'NORMAL_21'  => array('name'=>'언리얼 페스트 서울 2026 1일권(8월 21일)'),
);
if (!isset($PRODUCTS[$apply_product_code])) { exit('잘못된 상품입니다.'); }
$apply_product_name  = $PRODUCTS[$apply_product_code]['name'];
$apply_product_price = (string)ufs_ticket_price($apply_product_code);  // 얼리버드/정가 자동

// ── 검증 ──
if ($apply_ci === '')          { exit('<script>alert("본인인증을 먼저 진행해주세요.");history.back();</script>'); }
if ($apply_user_email === '' || $apply_user_phone === '') { exit('<script>alert("이메일/연락처를 입력해주세요.");history.back();</script>'); }

// ── 본인인증 결과를 세션에 보존 ──
//   결제창에서 취소하고 등록폼(closeUrl)으로 돌아와도 _ticket_init 이 세션에서 복원 →
//   본인인증을 다시 하지 않아도 됨. (본인인증 결과가 폼 hidden 에만 있던 문제 보완)
$_SESSION['CI'] = $apply_ci;
$_SESSION['DI'] = $apply_di;
if ($apply_user_name  !== '') { $_SESSION['RSLT_NAME'] = $apply_user_name; }
if ($apply_user_phone !== '') { $_SESSION['TEL_NO']    = $apply_user_phone; }

// ── 중복 등록 체크 (확정건 기준) ──
// ※ 테스트모드($INICIS_TEST) 동안은 같은 본인인증/이메일로 반복 테스트가 가능하도록 건너뜀.
//   운영 전환($INICIS_TEST=false) 시 자동으로 1인 1등록 중복 차단이 복구됨.
// 중복 등록 차단 (테스트/운영 모두 적용). apply_pay_status<>0 : 취소(0)건은 제외 → 취소 후 재등록 허용.
$ci_esc = sql_real_escape_string($apply_ci);
$em_esc = sql_real_escape_string($apply_user_email);
$ph_esc = sql_real_escape_string($apply_user_phone);
$dup = sql_fetch("select count(*) as cnt from cb_unreal_2026_event2_apply where apply_ci = '$ci_esc' and apply_temp_yn = 'N' and apply_pay_status <> 0");
if ($dup && $dup['cnt'] > 0) { exit('<script>alert("이미 등록된 본인인증 정보입니다. 등록 확인 페이지에서 확인해주세요.");location.href="myticket.php";</script>'); }
$dup = sql_fetch("select count(*) as cnt from cb_unreal_2026_event2_apply where apply_user_email = '$em_esc' and apply_user_phone = '$ph_esc' and apply_temp_yn = 'N' and apply_pay_status <> 0");
if ($dup && $dup['cnt'] > 0) { exit('<script>alert("이미 등록된 이메일/연락처입니다.");location.href="myticket.php";</script>'); }

// ── 트랙 정원 체크 (오프라인만; 온라인은 무제한) — 결제 전 강제 ──
$_sel_tracks = array_filter(array_map('trim', explode(',', $apply_track)));
foreach ($_sel_tracks as $tk) {
    $tke = sql_real_escape_string($tk);
    $cap = sql_fetch("select date1 from 2026_event_ticket where name='$tke'");
    $capN = $cap ? (int)$cap['date1'] : 0;
    $reg = sql_fetch("select count(*) c from cb_unreal_2026_event2_apply where apply_temp_yn='N' and apply_pay_status<>0 and apply_track like '%$tke%'");
    $regN = $reg ? (int)$reg['c'] : 0;
    if ($capN > 0 && $regN >= $capN) {
        exit('<script>alert("선택하신 트랙의 정원이 마감되었습니다. 다른 트랙을 선택해 주세요.");history.back();</script>');
    }
}

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
$closeUrl   = $base."/".($apply_product_code === 'NORMAL_ALL' ? 'ticket-all.php' : 'ticket-day.php');

// 결제수단 라디오 → INICIS gopaymethod 매핑 (간편결제 직접호출)
//   간편결제 직접호출은 gopaymethod=onlyXXX + acceptmethod에 'cardonly' 가 함께 있어야
//   선택한 페이만 단독으로 뜬다 (없으면 통합 결제창이 노출됨). ref: INICIS_Stdpay#114
//   운영 MID에 각 페이 가맹 + '신용카드 직접호출' 설정이 되어 있어야 동작 (1588-4954).
//   cardonly 를 항상 부여: 일반카드는 신용/체크카드만(간편결제·계좌이체·가상계좌 제외),
//   간편결제(onlyXXX)는 선택한 페이만 단독 노출.
$gopaymethod  = "Card";                                       // 신용/체크카드
$acceptmethod = "HPP(1):below1000:centerCd(Y):cardonly";      // cardonly: 카드(또는 선택 페이) 외 노출 제외
if ($payment === 'kakaopay')      { $gopaymethod = "onlykakaopay"; }
else if ($payment === 'naverpay') { $gopaymethod = "onlynaverpay"; }
else if ($payment === 'tosspay')  { $gopaymethod = "onlytosspay"; }

// 모바일(mobile.inicis.com/smart/payment) 간편결제 직접호출.
//   모바일은 P_INI_PAYMENT 가 아니라 P_RESERVED 에 d_XXX=Y 를 추가하는 방식(카드 간편결제 페이지 직접호출).
//   ref: INICIS_Stdpay#88 (공식가이드/모바일). 구분자는 '&'. P_INI_PAYMENT 는 CARD 유지.
//   카카오=d_kakaopay=Y / 네이버=d_npay=Y / 토스=d_tosspay=Y.
//   ⚠️ 운영 MID에 '신용카드 직접호출' 설정 + 각 페이 가맹 필요(PC와 동일 조건, 1588-4954).
$mReserved = "below1000=Y&vbank_receipt=Y&centerCd=Y";
if ($payment === 'kakaopay')      { $mReserved = "d_kakaopay=Y&" . $mReserved; }
else if ($payment === 'naverpay') { $mReserved = "d_npay=Y&" . $mReserved; }
else if ($payment === 'tosspay')  { $mReserved = "d_tosspay=Y&" . $mReserved; }

// ── 디바이스 분기 ──
//   PC = INICIS 표준결제(INIStdPay, stdpay.inicis.com)
//   모바일 = INICIS 모바일 표준결제(mobile.inicis.com/smart/payment/, P_* 파라미터) — 별도 API
//   모바일 승인 콜백 = apply_pay_mobile_return.php (PC=apply_pay_return.php). apply_no 는 세션($_SESSION[final_idx]) 으로 식별.
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$is_mobile = (bool)preg_match('/Android|iPhone|iPad|iPod|Windows Phone|BlackBerry|IEMobile|Mobile/i', $ua);
$mNextUrl = $base."/apply_pay_mobile_return.php";

function ev($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ko"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>결제 진행 중 — Unreal Fest Seoul 2026</title>
<?php if (!$is_mobile): ?>
<script language="javascript" type="text/javascript" src="<?= $jsUrl ?>" charset="UTF-8"></script>
<?php endif; ?>
<style>body{background:#09090b;color:#fff;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}</style>
<?php include __DIR__.'/_wcs.php'; ?>
</head>
<?php if ($is_mobile): ?>
<body onload="document.getElementById('SendPayForm_mobile').submit();">
<div>결제 화면으로 이동 중입니다...</div>
<form name="SendPayForm_mobile" id="SendPayForm_mobile" method="post" action="https://mobile.inicis.com/smart/payment/" accept-charset="euc-kr" style="display:none">
  <input type="hidden" name="P_INI_PAYMENT" value="CARD">
  <input type="hidden" name="P_MID" value="<?= ev($mid) ?>">
  <input type="hidden" name="P_OID" value="<?= ev($oid) ?>">
  <input type="hidden" name="P_AMT" value="<?= ev($price) ?>">
  <input type="hidden" name="P_GOODS" value="<?= ev($apply_product_name) ?>">
  <input type="hidden" name="P_UNAME" value="<?= ev($apply_user_name) ?>">
  <input type="hidden" name="P_MOBILE" value="<?= ev($apply_user_phone) ?>">
  <input type="hidden" name="P_EMAIL" value="<?= ev($apply_user_email) ?>">
  <input type="hidden" name="P_NEXT_URL" value="<?= ev($mNextUrl) ?>">
  <input type="hidden" name="P_NOTI_URL" value="<?= ev($mNextUrl) ?>">
  <input type="hidden" name="P_CHARSET" value="utf8">
  <input type="hidden" name="P_RESERVED" value="<?= ev($mReserved) ?>">
  <input type="hidden" name="merchantData" value="<?= ev($apply_no) ?>">
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
  <input type="hidden" name="goodname" value="<?= ev($apply_product_name) ?>">
  <input type="hidden" name="buyername" value="<?= ev($apply_user_name) ?>">
  <input type="hidden" name="buyertel" value="<?= ev($apply_user_phone) ?>">
  <input type="hidden" name="buyeremail" value="<?= ev($apply_user_email) ?>">
  <input type="hidden" name="gopaymethod" value="<?= ev($gopaymethod) ?>">
  <input type="hidden" name="acceptmethod" value="<?= ev($acceptmethod) ?>">
  <input type="hidden" name="merchantData" value="<?= ev($apply_no) ?>">
  <input type="hidden" name="returnUrl" value="<?= ev($returnUrl) ?>">
  <input type="hidden" name="closeUrl" value="<?= ev($closeUrl) ?>">
</form>
</body></html>
<?php endif; ?>
