<?php
/* Unreal Fest Seoul 2026 — 해외(영문) 등록 INSERT + INICIS 해외카드 결제요청 (apply_pay_en.php)
 * ticket-en.php POST 수신 → 검증(본인인증 없음·이메일 중복) → 대기(temp) INSERT → INIStdPay 결제창 렌더.
 * 해외카드: PC=자동 노출 / 모바일=P_RESERVED 에 global_visa3d=Y. KRW 청구. 확정=apply_pay_en_return.php.
 * 쿠폰: 부분할인=할인 KRW / 100%=무료 즉시확정. PHP 7.0.
 */
require __DIR__ . '/_ticket_init.php';                          // common, $UFS_TRACKS, ufs_ticket_orig, _pricing(+_coupon)
require_once __DIR__ . '/_group_apply.php';                     // ufs_group_make_qr (100% 무료 확정 시 QR)
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";
if (is_file(__DIR__.'/_dodo_mail.php')) require_once __DIR__.'/_dodo_mail.php';   // ufs_dodo_confirm_mail (영문 메일)
if (is_file(__DIR__.'/_resend.php'))    require_once __DIR__.'/_resend.php';      // ufs_resend_send

$PRODNAME = array('NORMAL_ALL'=>'2-Day Pass (Aug 20-21)','NORMAL_20'=>'1-Day Pass (Aug 20)','NORMAL_21'=>'1-Day Pass (Aug 21)');
$T2P = array('ALL'=>'NORMAL_ALL','DAY1'=>'NORMAL_20','DAY2'=>'NORMAL_21');

// INICIS 운영 MID — 국내와 동일(해외카드 가맹 승인). 테스트 시 INIpayTest 로 교체.
$INICIS_TEST = false;
if ($INICIS_TEST) { $mid="INIpayTest"; $signKey="SU5JTElURV9UUklQTEVERVNfS0VZU1RS"; $jsUrl="https://stgstdpay.inicis.com/stdjs/INIStdPay.js"; }
else             { $mid="MOIepiclou"; $signKey="Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09"; $jsUrl="https://stdpay.inicis.com/stdjs/INIStdPay.js"; }

function pay_en_fail($msg) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registration</title>'
       . '<style>body{font-family:system-ui,sans-serif;background:#09090b;color:#e4e4e7;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}'
       . '.b{max-width:460px;padding:32px;text-align:center}a{color:#00C1D5}</style></head><body><div class="b">'
       . '<h2 style="color:#fff">We couldn\'t start your payment</h2>'
       . '<p style="color:#a1a1aa">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p>'
       . '<p><a href="ticket-en.php">← Back to registration</a></p></div></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ticket-en.php'); exit; }

$gp = function($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; };
$name=$gp('apply_user_name'); $email=$gp('apply_user_email'); $phone=$gp('apply_user_phone');
$job=$gp('apply_user_job'); $company=$gp('apply_user_company'); $depart=$gp('apply_user_depart');
$grade=$gp('apply_user_grade'); $ex1=$gp('apply_user_ex1'); $tshirt=$gp('tshirt');
$ticket=$gp('ticket'); $d1=$gp('day1track'); $d2=$gp('day2track');
$agree=$gp('agree_req'); $mkt = ($gp('agree_mkt')!=='') ? '1' : '0';
$pcode = isset($T2P[$ticket]) ? $T2P[$ticket] : '';
$tracks = array();

// ── 검증 (본인인증 없음) ──
if ($agree!=='on' && $agree!=='Y' && $agree!=='1') pay_en_fail('Please agree to the required terms.');
if ($name===''||$email===''||$phone===''||$company===''||$depart===''||$job===''||$grade===''||$ex1===''||$tshirt==='') pay_en_fail('Please complete all required fields.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) pay_en_fail('Please enter a valid email address.');
if ($pcode==='') pay_en_fail('Please select a ticket.');
if ($pcode==='NORMAL_ALL')      { if(!isset($UFS_TRACKS[1][$d1])) pay_en_fail('Please select a Day 1 track.'); if(!isset($UFS_TRACKS[2][$d2])) pay_en_fail('Please select a Day 2 track.'); $tracks=array($d1,$d2); }
elseif ($pcode==='NORMAL_20')   { if(!isset($UFS_TRACKS[1][$d1])) pay_en_fail('Please select a Day 1 track.'); $tracks=array($d1); }
elseif ($pcode==='NORMAL_21')   { if(!isset($UFS_TRACKS[2][$d2])) pay_en_fail('Please select a Day 2 track.'); $tracks=array($d2); }

// ── 쿠폰(선택) — 부분할인=할인 KRW / 100%=무료. 권위 검증. ──
$ccode = strtoupper($gp('coupon_code'));
$cpct = 0; $cstore = '';
if ($ccode !== '') {
    $ck = function_exists('ufs_coupon_check') ? ufs_coupon_check($ccode) : array('ok'=>false,'percent'=>0);
    if (empty($ck['ok'])) pay_en_fail('Invalid or unavailable coupon code.');
    $cpct = (int)$ck['percent']; $cstore = $ck['code'];
}

// ── 중복(완료 등록 이메일) ──
$dup = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE apply_user_email='".sql_real_escape_string($email)."' AND apply_temp_yn='N' AND apply_pay_status<>0");
if ($dup && (int)$dup['c']>0) pay_en_fail('This email is already registered.');

// ── 트랙 정원 ──
foreach ($tracks as $tk) {
    $cap = sql_fetch("SELECT date1 FROM 2026_event_ticket WHERE name='".sql_real_escape_string($tk)."'");
    $capN = $cap ? (int)$cap['date1'] : 0;
    if ($capN > 0) {
        $reg = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE apply_temp_yn='N' AND apply_pay_status<>0 AND apply_track LIKE '%".sql_real_escape_string($tk)."%'");
        if ($reg && (int)$reg['c'] >= $capN) pay_en_fail('The selected track is full. Please choose another track.');
    }
}

// ── 금액(KRW) — 정가 기준, 쿠폰 부분할인 시 할인 KRW ──
$krw = function_exists('ufs_ticket_orig') ? (int)ufs_ticket_orig($pcode) : 0;
if ($cpct > 0 && $cpct < 100 && function_exists('ufs_coupon_apply_price')) {
    $krw = (int)ufs_coupon_apply_price($krw, $cpct);
}
$f = function($v){ return sql_real_escape_string(strip_tags((string)$v)); };
$pw = md5(str_replace("'","\\'",$email));
$track_str = implode(',', $tracks);

// ── 100% 쿠폰 → 무료 즉시 확정(결제 없음) ──
if ($cpct >= 100) {
    sql_query("INSERT INTO cb_unreal_2026_event2_apply
       (apply_user_name,apply_user_email,apply_user_phone,apply_user_job,apply_user_company,apply_user_depart,apply_user_grade,apply_user_ex1,
        apply_product_code,apply_product_name,apply_product_price,apply_tshirt,apply_track,apply_user_event_agree,apply_coupon_code,apply_coupon_pct,
        apply_password,apply_ci,apply_di,apply_pay_status,pay_complete,free_yn,apply_temp_yn,apply_group_code,pay_paymethod,apply_reg_datetime)
       VALUES ('".$f($name)."','".$f($email)."','".$f($phone)."','".$f($job)."','".$f($company)."','".$f($depart)."','".$f($grade)."','".$f($ex1)."',
        '".$f($pcode)."','".$f($PRODNAME[$pcode])."','0','".$f($tshirt)."','".$f($track_str)."','".$mkt."','".$f($cstore)."',".$cpct.",
        '".sql_real_escape_string($pw)."','','',10,'Y','Y','N','','coupon_free',now())");
    $r = sql_query("SELECT LAST_INSERT_ID() as idx")->fetch_array();
    $apply_no = (int)$r['idx'];
    if ($apply_no <= 0) pay_en_fail('Registration failed. Please try again.');
    if (function_exists('ufs_coupon_use')) ufs_coupon_use($cstore);
    if (function_exists('ufs_group_make_qr')) ufs_group_make_qr($apply_no, $pw);
    // 영문 완료 메일 (무료·비차단)
    if (function_exists('ufs_dodo_confirm_mail') && function_exists('ufs_resend_send')) {
        $frow = sql_fetch("SELECT * FROM cb_unreal_2026_event2_apply WHERE apply_no=".$apply_no);
        if ($frow) { $m = ufs_dodo_confirm_mail($frow, 'FREE (100% coupon)'); @ufs_resend_send($frow['apply_user_email'], $m['subject'], $m['html'], '', $m['text']); }
    }
    header('Location: ticket-en-complete.php?apply_no='.$apply_no);
    exit;
}

// ── 대기(temp) 등록건 INSERT — 청구 KRW($krw) 저장, 확정 전 정원/중복 집계 제외(temp_yn=Y). ──
sql_query("INSERT INTO cb_unreal_2026_event2_apply
   (apply_user_name,apply_user_email,apply_user_phone,apply_user_job,apply_user_company,apply_user_depart,apply_user_grade,apply_user_ex1,
    apply_product_code,apply_product_name,apply_product_price,apply_tshirt,apply_track,apply_user_event_agree,apply_coupon_code,apply_coupon_pct,
    apply_password,apply_ci,apply_di,apply_pay_status,pay_complete,free_yn,apply_temp_yn,apply_group_code,pay_paymethod,apply_reg_datetime)
   VALUES ('".$f($name)."','".$f($email)."','".$f($phone)."','".$f($job)."','".$f($company)."','".$f($depart)."','".$f($grade)."','".$f($ex1)."',
    '".$f($pcode)."','".$f($PRODNAME[$pcode])."','".$krw."','".$f($tshirt)."','".$f($track_str)."','".$mkt."','".$f($cstore)."',".$cpct.",
    '".sql_real_escape_string($pw)."','','',0,'N','N','Y','','inicis_pending',now())");
$r = sql_query("SELECT LAST_INSERT_ID() as idx")->fetch_array();
$apply_no = (int)$r['idx'];
if ($apply_no <= 0) pay_en_fail('Registration failed. Please try again.');

// ── INICIS 결제 요청 전문 ──
$util      = new INIStdPayUtil();
$timestamp = $util->getTimestamp();
$mKey      = $util->makeHash($signKey, "sha256");
$oid       = $mid . "_" . $timestamp;
$price     = (string)$krw;
$sign      = $util->makeSignature(array("oid"=>$oid, "price"=>$price, "timestamp"=>$timestamp));
$sign2     = $util->makeSignature(array("oid"=>$oid, "price"=>$price, "signKey"=>$signKey, "timestamp"=>$timestamp));
$base      = "https://".$_SERVER['HTTP_HOST']."/v3/unrealfest2026";
$returnUrl = $base."/apply_pay_en_return.php";
$closeUrl  = $base."/ticket-en.php";
$goodname  = $PRODNAME[$pcode];

// 해외카드(카드 단독). PC=결제창 영문(LANG(ENGLISH)). 모바일은 P_RESERVED 에 global_visa3d=Y (해외카드 노출).
$gopaymethod  = "Card";
$acceptmethod = "LANG(ENGLISH):HPP(1):below1000:centerCd(Y):cardonly";   // LANG(ENGLISH)=영문 결제창(PC). 필요시 FORCARD 추가 검토
$mReserved    = "global_visa3d=Y&below1000=Y&centerCd=Y";

$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$is_mobile = (bool)preg_match('/Android|iPhone|iPad|iPod|Windows Phone|BlackBerry|IEMobile|Mobile/i', $ua);
$mNextUrl = $base."/apply_pay_en_return.php";

function ev($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Processing payment — Unreal Fest Seoul 2026</title>
<?php if (!$is_mobile): ?>
<script language="javascript" type="text/javascript" src="<?= $jsUrl ?>" charset="UTF-8"></script>
<?php endif; ?>
<style>body{background:#09090b;color:#fff;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}</style>
<?php include __DIR__ . '/_favicon.php'; ?>
</head>
<?php if ($is_mobile): ?>
<body onload="document.getElementById('SendPayForm_mobile').submit();">
<div>Redirecting to the secure payment page…</div>
<form name="SendPayForm_mobile" id="SendPayForm_mobile" method="post" action="https://mobile.inicis.com/smart/payment/" accept-charset="euc-kr" style="display:none">
  <input type="hidden" name="P_INI_PAYMENT" value="CARD">
  <input type="hidden" name="P_MID" value="<?= ev($mid) ?>">
  <input type="hidden" name="P_OID" value="<?= ev($oid) ?>">
  <input type="hidden" name="P_AMT" value="<?= ev($price) ?>">
  <input type="hidden" name="P_GOODS" value="<?= ev($goodname) ?>">
  <input type="hidden" name="P_UNAME" value="<?= ev($name) ?>">
  <input type="hidden" name="P_MOBILE" value="<?= ev($phone) ?>">
  <input type="hidden" name="P_EMAIL" value="<?= ev($email) ?>">
  <input type="hidden" name="P_NEXT_URL" value="<?= ev($mNextUrl) ?>">
  <input type="hidden" name="P_NOTI_URL" value="<?= ev($mNextUrl) ?>">
  <input type="hidden" name="P_CHARSET" value="utf8">
  <input type="hidden" name="P_RESERVED" value="<?= ev($mReserved) ?>">
  <input type="hidden" name="merchantData" value="<?= ev($apply_no) ?>">
</form>
</body></html>
<?php else: ?>
<body onload="INIStdPay.pay('SendPayForm_id');">
<div>Redirecting to the secure payment page…</div>
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
  <input type="hidden" name="goodname" value="<?= ev($goodname) ?>">
  <input type="hidden" name="buyername" value="<?= ev($name) ?>">
  <input type="hidden" name="buyertel" value="<?= ev($phone) ?>">
  <input type="hidden" name="buyeremail" value="<?= ev($email) ?>">
  <input type="hidden" name="gopaymethod" value="<?= ev($gopaymethod) ?>">
  <input type="hidden" name="acceptmethod" value="<?= ev($acceptmethod) ?>">
  <input type="hidden" name="merchantData" value="<?= ev($apply_no) ?>">
  <input type="hidden" name="returnUrl" value="<?= ev($returnUrl) ?>">
  <input type="hidden" name="closeUrl" value="<?= ev($closeUrl) ?>">
</form>
</body></html>
<?php endif; ?>
