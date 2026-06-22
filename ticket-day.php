<?php
/* Unreal Fest Seoul 2026 — 일일권 오프라인 등록 (ticket-day.php)
 * Day 1 / Day 2 중 택1. 선택한 날 트랙만 선택. product=NORMAL_20 | NORMAL_21.
 * ?d=2 진입 시 Day 2 기본 선택. 공통 partial + assets/js/ticket.js 공유.
 */
require __DIR__ . '/_ticket_init.php';
$d = (isset($_GET['d']) && $_GET['d'] === '2') ? 'DAY2' : 'DAY1';
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- robots: 공개 오픈 — 검색 색인 허용 -->
<title>일일권 등록 — Unreal Fest Seoul 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
<?php include __DIR__ . '/_favicon.php'; ?>
<?php if (defined('_GNUBOARD_')) include __DIR__ . '/../inc/marketing_head.php'; /* 라운지 전역 SEO/마케팅 */ ?>
<?php include __DIR__.'/_wcs.php'; ?>
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

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-7xl mx-auto px-6">
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">1일권 등록</h1>
    <p class="text-[#a1a1aa] mb-10">8월 20일(목) 또는 8월 21일(금) 중 참석하실 날짜의 프로그램에 참여할 수 있습니다. 등록을 위해 아래 정보를 입력해 주세요.</p>

    <div class="grid lg:grid-cols-12 gap-8 items-start">
      <!-- 좌측 폼 -->
      <div class="lg:col-span-7 xl:col-span-8 space-y-4">

        <?php include __DIR__ . '/_ticket_agree.php'; ?>

        <!-- 티켓 선택 (Day1 / Day2) -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">참석일 선택</h2>
          <div class="grid md:grid-cols-2 gap-4 mb-8" id="ticketGroup">
            <label class="ticket-card relative p-5 border cursor-pointer transition-all border-[#27272a] hover:border-white/20"
                   data-code="DAY1" data-price="<?= ufs_ticket_price('NORMAL_20') ?>" data-orig="<?= ufs_ticket_orig('NORMAL_20') ?>" data-sub="1일권 - 8월 20일(목)" data-benefit="8월 20일 전체 세션 참여" data-pcode="NORMAL_20" data-days="1">
              <input type="radio" name="ticket" value="DAY1" class="sr-only">
              <div class="text-base font-bold text-white mb-3">1일권 - 8월 20일(목)</div>
              <div class="mb-1">
                <?php if (ufs_is_earlybird()): ?>
                <div class="text-base text-[#71717a] line-through">₩<?= number_format(ufs_ticket_orig('NORMAL_20')) ?></div>
                <div class="text-xs font-bold text-[#00C1D5] my-0.5">얼리버드 50% 할인</div>
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
                <div class="text-xs font-bold text-[#00C1D5] my-0.5">얼리버드 50% 할인</div>
                <?php endif; ?>
                <div class="text-2xl font-black text-white">₩<?= number_format(ufs_ticket_price('NORMAL_21')) ?></div>
              </div>
              <div class="tk-check absolute top-3 right-3 hidden"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg></div>
            </label>
          </div>
          <div class="bg-[#111115] p-5 border border-[#27272a]">
            <h4 class="text-sm font-bold text-[#a1a1aa] mb-3">혜택</h4>
            <div class="grid sm:grid-cols-2 gap-2 text-sm text-[#a1a1aa]">
              <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#00C1D5]"></span><span id="benefitSession">8월 20일 전체 세션 참여</span></div>
              <?php foreach (array('한정판 굿즈 제공','Q&A 참여','전시 및 체험존 이용','이벤트 및 경품 참여') as $b): ?>
              <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#00C1D5]"></span><?= e($b) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <?php include __DIR__ . '/_ticket_fields.php'; ?>

        <!-- 트랙 선택 (선택일에 따라 ticket.js가 토글) -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <?php ufs_track_box(1, $UFS_TRACKS[1], $trackRemain); ?>
          <?php ufs_track_box(2, $UFS_TRACKS[2], $trackRemain); ?>
          <p class="text-xs text-[#71717a] mt-2">※ 현장 혼잡 시 선택한 트랙 참석자가 우선 입장될 수 있습니다.</p>
        </div>
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

<script src="<?= asset_v('assets/js/ticket.js') ?>"></script>
<script>selectTicket('<?= $d ?>');</script>
</body>
</html>
