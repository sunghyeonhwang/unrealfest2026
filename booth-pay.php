<?php
/* Unreal Fest Seoul 2026 — 단건 카드결제 창(부스 렌탈 등) (booth-pay.php)
 * 고정금액 INICIS STDPAY. GET=안내 화면 / POST(pay=1)=결제 레코드 생성 후 INICIS 결제창.
 * 승인 콜백 = booth-pay-return.php. 추가 결제건은 $ORDERS 배열에 한 줄. noindex. PHP 7.0 호환.
 */
include_once "../common.php";
require_once "../unrealfest2025/inisis_pc/libs/INIStdPayUtil.php";

$mid = "MOIepiclou"; $signKey = "Wno0S3hIQVhUZ1BKSHFYMXRIVUJpQT09"; $jsUrl = "https://stdpay.inicis.com/stdjs/INIStdPay.js";
function ev($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ── 결제 건 정의(슬러그별). 추가 건은 여기에 한 줄 추가. token=비공개 접근키(추측 불가) ──
$ORDERS = array(
  'giantstep' => array(
    'item'  => '언리얼 페스트 서울 2026 부스 추가 결제 (자이언트스텝)',
    'amount'=> 117000,
    'name'  => '정지현',
    'tel'   => '01042499002',
    'email' => 'jihyun.jeong@giantstepcorp.com',
    'token' => 'gs26-booth-7fa93kd2',
  ),
);
$slug = isset($_GET['o']) ? preg_replace('/[^a-z0-9_-]/i', '', $_GET['o']) : '';
$key  = isset($_GET['k']) ? trim($_GET['k']) : '';
// 비공개: 슬러그 + 정확한 토큰이 있어야만 접근(링크를 받은 사람만). 노출/오접속 차단.
if ($slug === '' || !isset($ORDERS[$slug]) || !hash_equals($ORDERS[$slug]['token'], $key)) {
    http_response_code(404); exit('Not Found');
}
$o = $ORDERS[$slug];
$price = (int)$o['amount'];

// 결제 기록 테이블 보장
sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_misc_pay (
  mp_no INT UNSIGNED NOT NULL AUTO_INCREMENT, mp_slug VARCHAR(40) NOT NULL DEFAULT '',
  mp_oid VARCHAR(60) NOT NULL DEFAULT '', mp_item VARCHAR(200) NOT NULL DEFAULT '',
  mp_amount INT NOT NULL DEFAULT 0, mp_name VARCHAR(60) NOT NULL DEFAULT '',
  mp_tel VARCHAR(30) NOT NULL DEFAULT '', mp_email VARCHAR(120) NOT NULL DEFAULT '',
  mp_status VARCHAR(20) NOT NULL DEFAULT 'pending', mp_tid VARCHAR(60) NOT NULL DEFAULT '',
  mp_applnum VARCHAR(40) NOT NULL DEFAULT '', mp_reg DATETIME DEFAULT NULL, mp_paid_at DATETIME DEFAULT NULL,
  PRIMARY KEY(mp_no), KEY k_oid(mp_oid)) DEFAULT CHARSET=utf8");

$do_pay = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay']));

if ($do_pay) {
    // ── 결제 개시: 대기 레코드 생성 후 INICIS 결제창 ──
    $util      = new INIStdPayUtil();
    $timestamp = $util->getTimestamp();
    $mKey      = $util->makeHash($signKey, "sha256");
    $oid       = $mid."_".$timestamp;

    sql_query("INSERT INTO cb_unreal_2026_misc_pay (mp_slug,mp_oid,mp_item,mp_amount,mp_name,mp_tel,mp_email,mp_status,mp_reg)
      VALUES ('".sql_real_escape_string($slug)."','".sql_real_escape_string($oid)."','".sql_real_escape_string($o['item'])."',".$price.",
      '".sql_real_escape_string($o['name'])."','".sql_real_escape_string($o['tel'])."','".sql_real_escape_string($o['email'])."','pending',now())");
    $r = sql_fetch("SELECT LAST_INSERT_ID() id"); $mp_no = $r ? (int)$r['id'] : 0;

    $sign  = $util->makeSignature(array("oid"=>$oid, "price"=>$price, "timestamp"=>$timestamp));
    $sign2 = $util->makeSignature(array("oid"=>$oid, "price"=>$price, "signKey"=>$signKey, "timestamp"=>$timestamp));
    $base      = "https://".$_SERVER['HTTP_HOST']."/v3/unrealfest2026";
    $returnUrl = $base."/booth-pay-return.php";
    $closeUrl  = $base."/booth-pay.php?o=".$slug."&k=".rawurlencode($key);
    $mNextUrl  = $base."/booth-pay-return.php";
    $goods = $o['item']; $buyername = $o['name']; $buyertel = $o['tel']; $buyeremail = $o['email'];

    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $is_mobile = preg_match('/(android|iphone|ipad|ipod|mobile)/i', $ua);
    ?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
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
  <input type="hidden" name="P_RESERVED" value="below1000=Y&centerCd=Y">
  <input type="hidden" name="merchantData" value="<?= ev($mp_no) ?>">
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
  <input type="hidden" name="merchantData" value="<?= ev($mp_no) ?>">
  <input type="hidden" name="returnUrl" value="<?= ev($returnUrl) ?>">
  <input type="hidden" name="closeUrl" value="<?= ev($closeUrl) ?>">
</form>
</body></html>
<?php endif;
    exit;
}
// ── 안내 화면(GET) ──
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>카드 결제 — <?= ev($o['item']) ?></title>
<style>
*{box-sizing:border-box}
body{background:#09090b;color:#e4e4e7;font-family:system-ui,-apple-system,sans-serif;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{width:100%;max-width:460px;background:#111115;border:1px solid #27272a;border-radius:16px;padding:32px}
.brand{font-size:12px;font-weight:800;letter-spacing:.08em;color:#00C1D5;margin-bottom:20px}
h1{font-size:20px;font-weight:700;color:#fff;margin:0 0 24px;line-height:1.4}
.row{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #1f1f23;font-size:14px}
.row .k{color:#71717a}.row .v{color:#e4e4e7;text-align:right;font-weight:600}
.amt{display:flex;justify-content:space-between;align-items:baseline;margin:22px 0 6px}
.amt .k{color:#a1a1aa;font-size:14px}.amt .v{color:#00C1D5;font-size:30px;font-weight:800}
.btn{width:100%;margin-top:22px;padding:15px;background:#00C1D5;color:#001b1f;border:0;border-radius:10px;font-size:16px;font-weight:800;cursor:pointer}
.btn:hover{background:#00a8ba}
.note{margin-top:16px;font-size:12px;color:#71717a;line-height:1.6}
</style>
</head>
<body>
  <div class="card">
    <div class="brand">UNREAL FEST SEOUL 2026</div>
    <h1><?= ev($o['item']) ?></h1>
    <div class="row"><span class="k">담당자</span><span class="v"><?= ev($o['name']) ?></span></div>
    <div class="row"><span class="k">연락처</span><span class="v"><?= ev($o['tel']) ?></span></div>
    <div class="row"><span class="k">이메일</span><span class="v"><?= ev($o['email']) ?></span></div>
    <div class="row"><span class="k">결제수단</span><span class="v">신용카드</span></div>
    <div class="amt"><span class="k">결제 금액</span><span class="v">₩<?= number_format($price) ?></span></div>
    <form method="post" action="booth-pay.php?o=<?= ev($slug) ?>&k=<?= ev($key) ?>">
      <input type="hidden" name="pay" value="1">
      <button type="submit" class="btn">카드로 결제하기</button>
    </form>
    <p class="note">· 결제 버튼을 누르면 이니시스(INICIS) 카드 결제창이 열립니다.<br>· 세금계산서·문의: 언리얼 페스트 사무국 02-326-3701 · info@epiclounge.co.kr</p>
  </div>
</body></html>
