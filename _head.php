<?php
// Unreal Fest Seoul 2026 — 공통 head + GNB (자체 레이아웃, Gnuboard member_header 미사용)
// Design Ref: design doc §5.3 / dist globalUI. 다크 고정.
// 페이지에서 사용 전 $page_title / $page_desc 설정 가능. require_once data/lib.php 선행.
if (!function_exists('e')) { require __DIR__ . '/data/lib.php'; }
$page_title = isset($page_title) ? $page_title : 'Unreal Fest Seoul 2026';
$page_desc  = isset($page_desc) ? $page_desc : '언리얼 페스트 서울 2026 — 2026.8.20~21, 웨스틴 서울 파르나스. Unreal Ideas Start Here.';
// GNB 앵커는 홈(index.php) 기준. 다른 페이지에서도 홈 앵커로 이동.
$nav = array(
  '소개' => 'index.php#overview',
  '아젠다' => 'sessions.php',
  '티켓' => 'index.php#register',
  '행사장 안내' => 'index.php#venue',
  '이벤트' => 'index.php#event-benefits',
  'FAQ' => 'index.php#faq',
);
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($page_desc) ?>">
<meta property="og:type" content="website">
<meta property="og:image" content="public/ufs26_seoul_main_logo.svg">
<link rel="icon" href="public/black_logo.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-ink-900 text-white font-sans antialiased min-h-screen">

<!-- GNB -->
<header id="gnb" class="fixed top-0 inset-x-0 z-50 bg-ink-900/95 backdrop-blur transition-all duration-300 border-b border-white/5">
  <div class="max-w-7xl mx-auto px-6 flex items-center justify-between py-6 transition-all duration-300" data-gnb-bar>
    <a href="index.php" class="shrink-0">
      <img src="public/white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto">
    </a>
    <nav class="hidden lg:flex items-center gap-8">
      <?php foreach ($nav as $label => $href): ?>
        <a href="<?= e($href) ?>" class="text-sm text-slate-300 hover:text-white transition-colors"><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="hidden lg:flex items-center gap-3">
      <a href="myticket.php" class="text-sm text-slate-300 hover:text-white transition-colors">등록 확인</a>
      <a href="index.php#register" class="clip-btn-sm bg-brand hover:bg-brand-shadow text-white font-bold text-sm px-5 py-2.5 transition-colors">지금 등록하기</a>
    </div>
    <button type="button" class="lg:hidden text-white p-2" data-menu-toggle aria-label="메뉴" aria-expanded="false">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
  </div>
  <!-- 모바일 메뉴 -->
  <div class="lg:hidden hidden border-t border-white/10 bg-ink-900" data-mobile-menu>
    <nav class="max-w-7xl mx-auto px-6 py-4 flex flex-col gap-1">
      <?php foreach ($nav as $label => $href): ?>
        <a href="<?= e($href) ?>" class="py-3 text-slate-200 hover:text-white border-b border-white/5"><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="myticket.php" class="py-3 text-slate-200 hover:text-white border-b border-white/5">등록 확인</a>
      <a href="index.php#register" class="mt-3 clip-btn-sm bg-brand hover:bg-brand-shadow text-white font-bold px-5 py-3 text-center">지금 등록하기</a>
    </nav>
  </div>
</header>
