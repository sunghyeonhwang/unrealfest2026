<?php
// Unreal Fest Seoul 2026 — 홈 (Design Ref: design doc §5.4 홈 / dist homeSections)
require __DIR__ . '/data/lib.php';
require __DIR__ . '/_components.php';
$page_title = 'Unreal Fest Seoul 2026 — 언리얼 페스트 서울 2026';
$sessions = ufs_sessions();
$keynotes = ufs_keynotes();
$tracks = array(
  '게임 - 프로그래밍' => 'public/AAAGames-Fall2025-WebBanner_1080p30-H265-5Mbps.mp4',
  '게임 - 아트' => 'public/unreal-engine-animation-reel.mp4',
  '미디어 & 엔터테인먼트' => 'public/film-and-tv-hero.mp4',
  '산업 & 시뮬레이션' => 'public/automotive-and-transport-hero.mp4',
);
include __DIR__ . '/_head.php';
?>

<!-- Hero -->
<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden bg-black">
  <video class="absolute inset-0 w-full h-full object-cover object-bottom opacity-60" autoplay loop muted playsinline poster="public/ufs26_seoul_main_logo.svg">
    <source src="public/AmbientLoop_WIP.mp4" type="video/mp4">
  </video>
  <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-ink-900"></div>
  <div class="relative z-10 text-center px-6 max-w-3xl mx-auto">
    <img src="public/ufs26_seoul_main_logo.svg" alt="Unreal Fest Seoul 2026" class="w-full max-w-2xl mx-auto mb-8">
    <p class="text-2xl md:text-4xl text-white mb-6" style="font-family:var(--font-display);font-weight:800;">Unreal Ideas Start Here</p>
    <p class="text-base md:text-lg text-slate-300 mb-2">2026. 8. 20 (목) ~ 21 (금)</p>
    <p class="text-sm md:text-base text-slate-400 mb-10">웨스틴 서울 파르나스</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="#register" class="clip-btn bg-brand hover:bg-brand-shadow text-white font-bold px-8 py-4 transition-colors">지금 등록하기</a>
      <a href="sessions.php" class="clip-btn bg-white/10 hover:bg-white/20 text-white font-bold px-8 py-4 transition-colors backdrop-blur">아젠다 보기</a>
    </div>
  </div>
</section>

<!-- Overview -->
<section id="overview" class="scroll-mt-24 py-24 bg-ink-900">
  <div class="max-w-7xl mx-auto px-6">
    <div class="max-w-3xl mb-16">
      <h2 class="text-3xl md:text-5xl font-bold mb-6">리얼타임 3D의<br>모든 것을 한자리에</h2>
      <p class="text-lg text-slate-400 leading-relaxed">게임, 영화 및 TV, 애니메이션, 자동차, 시뮬레이션 등 산업 전반을 아우르는 언리얼 엔진 생태계의 최신 기술과 인사이트를 만나보세요. 국내외 전문가들의 깊이 있는 세션과 네트워킹의 장이 펼쳐집니다.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php
      $features = array(
        array('전문가 인사이트', '국내외 현업 전문가들이 직접 전하는 실전 노하우와 최신 트렌드.'),
        array('최신 기술과 워크플로', '언리얼 엔진 6.0을 비롯한 차세대 도구와 파이프라인을 한발 앞서.'),
        array('에픽 에코시스템 경험', 'UEFN, 메타휴먼, 페이블 등 에픽 생태계를 현장에서 직접 체험.'),
      );
      foreach ($features as $f): ?>
      <div class="clip-tr bg-ink-800 border border-white/10 p-8">
        <h3 class="text-xl font-bold mb-3"><?= e($f[0]) ?></h3>
        <p class="text-sm text-slate-400 leading-relaxed"><?= e($f[1]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="mt-12 text-sm text-slate-500">총 <span class="text-brand font-bold"><?= count($sessions) ?></span>개의 세션 · 4개 트랙 · 양일간 진행</p>
  </div>
</section>

<!-- 트랙 + 키노트 -->
<section id="tracks" class="scroll-mt-24 py-24 bg-ink-850">
  <div class="max-w-7xl mx-auto px-6">
    <h2 class="text-3xl md:text-5xl font-bold mb-12">4개 트랙</h2>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-20">
      <?php foreach ($tracks as $track => $video) { render_track_card($track, $video); } ?>
    </div>

    <h2 class="text-3xl md:text-5xl font-bold mb-12">키노트</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($keynotes as $k): ?>
      <a href="session.php?id=<?= e($k['id']) ?>" class="group clip-tr bg-ink-800 border border-white/10 hover:border-brand/40 p-8 transition-all">
        <span class="text-xs font-semibold text-brand">KEYNOTE</span>
        <h3 class="text-2xl font-bold mt-3 mb-4 group-hover:text-brand-highlight transition-colors"><?= e($k['title']) ?></h3>
        <p class="text-sm text-slate-400"><?= e($k['speaker']) ?> · <?= e($k['affiliation']) ?></p>
        <p class="text-xs text-slate-500 mt-1">Day <?= e($k['day']) ?> · <?= e($k['time']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 아젠다 (Day별 캐러셀) -->
<section id="agenda" class="scroll-mt-24 py-24 bg-ink-900">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex items-end justify-between mb-12">
      <h2 class="text-3xl md:text-5xl font-bold">아젠다</h2>
      <a href="sessions.php" class="text-sm text-brand hover:text-brand-highlight font-semibold">전체 세션 보기 →</a>
    </div>
    <?php for ($day = 1; $day <= 2; $day++):
      $dsessions = ufs_sessions_by_day($day); ?>
    <div class="mb-14" data-carousel>
      <div class="flex items-center justify-between mb-5">
        <h3 class="text-lg font-bold">Day. <?= $day ?> <span class="text-slate-500 font-normal">| <?= e(ufs_day_date($day)) ?></span></h3>
        <div class="flex gap-2">
          <button type="button" data-carousel-prev class="w-9 h-9 rounded-full border border-white/15 hover:bg-white/10 flex items-center justify-center" aria-label="이전">‹</button>
          <button type="button" data-carousel-next class="w-9 h-9 rounded-full border border-white/15 hover:bg-white/10 flex items-center justify-center" aria-label="다음">›</button>
        </div>
      </div>
      <div data-carousel-track class="flex gap-4 overflow-x-auto no-scrollbar snap-x cursor-grab pb-2">
        <?php foreach ($dsessions as $i => $s) { render_session_card($s, $i, 'agenda'); } ?>
      </div>
    </div>
    <?php endfor; ?>
  </div>
</section>

<!-- 티켓 / 등록 -->
<section id="register" class="scroll-mt-24 py-24 bg-ink-850">
  <div class="max-w-7xl mx-auto px-6">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-5xl font-bold mb-4">등록</h2>
      <p class="text-slate-400">얼리버드 할인 (~7/13 마감)</p>
    </div>
    <!-- 카운트다운 -->
    <div data-countdown data-deadline="2026-07-13T23:59:59" class="flex justify-center gap-4 md:gap-8 mb-14">
      <?php
      $cd = array('days'=>'일','hours'=>'시간','mins'=>'분','secs'=>'초');
      foreach ($cd as $key => $label): ?>
      <div class="text-center">
        <div class="text-3xl md:text-5xl font-bold text-brand tabular-nums" data-cd-<?= $key ?>>00</div>
        <div class="text-xs text-slate-500 mt-1"><?= $label ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
      <?php
      $tickets = array(
        array('오프라인 양일권', 'Day 1 + Day 2 전 세션 + 굿즈 + 부스', 'ticket.php?type=all', false),
        array('오프라인 1일권', '원하는 하루 + 굿즈 + 부스', 'ticket.php?type=day1', false),
        array('온라인 무료', '라이브 세션 시청 (Q&A 제외)', 'ticket-online.php', true),
      );
      foreach ($tickets as $t): ?>
      <div class="clip-br bg-ink-800 border <?= $t[3] ? 'border-brand/40' : 'border-white/10' ?> p-8 flex flex-col">
        <h3 class="text-xl font-bold mb-2"><?= e($t[0]) ?></h3>
        <p class="text-sm text-slate-400 mb-8 flex-1"><?= e($t[1]) ?></p>
        <a href="<?= e($t[2]) ?>" class="clip-btn text-center font-bold px-6 py-3 transition-colors <?= $t[3] ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-brand hover:bg-brand-shadow text-white' ?>"><?= $t[3] ? '무료 등록' : '등록하기' ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 행사장 -->
<section id="venue" class="scroll-mt-24 py-24 bg-ink-900">
  <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
    <div>
      <h2 class="text-3xl md:text-5xl font-bold mb-6">행사장 안내</h2>
      <p class="text-xl font-bold mb-2">웨스틴 서울 파르나스</p>
      <p class="text-slate-400 mb-8">서울 강남구 테헤란로 606</p>
      <div class="grid grid-cols-3 gap-4 mb-8">
        <?php foreach (array('행사장 체크인','주차 안내','대중교통') as $info): ?>
        <div class="clip-tr-16 bg-ink-800 border border-white/10 p-4 text-center text-sm text-slate-300"><?= e($info) ?></div>
        <?php endforeach; ?>
      </div>
      <a href="https://map.naver.com/p/search/웨스틴%20서울%20파르나스" target="_blank" rel="noopener" class="clip-btn inline-block bg-brand hover:bg-brand-shadow text-white font-bold px-6 py-3 transition-colors">지도에서 열기</a>
    </div>
    <div class="clip-tr overflow-hidden border border-white/10 aspect-[4/3]">
      <iframe class="w-full h-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
        src="https://www.google.com/maps?q=웨스틴%20서울%20파르나스&output=embed"></iframe>
    </div>
  </div>
</section>

<!-- 이벤트 -->
<section id="event-benefits" class="scroll-mt-24 py-24 bg-ink-850">
  <div class="max-w-7xl mx-auto px-6">
    <h2 class="text-3xl md:text-5xl font-bold mb-12">이벤트 &amp; 혜택</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="clip-tr bg-ink-800 border border-white/10 p-8 md:col-span-2">
        <h3 class="text-xl font-bold mb-2">현장 한정 굿즈 &amp; 웰컴 키트</h3>
        <p class="text-sm text-slate-400">오프라인 등록자 전원에게 한정판 굿즈와 웰컴 키트를 제공합니다.</p>
      </div>
      <?php
      $events = array(
        array('얼리버드 체크인 이벤트', '얼리버드 등록 후 현장 체크인 시 추가 혜택.'),
        array('출석 체크 이벤트', '세션 출석 도장을 모아 경품 응모.'),
        array('경품 추첨 이벤트', '현장 참여자 대상 푸짐한 경품 추첨.'),
        array('온라인 시청 이벤트', '온라인 라이브 시청자 대상 이벤트.'),
      );
      foreach ($events as $ev): ?>
      <div class="clip-tr bg-ink-800 border border-white/10 p-8">
        <h3 class="text-lg font-bold mb-2"><?= e($ev[0]) ?></h3>
        <p class="text-sm text-slate-400"><?= e($ev[1]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 스폰서 티저 -->
<section id="sponsors" class="scroll-mt-24 py-24 bg-ink-900">
  <div class="max-w-7xl mx-auto px-6 text-center">
    <h2 class="text-3xl md:text-5xl font-bold mb-4">스폰서</h2>
    <p class="text-slate-400 mb-10 max-w-2xl mx-auto">언리얼 페스트 서울 2026을 함께 만들어가는 파트너사입니다.</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="sponsors.php" class="clip-btn bg-brand hover:bg-brand-shadow text-white font-bold px-8 py-4 transition-colors">스폰서 자세히 보기</a>
      <a href="mailto:info@epiclounge.co.kr" class="clip-btn bg-white/10 hover:bg-white/20 text-white font-bold px-8 py-4 transition-colors">스폰서십 문의</a>
    </div>
  </div>
</section>

<!-- FAQ -->
<section id="faq" class="scroll-mt-24 py-24 bg-ink-850">
  <div class="max-w-4xl mx-auto px-6" data-faq>
    <h2 class="text-3xl md:text-5xl font-bold mb-12 text-center">자주 묻는 질문</h2>
    <div class="flex flex-wrap justify-center gap-2 mb-10">
      <?php foreach (ufs_faq_tabs() as $tab): ?>
      <button type="button" data-faq-tab="<?= e($tab) ?>" class="rounded-full px-5 py-2 text-sm border border-white/15 text-slate-400 transition-colors"><?= e($tab) ?></button>
      <?php endforeach; ?>
    </div>
    <?php $faqs = ufs_faqs(); foreach (ufs_faq_tabs() as $tab): ?>
    <div data-faq-item="<?= e($tab) ?>" class="space-y-3">
      <?php foreach ($faqs as $q): if ($q['tab'] !== $tab) continue; ?>
      <div class="clip-tr-16 bg-ink-800 border border-white/10 overflow-hidden">
        <button type="button" data-acc-trigger class="w-full text-left px-6 py-5 flex items-center justify-between gap-4 hover:bg-white/5 transition-colors">
          <span class="font-semibold text-sm md:text-base"><?= e($q['question']) ?></span>
          <span class="text-brand text-xl shrink-0">+</span>
        </button>
        <div data-acc-body class="hidden px-6 pb-5 text-sm text-slate-400 leading-relaxed"><?= e_nl($q['answer']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/_foot.php'; ?>
