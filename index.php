<?php
// Unreal Fest Seoul 2026 — 홈 랜딩 (index.php). 그리드 아젠다·하이라이트 없는 버전 (2026-07-21 프리뷰→라이브 승격, 구버전=_golive_backup).
// 순수 PHP/HTML/CSS/JS. 데이터는 data/lib.php 접근자.
$ufs_page = 'home';
$ufs_el_gnb = true;                              // 에픽라운지 공통 GNB(다크) 노출 — 홈에서만
include_once __DIR__ . '/../common.php';        // DB (sql_query)
require_once __DIR__ . '/data/lib.php';
require_once __DIR__ . '/data/agenda_db.php';
require_once __DIR__ . '/data/agenda_grid.php';   // 그리드(타임테이블) 렌더 — 랜딩 아젠다
require_once __DIR__ . '/_pricing.php';   // 가격 단일 소스(얼리버드/정가 자동)
// [2026-08-13] '인기 트랙 마감 임박' 배지 제거. 이 배지 하나를 위해 매 홈 로드마다
// 등록 테이블(cb_unreal_2026_event2_apply) 풀스캔 COUNT(LIKE)를 티켓 수만큼 돌아 원본 부하가 컸음 → 삭제.

// [2026-08-14] 라이브 배너 노출기간(관리자 설정 Day1/Day2 2구간) — config 1회 조회.
//   표시 여부 판정은 서버가 아니라 브라우저가 하므로(아래 data-ranges + JS) 페이지를 엣지 캐시해도 전환 시각이 정확하다.
$__branges = array();
$__bq = @sql_query("SELECT cfg_key,cfg_val FROM cb_unreal_2026_config WHERE cfg_key LIKE 'live_banner_%'");
if ($__bq) {
    $__bcfg = array();
    while ($__br = $__bq->fetch_assoc()) { $__bcfg[$__br['cfg_key']] = $__br['cfg_val']; }
    foreach (array('d1','d2') as $__bd) {
        $__s = isset($__bcfg['live_banner_'.$__bd.'_start']) ? $__bcfg['live_banner_'.$__bd.'_start'] : '';
        $__e = isset($__bcfg['live_banner_'.$__bd.'_end'])   ? $__bcfg['live_banner_'.$__bd.'_end']   : '';
        if ($__s !== '' && $__e !== '') {
            $__branges[] = array(str_replace(' ', 'T', $__s) . ':00+09:00', str_replace(' ', 'T', $__e) . ':00+09:00');
        }
    }
}

require_once __DIR__ . '/_edge_cache.php'; ufs_edge_cache(3600, 60);   // 엣지 캐시(비개인화 공개 페이지) — 프리뷰/관리자/POST 자동 제외
// 등록 마감이면 등록 버튼을 '등록 마감'으로 바꾼다.
// ⚠️ 이 페이지는 1시간 엣지 캐시된다 → 관리자에서 마감 스위치를 켜면 반드시 캐시를 비워야 즉시 반영된다
//    (온라인 라이브 설정 저장 시 자동 퍼지되도록 연결해 둠).
require_once __DIR__ . '/_reg_gate.php';
$__regclosed     = ufs_reg_closed();            // 전역 마감(시각·수동)
$__regclosed_off = ufs_reg_closed_offline();    // 현장 = 전역 + 정원(1,690명) 도달
// 양일권(NORMAL_ALL)은 별도 마감 — 남은 좌석을 1일권에만 배정한다(사무국 결정)
$__closed_all = true;
include __DIR__ . '/_head.php';
?>

<!-- ===== Hero ===== -->
<style>
/* 짧은 뷰포트 높이(고배율 디스플레이 등: 예) 3840x2400 @250% → CSS 높이 ~960px)에서
   히어로 상단 패딩/로고가 커서 CTA 버튼이 화면 밖으로 밀려 안 보이던 문제 대응. */
@media (max-height: 900px) {
  #hero .hero-inner { padding-top: 7rem; padding-bottom: 12vh; }
  #hero .hero-logo  { width: 520px; }
}
@media (max-height: 760px) {
  #hero .hero-inner { padding-top: 5rem; padding-bottom: 8vh; }
  #hero .hero-logo  { width: 420px; }
}
@media (max-height: 620px) {
  #hero .hero-inner { padding-top: 4rem; padding-bottom: 6vh; }
  #hero .hero-logo  { width: 340px; }
}
/* ── 판촉 데코: Border Beam(1일권) + 버튼 hover ── */
/*    양일권은 마감이라 빔 제거(2026-08-19). spd-a 는 남겨 둠 — 되살릴 때 클래스만 붙이면 된다. */
@property --ufs-beam { syntax:'<angle>'; initial-value:0deg; inherits:false; }
.ufs-beam { position:relative; }
.ufs-beam::after{
  content:''; position:absolute; inset:0; border-radius:inherit; padding:1.5px; pointer-events:none; z-index:2;
  background: conic-gradient(from var(--ufs-beam), transparent 0deg, transparent 300deg, #00C1D5 338deg, #eafcff 350deg, #00C1D5 356deg, transparent 360deg);
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
          mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor; mask-composite: exclude;
  animation: ufs-beam-rot 4.5s linear infinite;
}
.ufs-beam.spd-a::after{ animation-duration: 6s; }     /* 양일권: 느리게 */
.ufs-beam.spd-b::after{ animation-duration: 3s; }     /* 1일권: 빠르게 */
@keyframes ufs-beam-rot { to { --ufs-beam:360deg; } }
@media (prefers-reduced-motion: reduce){ .ufs-beam::after{ animation:none; } }
.ufs-cd-num{ font-variant-numeric:tabular-nums; font-family:ui-monospace,monospace; }
.promo-badge{ width:62%; padding:7px 16px; text-align:center; color:#ffffff !important; z-index:5; }
.btn-off{ transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease, filter .2s ease; }
.btn-off:hover{ background-color:#00d9ef !important; transform:translateY(-3px); box-shadow:0 12px 34px rgba(0,193,213,.6); filter:brightness(1.06); }
.btn-on{ transition: background-color .2s ease, color .2s ease, border-color .2s ease; }
.btn-on:hover{ background-color:#ffffff !important; color:#09090b !important; border-color:#ffffff !important; }
/* 할인 혜택 보기 버튼 */
.btn-disc{ border:1px solid #00C1D5; color:#00C1D5; background:transparent; cursor:pointer; transition: background-color .2s ease, color .2s ease; }
.btn-disc:hover{ background:#00C1D5; color:#09090b; }
/* 단체 할인율 모달 */
.modal-ov{ position:fixed; inset:0; background:rgba(0,0,0,.72); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px); z-index:1000; display:none; align-items:center; justify-content:center; padding:20px; }
.modal-ov.open{ display:flex; }
.modal-box{ background:#0e0f14; border:1px solid #27272a; width:100%; max-width:760px; max-height:86vh; overflow:auto; padding:28px; }
.modal-box table{ width:100%; border-collapse:collapse; font-size:14px; margin-top:16px; }
.modal-box th,.modal-box td{ border:1px solid #27272a; padding:11px 12px; text-align:center; color:#e4e4e7; white-space:nowrap; }
.modal-box thead th{ background:#111115; color:#a1a1aa; font-weight:700; }
.modal-box .disc{ color:#00C1D5; font-weight:800; }
.modal-box .save{ color:#ff8674; }
</style>
<section id="hero" class="relative h-screen overflow-hidden">
  <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover object-bottom" style="object-position: calc(50% + 200px) bottom;">
    <source src="https://unrealsummit16.cafe24.com/2026/WEBSITE_USE_ONLY_Fest_ambient_loop_1920x1080_v05.webm" type="video/webm">
    <source src="https://unrealsummit16.cafe24.com/2026/WEBSITE_USE_ONLY_Fest_ambient_loop_1920x1080_v05.mp4" type="video/mp4">
  </video>
  <div class="absolute inset-0 bg-gradient-to-b from-black via-black/60 to-transparent"></div>
  <div class="hero-inner relative z-10 max-w-7xl mx-auto px-6 w-full flex flex-col items-start pt-52 md:pt-64 pb-[45vh]">
    <div class="mb-10">
      <img class="hero-logo" src="https://unrealsummit16.cafe24.com/2026/ufs26/hero_new_main_logo2.svg" alt="Unreal Fest Seoul 2026" style="width: 700px; max-width: 100%;">
    </div>
    <?php if (ufs_promo_hero_line() !== ''): ?>
    <!-- 연장(전체 세션 공개 기념) 프로모 문구 — 자정 전에는 노출 안 됨. 날짜/장소는 로고 이미지에 포함. -->
    <p class="text-[#00C1D5] font-bold text-base md:text-lg -mt-4 mb-6 tracking-tight"><?= e(ufs_promo_hero_line()) ?></p>
    <?php endif; ?>
    <?php /* 온라인 라이브 배너 — 노출기간(관리자 Day1/Day2 설정)을 data-ranges 로 심고 표시 여부는 브라우저가 실시간 판단.
             페이지가 엣지 캐시돼도 ON/OFF 전환은 초 단위로 정확하다. ?livebanner=1 로 강제 미리보기. */ ?>
    <a href="live.php" target="_blank" rel="noopener" id="ufsLiveBanner" data-ranges="<?= e(json_encode($__branges)) ?>" class="inline-flex items-center justify-center gap-2 mb-8 px-7 py-4 font-bold text-lg text-white" style="display:none;min-width:min(400px,86vw);background:linear-gradient(90deg,rgba(239,68,68,.95),rgba(0,193,213,.95));box-shadow:0 8px 30px rgba(239,68,68,.25)">
      시청하기
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </a>
    <style>
    /* 배너 hover — 살짝 떠오르며 밝아지고 화살표가 밀려나간다. 클릭 시 눌리는 느낌. */
    #ufsLiveBanner{position:relative;overflow:hidden;transition:transform .18s cubic-bezier(.2,.8,.3,1),box-shadow .18s ease,filter .18s ease}
    #ufsLiveBanner svg{transition:transform .18s cubic-bezier(.2,.8,.3,1)}
    #ufsLiveBanner::after{content:'';position:absolute;top:0;left:-60%;width:40%;height:100%;pointer-events:none;
      background:linear-gradient(100deg,transparent,rgba(255,255,255,.32),transparent);transform:skewX(-18deg);transition:left .45s ease}
    #ufsLiveBanner:hover{transform:translateY(-3px);filter:brightness(1.08);box-shadow:0 16px 42px rgba(239,68,68,.45)}
    #ufsLiveBanner:hover svg{transform:translateX(6px)}
    #ufsLiveBanner:hover::after{left:120%}
    #ufsLiveBanner:active{transform:translateY(-1px);box-shadow:0 8px 24px rgba(239,68,68,.4)}
    #ufsLiveBanner:focus-visible{outline:3px solid #fff;outline-offset:3px}
    @media (prefers-reduced-motion:reduce){
      #ufsLiveBanner,#ufsLiveBanner svg,#ufsLiveBanner::after{transition:none}
      #ufsLiveBanner:hover{transform:none}
      #ufsLiveBanner:hover svg{transform:none}
    }
    </style>
    <script>
    /* 라이브 배너 ON/OFF — 브라우저 현재 시각으로 판단(페이지 캐시와 무관하게 정확).
       구간은 관리자 설정(Day1/Day2)에서 온 data-ranges. 30초마다 재판단해 열어둔 탭도 자동 전환. */
    (function(){
      var b=document.getElementById('ufsLiveBanner'); if(!b) return;
      var rs=[]; try{ rs=JSON.parse(b.getAttribute('data-ranges')||'[]')||[]; }catch(e){ rs=[]; }
      var force=(location.search.indexOf('livebanner')>=0);
      function upd(){
        var on=force, now=Date.now();
        for(var i=0;i<rs.length && !on;i++){
          var s=Date.parse(rs[i][0]), t=Date.parse(rs[i][1]);
          if(!isNaN(s)&&!isNaN(t)&&now>=s&&now<=t) on=true;
        }
        b.style.display = on ? '' : 'none';
      }
      upd(); setInterval(upd, 30000);
    })();
    </script>
    <div class="flex flex-col sm:flex-row items-start gap-4 mb-10">
      <button type="button" data-scroll="register" class="<?= $__regclosed ? 'bg-[#27272a] text-[#71717a] cursor-not-allowed' : 'bg-[#00C1D5] hover:bg-[#004F59] text-white hover:shadow-lg' ?> px-8 py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all shadow-sm clip-btn">
        <?= $__regclosed ? '등록 마감' : '지금 등록하기' ?>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </button>
      <button type="button" data-scroll="agenda" class="bg-white/10 backdrop-blur-md border border-white/20 hover:bg-white/20 text-white px-8 py-4 font-bold text-lg flex items-center justify-center transition-all">아젠다 보기</button>
    </div>
  </div>
  <?php if (ufs_is_earlybird()): /* 얼리버드 종료 카운터: 얼리버드 기간에만 표시(종료 후 자동 숨김) */ ?>
  <!-- 카운트다운: 도킹 헤더에 안 가리도록 위로(bottom-20), 본문(max-w-7xl) 우측에 정렬 -->
  <div class="absolute inset-x-0 bottom-20 z-10 pointer-events-none">
    <div class="max-w-7xl mx-auto px-6 flex justify-end">
      <div class="relative bg-[#050508] px-8 py-4 pointer-events-auto" data-countdown data-deadline="<?= e(ufs_earlybird_deadline()) ?>">
        <div class="absolute -top-[30px] left-0 bg-[#00C1D5] px-5 py-1 whitespace-nowrap">
          <span class="text-[#090a0f] text-[14px] font-bold tracking-tight"><?= e(ufs_promo_countdown_label()) ?></span>
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
  <?php endif; ?>
</section>

<!-- ===== Overview ===== -->
<?php $ov = ufs_overview();
$ov_icons = array(
  'layout-grid' => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
  'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
  'zap' => '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
  'video' => '<path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.87a.5.5 0 0 0-.752-.432L16 10.5"/><rect x="2" y="6" width="14" height="12" rx="2"/>',
); ?>
<section id="overview" class="py-24 relative">
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

  <!-- 타임테이블 그리드 (schedule.php 그리드뷰 형식) -->
  <style>
    @media (max-width:639px){
      .ufs-gtime{padding:.4rem .2rem!important;font-size:.7rem!important}
      th.ufs-gtime{width:44px!important}
      .ufs-tt{display:block}
    }
  </style>
  <?php
  // 공통슬롯은 일정표에서 숨김(환영사·경품추첨만 노출) — schedule.php와 동일
  $ufs_grid_keep = function ($s) {
      return empty($s['_slot_type']) || !ufs_slot_is_common($s['_slot_type'])
          || $s['_slot_type'] === 'welcome' || $s['_slot_type'] === 'raffle';
  };
  $grid_days = array(
    1 => array('title' => 'Day 1. 8월 20일(목)', 'sessions' => array_values(array_filter(ufs_db_day_all(1), $ufs_grid_keep))),
    2 => array('title' => 'Day 2. 8월 21일(금)', 'sessions' => array_values(array_filter(ufs_db_day_all(2), $ufs_grid_keep))),
  );
  ?>
  <!-- Day 탭 -->
  <div class="max-w-7xl mx-auto px-6 pt-8 mb-8 flex gap-2" data-agtab>
    <?php foreach ($grid_days as $gd => $gblock): $on = ($gd === 1); ?>
      <button type="button" data-agtab-btn="<?= $gd ?>" class="flex-1 text-center px-5 py-3 text-sm font-bold transition-colors <?= $on ? 'bg-[#00C1D5] text-black' : 'bg-white/5 text-[#a1a1aa] hover:text-white' ?>"><?= e($gblock['title']) ?></button>
    <?php endforeach; ?>
  </div>
  <?php foreach ($grid_days as $gd => $gblock): ?>
    <div data-agtab-panel="<?= $gd ?>" class="max-w-7xl mx-auto px-6 mb-12<?= $gd !== 1 ? ' hidden' : '' ?>">
      <?php ufs_render_grid_view($gblock['sessions'], $gd); ?>
    </div>
  <?php endforeach; ?>
  <script>
  (function(){
    var wrap = document.querySelector('[data-agtab]'); if(!wrap) return;
    var btns = wrap.querySelectorAll('[data-agtab-btn]');
    function show(d){
      var ps = document.querySelectorAll('[data-agtab-panel]');
      for (var i=0;i<ps.length;i++){ ps[i].classList.toggle('hidden', ps[i].getAttribute('data-agtab-panel')!==d); }
      for (var j=0;j<btns.length;j++){
        var on = btns[j].getAttribute('data-agtab-btn')===d;
        btns[j].classList.toggle('bg-[#00C1D5]', on); btns[j].classList.toggle('text-black', on);
        btns[j].classList.toggle('bg-white/5', !on); btns[j].classList.toggle('text-[#a1a1aa]', !on); btns[j].classList.toggle('hover:text-white', !on);
      }
    }
    for (var k=0;k<btns.length;k++){ btns[k].addEventListener('click', (function(b){ return function(){ show(b.getAttribute('data-agtab-btn')); }; })(btns[k])); }
  })();
  </script>

  <div class="max-w-7xl mx-auto px-6">
    <p class="text-sm text-[#71717a] mt-8 text-right tracking-tight">· 세션 내용, 발표자 및 일정은 사정에 따라 변경될 수 있습니다.</p>
    <p class="text-sm text-[#71717a] mt-1 text-right tracking-tight">· 온라인 중계 대상 세션은 운영 상황에 따라 변경될 수 있습니다.</p>
  </div>

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
        <?php if (ufs_is_earlybird()): ?><div class="absolute -top-[13px] left-0 bg-[#00C1D5] text-[#090a0f] font-bold whitespace-nowrap <?= ufs_promo_is_ext() ? 'text-[12px] px-[14px] py-[7px]' : 'text-[14px] px-[18px] py-[7px]' ?>"><?= e(ufs_promo_card_badge()) ?></div><?php else: ?><div class="promo-badge absolute -top-[13px] left-0 bg-[#00C1D5] text-[#090a0f] font-bold text-[13px]">한정 수량 · 조기 마감</div><?php endif; ?>
        <h3 class="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px] font-jamjil font-medium">오프라인 양일권</h3>
        <?php if (ufs_is_earlybird()): ?><div class="mb-1"><span class="text-[18px] text-[#71717a] line-through tracking-tight">₩ <?= number_format(ufs_ticket_orig('NORMAL_ALL')) ?></span></div><?php endif; ?>
        <div class="mb-2"><span class="text-[40px] font-bold text-white tracking-tight">₩ <?= number_format(ufs_ticket_price('NORMAL_ALL')) ?></span></div>
        <?php if (ufs_is_earlybird()): ?><p class="text-[13px] text-[#9adbe8] mb-auto"><?= e(ufs_promo_card_note()) ?></p><?php else: ?><div class="mb-auto"></div><?php endif; ?>
        <?php if ($__closed_all || $__regclosed_off): ?><span class="btn-off w-full mt-[16px] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 font-jamjil" style="background:#27272a;color:#71717a;cursor:not-allowed">등록 마감</span><?php else: ?><a href="ticket-all.php" class="btn-off mt-[16px] w-full bg-[#00C1D5] text-[#09090b] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 font-jamjil">
          양일권 등록하기
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a><?php endif; ?>
      </div>
      <!-- 1일권 (featured) -->
      <div class="ufs-beam spd-b relative bg-[#0e0f14] border border-[rgba(0,193,213,0.5)] p-9 flex flex-col items-center text-center shadow-[0_0_11px_rgba(0,193,213,0.1)]">
        <?php if (ufs_is_earlybird()): ?><div class="absolute -top-[13px] left-0 bg-[#00C1D5] text-[#090a0f] font-bold whitespace-nowrap <?= ufs_promo_is_ext() ? 'text-[12px] px-[14px] py-[7px]' : 'text-[14px] px-[18px] py-[7px]' ?>"><?= e(ufs_promo_card_badge()) ?></div><?php else: ?><div class="promo-badge absolute -top-[13px] left-0 bg-[#00C1D5] text-[#090a0f] font-bold text-[13px]">한정 수량 · 조기 마감</div><?php endif; ?>
        <h3 class="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px] font-jamjil font-medium">오프라인 1일권</h3>
        <?php if (ufs_is_earlybird()): ?><div class="mb-1"><span class="text-[18px] text-[#71717a] line-through tracking-tight">₩ <?= number_format(ufs_ticket_orig('NORMAL_20')) ?></span></div><?php endif; ?>
        <div class="mb-2"><span class="text-[40px] font-bold text-white tracking-tight">₩ <?= number_format(ufs_ticket_price('NORMAL_20')) ?></span></div>
        <?php if (ufs_is_earlybird()): ?><p class="text-[13px] text-[#9adbe8] mb-auto"><?= e(ufs_promo_card_note()) ?></p><?php else: ?><div class="mb-auto"></div><?php endif; ?>
        <?php if ($__regclosed_off): ?><span class="btn-off w-full mt-[16px] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 font-jamjil" style="background:#27272a;color:#71717a;cursor:not-allowed">등록 마감</span><?php else: ?><a href="ticket-day.php" class="btn-off mt-[16px] w-full bg-[#00C1D5] text-[#09090b] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 font-jamjil">
          1일권 등록하기
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a><?php endif; ?>
      </div>
      <!-- 온라인 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-9 flex flex-col items-center text-center">
        <h3 class="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px] font-jamjil font-medium">온라인</h3>
        <div class="mb-2"><span class="text-[26px] font-bold text-[#a1a1aa]">무료</span></div>
        <p class="text-[15px] text-[#71717a] mb-auto">(일부 세션 생중계)</p>
        <?php if ($__regclosed): ?><span class="btn-on w-full mt-[35px] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 font-jamjil" style="background:#27272a;color:#71717a;cursor:not-allowed">등록 마감</span><?php else: ?><a href="ticket-online.php" class="btn-on mt-[35px] w-full border border-[#27272a] text-[#a1a1aa] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 font-jamjil">
          무료 등록하기
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a><?php endif; ?>
      </div>
    </div>
    <p class="text-sm text-[#00C1D5] font-bold mt-8 text-right tracking-tight">· 오프라인 티켓은 한정 수량으로 조기 마감될 수 있습니다.</p>
    <div class="mt-12 border border-[#27272a] p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h3 class="text-xl font-bold text-[#fafafa] mb-2">단체 등록 및 기업 결제</h3>
        <p class="text-sm text-[#a1a1aa]">5인 이상 단체 등록 시 세금계산서 발행 및 무통장 입금을 지원합니다. 관련 문의는 운영 사무국으로 연락해 주세요.</p>
      </div>
      <div class="flex-shrink-0 flex flex-col md:items-end gap-2">
        <div class="flex flex-wrap gap-3">
          <button type="button" onclick="document.getElementById('groupDiscModal').classList.add('open')" class="btn-disc inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold whitespace-nowrap">할인 혜택 보기</button>
          <button type="button" onclick="ufsGroupClosed('gcNotice1')" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white text-black text-sm font-bold hover:bg-white/90 transition-colors whitespace-nowrap clip-btn-8">
            문의하기
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </button>
        </div>
        <p id="gcNotice1" style="display:none;margin:0;font-size:13px;font-weight:700;color:#ff6b6b">단체 등록은 종료되었습니다.</p>
      </div>
    </div>
    <!-- 단체 규모별 할인율 모달 -->
    <div class="modal-ov" id="groupDiscModal" onclick="if(event.target===this)this.classList.remove('open')">
      <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
          <div>
            <h3 style="font-size:20px;font-weight:800;color:#fff;margin:0">규모별 할인율</h3>
            <p style="color:#a1a1aa;font-size:13px;margin:6px 0 0">5인 이상 단체 등록 시 인원에 따라 <b style="color:#00C1D5">최대 50%</b> 할인이 적용됩니다. (정상가 기준)</p>
          </div>
          <button type="button" onclick="document.getElementById('groupDiscModal').classList.remove('open')" aria-label="닫기" style="color:#a1a1aa;font-size:26px;line-height:1;background:none;border:0;cursor:pointer;flex-shrink:0">&times;</button>
        </div>
        <div style="overflow-x:auto">
          <table>
            <thead><tr><th>단체 인원</th><th>할인율</th><th>양일권 (1인당)</th><th>양일권 할인 (1인당)</th><th>1일권 (1인당)</th><th>1일권 할인 (1인당)</th></tr></thead>
            <tbody>
              <tr><td>5 ~ 9인</td><td class="disc">10%</td><td>₩108,000</td><td class="save">-₩12,000</td><td>₩54,000</td><td class="save">-₩6,000</td></tr>
              <tr><td>10 ~ 19인</td><td class="disc">20%</td><td>₩96,000</td><td class="save">-₩24,000</td><td>₩48,000</td><td class="save">-₩12,000</td></tr>
              <tr><td>20 ~ 29인</td><td class="disc">30%</td><td>₩84,000</td><td class="save">-₩36,000</td><td>₩42,000</td><td class="save">-₩18,000</td></tr>
              <tr><td>30 ~ 39인</td><td class="disc">40%</td><td>₩72,000</td><td class="save">-₩48,000</td><td>₩36,000</td><td class="save">-₩24,000</td></tr>
              <tr><td>40인 이상</td><td class="disc">50%</td><td>₩60,000</td><td class="save">-₩60,000</td><td>₩30,000</td><td class="save">-₩30,000</td></tr>
            </tbody>
          </table>
        </div>
        <p style="color:#71717a;font-size:12px;margin-top:14px">· 정상가: 양일권 ₩120,000 / 1일권 ₩60,000 기준. 단체 등록·기업 결제 문의는 운영 사무국으로 연락해 주세요.</p>
        <div style="margin-top:18px;padding-top:16px;border-top:1px solid #27272a">
          <h4 style="font-size:14px;font-weight:700;color:#fafafa;margin:0 0 8px">안내사항</h4>
          <ul style="list-style:none;margin:0;padding:0;color:#a1a1aa;font-size:13px;line-height:1.7">
            <li style="display:flex;gap:8px"><span style="color:#00C1D5;flex-shrink:0">·</span><span>단체 할인은 5인 이상 등록 시 적용됩니다.</span></li>
            <li style="display:flex;gap:8px"><span style="color:#00C1D5;flex-shrink:0">·</span><span>할인율은 최종 등록 인원을 기준으로 적용됩니다.</span></li>
            <li style="display:flex;gap:8px"><span style="color:#00C1D5;flex-shrink:0">·</span><span>단체 등록은 부분 취소가 불가능하며, 취소 시 전체 등록이 취소됩니다.</span></li>
          </ul>
        </div>
        <div style="margin-top:20px;display:flex;flex-direction:column;align-items:center;gap:10px">
          <button type="button" onclick="ufsGroupClosed('gcNotice2')" class="inline-flex items-center gap-2 px-8 py-3 bg-white text-black text-sm font-bold hover:bg-white/90 transition-colors clip-btn-8">
            문의하기
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </button>
          <p id="gcNotice2" style="display:none;margin:0;font-size:13px;font-weight:700;color:#ff6b6b">단체 등록은 종료되었습니다.</p>
        </div>
      </div>
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
          <p class="text-[14px] text-[#90a1b9] font-display tracking-[-0.42px]"><?= e($ev['desc']) ?></p>
          <?php if (!empty($ev['img'])): ?><img src="<?= asset_v($ev['img']) ?>" alt="<?= e($ev['title']) ?> 티셔츠" class="w-full h-auto rounded-[4px]" loading="lazy"><?php endif; ?>
          <?php if (!empty($ev['note'])): ?><p class="text-[12px] text-[#71717a] font-display"><?= e($ev['note']) ?></p><?php endif; ?>
          <div class="flex-grow"></div>
          <div class="flex items-center gap-1.5 text-[12px] font-medium text-white font-display">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg><?= e($ev['date']) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="text-xs text-[#71717a] mt-8 text-right">· 이벤트와 경품은 사정에 따라 변경될 수 있습니다.</p>
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
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6">
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

<!-- GA4: 메인 페이지 30초 이상 체류(포그라운드 누적, 1회) -->
<script type="text/javascript">
(function(){
  var fired=false, acc=0, last=Date.now();
  function tick(){
    if(document.visibilityState!=='hidden'){ acc += Date.now()-last; }
    last=Date.now();
    if(!fired && acc>=30000){ fired=true; if(window.gtag){ gtag('event','메인_페이지_30초_이상_체류한_대상'); } }
  }
  document.addEventListener('visibilitychange', function(){ tick(); last=Date.now(); });
  setInterval(tick, 2000);
})();
</script>
<!-- 단체 등록 마감 안내: 버튼을 눌렀을 때만 문구를 드러낸다(미리 노출하지 않음) -->
<script>
function ufsGroupClosed(id){
  var el = document.getElementById(id);
  if (!el) return;
  el.style.display = 'block';
  el.setAttribute('role','status');
}
</script>

<!-- 문의 메일 버튼: 기본 메일앱이 없어도 이메일 주소 복사 + 안내(mailto 병행) -->
<script>
(function(){
  var toast;
  function showToast(msg){
    if(!toast){ toast=document.createElement('div');
      toast.style.cssText='position:fixed;left:50%;bottom:32px;transform:translateX(-50%);background:#00C1D5;color:#09090b;font-weight:700;font-size:14px;line-height:1.4;padding:12px 20px;border-radius:6px;z-index:99999;box-shadow:0 6px 24px rgba(0,0,0,.35);opacity:0;transition:opacity .2s;max-width:90vw;text-align:center';
      document.body.appendChild(toast); }
    toast.textContent=msg; toast.style.opacity='1';
    clearTimeout(toast._t); toast._t=setTimeout(function(){ toast.style.opacity='0'; },2800);
  }
  document.querySelectorAll('a[href^="mailto:"]').forEach(function(a){
    a.addEventListener('click', function(){
      var mail=(a.getAttribute('href')||'').replace(/^mailto:/i,'').split('?')[0];
      if(!mail) return;
      try{ if(navigator.clipboard && navigator.clipboard.writeText){ navigator.clipboard.writeText(mail); } }catch(e){}
      showToast('문의 이메일 주소가 복사되었습니다 · '+mail);
      // mailto는 기본 동작으로 시도됨(메일앱 있으면 작성창 열림)
    });
  });
})();
</script>
<!-- 판촉: 행사 실시간 카운트다운 구동 (카드별 [data-ev-cd]) -->
<script>
(function(){
  var els=document.querySelectorAll('[data-ev-cd]'); if(!els.length) return;
  function pad(n){return (n<10?'0':'')+n;}
  function tick(){
    var now=Date.now();
    els.forEach(function(el){
      var dl=new Date(el.getAttribute('data-deadline')).getTime();
      var diff=dl-now; if(diff<0) diff=0; var t=Math.floor(diff/1000);
      var d=el.querySelector('[data-cd-days]'),h=el.querySelector('[data-cd-hours]'),m=el.querySelector('[data-cd-mins]'),s=el.querySelector('[data-cd-secs]');
      if(d)d.textContent=Math.floor(t/86400); if(h)h.textContent=pad(Math.floor(t%86400/3600));
      if(m)m.textContent=pad(Math.floor(t%3600/60)); if(s)s.textContent=pad(t%60);
    });
  }
  tick(); setInterval(tick,1000);
})();
</script>
<?php include __DIR__ . '/_foot.php'; ?>
