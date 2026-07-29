<?php
/* Unreal Fest Seoul 2026 — 해외(영문) 등록 완료 페이지 (ticket-en-complete.php)
 * Dodo return_url. ?apply_no=..&payment_id=..&status=.. 수신.
 * 확정은 원칙적으로 웹훅에서 처리하되, 여기서 결제상태를 API로 재확인해 미확정이면 즉시 확정(폴백).
 * → 웹훅 시크릿 미설정 상태에서도 완료가 동작. noindex. PHP 7.0.
 */
require __DIR__ . '/_ticket_init.php';
require_once __DIR__ . '/_paypal.php';        // ufs_pp_capture_order
require_once __DIR__ . '/_paypal_apply.php';  // ufs_paypal_finalize_apply

$apply_no = isset($_GET['apply_no']) ? (int)$_GET['apply_no'] : 0;
$order_id = isset($_GET['token']) ? preg_replace('/[^A-Za-z0-9\-]/','',$_GET['token']) : '';   // PayPal 승인 후 order id
$cancelled = isset($_GET['paypal']) && $_GET['paypal'] === 'cancel';

$row = $apply_no > 0 ? sql_fetch("SELECT * FROM cb_unreal_2026_event2_apply WHERE apply_no=".$apply_no) : null;
$state = 'pending';   // pending | done | failed | notfound

if (!$row) {
    $state = 'notfound';
} elseif ($row['pay_complete'] === 'Y' && (int)$row['apply_pay_status'] !== 0) {
    $state = 'done';
} elseif ($cancelled) {
    $state = 'failed';
} elseif ($order_id !== '') {
    // 승인 후 복귀 → 주문 캡처(결제 확정) → finalize(멱등)
    $cap = ufs_pp_capture_order($order_id);
    if (!empty($cap['ok'])) {
        $fr = ufs_paypal_finalize_apply($apply_no, $cap['capture_id'], $cap['amount']);
        if (!empty($fr['ok'])) { $state = 'done'; if (!empty($fr['row'])) $row = $fr['row']; }
        else { $state = 'pending'; }
    } else {
        // 캡처 실패(거절/보류) — 결제 안 됨
        $state = 'failed';
    }
} else {
    $state = 'pending';
}

$PRODNAME = array('NORMAL_ALL'=>'2-Day Pass (Aug 20-21)','NORMAL_20'=>'1-Day Pass (Aug 20)','NORMAL_21'=>'1-Day Pass (Aug 21)');
$qr_url = ($row && $state==='done' && file_exists(__DIR__.'/qrdata/'.$apply_no.'.jpg')) ? ('qrdata/'.$apply_no.'.jpg') : '';
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Registration — Unreal Fest Seoul 2026</title>
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
<?php include __DIR__ . '/_favicon.php'; ?>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Segoe UI',Roboto,sans-serif">
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">Home</a>
  </div>
</header>

<div class="min-h-screen flex items-center justify-center px-6 pt-24 pb-16">
  <div class="max-w-lg w-full">
  <?php if ($state === 'done'): ?>
    <div class="text-center">
      <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-[rgba(0,193,213,0.15)] flex items-center justify-center">
        <svg class="w-8 h-8 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-white mb-2">Registration confirmed</h1>
      <p class="text-[#a1a1aa] mb-8">Your payment was received. A confirmation email with your QR code has been sent to <strong class="text-white"><?= e($row['apply_user_email']) ?></strong>.</p>
      <?php if ($qr_url !== ''): ?>
      <div class="bg-white p-4 inline-block mb-6"><img src="<?= e($qr_url) ?>" alt="Admission QR" width="180" style="display:block"></div>
      <p class="text-xs text-[#71717a] mb-8">Present this QR code at the venue entrance.</p>
      <?php endif; ?>
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 text-left text-sm space-y-2 mb-8">
        <div class="flex justify-between"><span class="text-[#71717a]">Name</span><span><?= e($row['apply_user_name']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#71717a]">Ticket</span><span><?= e(isset($PRODNAME[$row['apply_product_code']])?$PRODNAME[$row['apply_product_code']]:$row['apply_product_code']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#71717a]">Amount</span><span><?php
          $pm = isset($row['pay_paymethod']) ? $row['pay_paymethod'] : '';
          if ($pm === 'paypal') { echo '$'.e($row['pay_totprice']).' USD'; }
          else { $amt_krw = ($row['pay_totprice']!=='' ? (int)$row['pay_totprice'] : (int)$row['apply_product_price']); echo '&#8361;'.number_format($amt_krw).' KRW'; }
        ?></span></div>
        <div class="flex justify-between"><span class="text-[#71717a]">T-shirt</span><span><?= e($row['apply_tshirt']) ?> <span class="text-[#71717a]">(on-site pickup)</span></span></div>
      </div>
      <a href="myticket.php?lang=en" class="inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-3 font-bold">View my ticket</a>
    </div>
  <?php elseif ($state === 'pending'): ?>
    <div class="text-center">
      <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-[rgba(255,193,7,0.12)] flex items-center justify-center">
        <svg class="w-8 h-8 text-[#f0c000]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-white mb-2">Confirming your payment…</h1>
      <p class="text-[#a1a1aa] mb-8">We're finalizing your registration. If your payment succeeded, you'll receive a confirmation email with your QR code shortly. You can also look up your ticket below.</p>
      <div class="flex gap-3 justify-center">
        <a href="javascript:location.reload()" class="inline-block border border-[#27272a] hover:border-white/30 text-white px-6 py-3 font-bold">Refresh</a>
        <a href="myticket.php?lang=en" class="inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-6 py-3 font-bold">Look up my ticket</a>
      </div>
    </div>
  <?php elseif ($state === 'failed'): ?>
    <div class="text-center">
      <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-[rgba(255,134,116,0.12)] flex items-center justify-center">
        <svg class="w-8 h-8 text-[#ff8674]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-white mb-2">Payment not completed</h1>
      <p class="text-[#a1a1aa] mb-8">Your payment was not completed and you have not been charged. Please try again.</p>
      <a href="ticket-en.php" class="inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-3 font-bold">Back to registration</a>
    </div>
  <?php else: ?>
    <div class="text-center">
      <h1 class="text-2xl font-bold text-white mb-2">Registration not found</h1>
      <p class="text-[#a1a1aa] mb-8">We couldn't find this registration. If you completed a payment, please contact <a href="mailto:info@epiclounge.co.kr" class="text-[#00C1D5]">info@epiclounge.co.kr</a>.</p>
      <a href="ticket-en.php" class="inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-3 font-bold">Back to registration</a>
    </div>
  <?php endif; ?>
  </div>
</div>
</body>
</html>
