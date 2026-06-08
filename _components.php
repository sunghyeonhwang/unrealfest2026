<?php
// Unreal Fest Seoul 2026 — 재사용 렌더 컴포넌트 (PHP 7.0 호환)
// Design Ref: design doc §5.3 Component List. 클래스 리터럴 유지(Tailwind JIT).
if (!function_exists('e')) { require __DIR__ . '/data/lib.php'; }

// 세션 카드 (sessions 목록 / 홈 아젠다 캐러셀 공용)
// $idx: 교차 clip-path(짝=우상단, 홀=우하단). $variant: 'grid' | 'agenda'
function render_session_card($s, $idx, $variant) {
    if ($variant === null) { $variant = 'grid'; }
    $m = ufs_track_meta($s['track']);
    $clip = ($idx % 2 === 0) ? 'clip-tr-16' : 'clip-br-16';
    $search = $s['title'] . ' ' . $s['speaker'] . ' ' . $s['affiliation'];
    $w = ($variant === 'agenda') ? 'min-w-[320px] max-w-[320px] snap-start' : '';
    ?>
    <a href="session.php?id=<?= e($s['id']) ?>"
       class="ufs-session-card group block <?= $clip ?> <?= $w ?> bg-ink-800 border border-white/10 hover:border-white/25 p-6 transition-all duration-200"
       data-track="<?= e($s['track']) ?>" data-difficulty="<?= e($s['difficulty']) ?>" data-search="<?= e($search) ?>" data-day="<?= e($s['day']) ?>">
      <div class="flex items-center gap-2 mb-4">
        <span class="w-2 h-2 rounded-full <?= $m['dot'] ?>"></span>
        <span class="text-xs font-semibold <?= $m['text'] ?>"><?= e($s['track']) ?></span>
        <span class="ml-auto text-xs text-slate-500"><?= e($s['difficulty']) ?></span>
      </div>
      <h3 class="text-lg font-bold text-white leading-snug mb-4 group-hover:text-brand-highlight transition-colors"><?= e($s['title']) ?></h3>
      <div class="text-sm text-slate-400 space-y-1">
        <p><?= e($s['speaker']) ?> <span class="text-slate-500">· <?= e($s['affiliation']) ?></span></p>
        <p class="text-xs text-slate-500">Day <?= e($s['day']) ?> · <?= e($s['time']) ?> · <?= e($s['room']) ?></p>
      </div>
    </a>
    <?php
}

// 홈 트랙 카드 (배경 영상 + 트랙 컬러)
function render_track_card($track, $video) {
    $m = ufs_track_meta($track);
    ?>
    <div class="ufs-track-card relative overflow-hidden clip-tr bg-ink-800 border border-white/10 aspect-[4/5] group">
      <?php if ($video): ?>
      <video class="absolute inset-0 w-full h-full object-cover opacity-20 dark:opacity-50 group-hover:opacity-60 transition-opacity duration-500" autoplay loop muted playsinline preload="none">
        <source src="<?= e($video) ?>" type="video/mp4">
      </video>
      <?php endif; ?>
      <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-ink-900/40 to-transparent"></div>
      <div class="relative h-full flex flex-col justify-end p-6">
        <span class="w-10 h-1 mb-4 <?= $m['bg'] ?>"></span>
        <h3 class="text-xl font-bold text-white"><?= e($track) ?></h3>
        <p class="text-sm text-slate-400 mt-1"><?= e($m['room']) ?></p>
      </div>
    </div>
    <?php
}
