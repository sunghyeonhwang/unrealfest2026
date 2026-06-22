<?php
/* Unreal Fest Seoul 2026 — 단체 등록 (ticket-group.php) [Phase 1: 입력 폼]
 * 대표자(본인인증+기존 등록필드 동일) + 멤버(이름·연락처·트랙) 최소 5명~최대 30명.
 * 상품: 양일권/1일권 선택. 결제수단: 신용카드 / 무통장 입금.
 * 제출 → ticket-group-confirm.php (Phase 2). 공통: _ticket_init.php / _ticket_fields.php / ticket.js
 */
require __DIR__ . '/_ticket_init.php';
$P_ALL = ufs_ticket_price('NORMAL_ALL');
$P_20  = ufs_ticket_price('NORMAL_20');
$P_21  = ufs_ticket_price('NORMAL_21');
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>단체 등록 — Unreal Fest Seoul 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all} .gmember-grid{grid-template-columns:28px 1.2fr 1.2fr 1fr 1fr 32px}</style>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">

<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<form name="frm" id="frm" method="post" action="ticket-group-confirm.php" onsubmit="return gValidate()">
<input type="hidden" name="apply_ci" id="apply_ci" value="<?= e($sess_ci) ?>">
<input type="hidden" name="apply_di" id="apply_di" value="<?= e($sess_di) ?>">
<input type="hidden" name="apply_real_type" id="apply_real_type" value="">
<input type="hidden" name="group_product" id="group_product" value="NORMAL_ALL">
<input type="hidden" name="group_paymethod" id="group_paymethod" value="card">

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-7xl mx-auto px-6">
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">단체 등록</h1>
    <p class="text-[#a1a1aa] mb-10">5인 이상 단체로 등록하실 수 있습니다. 대표자 1인은 본인 인증 후 대표 정보를 입력하고, 함께 참석하실 인원(최소 4인, 최대 29인 추가)의 명단을 작성해 주세요.</p>

    <div class="grid lg:grid-cols-12 gap-8 items-start">
      <div class="lg:col-span-7 xl:col-span-8 space-y-4">

        <?php include __DIR__ . '/_ticket_agree.php'; ?>

        <!-- 상품 선택 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">티켓 종류</h2>
          <div class="grid sm:grid-cols-3 gap-3" id="gProduct">
            <label class="gprod relative p-4 border transition-all border-[#27272a] cursor-pointer hover:border-white/20" data-code="NORMAL_ALL" data-price="<?= $P_ALL ?>" data-days="1,2">
              <input type="radio" name="gprod" value="NORMAL_ALL" class="sr-only" checked>
              <div class="text-sm font-bold text-white mb-1">양일권</div>
              <div class="text-xs text-[#71717a] mb-2">8월 20일(목)~21일(금)</div>
              <div class="text-lg font-black text-white">₩<?= number_format($P_ALL) ?><span class="text-xs font-normal text-[#71717a]">/1인</span></div>
            </label>
            <label class="gprod relative p-4 border transition-all border-[#27272a] cursor-pointer hover:border-white/20" data-code="NORMAL_20" data-price="<?= $P_20 ?>" data-days="1">
              <input type="radio" name="gprod" value="NORMAL_20" class="sr-only">
              <div class="text-sm font-bold text-white mb-1">1일권 · Day 1</div>
              <div class="text-xs text-[#71717a] mb-2">8월 20일(목)</div>
              <div class="text-lg font-black text-white">₩<?= number_format($P_20) ?><span class="text-xs font-normal text-[#71717a]">/1인</span></div>
            </label>
            <label class="gprod relative p-4 border transition-all border-[#27272a] cursor-pointer hover:border-white/20" data-code="NORMAL_21" data-price="<?= $P_21 ?>" data-days="2">
              <input type="radio" name="gprod" value="NORMAL_21" class="sr-only">
              <div class="text-sm font-bold text-white mb-1">1일권 · Day 2</div>
              <div class="text-xs text-[#71717a] mb-2">8월 21일(금)</div>
              <div class="text-lg font-black text-white">₩<?= number_format($P_21) ?><span class="text-xs font-normal text-[#71717a]">/1인</span></div>
            </label>
          </div>
        </div>

        <!-- 대표자 정보 (본인인증 + 기존 등록필드 동일) -->
        <div class="text-sm font-bold text-[#00C1D5] mt-2 mb-1 px-1">대표자 정보</div>
        <?php include __DIR__ . '/_ticket_fields.php'; ?>

        <!-- 대표자 트랙 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">대표자 참석 트랙</h2>
          <?php ufs_track_box(1, $UFS_TRACKS[1], $trackRemain); ?>
          <?php ufs_track_box(2, $UFS_TRACKS[2], $trackRemain); ?>
          <p class="text-xs text-[#71717a] mt-2">※ 현장 혼잡 시 선택한 트랙 참석자가 우선 입장될 수 있습니다.</p>
        </div>

        <!-- 멤버 명단 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-bold text-white">함께 참석하는 인원 <span id="gMemCount" class="text-[#00C1D5] text-sm">(0명)</span></h2>
            <div class="flex gap-2">
              <a href="downloads/group_template.csv" download class="px-3 py-2 text-xs font-bold border border-[#27272a] text-[#a1a1aa] hover:border-white/20 hover:text-white transition-all">양식 다운로드</a>
              <label class="px-3 py-2 text-xs font-bold border border-[#27272a] text-[#a1a1aa] hover:border-white/20 hover:text-white transition-all cursor-pointer">양식 업로드<input type="file" id="gUpload" accept=".csv" class="hidden"></label>
            </div>
          </div>
          <p class="text-xs text-[#71717a] mb-4">대표자 외 최소 4인 ~ 최대 29인. 멤버는 이름·연락처·참석 트랙만 입력합니다.</p>

          <div class="hidden md:grid gmember-grid gap-2 px-1 pb-2 text-[11px] text-[#71717a] font-bold border-b border-[#27272a]">
            <div>#</div><div>이름</div><div>연락처</div><div data-col-day1>Day1 트랙</div><div data-col-day2>Day2 트랙</div><div></div>
          </div>
          <div id="gMembers" class="space-y-2 mt-2"></div>

          <button type="button" id="gAddBtn" class="mt-4 w-full py-3 border border-dashed border-[#27272a] text-[#a1a1aa] hover:border-[#00C1D5] hover:text-[#00C1D5] transition-all text-sm font-bold">+ 인원 추가</button>
        </div>

        <!-- 결제 수단 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">결제 수단</h2>
          <div class="grid sm:grid-cols-2 gap-3" id="gPay">
            <label class="gpay p-4 border transition-all border-[#00C1D5] bg-[rgba(0,79,89,0.2)] cursor-pointer" data-pay="card">
              <input type="radio" name="gpay" value="card" class="sr-only" checked>
              <div class="text-sm font-bold text-white">신용카드</div>
              <div class="text-xs text-[#71717a] mt-1">PG 결제창에서 일괄 결제</div>
            </label>
            <label class="gpay p-4 border transition-all border-[#27272a] cursor-pointer hover:border-white/20" data-pay="bank">
              <input type="radio" name="gpay" value="bank" class="sr-only">
              <div class="text-sm font-bold text-white">무통장 입금</div>
              <div class="text-xs text-[#71717a] mt-1">계좌·금액·기한 안내(LMS) 후 입금</div>
            </label>
          </div>
        </div>
      </div>

      <!-- 우측 요약 -->
      <div class="lg:col-span-5 xl:col-span-4">
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 lg:sticky lg:top-24">
          <h2 class="text-lg font-bold text-white mb-5">등록 요약</h2>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><span class="text-[#71717a]">티켓</span><span id="sumProd" class="font-bold text-right">양일권</span></div>
            <div class="flex justify-between gap-4"><span class="text-[#71717a]">1인 금액</span><span id="sumUnit" class="font-bold text-right">₩<?= number_format($P_ALL) ?></span></div>
            <div class="flex justify-between gap-4"><span class="text-[#71717a]">인원 (대표자 포함)</span><span id="sumPeople" class="font-bold text-right">1명</span></div>
            <div class="border-t border-[#27272a] pt-3 flex justify-between gap-4 items-end"><span class="text-[#71717a]">총 결제 금액</span><span id="sumTotal" class="text-2xl font-black text-[#00C1D5]">₩0</span></div>
          </div>
          <button type="submit" class="mt-6 w-full py-4 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors">등록 정보 확인</button>
          <p class="text-xs text-[#71717a] mt-3 leading-relaxed">대표자 본인 인증 후 진행됩니다. 무통장 입금 선택 시 대표자 연락처로 계좌·금액·입금 기한이 안내됩니다.</p>
        </div>
      </div>
    </div>
  </div>
</div>
</form>

<!-- 본인인증 팝업 타깃 (대표자) -->
<form name="form1" id="form1" method="post"></form>
<form name="kcbResultForm" id="kcbResultForm">
  <input type="hidden" name="CP_CD" value=""><input type="hidden" name="TX_SEQ_NO" value=""><input type="hidden" name="RSLT_CD" value="">
  <input type="hidden" name="RSLT_MSG" value=""><input type="hidden" name="RETURN_MSG" value=""><input type="hidden" name="RSLT_NAME" value="">
  <input type="hidden" name="RSLT_BIRTHDAY" value=""><input type="hidden" name="RSLT_SEX_CD" value=""><input type="hidden" name="RSLT_NTV_FRNR_CD" value="">
  <input type="hidden" name="DI" value=""><input type="hidden" name="CI" value=""><input type="hidden" name="CI_UPDATE" value="">
  <input type="hidden" name="TEL_COM_CD" value=""><input type="hidden" name="TEL_NO" value="">
</form>

<script>
/* 트랙 옵션 (단체 멤버용) — _ticket_init.php $UFS_TRACKS 와 동일 */
window.UFS_TRACKS = {
  1: <?= json_encode($UFS_TRACKS[1], JSON_UNESCAPED_UNICODE) ?>,
  2: <?= json_encode($UFS_TRACKS[2], JSON_UNESCAPED_UNICODE) ?>
};
window.UFS_MIN_MEMBERS = 4;   // 대표자 외 최소
window.UFS_MAX_TOTAL   = 30;  // 대표자 포함 최대
</script>
<script src="<?= asset_v('assets/js/ticket.js') ?>"></script>
<script src="<?= asset_v('assets/js/group.js') ?>"></script>
</body>
</html>
