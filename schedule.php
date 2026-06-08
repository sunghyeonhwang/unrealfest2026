<?php
// Unreal Fest Seoul 2026 — 타임테이블 (Design Ref: design doc §5.4 일정 / dist schedulePage)
require __DIR__ . '/data/lib.php';
$page_title = '타임테이블 — Unreal Fest Seoul 2026';
$sessions = ufs_sessions();
$cols = ufs_tracks(); // 프로그래밍/아트/미디어/산업

// (day|time|track) → 세션 룩업
$grid = array();
foreach ($sessions as $s) {
    if (!empty($s['is_keynote'])) continue;
    $grid[$s['day']][$s['time']][$s['track']] = $s;
}

$dayRows = array(
  1 => array(
    array('type'=>'keynote','time'=>'10:00 - 11:00','id'=>'keynote-1'),
    array('type'=>'keynote','time'=>'11:00 - 12:00','id'=>'keynote-2'),
    array('type'=>'break','time'=>'12:00 - 14:00','label'=>'점심 휴식'),
    array('type'=>'normal','time'=>'14:00 - 15:00'),
    array('type'=>'normal','time'=>'15:30 - 16:30'),
    array('type'=>'normal','time'=>'17:00 - 18:00'),
  ),
  2 => array(
    array('type'=>'normal','time'=>'10:00 - 11:30'),
    array('type'=>'break','time'=>'11:30 - 13:00','label'=>'점심 휴식'),
    array('type'=>'normal','time'=>'13:00 - 14:30'),
    array('type'=>'normal','time'=>'15:00 - 16:30'),
  ),
);
include __DIR__ . '/_head.php';
?>
<main class="pt-32 pb-24 bg-ink-900 min-h-screen">
  <div class="max-w-7xl mx-auto px-6">
    <h1 class="text-4xl md:text-5xl font-bold mb-8">타임테이블</h1>

    <!-- Day 탭 -->
    <div class="flex gap-2 mb-8">
      <button type="button" data-day-tab="1" class="rounded-full px-6 py-2.5 text-sm font-semibold bg-brand text-white transition-colors">Day 1 · 8.20 (목)</button>
      <button type="button" data-day-tab="2" class="rounded-full px-6 py-2.5 text-sm font-semibold text-slate-400 border border-white/15 transition-colors">Day 2 · 8.21 (금)</button>
    </div>

    <?php foreach ($dayRows as $day => $rows): ?>
    <div data-day-panel="<?= $day ?>" class="<?= $day === 1 ? '' : 'hidden' ?> overflow-x-auto">
      <table class="w-full min-w-[760px] border-collapse">
        <thead>
          <tr>
            <th class="w-28 text-left text-xs font-semibold text-slate-500 px-3 py-3 border-b border-white/10">시간</th>
            <?php foreach ($cols as $track): $tm = ufs_track_meta($track); ?>
            <th class="text-left text-sm font-bold px-3 py-3 border-b border-white/10 <?= $tm['text'] ?>"><?= e($track) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
          <tr class="align-top">
            <td class="text-xs text-slate-500 px-3 py-3 whitespace-nowrap"><?= e($row['time']) ?></td>
            <?php if ($row['type'] === 'break'): ?>
              <td colspan="4" class="px-3 py-3">
                <div class="bg-ink-850 border border-white/5 text-center text-sm text-slate-500 py-3 rounded"><?= e($row['label']) ?></div>
              </td>
            <?php elseif ($row['type'] === 'keynote'): $k = ufs_session($row['id']); ?>
              <td colspan="4" class="px-3 py-2">
                <a href="session.php?id=<?= e($k['id']) ?>" class="block clip-br-16 bg-ink-800 border border-brand/30 hover:border-brand/60 px-5 py-4 transition-all">
                  <span class="text-xs text-brand font-semibold">KEYNOTE</span>
                  <span class="font-bold ml-2"><?= e($k['title']) ?></span>
                  <span class="text-sm text-slate-400 ml-2">— <?= e($k['speaker']) ?></span>
                </a>
              </td>
            <?php else: foreach ($cols as $track):
              $cell = isset($grid[$day][$row['time']][$track]) ? $grid[$day][$row['time']][$track] : null; ?>
              <td class="px-3 py-2">
                <?php if ($cell !== null): $cm = ufs_track_meta($track); ?>
                <a href="session.php?id=<?= e($cell['id']) ?>" class="block clip-br-16 bg-ink-800 border border-white/10 hover:border-white/30 p-3 transition-all h-full">
                  <span class="w-6 h-1 block mb-2 <?= $cm['bg'] ?>"></span>
                  <p class="text-sm font-semibold leading-snug mb-1"><?= e($cell['title']) ?></p>
                  <p class="text-xs text-slate-500"><?= e($cell['speaker']) ?></p>
                </a>
                <?php endif; ?>
              </td>
            <?php endforeach; endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endforeach; ?>
  </div>
</main>
<?php include __DIR__ . '/_foot.php'; ?>
