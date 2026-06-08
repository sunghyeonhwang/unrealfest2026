<?php
// Unreal Fest Seoul 2026 — 스폰서 (Design Ref: design doc §5.4 스폰서 / dist sponsorsPage)
require __DIR__ . '/data/lib.php';
$page_title = '스폰서 — Unreal Fest Seoul 2026';
$sponsors = ufs_sponsors();
include __DIR__ . '/_head.php';
?>
<main class="pt-32 pb-24 bg-ink-900 min-h-screen">
  <div class="max-w-7xl mx-auto px-6">
    <header class="text-center mb-16">
      <h1 class="text-4xl md:text-5xl font-bold mb-4">스폰서</h1>
      <p class="text-slate-400 max-w-2xl mx-auto">언리얼 페스트 서울 2026을 함께 만들어가는 파트너사입니다.</p>
    </header>

    <!-- Gold -->
    <section class="mb-16">
      <h2 class="text-sm font-bold tracking-widest text-brand mb-6">GOLD SPONSORS</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($sponsors['gold'] as $sp): ?>
        <div class="clip-tr bg-ink-800 border border-white/10 p-8 flex flex-col sm:flex-row items-start gap-6">
          <img src="<?= e($sp['logo']) ?>" alt="<?= e($sp['name']) ?>" class="h-12 w-auto <?= !empty($sp['invert_dark']) ? 'dark:invert' : '' ?>">
          <div>
            <h3 class="font-bold text-lg mb-2"><?= e($sp['name']) ?></h3>
            <?php if (!empty($sp['desc'])): ?><p class="text-sm text-slate-400 leading-relaxed"><?= e($sp['desc']) ?></p><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Silver -->
    <section class="mb-20">
      <h2 class="text-sm font-bold tracking-widest text-slate-400 mb-6">SILVER SPONSORS</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach ($sponsors['silver'] as $sp): ?>
        <div class="clip-tr-16 bg-ink-800 border border-white/10 p-6 flex items-center justify-center aspect-[3/2]">
          <img src="<?= e($sp['logo']) ?>" alt="<?= e($sp['name']) ?>" class="max-h-8 w-auto <?= !empty($sp['invert_dark']) ? 'dark:invert' : '' ?>">
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <div class="text-center border-t border-white/10 pt-12">
      <p class="text-slate-400 mb-6">언리얼 페스트 서울 2026의 파트너가 되어주세요.</p>
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="mailto:info@epiclounge.co.kr" class="clip-btn bg-brand hover:bg-brand-shadow text-white font-bold px-8 py-4 transition-colors">스폰서십 문의</a>
        <a href="index.php" class="clip-btn bg-white/10 hover:bg-white/20 text-white font-bold px-8 py-4 transition-colors">홈으로</a>
      </div>
    </div>
  </div>
</main>
<?php include __DIR__ . '/_foot.php'; ?>
