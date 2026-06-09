<?php
// Unreal Fest Seoul 2026 — sessions.php 는 schedule.php 로 통합(2026-06-09 미사용). 301 리다이렉트.
// 직접/외부 링크·북마크 대비. 아래 구 세션목록 구현은 미사용(참조 보존).
header('Location: schedule.php', true, 301);
exit;
/* ──────────────── 이하 구현 미사용(deprecated) ──────────────── */
// 아젠다(세션 목록). React Sessions.tsx 1:1. 24개 카드 + 사이드바 필터 + Day탭 + 2/3단 뷰토글.
$ufs_page   = 'sessions';
$page_title = '아젠다 — Unreal Fest Seoul 2026';
$page_desc  = '원하는 트랙과 난이도의 세션을 찾아보고 일정을 계획하세요. Unreal Fest Seoul 2026 전체 세션 목록.';
require_once __DIR__ . '/data/lib.php';
include __DIR__ . '/_head.php';

$list = ufs_sessions_for_list();
?>

<!-- PAGE HEADING -->
<section class="relative pt-24 pb-16 overflow-hidden border-b border-[#27272a]" style="background-color:#0e0f14;">
  <div class="absolute right-0 top-0 bottom-0 w-[70%] z-0">
    <img src="./session_hero.jpg" alt="" class="w-full h-full object-cover opacity-40" onerror="this.style.display='none'">
    <div class="absolute inset-0 bg-gradient-to-r from-[#0e0f14] via-[#0e0f14]/70 to-transparent"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-[#0e0f14]/40 to-[#0e0f14]"></div>
  </div>
  <div class="relative z-10 max-w-7xl mx-auto px-6 pt-12">
    <h1 class="text-5xl md:text-6xl mb-4 tracking-tight font-jamjil font-medium">아젠다</h1>
    <p class="text-[#90a1b9] max-w-2xl text-base leading-relaxed">원하는 트랙과 난이도의 세션을 찾아보고 일정을 계획하세요. 세션 카드를 클릭하면 상세 정보를 확인할 수 있습니다.</p>
  </div>
</section>

<!-- TOP BAR: Day 탭 + 뷰 토글 -->
<div class="sticky top-[73px] z-40 bg-[#111115] border-b border-[#27272a]" data-sessions>
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-0">
      <button type="button" data-day-filter="all" class="px-6 py-2.5 text-sm font-bold transition-all bg-[#00C1D5] text-black">전체</button>
      <button type="button" data-day-filter="day1" class="px-6 py-2.5 text-sm font-bold transition-all bg-transparent text-[#71717a] hover:text-white">Day 1 (8.20 목)</button>
      <button type="button" data-day-filter="day2" class="px-6 py-2.5 text-sm font-bold transition-all bg-transparent text-[#71717a] hover:text-white">Day 2 (8.21 금)</button>
    </div>
    <div class="flex items-center gap-1">
      <button type="button" data-cols="2" class="p-2 transition-colors text-white" title="2단 뷰">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect width="7" height="18" x="3" y="3" rx="1"/><rect width="7" height="18" x="14" y="3" rx="1"/></svg>
      </button>
      <button type="button" data-cols="3" class="p-2 transition-colors text-[#71717a] hover:text-white" title="3단 뷰">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
      </button>
    </div>
  </div>
</div>

<!-- BODY -->
<div class="max-w-7xl mx-auto px-6 py-8 pb-24" data-sessions-body>
  <div class="flex flex-col lg:flex-row gap-8">
    <!-- SIDEBAR -->
    <aside class="lg:w-[280px] flex-shrink-0">
      <div class="lg:sticky lg:top-[140px] bg-[#0e0f14] border border-[#27272a] p-6 space-y-6">
        <!-- Search -->
        <div class="relative">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#71717a]"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" placeholder="세션 검색..." data-search-input class="w-full bg-[#0e0f14] border border-[#27272a] pl-10 pr-4 py-3 text-sm text-white placeholder:text-[#71717a] focus:outline-none focus:border-[#00C1D5] transition-colors">
        </div>

        <!-- 트랙 (기본 접힘) -->
        <div>
          <button type="button" data-section-toggle class="w-full flex items-center justify-between text-xs font-semibold text-[#71717a] uppercase tracking-[0.96px] mb-3 hover:text-white transition-colors">
            트랙
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 transition-transform -rotate-90" data-section-chevron><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="flex-col gap-0 hidden" data-section-body>
            <?php foreach (ufs_track_filters() as $t):
              $on = $t['key'] === 'all'; ?>
              <button type="button" data-track-filter="<?= e($t['key']) ?>" class="w-full text-left px-3 py-2.5 text-sm transition-colors <?= $on ? 'bg-[rgba(0,79,89,0.5)] text-[#9adbe8] font-semibold' : 'bg-transparent text-[#a1a1aa] hover:text-white' ?>"><?= e($t['label']) ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- 난이도 (기본 접힘) -->
        <div>
          <button type="button" data-section-toggle class="w-full flex items-center justify-between text-xs font-semibold text-[#71717a] uppercase tracking-[0.96px] mb-3 hover:text-white transition-colors">
            난이도
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 transition-transform -rotate-90" data-section-chevron><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="flex-col gap-0 hidden" data-section-body>
            <?php foreach (ufs_difficulty_filters() as $d):
              $on = $d['key'] === 'all'; ?>
              <button type="button" data-level-filter="<?= e($d['key']) ?>" class="w-full text-left px-3 py-2.5 text-sm transition-colors <?= $on ? 'bg-[rgba(0,79,89,0.5)] text-[#9adbe8] font-semibold' : 'bg-transparent text-[#a1a1aa] hover:text-white' ?>"><?= e($d['label']) ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- 기술 분야 (기본 펼침) -->
        <div>
          <button type="button" data-section-toggle data-section-open class="w-full flex items-center justify-between text-xs font-semibold text-[#71717a] uppercase tracking-[0.96px] mb-3 hover:text-white transition-colors">
            기술 분야
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 transition-transform rotate-0" data-section-chevron><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="flex flex-col gap-0" data-section-body>
            <?php foreach (ufs_all_categories() as $cat):
              $on = $cat === '전체'; ?>
              <button type="button" data-cat-filter="<?= e($cat) ?>" class="w-full text-left px-3 py-2.5 text-sm transition-colors <?= $on ? 'bg-[rgba(0,79,89,0.5)] text-[#9adbe8] font-semibold' : 'bg-transparent text-[#a1a1aa] hover:text-white' ?>"><?= e($cat) ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="button" data-filter-reset class="w-full py-2.5 text-sm text-[#71717a] border border-[#27272a] text-center hover:text-white hover:border-white/20 transition-colors">필터 초기화</button>
      </div>
    </aside>

    <!-- CONTENT -->
    <div class="flex-grow min-w-0">
      <p class="text-sm text-[#a1a1aa] mb-6">총 <span class="text-[#00C1D5] font-bold" data-session-count><?= count($list) ?></span>개의 세션</p>

      <div class="grid gap-5 md:grid-cols-2" data-session-grid>
        <?php foreach ($list as $s):
          $cats = ufs_session_categories($s);
          $haystack = mb_strtolower($s['title'].' '.$s['desc'].' '.$s['speaker']['name'].' '.$s['speaker']['company'], 'UTF-8'); ?>
          <a href="session.php?id=<?= e($s['id']) ?>" data-session-card
             data-track="<?= e($s['track']) ?>" data-level="<?= e($s['level']) ?>"
             data-day="<?= $s['day'] === 1 ? 'day1' : 'day2' ?>"
             data-cats="<?= e(implode(' ', $cats)) ?>" data-search="<?= e($haystack) ?>"
             class="group bg-[#0e0f14] p-6 cursor-pointer hover:bg-[#111115] transition-all flex flex-col">
            <div class="flex items-center justify-between mb-3">
              <span class="px-3 py-0.5 text-xs font-medium <?= ufs_track_badge_list($s['track']) ?>"><?= e(ufs_track_label_list($s['track'])) ?></span>
              <span class="text-xs text-[#71717a]"><?= e(ufs_level_label_sessions($s['level'])) ?></span>
            </div>
            <div class="flex flex-wrap gap-1.5 mb-3">
              <?php foreach ($cats as $c): ?><span class="px-2.5 py-0.5 text-[10px] text-[#71717a] border border-[#27272a] bg-[#0e0f14] rounded-full tracking-wide"><?= e($c) ?></span><?php endforeach; ?>
            </div>
            <h3 class="text-lg font-bold text-[#fafafa] mb-2 group-hover:text-[#00C1D5] transition-colors leading-snug flex-grow tracking-tight"><?= e($s['title']) ?></h3>
            <p class="text-sm text-[#a1a1aa] mb-4 line-clamp-2 leading-relaxed"><?= e($s['desc']) ?></p>
            <div class="flex items-center gap-3 mb-3">
              <div class="w-8 h-8 rounded-full bg-[#0e0f14] border border-[#27272a] flex items-center justify-center flex-shrink-0">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-[#71717a]"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
              <div class="min-w-0">
                <div class="text-sm font-semibold text-[#fafafa] truncate"><?= e($s['speaker']['name']) ?></div>
                <div class="text-xs text-[#71717a] truncate"><?= e($s['speaker']['role']) ?> · <?= e($s['speaker']['company']) ?></div>
              </div>
            </div>
            <div class="flex items-center gap-3 text-xs text-[#71717a] pt-3 border-t border-white/5">
              <span><?= e(ufs_day_short($s['day'])) ?></span>
              <span class="w-px h-3 bg-[#27272a]"></span>
              <span><?= e($s['time']) ?></span>
              <span class="w-px h-3 bg-[#27272a]"></span>
              <span><?= e($s['location']) ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="py-20 text-center border border-dashed border-[#27272a] hidden" data-empty>
        <p class="text-[#71717a] mb-2">선택한 조건에 맞는 세션이 없습니다.</p>
        <button type="button" data-filter-reset class="text-[#00C1D5] text-sm font-medium hover:underline">필터 초기화하기</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_foot.php'; ?>
