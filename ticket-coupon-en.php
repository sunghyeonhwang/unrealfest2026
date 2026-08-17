<?php
/* Unreal Fest Seoul 2026 — Coupon registration (English, no identity verification) (ticket-coupon-en.php)
 * 영문 쿠폰 등록 전용: 본인인증 없음(수동 입력). 100% 무료 쿠폰만 허용 → 결제 없이 즉시 완료(QR).
 * 부분할인(50~99%) 쿠폰은 한국어 페이지(ticket-coupon.php, 본인인증+카드)로 안내. noindex.
 * 기반: ticket-en.php UI + apply_pay 무료 경로 로직. PHP 7.0.
 */
require __DIR__ . '/_ticket_init.php';          // common.php, e(), asset_v(), _pricing(+_coupon), $UFS_TRACKS, $trackRemain
if (ufs_reg_closed()) ufs_reg_closed_page();   // 등록 마감(8/21 17:00)
require_once __DIR__ . '/_group_apply.php';      // ufs_group_make_qr

$PRODNAME = array('NORMAL_ALL'=>'2-Day Pass (Aug 20–21)','NORMAL_20'=>'1-Day Pass (Aug 20)','NORMAL_21'=>'1-Day Pass (Aug 21)');
$T2P = array('ALL'=>'NORMAL_ALL','DAY1'=>'NORMAL_20','DAY2'=>'NORMAL_21');

function ufs_track_label_en($v) {
    $m = array('DAY1_TR1'=>'Game: Programming','DAY1_TR2'=>'Game: Art','DAY1_TR3'=>'Media & Entertainment','DAY1_TR4'=>'Cross-Industries',
               'DAY2_TR1'=>'Game: Programming','DAY2_TR2'=>'Game: Art','DAY2_TR3'=>'Media & Entertainment','DAY2_TR4'=>'Manufacturing & Simulation');
    return isset($m[$v]) ? $m[$v] : $v;
}
function ufs_track_box_en($day, $tracks, $trackRemain) {
    $dlabel = ($day === 1) ? 'Day 1 · Aug 20 (Thu)' : 'Day 2 · Aug 21 (Fri)';
    $field  = ($day === 1) ? 'day1track' : 'day2track';
    echo '<div class="mb-6"><h3 class="text-sm font-bold text-white mb-3">'.e($dlabel).' — Select a track <span class="text-[#00C1D5]">*</span></h3><div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">';
    foreach ($tracks as $v=>$l) {
        $full = isset($trackRemain[$v]) && $trackRemain[$v] <= 0;
        echo '<label class="track-en '.($full?'opacity-40 cursor-not-allowed':'cursor-pointer hover:border-white/20').' p-3 border text-center text-sm font-medium transition-all border-[#27272a] text-[#71717a]">';
        echo '<input type="radio" name="'.$field.'" value="'.e($v).'" class="sr-only" '.($full?'disabled':'').'>'.e(ufs_track_label_en($v));
        if ($full) echo ' <span class="text-[#ff8674] text-xs">(Full)</span>';
        echo '</label>';
    }
    echo '</div></div>';
}

// ── POST: 100% 무료 쿠폰 등록 처리 ──
$err = '';
$gp = function($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; };
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name=$gp('apply_user_name'); $email=$gp('apply_user_email'); $phone=$gp('apply_user_phone');
    $job=$gp('apply_user_job'); $company=$gp('apply_user_company'); $depart=$gp('apply_user_depart');
    $grade=$gp('apply_user_grade'); $ex1=$gp('apply_user_ex1'); $tshirt=$gp('tshirt');
    $ticket=$gp('ticket'); $d1=$gp('day1track'); $d2=$gp('day2track');
    $ccode=strtoupper($gp('coupon_code')); $agree=$gp('agree_req');
    $mkt = ($gp('agree_mkt')!=='') ? '1' : '0';
    $pcode = isset($T2P[$ticket]) ? $T2P[$ticket] : '';
    $tracks = array();

    if ($agree!=='on' && $agree!=='Y' && $agree!=='1') { $err='Please agree to the required terms.'; }
    elseif ($name===''||$email===''||$phone===''||$company===''||$depart===''||$job===''||$grade===''||$ex1===''||$tshirt==='') { $err='Please complete all required fields.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $err='Please enter a valid email address.'; }
    elseif ($pcode==='') { $err='Please select a ticket.'; }
    elseif ($pcode==='NORMAL_ALL') { if(!isset($UFS_TRACKS[1][$d1])){$err='Please select a Day 1 track.';} elseif(!isset($UFS_TRACKS[2][$d2])){$err='Please select a Day 2 track.';} else { $tracks=array($d1,$d2); } }
    elseif ($pcode==='NORMAL_20') { if(!isset($UFS_TRACKS[1][$d1])){$err='Please select a Day 1 track.';} else { $tracks=array($d1); } }
    elseif ($pcode==='NORMAL_21') { if(!isset($UFS_TRACKS[2][$d2])){$err='Please select a Day 2 track.';} else { $tracks=array($d2); } }

    // 쿠폰 (100% 만 허용)
    if ($err==='') {
        $ck = function_exists('ufs_coupon_check') ? ufs_coupon_check($ccode) : array('ok'=>false);
        if (empty($ck['ok'])) { $err='Invalid or unavailable coupon code.'; }
        elseif ((int)$ck['percent'] < 100) { $err='This is a partial-discount coupon. Please register on the Korean page (identity verification and card payment required).'; }
    }
    // 중복(완료 등록 이메일)
    if ($err==='') {
        $dup = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE apply_user_email='".sql_real_escape_string($email)."' AND apply_temp_yn='N' AND apply_pay_status<>0");
        if ($dup && (int)$dup['c']>0) { $err='This email is already registered.'; }
    }
    // 트랙 정원
    if ($err==='') {
        foreach ($tracks as $tk) {
            $cap = sql_fetch("SELECT date1 FROM 2026_event_ticket WHERE name='".sql_real_escape_string($tk)."'");
            $capN = $cap ? (int)$cap['date1'] : 0;
            if ($capN > 0) {
                $reg = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE apply_temp_yn='N' AND apply_pay_status<>0 AND apply_track LIKE '%".sql_real_escape_string($tk)."%'");
                if ($reg && (int)$reg['c'] >= $capN) { $err='The selected track is full. Please choose another track.'; break; }
            }
        }
    }
    if ($err==='') {
        $f = function($v){ return sql_real_escape_string(strip_tags((string)$v)); };
        $pw = md5(str_replace("'","\\'",$email));
        $track_str = implode(',', $tracks);
        sql_query("INSERT INTO cb_unreal_2026_event2_apply
          (apply_user_name,apply_user_email,apply_user_phone,apply_user_job,apply_user_company,apply_user_depart,apply_user_grade,apply_user_ex1,
           apply_product_code,apply_product_name,apply_product_price,apply_tshirt,apply_track,apply_user_event_agree,apply_coupon_code,apply_coupon_pct,
           apply_password,apply_ci,apply_di,apply_pay_status,pay_complete,free_yn,apply_temp_yn,apply_group_code,apply_reg_datetime)
          VALUES ('".$f($name)."','".$f($email)."','".$f($phone)."','".$f($job)."','".$f($company)."','".$f($depart)."','".$f($grade)."','".$f($ex1)."',
           '".$f($pcode)."','".$f($PRODNAME[$pcode])."','0','".$f($tshirt)."','".$f($track_str)."','".$mkt."','".$f($ck['code'])."',".(int)$ck['percent'].",
           '".sql_real_escape_string($pw)."','','',10,'Y','Y','N','',now())");
        $row = sql_query("SELECT LAST_INSERT_ID() as idx")->fetch_array();
        $apply_no = (int)$row['idx'];
        if ($apply_no > 0) {
            if (function_exists('ufs_coupon_use')) ufs_coupon_use($ck['code']);
            if (function_exists('ufs_group_make_qr')) ufs_group_make_qr($apply_no, $pw);
            header('Location: ticket-complete.php?k='.rawurlencode(base64_encode($apply_no))); exit;
        }
        $err='Registration failed. Please try again.';
    }
}

$eb = ufs_is_earlybird();
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

<form name="frm" id="frm" method="post" action="ticket-coupon-en.php" onsubmit="return couponEnSubmit()">
<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-7xl mx-auto px-6">
    <a href="index.php" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> Back</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">Registration</h1>
    <p class="text-[#a1a1aa] mb-6">Please fill in the details below to complete your registration. No identity verification required.</p>
    <?php if ($err!==''): ?><div class="mb-8 px-4 py-3 border border-[#ff8674]/50 bg-[rgba(255,134,116,0.12)] text-[#ff8674] text-sm"><?= e($err) ?></div><?php endif; ?>

    <div class="grid lg:grid-cols-12 gap-8 items-start">
      <div class="lg:col-span-7 xl:col-span-8 space-y-4">

        <!-- Terms -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Agreement</h2>
          <div class="space-y-3">
            <label class="flex items-center gap-3 p-3 bg-[rgba(0,79,89,0.3)] border border-[rgba(0,193,213,0.3)] cursor-pointer">
              <input type="checkbox" id="agree_all" class="accent-[#00C1D5]"><span class="text-sm font-bold text-white">Agree to all</span></label>
            <div class="h-px bg-[#27272a]"></div>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_req" value="Y" class="agree-item mt-0.5 accent-[#00C1D5]">
              <span class="text-sm text-[#a1a1aa]">I agree to the <a href="legal-en.php#terms" target="_blank" rel="noopener" class="underline text-[#00C1D5]">Terms of Service</a>, <a href="legal-en.php#refund" target="_blank" rel="noopener" class="underline text-[#00C1D5]">Refund Policy</a> and <a href="legal-en.php#privacy" target="_blank" rel="noopener" class="underline text-[#00C1D5]">Privacy Policy</a><span class="ml-1 text-xs text-[#00C1D5]">(required)</span></span></label>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_mkt" value="Y" class="agree-item mt-0.5 accent-[#00C1D5]">
              <span class="text-sm text-[#a1a1aa]">I agree to receive marketing communications<span class="ml-1 text-xs text-[#71717a]">(optional)</span></span></label>
          </div>
        </div>

        <!-- Ticket -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Ticket</h2>
          <div class="grid gap-4" id="ticketGroup">
            <?php $opts = array(
              array('code'=>'ALL','pcode'=>'NORMAL_ALL','label'=>'2-Day Pass — Aug 20 (Thu) & 21 (Fri)'),
              array('code'=>'DAY1','pcode'=>'NORMAL_20','label'=>'1-Day Pass — Aug 20 (Thu)'),
              array('code'=>'DAY2','pcode'=>'NORMAL_21','label'=>'1-Day Pass — Aug 21 (Fri)'));
            foreach ($opts as $o): $price=(int)ufs_ticket_orig($o['pcode']); ?>
            <label class="ticket-en relative p-5 border cursor-pointer transition-all border-[#27272a] hover:border-white/20 block" data-code="<?= e($o['code']) ?>" data-price="<?= $price ?>">
              <input type="radio" name="ticket" value="<?= e($o['code']) ?>" class="sr-only">
              <div class="tk-label text-base font-bold text-white mb-1"><?= e($o['label']) ?></div>
              <div class="text-2xl font-black text-white">&#8361;<?= number_format($price) ?></div>
              <div class="text-xs text-[#71717a] mt-1">Free with a 100% coupon</div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Attendee info -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Attendee Information</h2>
          <div class="grid md:grid-cols-3 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Full name <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_name" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Email <span class="text-[#00C1D5]">*</span></label>
              <input type="email" name="apply_user_email" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Phone <span class="text-[#00C1D5]">*</span></label>
              <input type="tel" name="apply_user_phone" placeholder="+1 234 567 8900" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
        </div>

        <!-- Professional info -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">Professional Information</h2>
          <div class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Occupation <span class="text-[#00C1D5]">*</span></label>
                <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">Select</option><option>Professional</option><option>Student</option><option>Educator / Institution</option><option>Indie developer</option><option>Freelancer</option></select></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Company / Organization <span class="text-[#00C1D5]">*</span></label>
                <input type="text" name="apply_user_company" placeholder="Epic Games" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Department <span class="text-[#00C1D5]">*</span></label>
                <input type="text" name="apply_user_depart" placeholder="Dev Team" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm"></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Role <span class="text-[#00C1D5]">*</span></label>
                <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">Select</option><option>Visual Art</option><option>Programming</option><option>Production</option><option>Engineering</option><option>Design</option><option>Planning</option><option>R&amp;D</option><option>IT</option><option>Director / PD</option><option>Business / Marketing</option><option>C-level</option><option>Other</option></select></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Industry <span class="text-[#00C1D5]">*</span></label>
                <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">Select</option><option>Games</option><option>Film &amp; TV</option><option>Broadcast &amp; Live Events</option><option>Animation</option><option>Architecture</option><option>Automotive</option><option>Manufacturing / Simulation</option><option>Software &amp; Tools Dev</option><option>VR / AR</option><option>Education</option><option>Other</option></select></div>
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
          <p class="text-xs text-[#71717a] mt-2">※ For a 2-Day Pass, select a track for each day. For a 1-Day Pass, select the track for that day.</p>
        </div>

        <!-- Coupon (bottom) -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-2">Coupon <span class="text-[#00C1D5]">*</span></h2>
          <p class="text-xs text-[#71717a] mb-4">Enter your coupon code. Only 100% (complimentary) coupons can be used on this page.</p>
          <div class="flex gap-2">
            <input type="text" name="coupon_code" id="coupon_code" placeholder="e.g. UECPN-XXXX-XXXX" autocomplete="off"
                   value="<?= isset($_GET['coupon']) ? e(strtoupper(trim($_GET['coupon']))) : e(strtoupper($gp('coupon_code'))) ?>"
                   class="flex-1 bg-[#09090b] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm uppercase">
            <button type="button" onclick="couponEnApply()" class="px-6 py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-bold text-sm whitespace-nowrap">Apply</button>
          </div>
          <div id="coupon_result" class="mt-3 text-sm" style="display:none"></div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="lg:col-span-5 xl:col-span-4 self-start sticky top-28">
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 lg:p-8 space-y-6">
          <h3 class="text-lg font-bold text-white">Order Summary</h3>
          <div class="pb-5 border-b border-[#27272a]">
            <div class="text-[#00C1D5] font-bold text-sm mb-1" id="sumSub">&nbsp;</div>
            <div class="flex justify-between items-center"><span class="text-sm text-[#a1a1aa]">Coupon</span><span class="text-sm text-[#a1a1aa]" id="sumCoupon">—</span></div>
          </div>
          <div class="flex justify-between items-end"><span class="text-[#a1a1aa] font-medium">Total</span><span class="text-3xl font-black text-white" id="sumTotal">&mdash;</span></div>
          <button type="submit" class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all">Complete free registration</button>
          <p class="text-xs text-[#71717a]">A QR code and lookup link will be provided after registration.</p>
        </div>
      </div>
    </div>
  </div>
</div>
</form>

<?php include __DIR__ . '/_pf_footer.php'; ?>

<script>
var ppSelPrice=0, ppCoupon100=false;   // 정상가에서 시작 → 100% 쿠폰 시 FREE
function ppUpdateTotal(){
  var t=document.getElementById('sumTotal');
  if(ppCoupon100){ t.textContent='FREE'; }
  else { t.textContent = ppSelPrice ? ('₩'+ppSelPrice.toLocaleString('en-US')) : '—'; }
}
(function(){
  document.querySelectorAll('.ticket-en').forEach(function(card){
    card.addEventListener('click', function(){
      document.querySelectorAll('.ticket-en').forEach(function(c){ c.style.removeProperty('border-color'); c.style.removeProperty('background-color'); });
      card.style.setProperty('border-color','#00C1D5','important');   // T셔츠 선택과 동일: 시안 테두리 + 배경틴트 (Tailwind !important 대응)
      card.style.setProperty('background-color','rgba(0,79,89,0.2)','important');
      card.querySelector('input[type=radio]').checked = true;
      var l=card.querySelector('.tk-label'); if(l) document.getElementById('sumSub').textContent=l.textContent;
      ppSelPrice = parseInt(card.getAttribute('data-price'),10)||0;
      ppUpdateTotal();
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
function couponEnApply(){
  var el=document.getElementById('coupon_code'); var code=(el.value||'').trim().toUpperCase(); el.value=code;
  var box=document.getElementById('coupon_result'); box.style.display=''; box.className='mt-3 text-sm';
  if(!code){ box.style.color='#ff8674'; box.textContent='Please enter a coupon code.'; return; }
  box.style.color='#a1a1aa'; box.textContent='Checking…';
  fetch('group-coupon-check.php?code='+encodeURIComponent(code)).then(function(r){return r.json();}).then(function(d){
    if(!d.ok){ box.style.color='#ff8674'; box.textContent='Invalid or unavailable coupon.'; document.getElementById('sumCoupon').textContent='—'; ppCoupon100=false; ppUpdateTotal(); return; }
    var pct=parseInt(d.percent,10)||0;
    document.getElementById('sumCoupon').textContent=pct+'%';
    if(pct<100){ box.style.color='#ff8674'; box.textContent='This is a '+pct+'% partial-discount coupon. Please use the Korean registration page (payment required).'; ppCoupon100=false; }
    else { box.style.color='#00C1D5'; box.textContent='100% complimentary coupon applied — registration is free.'; ppCoupon100=true; }
    ppUpdateTotal();
  }).catch(function(){ box.style.color='#ff8674'; box.textContent='Error checking the coupon.'; });
}
function couponEnSubmit(){
  var code=(document.getElementById('coupon_code').value||'').trim();
  if(!code){ alert('Please enter your coupon code.'); return false; }
  if(!document.querySelector('input[name="ticket"]:checked')){ alert('Please select a ticket.'); return false; }
  if(!document.querySelector('input[name="agree_req"]').checked){ alert('Please agree to the required terms.'); return false; }
  return true;
}
(function(){ var el=document.getElementById('coupon_code'); if(el && (el.value||'').trim()!==''){ setTimeout(couponEnApply, 60); } })();
</script>
</body>
</html>
