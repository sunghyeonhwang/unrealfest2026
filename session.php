<?php
// Unreal Fest Seoul 2026 — 세션 상세. React SessionDetail.tsx 1:1. URL: session.php?id=<id>
$ufs_page = 'session';
include_once __DIR__ . '/../common.php';        // DB (sql_query)
require_once __DIR__ . '/data/lib.php';
require_once __DIR__ . '/data/agenda_db.php';

$id = isset($_GET['id']) ? (string)$_GET['id'] : '';
$session = $id !== '' ? ufs_db_session($id) : null;

$page_title = $session ? ($session['title'] . ' — Unreal Fest Seoul 2026') : '세션을 찾을 수 없습니다 — Unreal Fest Seoul 2026';
$page_desc  = $session ? $session['desc'] : 'Unreal Fest Seoul 2026 세션 정보.';
include __DIR__ . '/_head.php';

// 연사 기본 소개(라이브와 동일한 고정 카피)
$speaker_bio = '에픽게임즈 스토어의 포트폴리오 전략을 총괄하며, 에픽게임즈 퍼블리싱, 무료 게임 프로그램, 그리고 스토어 콘텐츠 등 전반에 걸쳐 에픽게임즈가 지원하는 다양한 프로그램의 방향을 결정합니다. 내부 주요 팀들과 협업해 우수한 개발자를 발굴 및 지원하고, 지속 가능한 비즈니스 성장을 돕고 있습니다.';
?>

<?php if (!$session): ?>
  <div class="bg-[#09090b] min-h-screen text-white pt-32 text-center">
    <p class="text-[#71717a]">세션을 찾을 수 없습니다.</p>
    <a href="schedule.php" class="text-[#00C1D5] mt-4 inline-block hover:underline">아젠다로 돌아가기</a>
  </div>
<?php else:
  // ③ 배지: SH리뷰 제품군(ag_product). 비어있으면 자동 키워드로 폴백.
  $cats = !empty($session['product']) ? $session['product'] : ufs_session_keywords($session);
  $date_iso = ufs_day_iso($session['day']);
  $related = ufs_db_related($session, 2);
?>
<div class="bg-[#09090b] min-h-screen text-white">
  <!-- 상단 헤딩 -->
  <section class="bg-[#0e0f14] border-b border-[#27272a] pt-24 pb-10">
    <div class="max-w-7xl mx-auto px-6">
      <a href="schedule.php" class="inline-flex items-center gap-2 text-sm text-[#71717a] hover:text-white transition-colors mb-6">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        아젠다로 돌아가기
      </a>
      <!-- 키워드 (강연 제목 상단) — 일정표 세션 배지와 동일 디자인, 크기 유지 -->
      <div class="flex flex-wrap gap-2 mb-5">
        <span class="px-1.5 py-1 text-xs rounded-[4px] <?= ufs_track_badge_home($session['track']) ?>"><?= e(ufs_track_label_day($session['track'], $session['day'])) ?></span>
        <span class="px-1.5 py-1 text-xs font-semibold rounded-[4px] bg-[#27272a] text-[#f4f4f5]"><?= e(ufs_level_label_detail($session['level'])) ?></span>
        <?php foreach ($cats as $cat): ?>
          <span class="px-1.5 py-1 text-xs text-[#a1a1aa] border border-[#27272a] rounded-[4px]"><?= e($cat) ?></span>
        <?php endforeach; ?>
      </div>
      <h1 class="text-3xl md:text-4xl font-bold text-[#fafafa] mb-6 tracking-tight leading-tight"><?= e($session['title']) ?></h1>
      <div class="flex flex-wrap items-center gap-6 text-sm text-[#a1a1aa] mb-6">
        <span class="flex items-center gap-1.5">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
          <?= e($date_iso) ?>
        </span>
        <span class="flex items-center gap-1.5">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <?= e($session['time']) ?>
        </span>
        <span class="flex items-center gap-1.5">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
          <?= e($session['location']) ?>
        </span>
      </div>
    </div>
  </section>

  <!-- 본문 -->
  <section class="max-w-7xl mx-auto px-6 py-12">
    <div class="grid lg:grid-cols-[1fr_360px] gap-12">
      <!-- 좌측 -->
      <div class="space-y-10">
        <div>
          <h2 class="text-xl font-bold text-white mb-4">세션 소개</h2>
          <p class="text-[#a1a1aa] leading-relaxed"><?= e($session['desc']) ?></p>
        </div>
        <div>
          <h2 class="text-xl font-bold text-white mb-4">세션 목차</h2>
          <ul class="space-y-2">
            <?php foreach ($session['contents'] as $c): ?>
              <li class="flex items-baseline gap-2 text-[#a1a1aa]"><span class="text-[#00C1D5] flex-shrink-0">•</span><span><?= e($c) ?></span></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div>
          <h2 class="text-xl font-bold text-white mb-4">권장 대상</h2>
          <ul class="space-y-2">
            <?php foreach (explode(',', $session['target']) as $tg): ?>
              <li class="flex items-baseline gap-2 text-[#a1a1aa]"><span class="text-[#00C1D5] flex-shrink-0">•</span><span><?= e(trim($tg)) ?></span></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <!-- 우측 -->
      <div class="space-y-6">
        <?php foreach ($session['speakers'] as $spi => $sp): ?>
        <div class="bg-[#0e0f14] p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-24 h-24 rounded-full bg-[#1a1a1f] border border-[#27272a] flex items-center justify-center flex-shrink-0 overflow-hidden">
              <?php $sp_photo = $sp['photo'] !== '' ? $sp['photo'] : ($spi === 0 ? ufs_keynote_img($session['id']) : ''); ?>
              <?php if ($sp_photo !== ''): ?>
                <img src="<?= e($sp_photo) ?>" alt="<?= e($sp['name']) ?>" class="w-full h-full object-cover" onerror="this.style.display='none'">
              <?php else: ?>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 text-[#71717a]"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <?php endif; ?>
            </div>
            <div class="min-w-0">
              <div class="text-xl font-bold text-[#fafafa]"><?= e($sp['name']) ?></div>
              <?php if ($sp['role'] !== ''): ?><div class="text-sm text-[#a1a1aa]"><?= e($sp['role']) ?></div><?php endif; ?>
              <?php if ($sp['company'] !== ''): ?><div class="text-xs text-[#71717a]"><?= e($sp['company']) ?></div><?php endif; ?>
            </div>
          </div>
          <?php $bio_txt = $sp['bio'] !== '' ? $sp['bio'] : $speaker_bio; ?>
          <?php if (count($session['speakers']) > 1): ?>
            <details class="mt-1 group"><summary class="text-sm font-semibold text-[#00C1D5] cursor-pointer select-none list-none flex items-center gap-1.5"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform group-open:rotate-180"><path d="m6 9 6 6 6-6"/></svg>약력 보기</summary><p class="text-sm text-[#a1a1aa] leading-relaxed mt-3"><?= e($bio_txt) ?></p></details>
          <?php else: ?>
            <p class="text-sm text-[#a1a1aa] leading-relaxed"><?= e($bio_txt) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php
          $cal_date  = str_replace('-', '', ufs_day_iso($session['day']));      // 20260820
          $cal_tp    = preg_split('/\s*[~\-]\s*/u', $session['time'], 2);
          $cal_start = isset($cal_tp[0]) ? preg_replace('/[^0-9]/', '', $cal_tp[0]) : '';
          $cal_end   = isset($cal_tp[1]) ? preg_replace('/[^0-9]/', '', $cal_tp[1]) : '';
        ?>
        <div class="space-y-3">
          <button type="button" data-addcal
            data-title="<?= e($session['title']) ?>"
            data-date="<?= e($cal_date) ?>"
            data-start="<?= e($cal_start) ?>"
            data-end="<?= e($cal_end) ?>"
            data-location="<?= e($session['location']) ?>"
            data-desc="<?= e($session['desc']) ?>"
            class="w-full flex items-center justify-center gap-2 py-3 border border-[#27272a] text-[#a1a1aa] text-sm font-medium hover:text-white hover:border-white/20 transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            일정에 추가하기
          </button>
          <button type="button" data-share data-title="<?= e($session['title']) ?>" class="w-full flex items-center justify-center gap-2 py-3 border border-[#27272a] text-[#a1a1aa] text-sm font-medium hover:text-white hover:border-white/20 transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
            세션 공유하기
          </button>
        </div>

        <div class="p-6 text-center">
          <p class="text-sm text-[#a1a1aa] mb-4">언리얼 페스트 등록 전이신가요?</p>
          <a href="index.php#register" class="inline-flex items-center justify-center w-full py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-bold transition-colors clip-btn-sm">지금 등록하기</a>
        </div>
      </div>
    </div>
  </section>

  <!-- 관련 세션 -->
  <?php if (count($related) > 0): ?>
  <section class="max-w-7xl mx-auto px-6 pb-24">
    <h2 class="text-xl font-bold text-white mb-6">관련 세션</h2>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($related as $r): ?>
        <a href="session.php?id=<?= e($r['id']) ?>" class="bg-[#0e0f14] p-5 hover:bg-[#111115] transition-colors flex flex-col gap-2">
          <span class="self-start px-2.5 py-0.5 text-xs font-medium <?= ufs_track_badge_detail($r['track']) ?>"><?= e(ufs_track_label_day($r['track'], $r['day'])) ?></span>
          <h3 class="text-base font-bold text-[#fafafa] tracking-tight"><?= e($r['title']) ?></h3>
          <span class="text-xs text-[#71717a]"><?= e($r['time']) ?> · <?= e($r['location']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</div>
<?php endif; ?>

<script>
(function(){
  function t4(s){ s = (s || '').replace(/[^0-9]/g, ''); return (s + '0000').slice(0, 4); }
  var add = document.querySelector('[data-addcal]');
  if (add) add.addEventListener('click', function(){
    var date = add.getAttribute('data-date') || '';
    if (!date) { alert('일정 정보가 없습니다.'); return; }
    var st = t4(add.getAttribute('data-start')), en = t4(add.getAttribute('data-end'));
    if (en === '0000') en = st;
    var title = add.getAttribute('data-title') || '세션';
    var loc = add.getAttribute('data-location') || '';
    var desc = add.getAttribute('data-desc') || '';
    var details = (desc ? desc + '\n\n' : '') + 'Unreal Fest Seoul 2026';
    var dates = date + 'T' + st + '00/' + date + 'T' + en + '00';
    var url = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
      + '&text=' + encodeURIComponent('[UNREAL FEST SEOUL 2026] ' + title)
      + '&dates=' + dates
      + '&ctz=Asia/Seoul'
      + '&location=' + encodeURIComponent(loc)
      + '&details=' + encodeURIComponent(details);
    window.open(url, '_blank');
  });
  var sh = document.querySelector('[data-share]');
  if (sh) sh.addEventListener('click', function(){
    var title = sh.getAttribute('data-title') || document.title;
    var url = location.href;
    if (navigator.share) { navigator.share({title: title, text: title, url: url}).catch(function(){}); }
    else if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(function(){ alert('세션 링크가 복사되었습니다.'); }, function(){ window.prompt('아래 링크를 복사하세요', url); });
    } else { window.prompt('아래 링크를 복사하세요', url); }
  });
})();
</script>
<?php include __DIR__ . '/_foot.php'; ?>
