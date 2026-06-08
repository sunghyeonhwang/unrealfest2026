<?php
// 2단계(등록·결제) 준비 중 안내 스텁. $coming_title / $coming_desc 설정 후 include.
require __DIR__ . '/data/lib.php';
$page_title = (isset($coming_title) ? $coming_title : '준비 중') . ' — Unreal Fest Seoul 2026';
include __DIR__ . '/_head.php';
?>
<main class="pt-40 pb-40 min-h-screen flex items-center justify-center text-center">
  <div class="px-6">
    <p class="text-sm font-semibold text-brand mb-4">COMING SOON</p>
    <h1 class="text-3xl md:text-4xl font-bold mb-4"><?= e(isset($coming_title) ? $coming_title : '준비 중입니다') ?></h1>
    <p class="text-slate-400 mb-10 max-w-md mx-auto"><?= e(isset($coming_desc) ? $coming_desc : '등록·결제 기능은 곧 오픈됩니다. 조금만 기다려 주세요.') ?></p>
    <a href="index.php" class="clip-btn inline-block bg-brand hover:bg-brand-shadow text-white font-bold px-6 py-3">홈으로</a>
  </div>
</main>
<?php include __DIR__ . '/_foot.php'; ?>
