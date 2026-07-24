<?php
/* Unreal Fest Seoul 2026 — [프리뷰] 양일권 등록 + 개인 쿠폰 입력창 (ticket-all-coupon-preview.php)
 * ⚠️ 프리뷰 전용. ticket-all.php 복제 + 쿠폰 UI. 실제 결제/본인인증/등록은 동작하지 않음(차단).
 * 개발(정상가 전환 시) 방향 확인용 시안. noindex.
 */
require __DIR__ . '/_ticket_init.php';
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>[프리뷰] 양일권 등록 + 쿠폰 — Unreal Fest Seoul 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
<?php include __DIR__ . '/_favicon.php'; ?>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">

<!-- 프리뷰 배너 -->
<div style="position:fixed;top:0;inset-inline:0;z-index:60;background:#00C1D5;color:#09090b;font-size:12px;font-weight:700;text-align:center;padding:6px 12px">
  프리뷰 — 개인 쿠폰 입력 UI 시안 · 실제 결제·본인인증·등록은 동작하지 않습니다 (정상가 전환 시 개발 예정)
</div>

<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]" style="margin-top:30px">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<form name="frm" id="frm" method="post" action="#" onsubmit="return ufsPreviewBlock()">
<input type="hidden" name="apply_ci" id="apply_ci" value="">
<input type="hidden" name="apply_di" id="apply_di" value="">
<input type="hidden" name="apply_real_type" id="apply_real_type" value="">
<input type="hidden" name="apply_product_code" id="apply_product_code" value="">
<input type="hidden" name="apply_product_name" id="apply_product_name" value="">
<input type="hidden" name="apply_product_price" id="apply_product_price" value="">
<input type="hidden" name="apply_track" id="apply_track" value="">

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]" style="padding-top:9rem">
  <div class="max-w-7xl mx-auto px-6">
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">양일권 등록</h1>
    <p class="text-[#a1a1aa] mb-10">8월 20일(목)부터 21일(금)까지 진행되는 전체 프로그램에 참여할 수 있습니다. 등록을 위해 아래 정보를 입력해 주세요.</p>

    <div class="grid lg:grid-cols-12 gap-8 items-start">
      <!-- 좌측 폼 -->
      <div class="lg:col-span-7 xl:col-span-8 space-y-4">

        <?php include __DIR__ . '/_ticket_agree.php'; ?>

        <!-- 티켓(양일권 고정) -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">티켓</h2>
          <div class="grid gap-4 mb-8" id="ticketGroup">
            <label class="ticket-card relative p-5 border transition-all border-[#27272a]"
                   data-code="ALL" data-price="<?= ufs_ticket_price('NORMAL_ALL') ?>" data-orig="<?= ufs_ticket_orig('NORMAL_ALL') ?>" data-sub="양일권 (8월 20일-21일)" data-pcode="NORMAL_ALL" data-days="1,2">
              <input type="radio" name="ticket" value="ALL" class="sr-only" checked>
              <div class="text-base font-bold text-white mb-3">양일권 - 8월 20일(목)~21일(금)</div>
              <div class="mb-1">
                <?php if (ufs_is_earlybird()): ?>
                <div class="text-base text-[#71717a] line-through">₩<?= number_format(ufs_ticket_orig('NORMAL_ALL')) ?></div>
                <div class="text-xs font-bold text-[#00C1D5] my-0.5"><?= e(ufs_promo_ticket_note()) ?></div>
                <?php endif; ?>
                <div class="text-2xl font-black text-white">₩<?= number_format(ufs_ticket_price('NORMAL_ALL')) ?></div>
              </div>
              <div class="tk-check absolute top-3 right-3 hidden"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg></div>
            </label>
          </div>
          <div class="bg-[#111115] p-5 border border-[#27272a]">
            <h4 class="text-sm font-bold text-[#a1a1aa] mb-3">혜택</h4>
            <div class="grid sm:grid-cols-2 gap-2 text-sm text-[#a1a1aa]">
              <?php foreach (array('양일간 전체 세션 참여','한정판 굿즈 제공','Q&A 참여','전시 및 체험존 이용','이벤트 및 경품 참여') as $b): ?>
              <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#00C1D5]"></span><?= e($b) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <?php include __DIR__ . '/_ticket_fields.php'; ?>

        <!-- 트랙 선택 (양일권: Day1 + Day2 모두) -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <?php ufs_track_box(1, $UFS_TRACKS[1], $trackRemain); ?>
          <?php ufs_track_box(2, $UFS_TRACKS[2], $trackRemain); ?>
          <p class="text-xs text-[#71717a] mt-2">※ 현장 혼잡 시 선택한 트랙 참석자가 우선 입장될 수 있습니다.</p>
        </div>

        <!-- ▼▼ [프리뷰] 쿠폰 입력 패널 (최하단) ▼▼ -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
            쿠폰 <span class="text-xs font-normal text-[#71717a]">(선택)</span>
          </h2>
          <p class="text-xs text-[#71717a] mb-4">보유하신 쿠폰 코드가 있다면 입력 후 적용해 주세요.</p>
          <div class="flex gap-2">
            <input type="text" id="coupon_code" placeholder="쿠폰 코드 입력" autocomplete="off"
                   class="flex-1 bg-[#09090b] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm uppercase">
            <button type="button" onclick="ufsApplyCoupon()" class="px-6 py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-bold text-sm transition-all whitespace-nowrap">적용</button>
            <button type="button" onclick="ufsClearCoupon(true)" class="px-4 py-3 border border-[#27272a] text-[#a1a1aa] hover:text-white text-sm transition-all whitespace-nowrap">해제</button>
          </div>
          <div id="coupon_result" class="mt-3 text-sm" style="display:none"></div>
        </div>
        <!-- ▲▲ [프리뷰] 쿠폰 입력 패널 (최하단) ▲▲ -->
      </div>

      <?php include __DIR__ . '/_ticket_sidebar.php'; ?>
    </div>
  </div>
</div>
</form>

<?php include __DIR__ . '/_pf_footer.php'; ?>

<!-- 본인인증 팝업 타깃(프리뷰: 미사용) -->
<form name="form1" id="form1" method="post"></form>
<form name="kcbResultForm" id="kcbResultForm"></form>

<script src="<?= asset_v('assets/js/ticket.js') ?>"></script>
<script>
selectTicket('ALL');

/* ── 프리뷰 차단: 실제 결제/본인인증 무력화 ── */
function ufsPreviewBlock(){ alert('프리뷰 페이지입니다. 실제 결제·등록은 진행되지 않습니다.'); return false; }
try { jsSubmit = function(){ alert('프리뷰 페이지입니다. 본인인증은 동작하지 않습니다.'); }; jsSubmitPin = jsSubmit; } catch(e){}

/* ── 쿠폰(프리뷰): 기존 group-coupon-check.php 검증 재사용 → 사이드바 총액 미리보기 (DOM 안전 생성) ── */
function _selPrice(){
  var sel=document.querySelector('input[name="ticket"]:checked');
  var card=sel?sel.closest('.ticket-card'):document.querySelector('.ticket-card');
  return card?(parseInt(card.getAttribute('data-price'),10)||0):0;
}
function _won(n){ return '₩'+(n||0).toLocaleString(); }
function _span(cls,txt){ var s=document.createElement('span'); s.className=cls; s.textContent=txt; return s; }
function ufsClearCoupon(reselect){
  var box=document.getElementById('coupon_result'); box.style.display='none'; box.textContent='';
  var row=document.getElementById('ufsCouponRow'); if(row) row.remove();
  if(reselect){ document.getElementById('coupon_code').value=''; }
  var p=_selPrice();
  var t=document.getElementById('sumTotal'); if(t) t.textContent=_won(p);
  var lbl=document.getElementById('payBtnLabel'); if(lbl) lbl.textContent=_won(p)+' 결제하기';
}
function ufsApplyCoupon(){
  var code=(document.getElementById('coupon_code').value||'').trim().toUpperCase();
  var box=document.getElementById('coupon_result');
  box.style.display=''; box.className='mt-3 text-sm';
  if(!code){ box.style.color='#f87171'; box.textContent='쿠폰 코드를 입력해 주세요.'; return; }
  box.style.color='#a1a1aa'; box.textContent='확인 중…';
  fetch('group-coupon-check.php?code='+encodeURIComponent(code))
    .then(function(r){ return r.json(); })
    .then(function(d){
      if(!d.ok){ ufsClearCoupon(); box.style.display=''; box.style.color='#f87171'; box.textContent=d.msg||'유효하지 않은 쿠폰입니다.'; return; }
      var pct=parseInt(d.percent,10)||0;
      var price=_selPrice();
      var discounted=Math.round(price*(1-pct/100)/100)*100; // 100원 단위
      var total=document.getElementById('sumTotal');
      if(total){
        var anchor=total.closest('div');
        var row=document.getElementById('ufsCouponRow');
        if(!row){
          row=document.createElement('div');
          row.id='ufsCouponRow';
          row.className='flex justify-between items-center mt-1';
          anchor.parentNode.insertBefore(row, anchor);
        }
        row.textContent='';
        row.appendChild(_span('text-sm text-[#00C1D5]','쿠폰 할인 ('+pct+'%)'));
        row.appendChild(_span('text-sm font-bold text-[#00C1D5]','-'+_won(price-discounted)));
        total.textContent=_won(discounted);
      }
      var lbl=document.getElementById('payBtnLabel'); if(lbl) lbl.textContent=_won(discounted)+' 결제하기';
      box.style.display=''; box.style.color='#00C1D5'; box.textContent='';
      var b1=document.createElement('b'); b1.textContent=code; box.appendChild(b1);
      box.appendChild(document.createTextNode(' · '+pct+'% 할인 적용 — '+_won(price)+' → '));
      var b2=document.createElement('b'); b2.textContent=_won(discounted); box.appendChild(b2);
    })
    .catch(function(){ box.style.display=''; box.style.color='#f87171'; box.textContent='쿠폰 확인 중 오류가 발생했습니다.'; });
}
</script>
</body>
</html>
