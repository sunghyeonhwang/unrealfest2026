<?php
// Unreal Fest Seoul 2026 — 스폰서 상세. React SponsorsDetail.tsx 1:1.
// 골드(2, 설명+웹사이트) / 실버(4, 설명+자세히) / 스폰서십 문의(mailto). 설명문 일부는 라이브 더미 그대로.
$ufs_page   = 'sponsors';
$page_title = '스폰서 — Unreal Fest Seoul 2026';
$page_desc  = '언리얼 페스트 서울 2026은 차세대 3D 생태계를 이끌어가는 최고의 파트너들과 함께합니다.';
require_once __DIR__ . '/data/lib.php';
include __DIR__ . '/_head.php';

$sp = ufs_sponsors_detail();
?>

<div class="bg-[#09090b] min-h-screen text-white">
  <!-- 헤딩 -->
  <section class="relative pt-24 pb-16 overflow-hidden border-b border-[#27272a]" style="background-color:#0e0f14;">
    <div class="absolute right-0 top-0 bottom-0 w-[70%] z-0">
      <img src="./session_hero.jpg" alt="" class="w-full h-full object-cover opacity-30" onerror="this.style.display='none'">
      <div class="absolute inset-0 bg-gradient-to-r from-[#0e0f14] via-[#0e0f14]/70 to-transparent"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-[#0e0f14]/40 to-[#0e0f14]"></div>
    </div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 pt-12">
      <h1 class="text-5xl md:text-6xl mb-4 tracking-tight font-jamjil font-medium">스폰서</h1>
      <p class="text-[#90a1b9] max-w-2xl text-base leading-relaxed">언리얼 페스트 서울 2026은 차세대 3D 생태계를 이끌어가는 최고의 파트너들과 함께합니다. 행사장에 마련된 파트너 부스에서 최신 기술 데모를 직접 경험해 보세요.</p>
    </div>
  </section>

  <!-- 실버 -->
  <section class="max-w-7xl mx-auto px-6 pt-16 pb-16">
    <h2 class="text-2xl font-bold text-[#a1a1aa] mb-8 tracking-tight">실버 스폰서</h2>
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($sp['silver'] as $s): ?>
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 flex flex-col">
          <div class="h-32 flex items-center justify-center mb-4">
            <img src="<?= e($s['logo']) ?>" alt="<?= e($s['name']) ?>" class="h-[72px] object-contain invert" onerror="this.style.display='none'">
          </div>
          <p class="text-xs text-[#a1a1aa] leading-relaxed mb-4 flex-grow"><?= e($s['desc']) ?></p>
          <a href="<?= e($s['link']) ?>" class="inline-flex items-center gap-1 text-xs text-[#71717a] hover:text-white transition-colors">
            자세히 알아보기
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <!-- 부스 배치도 -->
    <h2 class="text-2xl font-bold text-[#a1a1aa] mb-8 mt-16 tracking-tight">부스 배치도</h2>
    <div class="bg-[#0e0f14] border border-[#27272a] p-12 text-center min-h-[320px] flex flex-col items-center justify-center">
      <div class="text-xl font-bold text-white mb-2">부스 안내</div>
      <div class="text-[#71717a]">곧 공개 예정</div>
    </div>
  </section>

  <!-- 스폰서십 문의 (임시 숨김) -->
  <?php if (false): ?>
  <section class="max-w-7xl mx-auto px-6 pb-24">
    <div class="text-center py-16 border-t border-[#27272a]">
      <h2 class="text-2xl md:text-3xl font-bold text-white mb-4 tracking-tight">스폰서십 문의</h2>
      <p class="text-[#a1a1aa] max-w-2xl mx-auto mb-8 leading-relaxed">리얼타임 3D 생태계를 이끌어가는 진보가 및 의사결정자들을 직접 만날 수 있는 특별한 기회를 잡으세요. 다양한 스폰서십 패키지가 준비되어 있습니다.</p>
      <a href="mailto:info@epiclounge.co.kr" class="inline-flex items-center gap-2 px-8 py-3.5 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-medium transition-colors clip-btn">스폰서십 브로셔 요청하기</a>
    </div>
  </section>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
