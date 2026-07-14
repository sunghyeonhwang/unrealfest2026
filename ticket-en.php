<?php
/* Unreal Fest Seoul 2026 — Overseas (English) offline registration — SKELETON (ticket-en.php)
 * 외국인 등록 뼈대: 본인인증(PASS/KCB) 없음(이름·연락처 수동), 영문 UI.
 * 결제(해외 카드 = INICIS Global)는 PG 확정 후 연동 예정 → 지금은 제출 버튼 비활성 placeholder.
 * 중복방지는 결제 연동 단계에서 이메일 기준으로 처리 예정(현 CI 기준 아님).
 * 데이터/가격/트랙 정원은 _ticket_init.php 재사용. PHP 7.0 호환.
 */
require __DIR__ . '/_ticket_init.php';   // common.php, e(), asset_v(), ufs_ticket_price/orig, $UFS_TRACKS, $trackRemain

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
        echo '<label class="'.($full?'opacity-40 cursor-not-allowed':'cursor-pointer hover:border-white/20').' p-3 border text-center text-sm font-medium transition-all border-[#27272a] text-[#71717a]">';
        echo '<input type="radio" name="'.$field.'" value="'.e($v).'" class="sr-only" '.($full?'disabled':'').'>'.e(ufs_track_label_en($v));
        if ($full) echo ' <span class="text-[#ff8674] text-xs">(Full)</span>';
        echo '</label>';
    }
    echo '</div></div>';
}

$eb = ufs_is_earlybird();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Registration (Overseas) — Unreal Fest Seoul 2026</title>
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

<!-- ⚠️ SKELETON: 결제(INICIS Global) 미연동. 제출 시 실제 등록/결제 안 됨. -->
<form name="frm" id="frm" method="post" action="#" onsubmit="alert('Payment integration is in progress. This is a preview page.'); return false;">
<input type="hidden" name="apply_product_code" id="apply_product_code" value="">
<input type="hidden" name="apply_product_name" id="apply_product_name" value="">
<input type="hidden" name="apply_product_price" id="apply_product_price" value="">

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-7xl mx-auto px-6">
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> Back</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">Registration (Overseas Attendees)</h1>
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
              <span class="text-sm text-[#a1a1aa]">I agree to the <a href="#" class="underline text-[#00C1D5]">Terms of Service</a> and <a href="#" class="underline text-[#00C1D5]">Privacy Policy</a><span class="ml-1 text-xs text-[#00C1D5]">(required)</span></span>
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
              $price = ufs_ticket_price($o['pcode']); $orig = ufs_ticket_orig($o['pcode']); ?>
            <label class="ticket-en relative p-5 border cursor-pointer transition-all border-[#27272a] hover:border-white/20 block"
                   data-pcode="<?= e($o['pcode']) ?>" data-price="<?= $price ?>" data-sub="<?= e($o['sub']) ?>">
              <input type="radio" name="ticket" value="<?= e($o['code']) ?>" class="sr-only">
              <div class="text-base font-bold text-white mb-2"><?= e($o['label']) ?></div>
              <?php if ($eb): ?>
              <div class="text-base text-[#71717a] line-through">&#8361;<?= number_format($orig) ?></div>
              <div class="text-xs font-bold text-[#00C1D5] my-0.5">Early Bird 50% OFF</div>
              <?php endif; ?>
              <div class="text-2xl font-black text-white">&#8361;<?= number_format($price) ?></div>
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
                  <option value="">Select</option><option>Games</option><option>Film &amp; TV</option><option>Broadcast &amp; Live Events</option><option>Animation</option><option>Architecture</option><option>Automotive</option><option>Manufacturing / Simulation</option><option>Software &amp; Tools</option><option>VR / AR</option><option>Education</option><option>Other</option>
                </select></div>
            </div>
          </div>
        </div>

        <!-- T-shirt -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-2">T-shirt size <span class="text-[#00C1D5]">*</span></h2>
          <p class="text-xs text-[#71717a] mb-4">Your selected size may not be available depending on on-site stock.</p>
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
            <?php if ($eb): ?><div class="flex justify-between items-center mt-1"><span class="text-sm text-[#00C1D5]">Early Bird (50%)</span><span class="text-sm font-bold text-[#00C1D5]" id="sumDiscount">&nbsp;</span></div><?php endif; ?>
            <div class="flex justify-between items-center mt-1"><span class="text-sm text-[#a1a1aa]">VAT</span><span class="text-sm text-[#a1a1aa]">Included</span></div>
          </div>
          <div class="flex justify-between items-end"><span class="text-[#a1a1aa] font-medium">Total</span><span class="text-3xl font-black text-white" id="sumTotal">&nbsp;</span></div>

          <!-- Payment (placeholder) -->
          <div class="border border-dashed border-[#3f3f46] bg-[#111115] p-4 text-xs text-[#a1a1aa] leading-relaxed">
            <div class="font-bold text-[#e4e4e7] mb-1">Payment method: Overseas card</div>
            Overseas card payment (INICIS Global) is being set up. Online checkout will be available shortly.
          </div>
          <button type="submit" disabled class="w-full bg-[#27272a] text-[#71717a] py-4 font-bold text-lg flex items-center justify-center gap-2 cursor-not-allowed">
            Proceed to Payment (coming soon)
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
</form>

<?php include __DIR__ . '/_pf_footer.php'; ?>

<script>
// 뼈대용 최소 스크립트: 티켓 선택 → 주문 요약 갱신
(function(){
  var won='₩';
  function fmt(n){ return won + n.toLocaleString('en-US'); }
  document.querySelectorAll('.ticket-en').forEach(function(card){
    card.addEventListener('click', function(){
      document.querySelectorAll('.ticket-en').forEach(function(c){ c.classList.remove('border-[#00C1D5]'); });
      card.classList.add('border-[#00C1D5]');
      card.querySelector('input[type=radio]').checked = true;
      var price=parseInt(card.getAttribute('data-price'),10)||0;
      var orig=price*2; // early bird 50%
      document.getElementById('sumSub').textContent=card.getAttribute('data-sub');
      document.getElementById('sumPrice').textContent=fmt(orig);
      var d=document.getElementById('sumDiscount'); if(d) d.textContent='-'+fmt(orig-price);
      document.getElementById('sumTotal').textContent=fmt(price);
      document.getElementById('apply_product_code').value=card.getAttribute('data-pcode');
      document.getElementById('apply_product_price').value=price;
    });
  });
  var all=document.getElementById('agree_all');
  if(all) all.addEventListener('change', function(){ document.querySelectorAll('.agree-item').forEach(function(c){ c.checked=all.checked; }); });
})();
</script>
</body>
</html>
