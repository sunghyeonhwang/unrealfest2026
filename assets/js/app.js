/* Unreal Fest Seoul 2026 — 공개 사이트 인터랙션 (Vanilla JS, React 동작 1:1 포팅)
 * 요소가 없으면 각 init는 자동 no-op. 다크 고정.
 */
(function () {
  'use strict';
  document.documentElement.classList.add('dark');

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $all = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  /* 1) 헤더: 홈(data-floatnav)은 하단에서 시작→Hero 지나면 상단으로 슬라이드(2025식). 그 외는 스크롤 축소. */
  function initHeader() {
    var h = document.getElementById('site-header');
    if (!h) return;
    var TOP = ['bg-[#09090b]/70', 'backdrop-blur-sm', 'border-transparent', 'py-6'];
    var SCROLLED = ['bg-[#09090b]/90', 'backdrop-blur-md', 'border-white/10', 'py-4', 'shadow-sm'];
    function addAll(a) { a.forEach(function (c) { h.classList.add(c); }); }
    function rmAll(a) { a.forEach(function (c) { h.classList.remove(c); }); }

    if (h.hasAttribute('data-floatnav')) {
      var hero = document.getElementById('hero');
      var BOTTOM = ['bg-[#09090b]/70', 'backdrop-blur-sm', 'border-transparent', 'py-3']; // 하단 도킹: 슬림 바
      var TOPD = ['bg-[#09090b]/90', 'backdrop-blur-md', 'border-white/10', 'py-4', 'shadow-sm']; // 상단 도킹
      var logo = h.querySelector('[data-header-logo]'); // 하단 도킹 시 숨김, 상단 도킹 시 표시
      rmAll(TOP); // 마크업 기본(py-6 등) 잔재 제거
      var docked = false; // false=하단, true=상단
      function bottomTop() { return Math.max(0, window.innerHeight - h.offsetHeight); }
      function threshold() { return (hero ? hero.offsetHeight : window.innerHeight) - 64; }
      function showLogo(on) { if (!logo) return; logo.classList.toggle('opacity-100', on); logo.classList.toggle('opacity-0', !on); }
      function dock(toTop) {
        if (toTop === docked) return;
        if (toTop) { rmAll(BOTTOM); addAll(TOPD); h.style.top = '0px'; showLogo(true); h.classList.remove('hdr-bottom'); }
        else { rmAll(TOPD); addAll(BOTTOM); h.style.top = bottomTop() + 'px'; showLogo(false); h.classList.add('hdr-bottom'); }
        docked = toTop;
      }
      // 초기 배치: 하단 (전환 애니메이션 없이)
      h.style.transition = 'none';
      addAll(BOTTOM); h.style.top = bottomTop() + 'px'; h.classList.add('hdr-bottom'); // 하단 도킹: 모바일 메뉴는 위로 열림
      void h.offsetHeight;          // reflow
      h.style.transition = '';      // 클래스 기반 transition(duration-700) 복원
      function upd() { dock(window.scrollY >= threshold()); }
      upd();
      window.addEventListener('scroll', upd, { passive: true });
      window.addEventListener('resize', function () { if (!docked) h.style.top = bottomTop() + 'px'; });
    } else {
      function upd() {
        if (window.scrollY > 50) { rmAll(TOP); addAll(SCROLLED); }
        else { rmAll(SCROLLED); addAll(TOP); }
      }
      upd();
      window.addEventListener('scroll', upd, { passive: true });
    }
  }

  /* 2) 모바일 메뉴 + 아이콘 스왑 */
  function closeMobile() {
    var mn = document.getElementById('mobile-nav'), mt = document.getElementById('mobile-menu-toggle');
    if (mn) mn.classList.add('hidden');
    if (mt) {
      mt.setAttribute('aria-expanded', 'false');
      var ci = mt.querySelector('[data-menu-icon-closed]'), oi = mt.querySelector('[data-menu-icon-open]');
      if (ci) ci.classList.remove('hidden'); if (oi) oi.classList.add('hidden');
    }
  }
  function initMobileMenu() {
    var mt = document.getElementById('mobile-menu-toggle'), mn = document.getElementById('mobile-nav');
    if (!mt || !mn) return;
    mt.addEventListener('click', function () {
      var open = mn.classList.toggle('hidden') === false;
      mt.setAttribute('aria-expanded', open ? 'true' : 'false');
      var ci = mt.querySelector('[data-menu-icon-closed]'), oi = mt.querySelector('[data-menu-icon-open]');
      if (ci) ci.classList.toggle('hidden', open);
      if (oi) oi.classList.toggle('hidden', !open);
    });
  }

  /* 3) 스무스 스크롤 ([data-scroll], 홈 전용) */
  function initSmoothScroll() {
    document.addEventListener('click', function (e) {
      var b = e.target.closest ? e.target.closest('[data-scroll]') : null;
      if (!b) return;
      var el = document.getElementById(b.getAttribute('data-scroll'));
      if (el) el.scrollIntoView({ behavior: 'smooth' });
      if (b.hasAttribute('data-close-mobile')) closeMobile();
    });
  }

  /* 4) 카운트다운 */
  function initCountdown() {
    var cd = $('[data-countdown]');
    if (!cd) return;
    var dl = new Date(cd.getAttribute('data-deadline')).getTime();
    var dEl = cd.querySelector('[data-cd-days]'), hEl = cd.querySelector('[data-cd-hours]'),
        mEl = cd.querySelector('[data-cd-mins]'), sEl = cd.querySelector('[data-cd-secs]');
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function tick() {
      var diff = dl - Date.now(); if (diff < 0) diff = 0;
      var d = Math.floor(diff / 86400000), h = Math.floor((diff % 86400000) / 3600000),
          m = Math.floor((diff % 3600000) / 60000), s = Math.floor((diff % 60000) / 1000);
      if (dEl) dEl.textContent = pad(d); if (hEl) hEl.textContent = pad(h);
      if (mEl) mEl.textContent = pad(m); if (sEl) sEl.textContent = pad(s);
    }
    tick(); setInterval(tick, 1000);
  }

  /* 5) 아젠다 캐러셀 (드래그 + 화살표) */
  function initCarousels() {
    $all('[data-carousel]').forEach(function (wrap) {
      var track = wrap.querySelector('[data-carousel-track]');
      if (!track) return;
      var prev = wrap.querySelector('[data-carousel-prev]'), next = wrap.querySelector('[data-carousel-next]');
      var STEP = 340;
      if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -STEP, behavior: 'smooth' }); });
      if (next) next.addEventListener('click', function () { track.scrollBy({ left: STEP, behavior: 'smooth' }); });
      var down = false, startX = 0, startScroll = 0, moved = false;
      track.addEventListener('pointerdown', function (e) { down = true; moved = false; startX = e.clientX; startScroll = track.scrollLeft; track.classList.add('cursor-grabbing'); });
      window.addEventListener('pointermove', function (e) { if (!down) return; var dx = e.clientX - startX; if (Math.abs(dx) > 5) moved = true; track.scrollLeft = startScroll - dx; });
      window.addEventListener('pointerup', function () { down = false; track.classList.remove('cursor-grabbing'); });
      track.addEventListener('click', function (e) { if (moved) { e.preventDefault(); e.stopPropagation(); } }, true);
    });
  }

  /* 6) FAQ 탭 + 아코디언 */
  function initFaq() {
    var faq = $('[data-faq]');
    if (!faq) return;
    var tabs = $all('[data-faq-tab]', faq), panels = $all('[data-faq-panel]', faq);
    function showTab(idx) {
      panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-faq-panel') !== idx); });
      tabs.forEach(function (t) {
        var on = t.getAttribute('data-faq-tab') === idx;
        t.classList.toggle('bg-white', on); t.classList.toggle('text-black', on);
        t.classList.toggle('bg-white/5', !on); t.classList.toggle('text-[#a1a1aa]', !on); t.classList.toggle('hover:text-white', !on);
      });
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { showTab(t.getAttribute('data-faq-tab')); }); });

    function setAcc(item, open) {
      var body = item.querySelector('[data-acc-body]'), plus = item.querySelector('[data-acc-plus]'), minus = item.querySelector('[data-acc-minus]');
      if (body) body.classList.toggle('hidden', !open);
      if (plus) plus.classList.toggle('hidden', open);
      if (minus) minus.classList.toggle('hidden', !open);
      item.classList.toggle('border-[rgba(0,193,213,0.3)]', open);
      item.classList.toggle('border-[#27272a]', !open);
      item.classList.toggle('hover:border-white/20', !open);
    }
    $all('[data-acc-trigger]', faq).forEach(function (trg) {
      trg.addEventListener('click', function () {
        var item = trg.closest('[data-acc]'); if (!item) return;
        var body = item.querySelector('[data-acc-body]'); if (!body) return;
        var isOpen = !body.classList.contains('hidden');
        // 독립 토글: 다른 항목은 그대로 두고 클릭한 항목만 열고/닫음 (여러 개 동시 열림 허용)
        setAcc(item, !isOpen);
      });
    });
  }

  /* 7) 사이드바 접기/펼치기 (sessions) */
  function initSidebarSections() {
    $all('[data-section-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var wrap = btn.parentNode;
        var body = wrap.querySelector('[data-section-body]'); if (!body) return;
        var chev = btn.querySelector('[data-section-chevron]');
        var willOpen = body.classList.contains('hidden');
        if (willOpen) { body.classList.remove('hidden'); body.classList.add('flex'); }
        else { body.classList.add('hidden'); }
        if (chev) { chev.classList.toggle('-rotate-90', !willOpen); chev.classList.toggle('rotate-0', willOpen); }
      });
    });
  }

  /* 8) 세션 목록 필터 (sessions) */
  function initSessionFilter() {
    var top = $('[data-sessions]');
    var grid = $('[data-session-grid]');
    if (!top || !grid) return;
    var cards = $all('[data-session-card]', grid);
    var countEl = $('[data-session-count]'), empty = $('[data-empty]');
    var si = $('[data-search-input]');
    var state = { day: 'all', track: 'all', level: 'all', cats: {}, q: '' };

    function catCount() { var n = 0; for (var k in state.cats) { if (state.cats[k]) n++; } return n; }
    function apply() {
      var vis = 0;
      cards.forEach(function (c) {
        var okDay = state.day === 'all' || c.getAttribute('data-day') === state.day;
        var okTrack = state.track === 'all' || c.getAttribute('data-track') === state.track;
        var okLevel = state.level === 'all' || c.getAttribute('data-level') === state.level;
        var okCats = true;
        if (catCount() > 0) {
          var cc = (c.getAttribute('data-cats') || '').split(' ');
          for (var k in state.cats) { if (state.cats[k] && cc.indexOf(k) === -1) { okCats = false; break; } }
        }
        var okQ = state.q === '' || (c.getAttribute('data-search') || '').indexOf(state.q) !== -1;
        var show = okDay && okTrack && okLevel && okCats && okQ;
        c.classList.toggle('hidden', !show); if (show) vis++;
      });
      if (countEl) countEl.textContent = vis;
      if (empty) empty.classList.toggle('hidden', vis !== 0);
      grid.classList.toggle('hidden', vis === 0);
    }
    function toggleFilterBtn(b, on) {
      b.classList.toggle('bg-[rgba(0,79,89,0.5)]', on); b.classList.toggle('text-[#9adbe8]', on); b.classList.toggle('font-semibold', on);
      b.classList.toggle('bg-transparent', !on); b.classList.toggle('text-[#a1a1aa]', !on); b.classList.toggle('hover:text-white', !on);
    }
    function setSingle(sel, active) { $all(sel).forEach(function (b) { toggleFilterBtn(b, b === active); }); }
    function setDay(active) {
      $all('[data-day-filter]').forEach(function (b) {
        var on = b === active;
        b.classList.toggle('bg-[#00C1D5]', on); b.classList.toggle('text-black', on);
        b.classList.toggle('bg-transparent', !on); b.classList.toggle('text-[#71717a]', !on); b.classList.toggle('hover:text-white', !on);
      });
    }
    function setCats() {
      $all('[data-cat-filter]').forEach(function (b) {
        var cat = b.getAttribute('data-cat-filter');
        var on = cat === '전체' ? catCount() === 0 : !!state.cats[cat];
        toggleFilterBtn(b, on);
      });
    }

    $all('[data-day-filter]').forEach(function (b) { b.addEventListener('click', function () { state.day = b.getAttribute('data-day-filter'); setDay(b); apply(); }); });
    $all('[data-track-filter]').forEach(function (b) { b.addEventListener('click', function () { state.track = b.getAttribute('data-track-filter'); setSingle('[data-track-filter]', b); apply(); }); });
    $all('[data-level-filter]').forEach(function (b) { b.addEventListener('click', function () { state.level = b.getAttribute('data-level-filter'); setSingle('[data-level-filter]', b); apply(); }); });
    $all('[data-cat-filter]').forEach(function (b) {
      b.addEventListener('click', function () {
        var cat = b.getAttribute('data-cat-filter');
        if (cat === '전체') { state.cats = {}; }
        else { state.cats[cat] = !state.cats[cat]; }
        setCats(); apply();
      });
    });
    if (si) si.addEventListener('input', function () { state.q = si.value.trim().toLowerCase(); apply(); });
    $all('[data-filter-reset]').forEach(function (b) {
      b.addEventListener('click', function () {
        state = { day: 'all', track: 'all', level: 'all', cats: {}, q: '' };
        if (si) si.value = '';
        setDay($('[data-day-filter="all"]'));
        setSingle('[data-track-filter]', $('[data-track-filter="all"]'));
        setSingle('[data-level-filter]', $('[data-level-filter="all"]'));
        setCats(); apply();
      });
    });
    apply();
  }

  /* 9) 스케줄 (schedule) */
  function initSchedule() {
    var sc = $('[data-schedule]');
    if (!sc) return;
    var day = 'day1', view = 'track', fTrack = 'all', fLevel = 'all', fTopics = {}, fProducts = {};
    var fbtn = sc.querySelector('[data-filter-btn]'), fpanel = sc.querySelector('[data-filter-panel]'), fdot = sc.querySelector('[data-filter-dot]');

    function pickedKeys(obj) { var a = []; for (var k in obj) { if (obj[k]) a.push(k); } return a; }
    function topicCount() { return pickedKeys(fTopics).length; }
    function productCount() { return pickedKeys(fProducts).length; }
    function anyMatch(attrVal, picks) { if (picks.length === 0) return true; var arr = (attrVal || '').split('|'); for (var i = 0; i < picks.length; i++) { if (arr.indexOf(picks[i]) >= 0) return true; } return false; }
    function hasFilter() { return fTrack !== 'all' || fLevel !== 'all' || topicCount() > 0 || productCount() > 0; }

    function showView(v) {
      view = v;
      var active = sc.querySelector('[data-day-content="' + day + '"]');
      if (active) $all('[data-view-content]', active).forEach(function (c) { c.classList.toggle('hidden', c.getAttribute('data-view-content') !== v); });
      $all('[data-sched-view]', sc).forEach(function (b) {
        var on = b.getAttribute('data-sched-view') === v;
        b.classList.toggle('bg-white', on); b.classList.toggle('text-black', on);
        b.classList.toggle('text-[#71717a]', !on); b.classList.toggle('hover:text-white', !on);
      });
    }
    function showDay(d) {
      day = d;
      $all('[data-day-content]', sc).forEach(function (c) { c.classList.toggle('hidden', c.getAttribute('data-day-content') !== d); });
      $all('[data-sched-day]', sc).forEach(function (b) {
        var on = b.getAttribute('data-sched-day') === d;
        b.classList.toggle('bg-[#00C1D5]', on); b.classList.toggle('text-black', on);
        b.classList.toggle('text-[#71717a]', !on); b.classList.toggle('hover:text-white', !on);
      });
      showView(view); applyFilter();
    }
    function applyFilter() {
      if (fdot) fdot.classList.toggle('hidden', !hasFilter());
      if (fbtn) {
        var hf = hasFilter();
        fbtn.classList.toggle('border-[#00C1D5]', hf); fbtn.classList.toggle('text-[#00C1D5]', hf);
        fbtn.classList.toggle('border-[#27272a]', !hf); fbtn.classList.toggle('text-[#a1a1aa]', !hf);
        fbtn.classList.toggle('hover:text-white', !hf); fbtn.classList.toggle('hover:border-white/20', !hf);
      }
      var active = sc.querySelector('[data-day-content="' + day + '"]'); if (!active) return;
      var tv = active.querySelector('[data-view-content="track"]'); if (!tv) return;
      var tps = pickedKeys(fTopics), pds = pickedKeys(fProducts);
      $all('[data-sched-card]', tv).forEach(function (card) {
        if (card.getAttribute('data-track') === '키노트') { card.classList.remove('hidden'); return; } // 키노트 항상 표시
        var okT = fTrack === 'all' || card.getAttribute('data-track') === fTrack;
        var okL = fLevel === 'all' || card.getAttribute('data-level') === fLevel;
        var okTop = anyMatch(card.getAttribute('data-topics'), tps);
        var okProd = anyMatch(card.getAttribute('data-products'), pds);
        card.classList.toggle('hidden', !(okT && okL && okTop && okProd));
      });
      $all('[data-slot-row]', tv).forEach(function (row) {
        // 공통 슬롯(환영사/휴식/점심 등)은 트랙 필터와 무관하게 항상 표시
        var hasCommon = $all('[data-sched-common]', row).length > 0;
        var any = hasCommon || $all('[data-sched-card]', row).some(function (c) { return !c.classList.contains('hidden'); });
        row.classList.toggle('hidden', !any);
      });
    }

    $all('[data-sched-day]', sc).forEach(function (b) { b.addEventListener('click', function () { showDay(b.getAttribute('data-sched-day')); }); });
    $all('[data-sched-view]', sc).forEach(function (b) { b.addEventListener('click', function () { showView(b.getAttribute('data-sched-view')); }); });
    if (fbtn && fpanel) fbtn.addEventListener('click', function () { fpanel.classList.toggle('hidden'); });
    var fclose = sc.querySelector('[data-filter-close]'); if (fclose && fpanel) fclose.addEventListener('click', function () { fpanel.classList.add('hidden'); });
    var fapply = sc.querySelector('[data-filter-apply]'); if (fapply && fpanel) fapply.addEventListener('click', function () { fpanel.classList.add('hidden'); });

    $all('[data-filter-track]', sc).forEach(function (cb) {
      cb.addEventListener('change', function () {
        var key = cb.getAttribute('data-filter-track');
        fTrack = (fTrack === key) ? 'all' : key;
        $all('[data-filter-track]', sc).forEach(function (o) { o.checked = (o.getAttribute('data-filter-track') === fTrack); });
        applyFilter();
      });
    });
    $all('[data-filter-level]', sc).forEach(function (cb) {
      cb.addEventListener('change', function () {
        var key = cb.getAttribute('data-filter-level');
        fLevel = (fLevel === key) ? 'all' : key;
        $all('[data-filter-level]', sc).forEach(function (o) { o.checked = (o.getAttribute('data-filter-level') === fLevel); });
        applyFilter();
      });
    });
    $all('[data-filter-topic]', sc).forEach(function (cb) {
      cb.addEventListener('change', function () { fTopics[cb.getAttribute('data-filter-topic')] = cb.checked; applyFilter(); });
    });
    $all('[data-filter-product]', sc).forEach(function (cb) {
      cb.addEventListener('change', function () { fProducts[cb.getAttribute('data-filter-product')] = cb.checked; applyFilter(); });
    });
    var freset = sc.querySelector('[data-filter-reset]');
    if (freset) freset.addEventListener('click', function () {
      fTrack = 'all'; fLevel = 'all'; fTopics = {}; fProducts = {};
      $all('[data-filter-track]', sc).forEach(function (o) { o.checked = (o.getAttribute('data-filter-track') === 'all'); });
      $all('[data-filter-level]', sc).forEach(function (o) { o.checked = (o.getAttribute('data-filter-level') === 'all'); });
      $all('[data-filter-topic]', sc).forEach(function (o) { o.checked = false; });
      $all('[data-filter-product]', sc).forEach(function (o) { o.checked = false; });
      applyFilter();
    });

    showDay('day1');
  }

  /* 10) 공유 버튼 */
  function initShare() {
    $all('[data-share]').forEach(function (b) {
      b.addEventListener('click', function () {
        var url = window.location.href;
        if (navigator.share) { navigator.share({ title: document.title, url: url }).catch(function () {}); }
        else if (navigator.clipboard) {
          navigator.clipboard.writeText(url).then(function () {
            var span = b.lastChild; var old = b.getAttribute('data-label') || '세션 공유하기';
            b.setAttribute('title', '링크가 복사되었습니다');
          });
        }
      });
    });
  }

  /* 11) 스크롤스파이: 현재 섹션의 GNB 메뉴 강조 (홈 전용 — data-scroll 메뉴가 있을 때만 동작) */
  function initScrollSpy() {
    var ids = ['overview', 'agenda', 'register', 'venue', 'event-benefits', 'faq', 'sponsors'];
    var sections = [];
    ids.forEach(function (id) { var el = document.getElementById(id); if (el) sections.push(el); });
    if (!sections.length) return;
    var navBtns = $all('#site-header ul [data-scroll]'); // GNB 메뉴(데스크탑/모바일), 로고·CTA 제외
    if (!navBtns.length) return;
    function setActive(id) {
      navBtns.forEach(function (b) {
        var on = b.getAttribute('data-scroll') === id;
        b.classList.toggle('text-[#00C1D5]', on);
        b.classList.toggle('text-slate-300', !on);
      });
    }
    function upd() {
      // 뷰포트 상단 기준선(도킹 헤더 아래 ~140px)을 지난 마지막 섹션을 현재 섹션으로 (실시간 rect — 레이아웃 변화에 견고)
      var cur = null;
      for (var i = 0; i < sections.length; i++) {
        if (sections[i].getBoundingClientRect().top <= 140) cur = sections[i].id;
      }
      setActive(cur);
    }
    upd();
    window.addEventListener('scroll', upd, { passive: true });
    window.addEventListener('resize', upd, { passive: true });
  }

  /* 12) 우측 고정 공유바 + 맨 위로 (2025 #sns_fixed 대응) */
  function snsToast(msg) {
    var t = document.createElement('div');
    t.textContent = msg;
    t.className = 'fixed left-1/2 -translate-x-1/2 bottom-10 z-[60] bg-[#00C1D5] text-[#09090b] text-sm font-bold px-5 py-2.5 shadow-lg';
    document.body.appendChild(t);
    setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 1600);
  }
  function initSnsFixed() {
    function openWin(url, name, w, hh) { window.open(url, name, 'width=' + w + ',height=' + hh + ',noopener'); }
    var fb = $('[data-share-fb]');
    if (fb) fb.addEventListener('click', function () { openWin('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(location.href), 'fb-share', 626, 436); });
    var x = $('[data-share-x]');
    if (x) x.addEventListener('click', function () { openWin('https://twitter.com/intent/tweet?text=' + encodeURIComponent(document.title) + '&url=' + encodeURIComponent(location.href), 'x-share', 550, 420); });
    var cp = $('[data-share-copy]');
    if (cp) cp.addEventListener('click', function () {
      if (navigator.clipboard) { navigator.clipboard.writeText(location.href).then(function () { snsToast('링크가 복사되었습니다.'); }); }
      else { snsToast(location.href); }
    });
    var tt = $('[data-to-top]');
    if (tt) {
      tt.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
      var upd = function () {
        var on = window.scrollY > 400;
        tt.classList.toggle('opacity-0', !on);
        tt.classList.toggle('pointer-events-none', !on);
        tt.classList.toggle('opacity-100', on);
      };
      upd();
      window.addEventListener('scroll', upd, { passive: true });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initHeader();
    initScrollSpy();
    initSnsFixed();
    initMobileMenu();
    initSmoothScroll();
    initCountdown();
    initCarousels();
    initFaq();
    initSidebarSections();
    initSessionFilter();
    initSchedule();
    initShare();
  });
})();
