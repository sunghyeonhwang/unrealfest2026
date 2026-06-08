<?php
// Unreal Fest Seoul 2026 — 세션 목록 (Design Ref: design doc §5.4 세션목록 / dist sessionsPage)
require __DIR__ . '/data/lib.php';
require __DIR__ . '/_components.php';
$page_title = '아젠다 — Unreal Fest Seoul 2026';
$sessions = ufs_sessions();
$keynotes = ufs_keynotes();
include __DIR__ . '/_head.php';
?>
<main class="pt-32 pb-24 bg-ink-900 min-h-screen">
  <div class="max-w-7xl mx-auto px-6">
    <header class="mb-10">
      <h1 class="text-4xl md:text-5xl font-bold mb-4">아젠다</h1>
      <p class="text-slate-400 max-w-2xl">원하는 트랙과 난이도의 세션을 찾아보고 일정을 계획하세요. 세션 카드를 클릭하면 상세 정보를 확인할 수 있습니다.</p>
    </header>

    <!-- 키노트 강조 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12">
      <?php foreach ($keynotes as $k): ?>
      <a href="session.php?id=<?= e($k['id']) ?>" class="group clip-tr bg-ink-800 border border-brand/30 hover:border-brand/60 p-6 transition-all">
        <span class="text-xs font-semibold text-brand">KEYNOTE · <?= e($k['time']) ?></span>
        <h3 class="text-xl font-bold mt-2 group-hover:text-brand-highlight transition-colors"><?= e($k['title']) ?></h3>
        <p class="text-sm text-slate-400 mt-2"><?= e($k['speaker']) ?> · <?= e($k['affiliation']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- 필터 -->
    <div class="mb-8 space-y-4">
      <input type="text" data-search-input placeholder="세션 검색..." class="w-full md:w-80 bg-ink-800 border border-white/10 rounded-full px-5 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-brand focus:outline-none">
      <div class="flex flex-wrap gap-2">
        <button type="button" data-filter-track="전체" class="rounded-full px-4 py-1.5 text-sm border border-brand bg-brand text-white transition-colors">전체 트랙</button>
        <?php foreach (ufs_tracks() as $t): ?>
        <button type="button" data-filter-track="<?= e($t) ?>" class="rounded-full px-4 py-1.5 text-sm border border-white/15 text-slate-400 hover:text-white transition-colors"><?= e($t) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button type="button" data-filter-difficulty="전체" class="rounded-full px-4 py-1.5 text-sm border border-brand bg-brand text-white transition-colors">전체 난이도</button>
        <?php foreach (ufs_difficulties() as $d): ?>
        <button type="button" data-filter-difficulty="<?= e($d) ?>" class="rounded-full px-4 py-1.5 text-sm border border-white/15 text-slate-400 hover:text-white transition-colors"><?= e($d) ?></button>
        <?php endforeach; ?>
        <button type="button" data-filter-reset class="ml-auto text-sm text-slate-400 hover:text-white">필터 초기화</button>
        <div class="flex gap-1 ml-2">
          <button type="button" data-density="2" class="rounded px-3 py-1 text-xs border border-white/15 text-slate-400">2단</button>
          <button type="button" data-density="3" class="rounded px-3 py-1 text-xs border border-brand bg-brand text-white">3단</button>
        </div>
      </div>
    </div>

    <!-- 세션 그리드 -->
    <div data-session-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php
      $i = 0;
      foreach ($sessions as $s) {
        if (!empty($s['is_keynote'])) continue;
        render_session_card($s, $i, 'grid');
        $i++;
      }
      ?>
    </div>
    <div data-empty class="hidden text-center py-20 text-slate-500">
      <p class="mb-4">선택한 조건에 맞는 세션이 없습니다.</p>
      <button type="button" data-filter-reset class="clip-btn bg-brand hover:bg-brand-shadow text-white font-bold px-6 py-3">필터 초기화하기</button>
    </div>
  </div>
</main>
<?php include __DIR__ . '/_foot.php'; ?>
