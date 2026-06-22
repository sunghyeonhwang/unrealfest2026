<?php
// Unreal Fest Seoul 2026 — 공통 head + 고정 Header (GNB). React Header.tsx 1:1.
// 사용: 페이지 상단에서 $ufs_page / (선택)$page_title,$page_desc 설정 후 include __DIR__.'/_head.php'.
//  $ufs_page: 'home'|'sessions'|'session'|'schedule'|'sponsors' — home이면 nav가 스크롤 버튼, 그 외엔 index.php#id 링크.
require_once __DIR__ . '/data/lib.php';
$ufs_page   = isset($ufs_page) ? $ufs_page : '';
$is_home    = ($ufs_page === 'home');
$page_title = isset($page_title) ? $page_title : 'Unreal Fest Seoul 2026 — 언리얼 페스트 서울 2026';
$page_desc  = isset($page_desc) ? $page_desc : '언리얼 페스트 서울 2026 · 2026. 8. 20~21 웨스틴 서울 파르나스. 언리얼 엔진과 에픽 에코시스템이 만들어가는 리얼타임 3D의 미래를 경험하세요.';
$nav        = ufs_nav_links();
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- robots: 공개 오픈 — 검색 색인 허용 -->
<?php
/* 라운지 전역 SEO/마케팅(v3_seo_config 'default') 연동 — description/keywords/og/GA4/Meta/Kakao.
 * marketing_head는 Gnuboard 전제(_GNUBOARD_ 미정의 시 exit)이므로 가드. 미로드 페이지는 자체 폴백 메타. */
if (defined('_GNUBOARD_')) {
  include __DIR__ . '/../inc/marketing_head.php';
} else {
  echo '<meta name="description" content="'.e($page_desc).'">'."\n";
  echo '<meta property="og:title" content="'.e($page_title).'">'."\n";
  echo '<meta property="og:description" content="'.e($page_desc).'">'."\n";
  echo '<meta property="og:type" content="website">'."\n";
}
?>
<title><?= e(!empty($seo_title) ? $seo_title : $page_title) ?></title>
<meta name="twitter:card" content="summary_large_image">
<?php include __DIR__ . '/_favicon.php'; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset_v('assets/style.css')) ?>">
<style>
/* 홈 플로팅 헤더가 하단 도킹 상태일 때, 모바일 메뉴를 위로 펼침(아래로 열면 화면 밖) */
#site-header.hdr-bottom #mobile-nav{ top:auto; bottom:100%; border-bottom:0; border-top:1px solid rgba(255,255,255,.1); }
</style>
<?php include __DIR__ . '/_wcs.php'; ?>
</head>
<body class="bg-white dark:bg-black min-h-screen text-black dark:text-white font-sans selection:bg-cyan-500/30 flex flex-col">

<?php if (!empty($ufs_el_gnb)) include __DIR__ . '/_el_gnb.php'; /* 에픽라운지 GNB는 플래그 페이지에만 */ ?>

<!-- ===== Header (fixed GNB) — 홈은 하단에서 시작해 스크롤 시 상단으로 슬라이드(2025식, data-floatnav) ===== -->
<header id="site-header" class="fixed left-0 right-0 z-50 transition-all border-b bg-[#09090b]/70 backdrop-blur-sm border-transparent <?= $is_home ? 'py-3 duration-700' : 'py-6 top-0 duration-300' ?>"<?php if ($is_home): ?> data-floatnav style="top: calc(100dvh - 3.5rem)"<?php endif; ?>>
  <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">

    <!-- Logo -->
    <?php if ($is_home): ?>
      <button type="button" data-scroll="hero" data-header-logo class="flex items-center group opacity-0 transition-opacity duration-500" aria-label="Unreal Fest 2026">
        <img src="./white_logo.svg" alt="Unreal Fest 2026" class="h-8">
      </button>
    <?php else: ?>
      <a href="index.php" class="flex items-center group" aria-label="Unreal Fest 2026">
        <img src="./white_logo.svg" alt="Unreal Fest 2026" class="h-8">
      </a>
    <?php endif; ?>

    <!-- Desktop nav -->
    <nav class="hidden lg:flex items-center gap-8">
      <ul class="flex items-center gap-6">
        <?php foreach ($nav as $n): ?>
          <li>
            <?php if ($is_home): ?>
              <button type="button" data-scroll="<?= e($n['id']) ?>" class="text-sm text-slate-300 hover:text-[#00C1D5] transition-colors font-bold"><?= e($n['name']) ?></button>
            <?php else: ?>
              <a href="index.php#<?= e($n['id']) ?>" class="text-sm text-slate-300 hover:text-[#00C1D5] transition-colors font-bold"><?= e($n['name']) ?></a>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="flex items-center gap-4 ml-4 pl-8 border-l border-white/10">
        <a href="myticket.php" class="text-sm text-slate-400 hover:text-white transition-colors">등록 확인</a>
        <?php if ($is_home): ?>
          <button type="button" data-scroll="register" class="bg-[#00C1D5] hover:bg-[#004F59] text-white px-5 py-2.5 text-sm font-bold flex items-center gap-2 transition-all clip-btn-sm">
            지금 등록하기
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </button>
        <?php else: ?>
          <a href="index.php#register" class="bg-[#00C1D5] hover:bg-[#004F59] text-white px-5 py-2.5 text-sm font-bold flex items-center gap-2 transition-all clip-btn-sm">
            지금 등록하기
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </a>
        <?php endif; ?>
      </div>
    </nav>

    <!-- Mobile toggle -->
    <div class="lg:hidden flex items-center gap-4">
      <button type="button" class="text-white p-2" id="mobile-menu-toggle" aria-label="메뉴 열기" aria-expanded="false" aria-controls="mobile-nav">
        <span data-menu-icon-closed>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </span>
        <span data-menu-icon-open class="hidden">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </span>
      </button>
    </div>
  </div>

  <!-- Mobile nav panel -->
  <div id="mobile-nav" class="hidden lg:hidden absolute top-full left-0 right-0 bg-[#09090b] border-b border-white/10 p-6 flex flex-col gap-6 shadow-2xl">
    <ul class="flex flex-col gap-4">
      <?php foreach ($nav as $n): ?>
        <li>
          <?php if ($is_home): ?>
            <button type="button" data-scroll="<?= e($n['id']) ?>" data-close-mobile class="text-lg text-slate-300 hover:text-[#00C1D5] transition-colors block font-medium"><?= e($n['name']) ?></button>
          <?php else: ?>
            <a href="index.php#<?= e($n['id']) ?>" data-close-mobile class="text-lg text-slate-300 hover:text-[#00C1D5] transition-colors block font-medium"><?= e($n['name']) ?></a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="flex flex-col gap-4 pt-4 border-t border-white/10">
      <?php if ($is_home): ?>
        <button type="button" data-scroll="register" data-close-mobile class="text-center bg-[#00C1D5] hover:bg-[#004F59] text-white px-5 py-3 font-bold clip-btn-sm">지금 등록하기</button>
      <?php else: ?>
        <a href="index.php#register" data-close-mobile class="text-center bg-[#00C1D5] hover:bg-[#004F59] text-white px-5 py-3 font-bold clip-btn-sm">지금 등록하기</a>
      <?php endif; ?>
      <a href="myticket.php" class="text-center text-slate-400 py-2">등록 확인</a>
    </div>
  </div>
</header>

<main class="flex-grow">
