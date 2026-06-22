<?php
/* Unreal Fest Seoul 2026 — 단체 등록 확인/결제 (ticket-group-confirm.php) [Phase 2 예정 stub] */
require __DIR__ . '/_assets.php';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$prod = isset($_POST['group_product']) ? $_POST['group_product'] : '';
$pay  = isset($_POST['group_paymethod']) ? $_POST['group_paymethod'] : '';
$names = isset($_POST['member_name']) && is_array($_POST['member_name']) ? $_POST['member_name'] : array();
$members = count($names);
$total = $members + 1; // 대표자 포함
?>
<!DOCTYPE html><html lang="ko" class="dark"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow"><title>단체 등록 확인 — Unreal Fest Seoul 2026</title>
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>"></head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">
<div class="max-w-2xl mx-auto px-6 py-32 text-center">
  <h1 class="text-2xl font-bold mb-4">단체 등록 — 다음 단계 준비 중</h1>
  <p class="text-[#a1a1aa] mb-6">입력이 정상 전송되었습니다. 확인/결제(카드 PG·무통장 안내) 단계는 다음 빌드에서 연결됩니다.</p>
  <div class="bg-[#0e0f14] border border-[#27272a] p-6 text-left text-sm space-y-2 inline-block">
    <div>상품: <b><?= e($prod) ?></b></div>
    <div>결제수단: <b><?= e($pay) ?></b></div>
    <div>총 인원(대표자 포함): <b><?= (int)$total ?>명</b> (멤버 <?= (int)$members ?>명)</div>
  </div>
  <div class="mt-8"><a href="ticket-group.php" class="text-[#00C1D5] hover:underline">← 단체 등록으로 돌아가기</a></div>
</div></body></html>
