<?php
// Unreal Fest Seoul 2026 — 세션 상세 (Design Ref: design doc §5.4 세션상세 / dist sessionDetail)
require __DIR__ . '/data/lib.php';
require __DIR__ . '/_components.php';

$id = isset($_GET['id']) ? (string)$_GET['id'] : '';
$s = ufs_session($id);

if ($s === null) {
    // 404 — 세션 없음
    http_response_code(404);
    $page_title = '세션을 찾을 수 없습니다 — Unreal Fest Seoul 2026';
    include __DIR__ . '/_head.php';
    ?>
    <main class="pt-40 pb-40 min-h-screen flex items-center justify-center text-center">
      <div>
        <h1 class="text-3xl font-bold mb-4">세션을 찾을 수 없습니다.</h1>
        <p class="text-slate-400 mb-8">요청하신 세션이 존재하지 않거나 변경되었습니다.</p>
        <a href="sessions.php" class="clip-btn inline-block bg-brand hover:bg-brand-shadow text-white font-bold px-6 py-3">아젠다로 돌아가기</a>
      </div>
    </main>
    <?php
    include __DIR__ . '/_foot.php';
    exit;
}

$m = ufs_track_meta($s['track']);
$related = ufs_related_sessions($s, 3);
$page_title = $s['title'] . ' — Unreal Fest Seoul 2026';
$page_desc = $s['speaker'] . ' · ' . $s['affiliation'] . ' · ' . mb_substr($s['description'], 0, 80);
include __DIR__ . '/_head.php';
?>
<main class="pt-32 pb-24 bg-ink-900 min-h-screen">
  <div class="max-w-4xl mx-auto px-6">
    <a href="sessions.php" class="text-sm text-slate-400 hover:text-white inline-flex items-center gap-1 mb-8">← 아젠다로 돌아가기</a>

    <div class="flex items-center gap-2 mb-4">
      <span class="clip-btn-sm <?= $m['badge'] ?> text-xs font-semibold px-3 py-1"><?= e($s['track']) ?></span>
      <span class="text-xs text-slate-500"><?= e($s['difficulty']) ?></span>
      <?php if (!empty($s['is_keynote'])): ?><span class="text-xs text-brand font-semibold">KEYNOTE</span><?php endif; ?>
    </div>
    <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-6"><?= e($s['title']) ?></h1>

    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-400 mb-10 pb-10 border-b border-white/10">
      <span>📅 Day <?= e($s['day']) ?> · <?= e(ufs_day_iso($s['day'])) ?></span>
      <span>🕐 <?= e($s['time']) ?></span>
      <span>📍 <?= e($s['room']) ?></span>
    </div>

    <!-- 발표자 -->
    <div class="flex items-center gap-4 mb-12">
      <div class="w-16 h-16 rounded-full bg-ink-800 border border-white/10 flex items-center justify-center text-2xl">🎤</div>
      <div>
        <p class="font-bold text-lg"><?= e($s['speaker']) ?></p>
        <p class="text-sm text-slate-400"><?= e($s['speaker_title']) ?> · <?= e($s['affiliation']) ?></p>
      </div>
    </div>

    <section class="mb-10">
      <h2 class="text-lg font-bold mb-3">세션 소개</h2>
      <p class="text-slate-300 leading-relaxed"><?= e($s['description']) ?></p>
    </section>

    <section class="mb-10">
      <h2 class="text-lg font-bold mb-3">세션 목차</h2>
      <ol class="space-y-2">
        <?php foreach ($s['toc'] as $idx => $item): ?>
        <li class="flex items-start gap-3 text-slate-300">
          <span class="<?= $m['text'] ?> font-bold text-sm mt-0.5"><?= sprintf('%02d', $idx + 1) ?></span>
          <span><?= e($item) ?></span>
        </li>
        <?php endforeach; ?>
      </ol>
    </section>

    <section class="mb-12">
      <h2 class="text-lg font-bold mb-3">권장 대상</h2>
      <p class="text-slate-300"><?= e($s['target']) ?></p>
    </section>

    <div class="flex flex-wrap gap-3 mb-16">
      <a href="#register" class="clip-btn bg-brand hover:bg-brand-shadow text-white font-bold px-6 py-3 transition-colors">일정에 추가하기</a>
      <button type="button" data-share class="clip-btn bg-white/10 hover:bg-white/20 text-white font-bold px-6 py-3 transition-colors">세션 공유하기</button>
    </div>

    <?php if (count($related)): ?>
    <section class="border-t border-white/10 pt-12">
      <h2 class="text-lg font-bold mb-6">관련 세션</h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <?php foreach ($related as $i => $r) { render_session_card($r, $i, 'grid'); } ?>
      </div>
    </section>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/_foot.php'; ?>
