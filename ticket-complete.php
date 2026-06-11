<?php
/* Unreal Fest Seoul 2026 — 등록 완료 (ticket-complete.php) */
include_once "../common.php";
require __DIR__ . '/_assets.php';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$k = isset($_GET['k']) ? $_GET['k'] : '';
$apply_no = preg_replace('/[^0-9]/', '', base64_decode($k));
$is_vbank = isset($_GET['vbank']);
$is_online = isset($_GET['online']);
$row = $apply_no !== '' ? sql_fetch("select * from cb_unreal_2026_event2_apply where apply_no = '".intval($apply_no)."'") : null;
$qr_jpg = ($apply_no !== '' && file_exists(__DIR__."/qrdata/".$apply_no.".jpg")) ? "qrdata/".$apply_no.".jpg" : '';
?>
<!DOCTYPE html>
<html lang="ko" class="dark"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<script>/* INICIS 결제 레이어(iframe) 안에서 로드된 경우 최상위로 탈출 → 스크롤 잠김 방지 */try{if(window.top!==window.self){window.top.location.replace(window.location.href);}}catch(e){}</script>
<title>등록 완료 — Unreal Fest Seoul 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<main class="pt-32 pb-24 min-h-screen">
  <div class="max-w-2xl mx-auto px-6 text-center">
    <?php if (!$row): ?>
      <h1 class="text-3xl font-bold mb-4">등록 정보를 찾을 수 없습니다.</h1>
      <a href="index.php" class="clip-btn inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-6 py-3 font-bold">홈으로</a>
    <?php elseif ($is_online): ?>
      <div class="w-16 h-16 rounded-full bg-[rgba(0,193,213,0.15)] flex items-center justify-center mx-auto mb-6">
        <svg class="w-9 h-9 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
      </div>
      <h1 class="text-3xl md:text-4xl font-bold mb-3">온라인 등록이 완료되었습니다!</h1>
      <p class="text-[#a1a1aa] mb-10">행사 당일 첫 세션 30분 전, 등록하신 이메일과 카카오톡(또는 문자)으로 <span class="text-white">시청 링크</span>가 발송됩니다.</p>
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 text-left space-y-3 mb-10">
        <div class="flex justify-between"><span class="text-[#a1a1aa]">이름</span><span class="font-bold"><?= e($row['apply_user_name']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#a1a1aa]">이메일</span><span><?= e($row['apply_user_email']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#a1a1aa]">등록 유형</span><span class="text-[#00C1D5] font-bold">온라인 무료</span></div>
      </div>
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="myticket.php" class="clip-btn bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-4 font-bold">등록 확인</a>
        <a href="index.php" class="clip-btn bg-white/10 hover:bg-white/20 text-white px-8 py-4 font-bold">홈으로</a>
      </div>
    <?php elseif ($is_vbank): ?>
      <div class="w-16 h-16 rounded-full bg-[rgba(0,193,213,0.15)] flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
      </div>
      <h1 class="text-3xl font-bold mb-3">가상계좌 발급 완료</h1>
      <p class="text-[#a1a1aa] mb-8">아래 계좌로 입금하시면 등록이 최종 확정되며, 체크인 QR이 문자로 발송됩니다.</p>
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 text-left space-y-2 mb-8">
        <div class="flex justify-between"><span class="text-[#a1a1aa]">입금 계좌</span><span class="font-bold"><?= e(isset($_SESSION['VBANK_NUM'])?$_SESSION['VBANK_NUM']:'') ?></span></div>
        <div class="flex justify-between"><span class="text-[#a1a1aa]">입금 금액</span><span class="font-bold text-[#00C1D5]">₩<?= e(number_format((int)(isset($_SESSION['VBANK_AMOUNT'])?$_SESSION['VBANK_AMOUNT']:$row['apply_product_price']))) ?></span></div>
      </div>
      <p class="text-xs text-[#71717a] mb-8">입금 안내가 입력하신 연락처로 문자 발송되었습니다.</p>
      <a href="index.php" class="clip-btn inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-4 font-bold">홈으로</a>
    <?php else: ?>
      <div class="w-16 h-16 rounded-full bg-[rgba(0,193,213,0.15)] flex items-center justify-center mx-auto mb-6">
        <svg class="w-9 h-9 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
      </div>
      <h1 class="text-3xl md:text-4xl font-bold mb-3">등록이 완료되었습니다!</h1>
      <p class="text-[#a1a1aa] mb-10">행사 당일 아래 QR 코드로 현장 체크인하세요.<br>QR 코드와 등록 정보는 입력하신 연락처로 문자(MMS) 발송됩니다.</p>

      <?php if ($qr_jpg): ?>
      <div class="bg-white p-5 inline-block mb-3 clip-tr-16">
        <img src="<?= asset_v($qr_jpg) ?>" alt="체크인 QR" class="w-56 h-56">
      </div>
      <p class="text-xs text-[#71717a] mb-3">QR 이미지를 저장하거나 카카오톡으로 보관해 두시면 현장 체크인이 빠릅니다.</p>
      <?php $qr_rel=$qr_jpg; $qr_name=$row['apply_user_name']; $qr_product=$row['apply_product_name']; include __DIR__.'/_qr_actions.php'; ?>
      <div class="mb-8"></div>
      <?php endif; ?>

      <div class="bg-[#0e0f14] border border-[#27272a] p-6 text-left space-y-3 mb-10">
        <div class="flex justify-between"><span class="text-[#a1a1aa]">이름</span><span class="font-bold"><?= e($row['apply_user_name']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#a1a1aa]">티켓</span><span class="font-bold"><?= e($row['apply_product_name']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#a1a1aa]">결제 금액</span><span class="font-bold text-[#00C1D5]">₩<?= e(number_format((int)$row['apply_product_price'])) ?></span></div>
        <div class="flex justify-between"><span class="text-[#a1a1aa]">이메일</span><span><?= e($row['apply_user_email']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#a1a1aa]">연락처</span><span><?= e($row['apply_user_phone']) ?></span></div>
      </div>
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="myticket.php" class="clip-btn bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-4 font-bold">등록 확인</a>
        <a href="index.php" class="clip-btn bg-white/10 hover:bg-white/20 text-white px-8 py-4 font-bold">홈으로</a>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/_pf_footer.php'; ?>
</body></html>
