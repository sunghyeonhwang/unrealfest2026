<?php
// Unreal Fest Seoul 2026 — 홈 (React Home.tsx 1:1: Hero/Overview/Agenda/Register/Venue/Sponsors/EventBenefits/FAQ)
// 순수 PHP/HTML/CSS/JS. 데이터는 data/lib.php 접근자. 디자인 기준 = 라이브 React 렌더 캡처.
$ufs_page = 'home';
include_once __DIR__ . '/../common.php';        // DB (sql_query)
require_once __DIR__ . '/data/lib.php';
require_once __DIR__ . '/data/agenda_db.php';
require_once __DIR__ . '/_pricing.php';   // 가격 단일 소스(얼리버드/정가 자동)
include __DIR__ . '/_head.php';
?>

<!-- ===== Hero ===== -->
<section id="hero" class="relative h-screen overflow-hidden">
  <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover object-bottom" style="object-position: calc(50% + 200px) bottom;">
    <source src="https://unrealsummit16.cafe24.com/2026/WEBSITE_USE_ONLY_Fest_ambient_loop_1920x1080_v05.webm" type="video/webm">
    <source src="https://unrealsummit16.cafe24.com/2026/WEBSITE_USE_ONLY_Fest_ambient_loop_1920x1080_v05.mp4" type="video/mp4">
  </video>
  <div class="absolute inset-0 bg-gradient-to-b from-black via-black/60 to-transparent"></div>
  <div class="relative z-10 max-w-7xl mx-auto px-6 w-full flex flex-col items-start pt-52 md:pt-64 pb-[45vh]">
    <div class="mb-10">
      <img src="https://unrealsummit16.cafe24.com/2026/ufs26/hero_new_main_logo.svg" alt="Unreal Fest Seoul 2026" style="width: 700px; max-width: 100%;">
    </div>
    <div class="flex flex-col sm:flex-row items-start gap-4 mb-10">
      <button type="button" data-scroll="register" class="bg-[#00C1D5] hover:bg-[#004F59] text-white px-8 py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow-lg clip-btn">
        지금 등록하기
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </button>
      <button type="button" data-scroll="agenda" class="bg-white/10 backdrop-blur-md border border-white/20 hover:bg-white/20 text-white px-8 py-4 font-bold text-lg flex items-center justify-center transition-all">아젠다 보기</button>
    </div>
  </div>
  <!-- 카운트다운: 도킹 헤더에 안 가리도록 위로(bottom-20), 본문(max-w-7xl) 우측에 정렬 -->
  <div class="absolute inset-x-0 bottom-20 z-10 pointer-events-none">
    <div class="max-w-7xl mx-auto px-6 flex justify-end">
      <div class="relative bg-[#050508] px-8 py-4 pointer-events-auto" data-countdown data-deadline="<?= e(ufs_earlybird_deadline()) ?>">
        <div class="absolute -top-[30px] left-0 bg-[#00C1D5] px-5 py-1">
          <span class="text-[#090a0f] text-[14px] font-bold tracking-tight">얼리버드 할인 종료까지</span>
        </div>
        <div class="flex items-center gap-0 mt-1">
          <div class="flex flex-col items-center w-[40px]">
            <span class="text-xl font-bold text-[#9adbe8] tabular-nums font-mono" data-cd-days>00</span>
            <span class="text-[10px] text-[#71717a] mt-1 tracking-wider">일</span>
          </div>
          <span class="text-lg text-[#3f3f46] mx-1.5 font-light">:</span>
          <div class="flex flex-col items-center w-[40px]">
            <span class="text-xl font-bold text-[#9adbe8] tabular-nums font-mono" data-cd-hours>00</span>
            <span class="text-[10px] text-[#71717a] mt-1 tracking-wider">시간</span>
          </div>
          <span class="text-lg text-[#3f3f46] mx-1.5 font-light">:</span>
          <div class="flex flex-col items-center w-[40px]">
            <span class="text-xl font-bold text-[#9adbe8] tabular-nums font-mono" data-cd-mins>00</span>
            <span class="text-[10px] text-[#71717a] mt-1 tracking-wider">분</span>
          </div>
          <span class="text-lg text-[#3f3f46] mx-1.5 font-light">:</span>
          <div class="flex flex-col items-center w-[40px]">
            <span class="text-xl font-bold text-[#9adbe8] tabular-nums font-mono" data-cd-secs>00</span>
            <span class="text-[10px] text-[#71717a] mt-1 tracking-wider">초</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== Overview ===== -->
<?php $ov = ufs_overview();
$ov_icons = array(
  'layout-grid' => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
  'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
  'zap' => '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
  'video' => '<path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.87a.5.5 0 0 0-.752-.432L16 10.5"/><rect x="2" y="6" width="14" height="12" rx="2"/>',
); ?>
<section id="overview" class="py-24 bg-[#09090b] relative border-t border-white/5">
  <div class="max-w-7xl mx-auto px-6 relative z-10">
    <div class="grid lg:grid-cols-[1fr_1.2fr] gap-12 items-start">
      <div>
        <img src="<?= e($ov['image']) ?>" alt="<?= e($ov['image_alt']) ?>" class="mb-6" style="width: 420px; max-width: 100%;">
        <div class="space-y-4 text-[#a1a1aa] leading-relaxed text-[18px] font-jamjil font-normal">
          <?php foreach ($ov['paragraphs'] as $p): ?><p><?= ufs_render_br($p) ?></p><?php endforeach; ?>
        </div>
      </div>
      <div class="grid sm:grid-cols-2 gap-6">
        <?php foreach ($ov['features'] as $f): ?>
          <div class="bg-[#0e0f14] p-6 text-center flex flex-col items-center">
            <div class="w-12 h-12 bg-[#111115] border border-[#27272a] flex items-center justify-center mb-4">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-[#00C1D5]"><?= $ov_icons[$f['icon']] ?></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-2"><?= e($f['title']) ?></h3>
            <p class="text-sm text-[#a1a1aa] leading-relaxed font-jamjil font-normal"><?= ufs_render_br($f['desc']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ===== Agenda ===== -->
<section id="agenda" class="py-24 bg-[#09090b] relative border-t border-white/5">
  <div class="max-w-7xl mx-auto px-6 mb-12">
    <h2 class="text-3xl md:text-5xl text-white mb-4 tracking-tight">아젠다</h2>
    <p class="text-[#90a1b9]">최신 기술과 새로운 아이디어, 다양한 산업 분야의 세션을 만나보세요.</p>
  </div>

  <!-- Day1 / Day2 캐러셀 (키노트는 Day1 헤더 하단) -->
  <?php
  $day_blocks = array(
    array('title' => 'Day 1. 8월 20일(목)', 'sessions' => ufs_db_day_sessions(1)),
    array('title' => 'Day 2. 8월 21일(금)', 'sessions' => ufs_db_day_sessions(2)),
  );
  foreach ($day_blocks as $bi => $db): ?>
    <div class="mb-10" data-carousel>
      <div class="max-w-7xl mx-auto px-6 flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-white tracking-tight"><?= e($db['title']) ?></h3>
        <div class="flex items-center gap-2">
          <button type="button" data-carousel-prev class="w-9 h-9 border border-[#27272a] flex items-center justify-center text-[#71717a] hover:text-white hover:border-white/30 transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="m15 18-6-6 6-6"/></svg>
          </button>
          <button type="button" data-carousel-next class="w-9 h-9 border border-[#27272a] flex items-center justify-center text-[#71717a] hover:text-white hover:border-white/30 transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="m9 18 6-6-6-6"/></svg>
          </button>
        </div>
      </div>
      <?php if ($bi === 0): ?>
      <!-- 키노트 (8월 20일 Day1 진행) -->
      <div class="max-w-7xl mx-auto px-6 mb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <?php
          foreach (ufs_db_keynotes() as $ki => $k):
            $img = !empty($k['speaker']['photo']) ? $k['speaker']['photo'] : ufs_keynote_img($k['id']); ?>
            <a href="session.php?id=<?= e($k['id']) ?>" class="block bg-[#00C1D5] p-6 hover:bg-[#00b0c2] transition-colors relative overflow-hidden min-h-[240px] rounded-[6px]">
              <div class="relative z-10 max-w-[65%]">
                <div class="flex items-center gap-2 mb-4">
                  <span class="px-2.5 py-0.5 text-[11px] font-bold bg-black/20 text-white">키노트</span>
                  <span class="px-2 py-0.5 text-[11px] font-semibold bg-black/20 text-white"><?= e(ufs_level_label_short($k['level'])) ?></span>
                </div>
                <h3 class="text-xl font-bold text-black mb-6 tracking-tight leading-snug"><?= e($k['title']) ?></h3>
                <div>
                  <div class="text-sm font-bold text-black"><?= e($k['speaker']['name']) ?></div>
                  <div class="text-xs text-black/60"><?= e($k['speaker']['role']) ?> · <?= e($k['speaker']['company']) ?></div>
                </div>
              </div>
              <?php if ($img): ?>
                <div class="absolute right-0 bottom-0 top-0 w-[42%] hidden md:flex items-end justify-end pointer-events-none">
                  <img src="<?= e($img) ?>" alt="<?= e($k['speaker']['name']) ?>" class="max-h-full w-auto object-contain object-bottom" onerror="this.style.display='none'">
                </div>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="max-w-7xl mx-auto px-6">
        <div class="relative">
          <div data-carousel-track class="overflow-x-auto pb-2 no-scrollbar cursor-grab select-none">
            <div class="flex gap-4">
              <?php for ($rep = 0; $rep < 3; $rep++): foreach ($db['sessions'] as $s): ?>
                <a href="session.php?id=<?= e($s['id']) ?>" class="flex-shrink-0 w-[320px] min-h-[240px] bg-[#131418] rounded-[6px] px-5 py-[22px] flex flex-col gap-2 cursor-pointer hover:bg-[#1a1b20] transition-colors">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-1.5 py-1 text-[10px] rounded-[4px] <?= ufs_track_badge_home($s['track']) ?>"><?= e(ufs_track_label_day($s['track'], $s['day'])) ?></span>
                  </div>
                  <h4 class="text-[18px] font-bold text-white leading-[28px] tracking-tight line-clamp-3 flex-grow font-display"><?= e($s['title']) ?></h4>
                  <div class="flex items-center gap-2.5 mt-auto">
                    <?php $cs_photo = !empty($s['speaker']['photo']) ? $s['speaker']['photo'] : ''; ?>
                    <?php if ($cs_photo !== ''): ?>
                      <img src="<?= e($cs_photo) ?>" alt="<?= e($s['speaker']['name']) ?>" class="w-12 h-12 rounded-full object-cover flex-shrink-0" onerror="this.style.display='none'">
                    <?php else: ?>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 <?= ufs_track_avatar_home($s['track']) ?>">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-black/60"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                      <div class="text-[13px] font-bold text-white truncate"><?= e($s['_speakers_label']) ?></div>
                      <div class="text-[13px] text-white/80 truncate"><?= e($s['speaker']['company']) ?></div>
                    </div>
                  </div>
                </a>
              <?php endforeach; endfor; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- CTA -->
  <div class="text-center mt-4">
    <a href="schedule.php" class="inline-flex items-center gap-2 px-10 py-3.5 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors clip-btn">
      전체 세션 보기
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </a>
  </div>
</section>

<!-- ===== Register (티켓) ===== -->
<section id="register" class="py-24 bg-[#0e0f14] relative border-t border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6">
    <div class="mb-16">
      <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight">티켓</h2>
      <p class="text-base text-[#90a1b9]">오프라인과 온라인으로 언리얼 페스트 서울 2026을 경험해 보세요.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-[26px] pt-[35px]">
      <!-- 양일권 -->
      <div class="relative bg-[#0e0f14] border border-[#27272a] p-9 flex flex-col items-center text-center">
        <?php if (ufs_is_earlybird()): ?><div class="absolute -top-[13px] left-0 bg-[#00C1D5] text-[#090a0f] text-[14px] font-bold px-[18px] py-[7px]">얼리버드 50% 할인</div><?php endif; ?>
        <h3 class="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px] font-jamjil font-medium">오프라인 양일권</h3>
        <?php if (ufs_is_earlybird()): ?><div class="mb-1"><span class="text-[18px] text-[#71717a] line-through tracking-tight">₩ <?= number_format(ufs_ticket_orig('NORMAL_ALL')) ?></span></div><?php endif; ?>
        <div class="mb-2"><span class="text-[40px] font-bold text-white tracking-tight">₩ <?= number_format(ufs_ticket_price('NORMAL_ALL')) ?></span></div>
        <?php if (ufs_is_earlybird()): ?><p class="text-[13px] text-[#9adbe8] mb-auto">얼리버드 할인 (~7/13 마감)</p><?php else: ?><div class="mb-auto"></div><?php endif; ?>
        <a href="ticket-all.php" class="mt-[35px] w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 transition-colors font-jamjil">
          양일권 등록하기
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>
      <!-- 1일권 (featured) -->
      <div class="relative bg-[#0e0f14] border border-[rgba(0,193,213,0.5)] p-9 flex flex-col items-center text-center shadow-[0_0_11px_rgba(0,193,213,0.1)]">
        <?php if (ufs_is_earlybird()): ?><div class="absolute -top-[13px] left-0 bg-[#00C1D5] text-[#090a0f] text-[14px] font-bold px-[18px] py-[7px]">얼리버드 50% 할인</div><?php endif; ?>
        <h3 class="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px] font-jamjil font-medium">오프라인 1일권</h3>
        <?php if (ufs_is_earlybird()): ?><div class="mb-1"><span class="text-[18px] text-[#71717a] line-through tracking-tight">₩ <?= number_format(ufs_ticket_orig('NORMAL_20')) ?></span></div><?php endif; ?>
        <div class="mb-2"><span class="text-[40px] font-bold text-white tracking-tight">₩ <?= number_format(ufs_ticket_price('NORMAL_20')) ?></span></div>
        <?php if (ufs_is_earlybird()): ?><p class="text-[13px] text-[#9adbe8] mb-auto">얼리버드 할인 (~7/13 마감)</p><?php else: ?><div class="mb-auto"></div><?php endif; ?>
        <a href="ticket-day.php" class="mt-[35px] w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 transition-colors font-jamjil">
          1일권 등록하기
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>
      <!-- 온라인 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-9 flex flex-col items-center text-center">
        <h3 class="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px] font-jamjil font-medium">온라인</h3>
        <div class="mb-2"><span class="text-[26px] font-bold text-[#a1a1aa]">무료</span></div>
        <p class="text-[15px] text-[#71717a] mb-auto">(일부 세션 생중계)</p>
        <a href="ticket-online.php" class="mt-[35px] w-full border border-[#27272a] text-[#a1a1aa] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 hover:border-white/30 hover:text-white transition-colors font-jamjil">
          무료 등록하기
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>
    </div>
    <p class="text-sm text-[#00C1D5] font-bold mt-8 text-right tracking-tight">· 오프라인 티켓은 한정 수량으로 조기 마감될 수 있습니다.</p>
    <div class="mt-12 border border-[#27272a] p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h3 class="text-xl font-bold text-[#fafafa] mb-2">단체 등록 및 기업 결제</h3>
        <p class="text-sm text-[#a1a1aa]">5인 이상 단체 등록 시 세금계산서 발행 및 무통장 입금을 지원합니다. 관련 문의는 운영 사무국으로 연락해 주세요.</p>
      </div>
      <a href="mailto:info@epiclounge.co.kr" class="flex-shrink-0 inline-flex items-center gap-2 px-6 py-2.5 bg-white text-black text-sm font-bold hover:bg-white/90 transition-colors whitespace-nowrap clip-btn-8">
        문의하기
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ===== Venue ===== -->
<?php $vn = ufs_venue(); ?>
<section id="venue" class="py-24 bg-[#09090b] relative border-t border-white/5">
  <div class="max-w-7xl mx-auto px-6">
    <div class="mb-16">
      <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight">행사장 안내</h2>
      <p class="text-[#90a1b9]">행사장 위치와 체크인, 교통 정보를 확인해 보세요.</p>
    </div>
    <div class="grid lg:grid-cols-2 gap-6">
      <div class="relative overflow-hidden h-[500px] lg:h-auto">
        <iframe src="<?= e($vn['map_embed']) ?>" class="w-full h-full min-h-[500px]" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="웨스틴 서울 파르나스 지도" style="border:0; filter: invert(90%) hue-rotate(180deg) brightness(0.9) contrast(1.1);"></iframe>
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent z-10 p-6 pt-16">
          <div class="flex justify-between items-end">
            <div>
              <h3 class="text-2xl font-bold text-white mb-1"><?= e($vn['name']) ?></h3>
              <p class="text-slate-300"><?= e($vn['address']) ?></p>
            </div>
            <a href="<?= e($vn['map_link']) ?>" target="_blank" rel="noopener noreferrer" class="bg-white text-black px-4 py-2 font-bold text-sm flex items-center gap-2 hover:bg-neutral-100 transition-colors">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
              지도 열기
            </a>
          </div>
        </div>
      </div>
      <div class="bg-[#0e0f14] flex flex-col divide-y divide-[#27272a]">
        <!-- 체크인 -->
        <div class="p-8 flex gap-5">
          <div class="w-10 h-10 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center flex-shrink-0 mt-1">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-[#00C1D5]"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <div>
            <h4 class="text-lg font-bold text-white mb-2"><?= e($vn['cards'][0]['title']) ?></h4>
            <p class="text-sm text-[#a1a1aa] leading-relaxed"><?= e($vn['cards'][0]['body']) ?></p>
          </div>
        </div>
        <!-- 대중교통 -->
        <div class="p-8 flex gap-5">
          <div class="w-10 h-10 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center flex-shrink-0 mt-1">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-[#00C1D5]"><rect width="16" height="16" x="4" y="3" rx="2"/><path d="M4 11h16"/><path d="M12 3v8"/><path d="m8 19-2 3"/><path d="m18 22-2-3"/><path d="M8 15h.01"/><path d="M16 15h.01"/></svg>
          </div>
          <div>
            <h4 class="text-lg font-bold text-white mb-2"><?= e($vn['cards'][1]['title']) ?></h4>
            <p class="text-sm text-[#a1a1aa] leading-relaxed"><strong class="text-white">지하철:</strong> <?= e($vn['cards'][1]['subway']) ?><br><strong class="text-white">버스:</strong> <?= e($vn['cards'][1]['bus']) ?></p>
          </div>
        </div>
        <!-- 주차 -->
        <div class="p-8 flex gap-5">
          <div class="w-10 h-10 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center flex-shrink-0 mt-1">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-[#00C1D5]"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
          </div>
          <div>
            <h4 class="text-lg font-bold text-white mb-2"><?= e($vn['cards'][2]['title']) ?></h4>
            <p class="text-sm text-[#a1a1aa] leading-relaxed"><?= e($vn['cards'][2]['body']) ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== Event Benefits ===== -->
<?php $evs = ufs_events(); ?>
<section id="event-benefits" class="py-24 bg-[#09090b] relative border-t border-white/5">
  <div class="max-w-7xl mx-auto px-6">
    <div class="mb-16">
      <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight">이벤트</h2>
      <p class="text-lg text-[#90a1b9]">현장과 온라인에서 진행되는 다양한 이벤트를 만나보세요.</p>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      <?php foreach ($evs as $ev):
        $badge = $ev['type'] === '온라인' ? 'bg-[#FF8F1C]' : 'bg-[#00C1D5]'; ?>
        <div class="bg-[#131418] rounded-[6px] p-6 md:p-8 flex flex-col gap-3 min-h-[192px]">
          <span class="inline-block self-start text-[12px] font-semibold px-3 py-1 font-display text-[#0b0c10] <?= $badge ?>"><?= e($ev['type']) ?> 전용</span>
          <h3 class="text-[24px] font-extrabold text-white leading-[32px] font-display"><?= e($ev['title']) ?></h3>
          <p class="text-[14px] text-[#90a1b9] flex-grow font-display tracking-[-0.42px]"><?= e($ev['desc']) ?></p>
          <?php if (!empty($ev['note'])): ?><p class="text-[12px] text-[#71717a] font-display"><?= e($ev['note']) ?></p><?php endif; ?>
          <div class="flex items-center gap-1.5 text-[12px] font-medium text-white font-display">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg><?= e($ev['date']) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="text-xs text-[#71717a] mt-8 text-right">※ 이벤트는 사정에 따라 변경될 수 있습니다.</p>
  </div>
</section>

<!-- ===== FAQ ===== -->
<?php $faq_tabs = ufs_faqs(); ?>
<section id="faq" class="py-24 bg-[#09090b] relative border-t border-white/5">
  <div class="max-w-7xl mx-auto px-6" data-faq>
    <div class="mb-12">
      <h2 class="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight">FAQ</h2>
      <p class="text-lg text-[#90a1b9]">참가 신청 및 행사 운영 관련 자주 묻는 질문을 확인해 보세요.</p>
    </div>
    <!-- 탭 -->
    <div class="flex gap-2 mb-10 flex-wrap">
      <?php foreach ($faq_tabs as $ti => $tab):
        $on = $ti === 0; ?>
        <button type="button" data-faq-tab="<?= $ti ?>" class="px-5 py-2.5 text-sm font-bold transition-all <?= $on ? 'bg-white text-black' : 'bg-white/5 text-[#a1a1aa] hover:text-white' ?>"><?= e($tab['label']) ?></button>
      <?php endforeach; ?>
    </div>
    <!-- 패널 -->
    <?php foreach ($faq_tabs as $ti => $tab): ?>
      <div data-faq-panel="<?= $ti ?>" class="space-y-4 max-w-full <?= $ti === 0 ? '' : 'hidden' ?>">
        <?php foreach ($tab['faqs'] as $qi => $item):
          $open = ($ti === 0 && $qi === 0); ?>
          <div data-acc class="bg-[#0e0f14] border overflow-hidden transition-colors <?= $open ? 'border-[rgba(0,193,213,0.3)]' : 'border-[#27272a] hover:border-white/20' ?>">
            <button type="button" data-acc-trigger class="w-full px-8 py-6 flex items-center justify-between text-left gap-6">
              <span class="text-lg md:text-xl font-bold text-white leading-snug"><?= e($item['q']) ?></span>
              <span class="flex-shrink-0 text-[#00C1D5]">
                <span data-acc-plus class="<?= $open ? 'hidden' : '' ?>"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M5 12h14"/><path d="M12 5v14"/></svg></span>
                <span data-acc-minus class="<?= $open ? '' : 'hidden' ?>"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M5 12h14"/></svg></span>
              </span>
            </button>
            <div data-acc-body class="px-8 pb-8 text-[#a1a1aa] leading-relaxed text-[16px] whitespace-pre-line <?= $open ? '' : 'hidden' ?>"><?= ufs_faq_html($item['a']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <!-- 문의 CTA -->
    <div class="mt-12 max-w-full">
      <div class="bg-[rgba(0,193,213,0.05)] p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-[#a1a1aa] font-medium">추가로 궁금한 사항이 있으신가요?</p>
        <a href="mailto:info@epiclounge.co.kr" class="bg-white text-black px-6 py-3 font-bold hover:bg-slate-200 transition-colors whitespace-nowrap clip-btn-8">이메일로 문의하기</a>
      </div>
    </div>
  </div>
</section>

<!-- ===== Sponsors (FAQ 다음 배치) ===== -->
<?php $sp = ufs_sponsors_home(); ?>
<section id="sponsors" class="py-24 bg-neutral-50 dark:bg-[#0B0C10] relative transition-colors duration-300">
  <div class="max-w-7xl mx-auto px-6">
    <div class="mb-16">
      <h2 class="text-3xl md:text-5xl font-bold text-black dark:text-white mb-4 tracking-tight">스폰서</h2>
      <p class="text-lg text-black/60 dark:text-slate-400">언리얼 페스트 서울 2026을 함께 만들어가는 파트너사를 소개합니다.</p>
    </div>
    <div class="space-y-16">
      <div>
        <h3 class="text-center text-black/60 dark:text-slate-400 font-bold tracking-[0.2em] mb-8 text-sm">Silver</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
          <?php foreach ($sp['silver'] as $s): ?>
            <div class="h-28 md:h-32 bg-white dark:bg-gradient-to-br dark:from-[#16161c] dark:to-[#0d0d11] border border-black/10 dark:border-white/10 hover:border-black/15 dark:hover:border-slate-400/50 rounded-none flex items-center justify-center transition-all group shadow-sm dark:shadow-none">
              <img src="<?= e($s['src']) ?>" alt="<?= e($s['name']) ?>" class="w-[8.8rem] h-[3.3rem] object-contain dark:invert transition-opacity">
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- <div class="mt-20 text-center">
      <a href="sponsors.php" class="inline-flex items-center px-8 py-3.5 bg-[#27272a] hover:bg-[#3f3f46] text-white font-semibold transition-all duration-200">자세히 보기</a>
    </div> -->
  </div>
</section>

<?php include __DIR__ . '/_foot.php'; ?>
