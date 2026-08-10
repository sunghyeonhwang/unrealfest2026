<?php
/* Unreal Fest Seoul 2026 — 쿠폰 등록 전용(한국어·본인인증) (ticket-coupon.php)
 * 양일권/1일권(Day1·Day2) 모두 선택. 쿠폰 패널 최하단·항상 노출($ufs_force_coupon)·?coupon= 프리필·
 * coupon_flow=1로 토글 무관 적용. 본인인증+카드. 처리=apply_pay.php. noindex.
 * 공통 partial + assets/js/ticket.js 공유(트랙 토글). 문구는 표준 등록 페이지 톤.
 */
require __DIR__ . '/_ticket_init.php';
$ufs_force_coupon = true;   // 토글과 무관하게 쿠폰 패널 노출/적용
// 무인증 모드: ?coupon= 이 유효한 100% 무료 쿠폰이면 본인인증 없이 수동입력 등록(즉시 완료·QR).
//   부분할인(유료) 쿠폰은 기존대로 본인인증+카드 유지.
$ufs_noauth = false;
$__pc = isset($_GET['coupon']) ? trim($_GET['coupon']) : '';
if ($__pc !== '' && function_exists('ufs_coupon_check')) {
    $__ck = ufs_coupon_check($__pc);
    if (!empty($__ck['ok']) && (int)$__ck['percent'] >= 100) $ufs_noauth = true;
}
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>등록 — Unreal Fest Seoul 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
<?php include __DIR__ . '/_favicon.php'; ?>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">

<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<form name="frm" id="frm" method="post" action="apply_pay.php" onsubmit="return validateForm()">
<input type="hidden" name="apply_ci" id="apply_ci" value="<?= e($sess_ci) ?>">
<input type="hidden" name="apply_di" id="apply_di" value="<?= e($sess_di) ?>">
<input type="hidden" name="apply_real_type" id="apply_real_type" value="">
<input type="hidden" name="apply_product_code" id="apply_product_code" value="">
<input type="hidden" name="apply_product_name" id="apply_product_name" value="">
<input type="hidden" name="apply_product_price" id="apply_product_price" value="">
<input type="hidden" name="apply_track" id="apply_track" value="">
<input type="hidden" name="coupon_flow" value="1">

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-7xl mx-auto px-6">
    <a href="index.php" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">참가 등록</h1>
    <p class="text-[#a1a1aa] mb-10">8월 20일(목)~21일(금) 진행되는 프로그램에 참여할 수 있습니다. 티켓을 선택하고 아래 정보를 입력해 주세요.</p>

    <div class="grid lg:grid-cols-12 gap-8 items-start">
      <!-- 좌측 폼 -->
      <div class="lg:col-span-7 xl:col-span-8 space-y-4">

        <?php include __DIR__ . '/_ticket_agree.php'; ?>

        <!-- 티켓 선택 (양일권 / 1일권 Day1 / Day2) -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">티켓 선택</h2>
          <div class="grid gap-4 mb-8" id="ticketGroup">
            <label class="ticket-card relative p-5 border cursor-pointer transition-all border-[#27272a] hover:border-white/20"
                   data-code="ALL" data-price="<?= ufs_ticket_price('NORMAL_ALL') ?>" data-orig="<?= ufs_ticket_orig('NORMAL_ALL') ?>" data-sub="양일권 (8월 20일-21일)" data-benefit="양일간 전체 세션 참여" data-pcode="NORMAL_ALL" data-days="1,2">
              <input type="radio" name="ticket" value="ALL" class="sr-only">
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
            <div class="grid md:grid-cols-2 gap-4">
            <label class="ticket-card relative p-5 border cursor-pointer transition-all border-[#27272a] hover:border-white/20"
                   data-code="DAY1" data-price="<?= ufs_ticket_price('NORMAL_20') ?>" data-orig="<?= ufs_ticket_orig('NORMAL_20') ?>" data-sub="1일권 - 8월 20일(목)" data-benefit="8월 20일 전체 세션 참여" data-pcode="NORMAL_20" data-days="1">
              <input type="radio" name="ticket" value="DAY1" class="sr-only">
              <div class="text-base font-bold text-white mb-3">1일권 - 8월 20일(목)</div>
              <div class="mb-1">
                <?php if (ufs_is_earlybird()): ?>
                <div class="text-base text-[#71717a] line-through">₩<?= number_format(ufs_ticket_orig('NORMAL_20')) ?></div>
                <div class="text-xs font-bold text-[#00C1D5] my-0.5"><?= e(ufs_promo_ticket_note()) ?></div>
                <?php endif; ?>
                <div class="text-2xl font-black text-white">₩<?= number_format(ufs_ticket_price('NORMAL_20')) ?></div>
              </div>
              <div class="tk-check absolute top-3 right-3 hidden"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg></div>
            </label>
            <label class="ticket-card relative p-5 border cursor-pointer transition-all border-[#27272a] hover:border-white/20"
                   data-code="DAY2" data-price="<?= ufs_ticket_price('NORMAL_21') ?>" data-orig="<?= ufs_ticket_orig('NORMAL_21') ?>" data-sub="1일권 - 8월 21일(금)" data-benefit="8월 21일 전체 세션 참여" data-pcode="NORMAL_21" data-days="2">
              <input type="radio" name="ticket" value="DAY2" class="sr-only">
              <div class="text-base font-bold text-white mb-3">1일권 - 8월 21일(금)</div>
              <div class="mb-1">
                <?php if (ufs_is_earlybird()): ?>
                <div class="text-base text-[#71717a] line-through">₩<?= number_format(ufs_ticket_orig('NORMAL_21')) ?></div>
                <div class="text-xs font-bold text-[#00C1D5] my-0.5"><?= e(ufs_promo_ticket_note()) ?></div>
                <?php endif; ?>
                <div class="text-2xl font-black text-white">₩<?= number_format(ufs_ticket_price('NORMAL_21')) ?></div>
              </div>
              <div class="tk-check absolute top-3 right-3 hidden"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg></div>
            </label>
            </div>
          </div>
          <div class="bg-[#111115] p-5 border border-[#27272a]">
            <h4 class="text-sm font-bold text-[#a1a1aa] mb-3">혜택</h4>
            <div class="grid sm:grid-cols-2 gap-2 text-sm text-[#a1a1aa]">
              <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#00C1D5]"></span><span id="benefitSession">양일간 전체 세션 참여</span></div>
              <?php foreach (array('한정판 굿즈 제공','Q&A 참여','전시 및 체험존 이용','이벤트 및 경품 참여') as $b): ?>
              <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#00C1D5]"></span><?= e($b) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <?php include __DIR__ . ($ufs_noauth ? '/_ticket_fields_noauth.php' : '/_ticket_fields.php'); ?>

        <!-- 트랙 선택 (선택 티켓에 따라 ticket.js가 토글) -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <?php ufs_track_box(1, $UFS_TRACKS[1], $trackRemain); ?>
          <?php ufs_track_box(2, $UFS_TRACKS[2], $trackRemain); ?>
          <p class="text-xs text-[#71717a] mt-2">※ 현장 혼잡 시 선택한 트랙 참석자가 우선 입장될 수 있습니다.</p>
        </div>

        <?php include __DIR__ . '/_ticket_coupon.php'; /* 전용 페이지: 항상 노출 + ?coupon= 프리필 · 최하단 */ ?>
      </div>

      <?php include __DIR__ . '/_ticket_sidebar.php'; ?>
    </div>
  </div>
</div>
</form>

<?php include __DIR__ . '/_pf_footer.php'; ?>

<!-- 본인인증 팝업 타깃 (2025 real/ 재사용) -->
<form name="form1" id="form1" method="post"></form>
<form name="kcbResultForm" id="kcbResultForm">
  <input type="hidden" name="CP_CD" value=""><input type="hidden" name="TX_SEQ_NO" value=""><input type="hidden" name="RSLT_CD" value="">
  <input type="hidden" name="RSLT_MSG" value=""><input type="hidden" name="RETURN_MSG" value=""><input type="hidden" name="RSLT_NAME" value="">
  <input type="hidden" name="RSLT_BIRTHDAY" value=""><input type="hidden" name="RSLT_SEX_CD" value=""><input type="hidden" name="RSLT_NTV_FRNR_CD" value="">
  <input type="hidden" name="DI" value=""><input type="hidden" name="CI" value=""><input type="hidden" name="CI_UPDATE" value="">
  <input type="hidden" name="TEL_COM_CD" value=""><input type="hidden" name="TEL_NO" value="">
</form>

<?php if ($ufs_noauth): ?><script>window.UFS_NOAUTH=true;</script><?php endif; ?>
<script src="<?= asset_v('assets/js/ticket.js') ?>"></script>
<script>selectTicket('ALL');</script>
</body>
</html>
