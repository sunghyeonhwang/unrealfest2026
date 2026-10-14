<?php
/* Unreal Fest Seoul 2026 — 토스페이먼츠 결제 데모 공통 템플릿 (paments/_checkout.php)
 *
 * PG(토스페이먼츠) 가맹 심사용. ticket-all.php / ticket-day.php 가 $CK(상품 구성)를 정의하고 include 한다.
 *   - 결제위젯 v2(https://js.tosspayments.com/v2/standard) + 문서용 공개 테스트 키 → 실제 과금 없음
 *   - 본인인증(PASS) 영역은 UI만 두고 동작하지 않으며, 인증 없이 결제 진행 가능
 *   - 승인은 success.php 에서 confirm API 호출
 * 심사 종료 후 paments/ 폴더째 삭제. PHP 7.0 호환.
 */
if (!isset($CK) || !is_array($CK)) { exit('direct access denied'); }

include_once __DIR__ . '/../../common.php';          // 세션·DB (가격 단일 소스용)
require_once __DIR__ . '/../_pricing.php';

/* 가격: _pricing.php(얼리버드/정가 자동) 우선, 실패 시 정상가 폴백 */
function ck_price($code) {
    if (function_exists('ufs_ticket_price')) { $p = (int)ufs_ticket_price($code); if ($p > 0) return $p; }
    $fallback = array('NORMAL_ALL' => 120000, 'NORMAL_20' => 60000, 'NORMAL_21' => 60000);
    return isset($fallback[$code]) ? $fallback[$code] : 0;
}
function ck_e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$CK_PRODUCTS = array();   // JS로 넘길 상품 목록 [{code,name,label,price}]
foreach ($CK['products'] as $p) {
    $p['price'] = ck_price($p['code']);
    $CK_PRODUCTS[] = $p;
}

/* 토스 문서용 공개 테스트 클라이언트 키 — 실결제 불가(샌드박스) */
$TOSS_CLIENT_KEY = 'test_gck_docs_Ovk5rk1EwkEbP0W43n07xlzm';
$BASE = 'https://epiclounge.co.kr/v3/unrealfest2026/paments';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<title><?= ck_e($CK['title']) ?> · Unreal Fest Seoul 2026</title>
<style>
  :root{--bg:#08080a;--card:#0e0f14;--line:#27272a;--mut:#a1a1aa;--mut2:#71717a;--acc:#00C1D5}
  *{box-sizing:border-box}
  body{margin:0;background:radial-gradient(900px 500px at 78% -8%,rgba(0,193,213,.10),transparent 60%),var(--bg);
    color:#eaeaef;font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;word-break:keep-all}
  .wrap{max-width:720px;margin:0 auto;padding:40px 20px 80px}
  .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:28px}
  .brand{font-size:12px;font-weight:800;letter-spacing:.14em;color:#00FFC8}
  .back{color:var(--mut2);font-size:13px;text-decoration:none}
  h1{font-size:24px;font-weight:800;margin:0 0 6px}
  .sub{color:var(--mut);font-size:13.5px;margin:0 0 24px;line-height:1.7}
  .card{background:var(--card);border:1px solid var(--line);padding:22px 24px;margin-bottom:16px}
  .card h2{font-size:15px;font-weight:800;margin:0 0 14px;color:#fff}
  .prod{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 16px;border:1px solid var(--line);cursor:pointer}
  .prod+.prod{margin-top:10px}
  .prod.on{border-color:var(--acc);box-shadow:0 0 0 1px var(--acc) inset}
  .prod b{font-size:15px;color:#fff}
  .prod .d{font-size:12.5px;color:var(--mut2);margin-top:3px}
  .prod .p{font-size:17px;font-weight:800;color:#fff;white-space:nowrap}
  .row{margin-bottom:12px}
  label{display:block;font-size:12.5px;color:var(--mut);margin-bottom:6px;font-weight:700}
  input[type=text],input[type=email],input[type=tel]{width:100%;background:#121318;border:1px solid var(--line);color:#fff;
    padding:12px 14px;font-size:14.5px;outline:none}
  input:focus{border-color:var(--acc)}
  .pass{display:flex;justify-content:space-between;align-items:center;gap:14px}
  .pass .t b{font-size:14px;color:#fff}
  .pass .t p{margin:4px 0 0;font-size:12.5px;color:var(--mut2);line-height:1.6}
  .pass button{background:#1b1c22;border:1px solid var(--line);color:var(--mut2);padding:11px 18px;font-size:13px;font-weight:800;cursor:not-allowed;white-space:nowrap}
  .badge{display:inline-block;font-size:11px;font-weight:800;color:var(--mut2);border:1px solid var(--line);padding:2px 8px;border-radius:99px;margin-left:8px;vertical-align:1px}
  .total{display:flex;justify-content:space-between;align-items:center;padding:16px 4px 4px;font-weight:800}
  .total .v{font-size:22px;color:#fff}
  #pay-btn{width:100%;background:var(--acc);color:#06060a;border:0;padding:16px;font-size:16.5px;font-weight:800;cursor:pointer;margin-top:14px}
  #pay-btn:disabled{background:#27272a;color:var(--mut2);cursor:not-allowed}
  .note{font-size:12px;color:var(--mut2);line-height:1.8;margin-top:14px}
  #payment-method,#agreement{min-height:50px}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div class="brand">UNREAL FEST SEOUL 2026</div>
    <a class="back" href="/v3/unrealfest2026/paments/index.php">← 행사 홈</a>
  </div>
  <h1><?= ck_e($CK['title']) ?></h1>
  <p class="sub">언리얼 페스트 서울 2026 · 2026년 8월 20일(목)–21일(금) · 코엑스 (서울)<br>행사/컨퍼런스 참가권 — 결제 완료 시 등록 확인 메일이 발송됩니다.</p>

  <div class="card">
    <h2>티켓 선택</h2>
    <div id="prods"></div>
  </div>

  <div class="card">
    <h2>구매자 정보</h2>
    <div class="row"><label>이름</label><input type="text" id="buyer-name" placeholder="홍길동"></div>
    <div class="row"><label>이메일</label><input type="email" id="buyer-email" placeholder="you@example.com"></div>
    <div class="row" style="margin-bottom:0"><label>휴대폰 번호</label><input type="tel" id="buyer-phone" placeholder="01012345678"></div>
  </div>

  <div class="card">
    <h2>본인인증 <span class="badge">선택</span></h2>
    <div class="pass">
      <div class="t"><b>PASS 본인인증</b>
        <p>현장 입장용 본인 확인 절차입니다. 본인인증 없이도 결제를 진행할 수 있습니다.</p></div>
      <button type="button" disabled>PASS 인증하기</button>
    </div>
  </div>

  <div class="card">
    <h2>결제 수단</h2>
    <div id="payment-method"></div>
    <div id="agreement"></div>
    <div class="total"><span>총 결제 금액</span><span class="v" id="total-amt"></span></div>
    <button id="pay-btn" disabled>결제하기</button>
    <p class="note">· 결제는 토스페이먼츠를 통해 안전하게 처리됩니다.<br>
      · 환불 규정: 행사 7일 전까지 전액 환불, 이후 50% 환불, 행사 당일 환불 불가. 문의: info@epiclounge.co.kr</p>
  </div>
</div>

<script src="https://js.tosspayments.com/v2/standard"></script>
<script>
(function(){
  var PRODUCTS = <?= json_encode($CK_PRODUCTS, JSON_UNESCAPED_UNICODE) ?>;
  var BASE = <?= json_encode($BASE) ?>;
  var sel = 0;

  var box = document.getElementById('prods');
  function won(n){ return '₩' + String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
  function el(tag, cls, text){
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }
  function draw(){
    while (box.firstChild) box.removeChild(box.firstChild);
    PRODUCTS.forEach(function(p, i){
      var d = el('div', 'prod' + (i === sel ? ' on' : ''));
      var left = el('div');
      left.appendChild(el('b', null, p.label));
      left.appendChild(el('div', 'd', p.name));
      d.appendChild(left);
      d.appendChild(el('div', 'p', won(p.price)));
      d.onclick = function(){ sel = i; draw(); setAmt(); };
      box.appendChild(d);
    });
    document.getElementById('total-amt').textContent = won(PRODUCTS[sel].price);
  }
  draw();

  var tossPayments = TossPayments(<?= json_encode($TOSS_CLIENT_KEY) ?>);
  var widgets = tossPayments.widgets({ customerKey: TossPayments.ANONYMOUS });
  var btn = document.getElementById('pay-btn');

  function setAmt(){
    return widgets.setAmount({ currency: 'KRW', value: PRODUCTS[sel].price });
  }

  setAmt().then(function(){
    return Promise.all([
      widgets.renderPaymentMethods({ selector: '#payment-method', variantKey: 'DEFAULT' }),
      widgets.renderAgreement({ selector: '#agreement', variantKey: 'AGREEMENT' })
    ]);
  }).then(function(){ btn.disabled = false; });

  btn.addEventListener('click', function(){
    var p = PRODUCTS[sel];
    var name  = document.getElementById('buyer-name').value.trim() || '언리얼페스트참가자';
    var email = document.getElementById('buyer-email').value.trim();
    var phone = document.getElementById('buyer-phone').value.replace(/\D/g, '');
    var req = {
      orderId: 'UFS26-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8),
      orderName: p.name,
      successUrl: BASE + '/success.php',
      failUrl: BASE + '/fail.php',
      customerName: name
    };
    if (email) req.customerEmail = email;
    if (phone.length >= 10) req.customerMobilePhone = phone;
    widgets.requestPayment(req).catch(function(e){
      if (e && e.code !== 'USER_CANCEL') alert(e.message || '결제 요청에 실패했습니다.');
    });
  });
})();
</script>
</body>
</html>
