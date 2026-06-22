<?php
/* Unreal Fest Seoul 2026 — 단체 등록 결제 완료 (ticket-group-complete.php) [Phase 3] */
include_once "../common.php";
require __DIR__ . '/_assets.php';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$grp_no = isset($_GET['g']) ? (int)$_GET['g'] : 0;
$tok    = isset($_GET['t']) ? trim($_GET['t']) : '';
$g = $grp_no ? sql_fetch("SELECT * FROM cb_unreal_2026_group WHERE grp_no=".$grp_no." LIMIT 1") : null;
if (!$g || $g['grp_code'] !== $tok) { exit('잘못된 접근입니다.'); }
$paid = ($g['pay_status'] === 'paid');
?>
<!DOCTYPE html><html lang="ko" class="dark"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow"><title>단체 등록 완료 — Unreal Fest Seoul 2026</title>
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>"><style>*{word-break:keep-all}</style></head>
<body class="bg-[#09090b] text-white min-h-screen flex flex-col" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">
<script>try{localStorage.removeItem('ufs_group_form')}catch(e){}</script>
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-3xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>
<main class="flex-grow flex items-center justify-center px-6 pt-24 pb-12">
<div class="max-w-xl w-full text-center">
  <svg class="w-16 h-16 mx-auto mb-6 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
  <h1 class="text-2xl md:text-3xl font-bold mb-2"><?= $paid ? '단체 등록 결제가 완료되었습니다' : '단체 등록이 접수되었습니다' ?></h1>
  <p class="text-[#a1a1aa] mb-8">접수번호 <b class="text-[#00C1D5]"><?= e($g['grp_code']) ?></b></p>
  <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 text-left text-sm space-y-3 mb-8">
    <div class="flex justify-between gap-4"><span class="text-[#71717a]">대표자</span><span class="font-bold"><?= e($g['rep_name']) ?> (<?= e($g['rep_company']) ?>)</span></div>
    <div class="flex justify-between gap-4"><span class="text-[#71717a]">등록 인원</span><span class="font-bold"><?= (int)$g['headcount'] ?>명</span></div>
    <div class="flex justify-between gap-4"><span class="text-[#71717a]">결제 수단</span><span class="font-bold"><?= $g['paymethod']==='card'?'신용카드':'무통장 입금' ?></span></div>
    <div class="flex justify-between gap-4"><span class="text-[#71717a]">결제 금액</span><span class="font-bold text-[#00C1D5]">₩<?= number_format((int)$g['total_amount']) ?></span></div>
    <div class="flex justify-between gap-4"><span class="text-[#71717a]">상태</span><span class="font-bold"><?= $paid ? '결제 완료' : '입금 대기' ?></span></div>
  </div>
  <a href="index.php" class="inline-block px-8 py-3.5 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors">홈으로</a>
</div>
</main>
<footer class="border-t border-[#27272a] py-8">
  <div class="max-w-3xl mx-auto px-6 text-center text-xs text-[#71717a] space-y-1">
    <p>© 2026 Unreal Fest Seoul · 주최 Epic Games · 주관 (주)그리프</p>
    <p>문의 <a href="mailto:info@epiclounge.co.kr" class="hover:text-white">info@epiclounge.co.kr</a> · <a href="tel:02-326-3701" class="hover:text-white">02-326-3701</a></p>
  </div>
</footer>
</body></html>
