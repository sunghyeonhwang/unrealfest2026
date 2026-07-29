<?php
/* Unreal Fest Seoul 2026 — Overseas (English) offline registration — SKELETON (ticket-en.php)
 * 외국인 등록 뼈대: 본인인증(PASS/KCB) 없음(이름·연락처 수동), 영문 UI.
 * 결제(해외 카드 = INICIS Global)는 PG 확정 후 연동 예정 → 지금은 제출 버튼 비활성 placeholder.
 * 중복방지는 결제 연동 단계에서 이메일 기준으로 처리 예정(현 CI 기준 아님).
 * 데이터/가격/트랙 정원은 _ticket_init.php 재사용. PHP 7.0 호환.
 */
require __DIR__ . '/_ticket_init.php';   // common.php, e(), asset_v(), ufs_ticket_price/orig, $UFS_TRACKS, $trackRemain (INICIS 해외카드 KRW)

// 트랙 영문 라벨 (요일별)
function ufs_track_label_en($v) {
    $m = array(
        'DAY1_TR1'=>'Game: Programming', 'DAY1_TR2'=>'Game: Art', 'DAY1_TR3'=>'Media & Entertainment', 'DAY1_TR4'=>'Common',
        'DAY2_TR1'=>'Game: Programming', 'DAY2_TR2'=>'Game: Art', 'DAY2_TR3'=>'Media & Entertainment', 'DAY2_TR4'=>'Manufacturing & Simulation',
    );
    return isset($m[$v]) ? $m[$v] : $v;
}
function ufs_track_box_en($day, $tracks, $trackRemain) {
    $dlabel = ($day === 1) ? 'Day 1 · Aug 20 (Thu)' : 'Day 2 · Aug 21 (Fri)';
    $field  = ($day === 1) ? 'day1track' : 'day2track';
    echo '<div class="mb-6"><h3 class="text-sm font-bold text-white mb-3">'.e($dlabel).' — Select a track <span class="text-[#00C1D5]">*</span></h3>';
    echo '<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">';
    foreach ($tracks as $v=>$l) {
        $full = isset($trackRemain[$v]) && $trackRemain[$v] <= 0;
        echo '<label class="track-en '.($full?'opacity-40 cursor-not-allowed':'cursor-pointer hover:border-white/20').' p-3 border text-center text-sm font-medium transition-all border-[#27272a] text-[#71717a]">';
        echo '<input type="radio" name="'.$field.'" value="'.e($v).'" class="sr-only" '.($full?'disabled':'').'>'.e(ufs_track_label_en($v));
        if ($full) echo ' <span class="text-[#ff8674] text-xs">(Full)</span>';
        echo '</label>';
    }
    echo '</div></div>';
}

$eb = false;   // 해외(Dodo) 등록은 얼리버드 없이 항상 정상가(KRW) 결제
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Registration — Unreal Fest Seoul 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
<?php include __DIR__ . '/_favicon.php'; ?>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Segoe UI',Roboto,sans-serif">

<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">Home</a>
  </div>
</header>

<form name="frm" id="frm" method="post" action="apply_pay_en.php" onsubmit="return validateEnForm()">
<input type="hidden" name="apply_product_code" id="apply_product_code" value="">
<input type="hidden" name="apply_product_name" id="apply_product_name" value="">
<input type="hidden" name="apply_product_price" id="apply_product_price" value="">

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-7xl mx-auto px-6">
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> Back</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">Registration</h1>
    <p class="text-[#a1a1aa] mb-10">For attendees paying with an internationally-issued card. No Korean identity verification required. Please fill in the details below.</p>

    <div class="grid lg:grid-cols-12 gap-8 items-start">
      <!-- LEFT -->
      <div class="lg:col-span-7 xl:col-span-8 space-y-4">

        <!-- Terms -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Agreement</h2>
          <div class="space-y-3">
            <label class="flex items-center gap-3 p-3 bg-[rgba(0,79,89,0.3)] border border-[rgba(0,193,213,0.3)] cursor-pointer">
              <input type="checkbox" id="agree_all" class="accent-[#00C1D5]"><span class="text-sm font-bold text-white">Agree to all</span>
            </label>
            <div class="h-px bg-[#27272a]"></div>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_req" class="agree-item mt-0.5 accent-[#00C1D5]">
              <span class="text-sm text-[#a1a1aa]">I agree to the <a href="legal-en.php#terms" target="_blank" rel="noopener" class="underline text-[#00C1D5]">Terms of Service</a>, <a href="legal-en.php#refund" target="_blank" rel="noopener" class="underline text-[#00C1D5]">Refund Policy</a> and <a href="legal-en.php#privacy" target="_blank" rel="noopener" class="underline text-[#00C1D5]">Privacy Policy</a><span class="ml-1 text-xs text-[#00C1D5]">(required)</span></span>
            </label>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_mkt" class="agree-item mt-0.5 accent-[#00C1D5]">
              <span class="text-sm text-[#a1a1aa]">I agree to receive marketing communications<span class="ml-1 text-xs text-[#71717a]">(optional)</span></span>
            </label>
          </div>
        </div>

        <!-- Ticket -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Ticket</h2>
          <div class="grid gap-4" id="ticketGroup">
            <?php
            $opts = array(
              array('code'=>'ALL',  'pcode'=>'NORMAL_ALL','sub'=>'2-Day Pass · Aug 20–21','label'=>'2-Day Pass — Aug 20 (Thu) & 21 (Fri)'),
              array('code'=>'DAY1', 'pcode'=>'NORMAL_20', 'sub'=>'1-Day Pass · Aug 20','label'=>'1-Day Pass — Aug 20 (Thu)'),
              array('code'=>'DAY2', 'pcode'=>'NORMAL_21', 'sub'=>'1-Day Pass · Aug 21','label'=>'1-Day Pass — Aug 21 (Fri)'),
            );
            foreach ($opts as $o):
              $krw = (int)ufs_ticket_orig($o['pcode']);
              $usd = (int)round($krw/1500); ?>
            <label class="ticket-en relative p-5 border cursor-pointer transition-all border-[#27272a] hover:border-white/20 block"
                   data-pcode="<?= e($o['pcode']) ?>" data-krw="<?= $krw ?>" data-usd="<?= $usd ?>" data-sub="<?= e($o['sub']) ?>">
              <input type="radio" name="ticket" value="<?= e($o['code']) ?>" class="sr-only">
              <div class="text-base font-bold text-white mb-2"><?= e($o['label']) ?></div>
              <div class="text-2xl font-black text-white">US$<?= $usd ?> <span class="text-sm text-[#71717a] font-normal">≈ &#8361;<?= number_format($krw) ?></span></div>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="mt-5 bg-[#111115] p-5 border border-[#27272a]">
            <h4 class="text-sm font-bold text-[#a1a1aa] mb-3">Included</h4>
            <div class="grid sm:grid-cols-2 gap-2 text-sm text-[#a1a1aa]">
              <?php foreach (array('Access to all sessions','Limited-edition goodie','Q&A participation','Exhibition & demo zone','Events & giveaways') as $b): ?>
              <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#00C1D5]"></span><?= e($b) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Attendee info (manual — no identity verification) -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Attendee Information</h2>
          <div class="grid md:grid-cols-3 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Full name <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_name" placeholder="As on your card" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Email <span class="text-[#00C1D5]">*</span></label>
              <input type="email" name="apply_user_email" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Phone <span class="text-[#00C1D5]">*</span></label>
              <input type="tel" name="apply_user_phone" placeholder="+1 234 567 8900" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
        </div>

        <!-- Professional info -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Professional Information</h2>
          <div class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Occupation <span class="text-[#00C1D5]">*</span></label>
                <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">Select</option><option>Professional</option><option>Student</option><option>Educator / Institution</option><option>Indie developer</option><option>Freelancer</option>
                </select></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Company / Organization <span class="text-[#00C1D5]">*</span></label>
                <input type="text" name="apply_user_company" placeholder="Epic Games" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Department <span class="text-[#00C1D5]">*</span></label>
                <input type="text" name="apply_user_depart" placeholder="Dev Team" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Role <span class="text-[#00C1D5]">*</span></label>
                <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">Select</option><option>Visual Art</option><option>Programming</option><option>Production</option><option>Engineering</option><option>Design</option><option>Planning</option><option>R&amp;D</option><option>IT</option><option>Director / PD</option><option>Business / Marketing</option><option>C-level</option><option>Other</option>
                </select></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Industry <span class="text-[#00C1D5]">*</span></label>
                <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">Select</option><option>Games</option><option>Film &amp; TV</option><option>Broadcast &amp; Live Events</option><option>Animation</option><option>Architecture</option><option>Automotive</option><option>Manufacturing / Simulation</option><option>Software &amp; Tools Dev</option><option>VR / AR</option><option>Education</option><option>Other</option>
                </select></div>
            </div>
          </div>
        </div>

        <!-- T-shirt -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-2">T-shirt size <span class="text-[#00C1D5]">*</span></h2>
          <p class="text-xs text-[#71717a] mb-4">The event T-shirt and goods are <span class="text-[#a1a1aa]">picked up on-site at the venue during the event (not shipped)</span>. Your selected size may not be available depending on on-site stock.</p>
          <div class="flex flex-wrap gap-3">
            <?php foreach (array('M','L','XL','XXL') as $size): ?>
            <label class="relative cursor-pointer"><input type="radio" name="tshirt" value="<?= $size ?>" class="peer sr-only">
              <div class="w-14 h-14 border border-[#27272a] bg-[#0e0f14] flex items-center justify-center text-sm font-bold text-[#71717a] peer-checked:border-[#00C1D5] peer-checked:bg-[rgba(0,79,89,0.2)] peer-checked:text-[#00C1D5] transition-all hover:border-white/20"><?= $size ?></div></label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Track selection -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Track Selection</h2>
          <?php ufs_track_box_en(1, $UFS_TRACKS[1], $trackRemain); ?>
          <?php ufs_track_box_en(2, $UFS_TRACKS[2], $trackRemain); ?>
          <p class="text-xs text-[#71717a] mt-2">※ Attendees who selected a track may be given priority entry when the venue is crowded.</p>
        </div>
      </div>

      <!-- RIGHT: Order summary -->
      <div class="lg:col-span-5 xl:col-span-4 self-start sticky top-28">
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 lg:p-8 space-y-6">
          <h3 class="text-lg font-bold text-white">Order Summary</h3>
          <div class="pb-5 border-b border-[#27272a]">
            <div class="text-[#00C1D5] font-bold text-sm mb-1" id="sumSub">&nbsp;</div>
            <div class="flex justify-between items-center"><span class="text-sm text-[#a1a1aa]">Ticket price</span><span class="text-sm text-[#a1a1aa]" id="sumPrice">&nbsp;</span></div>
            <div class="flex justify-between items-center mt-1"><span class="text-sm text-[#a1a1aa]">VAT</span><span class="text-sm text-[#a1a1aa]">Included</span></div>
            <div class="justify-between items-center mt-1" id="sumCouponRow" style="display:none"><span class="text-sm text-[#00C1D5]" id="sumCouponLabel">Coupon</span><span class="text-sm font-bold text-[#00C1D5]" id="sumCouponAmt">&nbsp;</span></div>
          </div>
          <div class="flex justify-between items-end"><span class="text-[#a1a1aa] font-medium">Total</span><span class="text-3xl font-black text-white" id="sumTotal">&nbsp;</span></div>
          <div class="text-right text-xs text-[#71717a] -mt-4" id="sumBilled">&nbsp;</div>

          <!-- Coupon (always visible) -->
          <div class="border-t border-[#27272a] pt-5">
            <label class="block text-xs font-bold text-[#e4e4e7] mb-2">Coupon code <span class="text-[#71717a] font-normal">(optional)</span></label>
            <div class="flex gap-2">
              <input type="text" id="couponInput" name="coupon_code" value="" autocomplete="off" placeholder="Enter code" class="flex-1 min-w-0 bg-[#111115] border border-[#27272a] text-white text-sm px-3 py-2.5 outline-none focus:border-[#00C1D5]" style="text-transform:uppercase">
              <button type="button" id="couponBtn" onclick="applyCouponEn()" class="px-4 py-2.5 border border-[#00C1D5] text-[#00C1D5] text-sm font-bold hover:bg-[#00C1D5] hover:text-[#09090b] transition-colors whitespace-nowrap">Apply</button>
            </div>
            <div id="couponMsg" class="text-xs mt-2"></div>
          </div>

          <!-- Payment -->
          <div class="border border-[#27272a] bg-[#111115] p-4 text-xs text-[#a1a1aa] leading-relaxed">
            <div class="font-bold text-[#e4e4e7] mb-1">Payment: International credit card</div>
            Secure checkout with <strong class="text-[#e4e4e7]">Visa · Mastercard · JCB · Amex · Diners · UnionPay</strong> (3D Secure). Your card is <strong class="text-[#e4e4e7]">charged in Korean Won (KRW)</strong>; your card issuer converts to your local currency.
          </div>
          <button type="submit" id="payBtn" class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all">
            Proceed to Payment
          </button>
          <p class="text-xs text-[#71717a]">By continuing you agree to our <a href="legal-en.php#refund" target="_blank" rel="noopener" class="underline text-[#a1a1aa]">Refund Policy</a>. A QR code will be emailed after payment.</p>
        </div>
      </div>
    </div>
  </div>
</div>
</form>

<?php include __DIR__ . '/_pf_footer.php'; ?>

<script>
// 티켓 선택 → 주문 요약(정상가) 갱신
(function(){
  document.querySelectorAll('.ticket-en').forEach(function(card){
    card.addEventListener('click', function(){
      document.querySelectorAll('.ticket-en').forEach(function(c){ c.style.removeProperty('border-color'); c.style.removeProperty('background-color'); });
      card.style.setProperty('border-color','#00C1D5','important');   // T셔츠 선택과 동일: 시안 테두리 + 시안 배경틴트 (Tailwind !important 대응)
      card.style.setProperty('background-color','rgba(0,79,89,0.2)','important');
      card.querySelector('input[type=radio]').checked = true;
      window.__ufsKrw = parseInt(card.getAttribute('data-krw'),10)||0;
      window.__ufsUsd = parseInt(card.getAttribute('data-usd'),10)||0;
      document.getElementById('sumSub').textContent=card.getAttribute('data-sub');
      document.getElementById('apply_product_code').value=card.getAttribute('data-pcode');
      document.getElementById('apply_product_price').value=window.__ufsKrw;   // 서버 청구 KRW
      if(typeof ufsRenderTotalEn==='function') ufsRenderTotalEn();
    });
  });
  var all=document.getElementById('agree_all');
  if(all) all.addEventListener('change', function(){ document.querySelectorAll('.agree-item').forEach(function(c){ c.checked=all.checked; }); });
  // 트랙 선택 하이라이트 (같은 요일 그룹 내 토글, Tailwind !important 대응)
  document.querySelectorAll('.track-en').forEach(function(lbl){
    lbl.addEventListener('click', function(){
      var radio=lbl.querySelector('input[type=radio]');
      if(!radio || radio.disabled) return;
      document.querySelectorAll('.track-en').forEach(function(l){
        var r=l.querySelector('input[type=radio]');
        if(r && r.name===radio.name){ l.style.removeProperty('border-color'); l.style.removeProperty('background-color'); l.style.removeProperty('color'); }
      });
      lbl.style.setProperty('border-color','#00C1D5','important');
      lbl.style.setProperty('background-color','rgba(0,79,89,0.2)','important');
      lbl.style.setProperty('color','#00C1D5','important');
    });
  });
})();
function validateEnForm(){
  var t=document.querySelector('input[name="ticket"]:checked');
  if(!t){ alert('Please select a ticket.'); return false; }
  var req=['apply_user_name','apply_user_email','apply_user_phone','apply_user_job','apply_user_company','apply_user_depart','apply_user_grade','apply_user_ex1'];
  for(var i=0;i<req.length;i++){ var el=document.querySelector('[name="'+req[i]+'"]'); if(!el||!(''+el.value).trim()){ alert('Please complete all required fields.'); if(el) el.focus(); return false; } }
  if(!document.querySelector('input[name="tshirt"]:checked')){ alert('Please select a T-shirt size.'); return false; }
  var code=(document.getElementById('apply_product_code')||{}).value||'';
  var needD1=(code==='NORMAL_ALL'||code==='NORMAL_20'), needD2=(code==='NORMAL_ALL'||code==='NORMAL_21');
  if(needD1 && !document.querySelector('input[name="day1track"]:checked')){ alert('Please select a Day 1 track.'); return false; }
  if(needD2 && !document.querySelector('input[name="day2track"]:checked')){ alert('Please select a Day 2 track.'); return false; }
  if(!document.querySelector('input[name="agree_req"]').checked){ alert('Please agree to the required terms.'); return false; }
  return true;
}

/* ── Coupon: 부분할인→할인 KRW(INICIS 청구) / 100%→무료. 표기는 US$ + KRW 병기. ── */
var UFS_couponPct = 0;   // 적용된 할인율(0=미적용)
function ufsKrw(){ return window.__ufsKrw || 0; }
function ufsUsd(){ return window.__ufsUsd || 0; }
function ufsFmt(n){ return (n||0).toLocaleString('en-US'); }
function ufsDiscKrw(base, pct){ if(pct>=100) return 0; return Math.round(base*(100-pct)/100/100)*100; }  // 서버 ufs_coupon_apply_price 동일(100원 단위)
function ufsRenderTotalEn(){
  var krw=ufsKrw(), usd=ufsUsd();
  var row=document.getElementById('sumCouponRow'), total=document.getElementById('sumTotal'),
      billed=document.getElementById('sumBilled'), price=document.getElementById('sumPrice'), btn=document.getElementById('payBtn');
  if(krw<=0) return;
  if(price) price.textContent='US$'+usd;
  if(UFS_couponPct>0){
    var dk = ufsDiscKrw(krw, UFS_couponPct);
    var du = UFS_couponPct>=100 ? 0 : Math.round(usd*(100-UFS_couponPct)/100);
    document.getElementById('sumCouponLabel').textContent='Coupon ('+UFS_couponPct+'% off)';
    document.getElementById('sumCouponAmt').textContent='-US$'+(usd-du);
    row.style.display='flex';
    if(UFS_couponPct>=100){ total.textContent='Free'; if(billed) billed.textContent='Billed ₩0 KRW'; }
    else { total.textContent='US$'+du; if(billed) billed.textContent='Billed ₩'+ufsFmt(dk)+' KRW'; }
    if(btn) btn.textContent = UFS_couponPct>=100 ? 'Complete Registration (Free)' : 'Proceed to Payment';
  } else {
    row.style.display='none';
    total.textContent='US$'+usd;
    if(billed) billed.textContent='Billed ₩'+ufsFmt(krw)+' KRW';
    if(btn) btn.textContent='Proceed to Payment';
  }
}
function applyCouponEn(){
  var inp = document.getElementById('couponInput'), msg = document.getElementById('couponMsg');
  var code = (inp.value || '').trim().toUpperCase(); inp.value = code;
  if(ufsKrw()<=0){ msg.style.color='#ff8674'; msg.textContent='Please select a ticket first.'; return; }
  if(!code){ UFS_couponPct = 0; msg.textContent = ''; ufsRenderTotalEn(); return; }
  var btn = document.getElementById('couponBtn'), ot = btn.textContent; btn.disabled = true; btn.textContent = '...';
  var fd = new FormData(); fd.append('code', code);
  fetch('coupon_en_check.php', {method:'POST', body:fd}).then(function(r){ return r.json(); }).then(function(d){
    btn.disabled = false; btn.textContent = ot;
    if(d && d.ok && d.percent > 0){
      UFS_couponPct = d.percent; msg.style.color = '#00C1D5';
      msg.textContent = d.percent >= 100 ? '100% coupon applied — free registration.' : (d.percent + '% discount applied.');
    } else {
      UFS_couponPct = 0; msg.style.color = '#ff8674'; msg.textContent = 'Invalid or unavailable coupon code.';
    }
    ufsRenderTotalEn();
  }).catch(function(){ btn.disabled = false; btn.textContent = ot; msg.style.color='#ff8674'; msg.textContent='Could not validate. Please try again.'; });
}

/* ── 입력 보존: PayPal 결제 왕복/뒤로가기 시 폼 내용 유지 (localStorage, 2시간 만료) ── */
(function(){
  var KEY='ufs_en_form_v1', TTL=2*60*60*1000;
  var form=document.getElementById('frm'); if(!form) return;
  function collect(){
    var d={_t:Date.now()};
    form.querySelectorAll('input[name],select[name],textarea[name]').forEach(function(el){
      if(el.type==='radio'||el.type==='checkbox'){ if(el.checked) d[el.name]=el.value; }
      else d[el.name]=el.value;
    });
    return d;
  }
  function save(){ try{ localStorage.setItem(KEY, JSON.stringify(collect())); }catch(e){} }
  function radioByVal(nm,val){ var e=form.querySelectorAll('input[name="'+nm+'"]'); for(var i=0;i<e.length;i++) if(e[i].value===val) return e[i]; return null; }
  function restore(){
    var raw; try{ raw=localStorage.getItem(KEY); }catch(e){ return; }
    if(!raw) return; var d; try{ d=JSON.parse(raw); }catch(e){ return; }
    if(!d || !d._t || (Date.now()-d._t)>TTL){ try{ localStorage.removeItem(KEY); }catch(e){} return; }
    // 텍스트/셀렉트/쿠폰 등 일반 입력
    form.querySelectorAll('input[name],select[name],textarea[name]').forEach(function(el){
      if(el.type==='radio'||el.type==='checkbox') return;
      if(d[el.name]!=null) el.value=d[el.name];
    });
    // 티켓(카드 클릭 → 요약·상품코드·가격 반영)
    if(d.ticket){ var tr=radioByVal('ticket',d.ticket), card=tr&&tr.closest('.ticket-en'); if(card) card.click(); }
    // 트랙(라벨 클릭 → 하이라이트)
    ['day1track','day2track'].forEach(function(nm){ if(d[nm]){ var r=radioByVal(nm,d[nm]), lb=r&&r.closest('.track-en'); if(lb) lb.click(); } });
    // 티셔츠(peer CSS → checked만)
    if(d.tshirt){ var ts=radioByVal('tshirt',d.tshirt); if(ts) ts.checked=true; }
    // 동의
    ['agree_req','agree_mkt'].forEach(function(nm){ if(d[nm]!=null){ var c=form.querySelector('input[name="'+nm+'"]'); if(c) c.checked=true; } });
    var all=document.getElementById('agree_all');
    if(all){ var items=form.querySelectorAll('.agree-item'), allc=items.length>0; items.forEach(function(c){ if(!c.checked) allc=false; }); all.checked=allc; }
    // 쿠폰 값 있으면 자동 재적용
    var cin=document.getElementById('couponInput');
    if(cin && cin.value.trim() && typeof applyCouponEn==='function') applyCouponEn();
  }
  form.addEventListener('input', save);
  form.addEventListener('change', save);
  restore();
})();
</script>

</body>
</html>
