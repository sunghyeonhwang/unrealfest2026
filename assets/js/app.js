/* Unreal Fest Seoul 2026 — 공개 사이트 인터랙션
 * Vanilla JS. 해당 요소가 없으면 각 init는 자동 no-op.
 * Design Ref: design doc §5.3 interactions / dist interactions 1~14
 */
(function () {
  'use strict';

  // 다크 고정 (dist와 동일 — 공개 페이지는 항상 다크)
  document.documentElement.classList.add('dark');

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  /* 1) GNB 스크롤 축소 */
  function initGnbScroll() {
    var bar = $('[data-gnb-bar]');
    var gnb = $('#gnb');
    if (!bar || !gnb) return;
    function onScroll() {
      if (window.scrollY > 50) {
        bar.classList.remove('py-6'); bar.classList.add('py-4');
        gnb.classList.add('shadow-lg', 'shadow-black/30');
      } else {
        bar.classList.add('py-6'); bar.classList.remove('py-4');
        gnb.classList.remove('shadow-lg', 'shadow-black/30');
      }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* 2) 모바일 햄버거 메뉴 */
  function initMobileMenu() {
    var btn = $('[data-menu-toggle]');
    var menu = $('[data-mobile-menu]');
    if (!btn || !menu) return;
    btn.addEventListener('click', function () {
      var open = menu.classList.toggle('hidden') === false;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    $all('a', menu).forEach(function (a) {
      a.addEventListener('click', function () {
        menu.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* 3) 카운트다운 (얼리버드) */
  function initCountdown() {
    var el = $('[data-countdown]');
    if (!el) return;
    var deadline = new Date(el.getAttribute('data-deadline')).getTime();
    var dEl = $('[data-cd-days]', el), hEl = $('[data-cd-hours]', el),
        mEl = $('[data-cd-mins]', el), sEl = $('[data-cd-secs]', el);
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function tick() {
      var diff = deadline - new Date().getTime();
      if (diff < 0) diff = 0;
      var d = Math.floor(diff / 86400000);
      var h = Math.floor((diff % 86400000) / 3600000);
      var m = Math.floor((diff % 3600000) / 60000);
      var s = Math.floor((diff % 60000) / 1000);
      if (dEl) dEl.textContent = pad(d);
      if (hEl) hEl.textContent = pad(h);
      if (mEl) mEl.textContent = pad(m);
      if (sEl) sEl.textContent = pad(s);
    }
    tick();
    setInterval(tick, 1000);
  }

  /* 4) 아젠다 가로 캐러셀 (드래그 + 화살표) */
  function initCarousels() {
    $all('[data-carousel]').forEach(function (wrap) {
      var track = $('[data-carousel-track]', wrap);
      if (!track) return;
      var prev = $('[data-carousel-prev]', wrap);
      var next = $('[data-carousel-next]', wrap);
      var STEP = 340;
      if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -STEP, behavior: 'smooth' }); });
      if (next) next.addEventListener('click', function () { track.scrollBy({ left: STEP, behavior: 'smooth' }); });
      // 포인터 드래그
      var down = false, startX = 0, startScroll = 0, moved = false;
      track.addEventListener('pointerdown', function (ev) {
        down = true; moved = false; startX = ev.clientX; startScroll = track.scrollLeft;
        track.classList.add('cursor-grabbing');
      });
      window.addEventListener('pointermove', function (ev) {
        if (!down) return;
        var dx = ev.clientX - startX;
        if (Math.abs(dx) > 5) moved = true;
        track.scrollLeft = startScroll - dx;
      });
      window.addEventListener('pointerup', function () {
        down = false; track.classList.remove('cursor-grabbing');
      });
      // 드래그 후 클릭 방지
      track.addEventListener('click', function (ev) {
        if (moved) { ev.preventDefault(); ev.stopPropagation(); }
      }, true);
    });
  }

  /* 7+8) 세션 필터 + 뷰 토글 (sessions.php) */
  function initSessionFilter() {
    var grid = $('[data-session-grid]');
    if (!grid) return;
    var cards = $all('[data-track]', grid);
    var empty = $('[data-empty]');
    var state = { track: '전체', difficulty: '전체', q: '' };

    function apply() {
      var visible = 0;
      cards.forEach(function (c) {
        var okT = state.track === '전체' || c.getAttribute('data-track') === state.track;
        var okD = state.difficulty === '전체' || c.getAttribute('data-difficulty') === state.difficulty;
        var okQ = state.q === '' || c.getAttribute('data-search').toLowerCase().indexOf(state.q) !== -1;
        var show = okT && okD && okQ;
        c.classList.toggle('hidden', !show);
        if (show) visible++;
      });
      if (empty) empty.classList.toggle('hidden', visible !== 0);
    }

    $all('[data-filter-track]').forEach(function (b) {
      b.addEventListener('click', function () {
        state.track = b.getAttribute('data-filter-track');
        setActive('[data-filter-track]', b);
        apply();
      });
    });
    $all('[data-filter-difficulty]').forEach(function (b) {
      b.addEventListener('click', function () {
        state.difficulty = b.getAttribute('data-filter-difficulty');
        setActive('[data-filter-difficulty]', b);
        apply();
      });
    });
    var input = $('[data-search-input]');
    if (input) input.addEventListener('input', function () { state.q = input.value.trim().toLowerCase(); apply(); });

    var reset = $('[data-filter-reset]');
    if (reset) reset.addEventListener('click', function () {
      state = { track: '전체', difficulty: '전체', q: '' };
      if (input) input.value = '';
      resetActive('[data-filter-track]', '전체');
      resetActive('[data-filter-difficulty]', '전체');
      apply();
    });

    // 뷰 밀도 토글 (2단/3단)
    $all('[data-density]').forEach(function (b) {
      b.addEventListener('click', function () {
        var cols = b.getAttribute('data-density');
        grid.classList.remove('lg:grid-cols-2', 'lg:grid-cols-3');
        grid.classList.add(cols === '3' ? 'lg:grid-cols-3' : 'lg:grid-cols-2');
        setActive('[data-density]', b);
      });
    });

    function setActive(sel, active) {
      $all(sel).forEach(function (el) {
        el.classList.toggle('bg-brand', el === active);
        el.classList.toggle('text-white', el === active);
        el.classList.toggle('border-brand', el === active);
      });
    }
    function resetActive(sel, val) {
      $all(sel).forEach(function (el) {
        var on = el.getAttribute(sel.replace(/[\[\]]/g, '')) === val;
        el.classList.toggle('bg-brand', on);
        el.classList.toggle('text-white', on);
        el.classList.toggle('border-brand', on);
      });
    }
    apply();
  }

  /* 10) 스케줄 Day 탭 (schedule.php) */
  function initScheduleTabs() {
    var tabs = $all('[data-day-tab]');
    if (!tabs.length) return;
    function show(day) {
      $all('[data-day-panel]').forEach(function (p) {
        p.classList.toggle('hidden', p.getAttribute('data-day-panel') !== day);
      });
      tabs.forEach(function (t) {
        var on = t.getAttribute('data-day-tab') === day;
        t.classList.toggle('bg-brand', on);
        t.classList.toggle('text-white', on);
        t.classList.toggle('text-slate-400', !on);
      });
    }
    tabs.forEach(function (t) {
      t.addEventListener('click', function () { show(t.getAttribute('data-day-tab')); });
    });
    show('1');
  }

  /* 11) FAQ 탭 + 아코디언 */
  function initFaq() {
    var root = $('[data-faq]');
    if (!root) return;
    var tabs = $all('[data-faq-tab]', root);
    function showTab(tab) {
      $all('[data-faq-item]', root).forEach(function (it) {
        it.classList.toggle('hidden', it.getAttribute('data-faq-item') !== tab);
      });
      tabs.forEach(function (t) {
        var on = t.getAttribute('data-faq-tab') === tab;
        t.classList.toggle('bg-brand', on);
        t.classList.toggle('text-white', on);
        t.classList.toggle('text-slate-400', !on);
      });
      // 탭 변경 시 모두 닫기
      $all('[data-acc-body]', root).forEach(function (b) { b.classList.add('hidden'); });
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { showTab(t.getAttribute('data-faq-tab')); }); });

    $all('[data-acc-trigger]', root).forEach(function (trg) {
      trg.addEventListener('click', function () {
        var body = trg.parentNode.querySelector('[data-acc-body]');
        if (!body) return;
        var isOpen = !body.classList.contains('hidden');
        // 단일 open: 같은 탭 내 모두 닫고 토글
        var item = trg.closest('[data-faq-item]');
        if (item) $all('[data-acc-body]', item).forEach(function (b) { b.classList.add('hidden'); });
        if (!isOpen) body.classList.remove('hidden');
      });
    });
    if (tabs.length) showTab(tabs[0].getAttribute('data-faq-tab'));
  }

  /* 세션 상세: 공유 버튼 */
  function initShare() {
    $all('[data-share]').forEach(function (b) {
      b.addEventListener('click', function () {
        var url = window.location.href;
        if (navigator.share) {
          navigator.share({ title: document.title, url: url }).catch(function () {});
        } else if (navigator.clipboard) {
          navigator.clipboard.writeText(url).then(function () {
            b.setAttribute('data-copied', '1');
            var old = b.textContent; b.textContent = '링크 복사됨';
            setTimeout(function () { b.textContent = old; }, 1500);
          });
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initGnbScroll();
    initMobileMenu();
    initCountdown();
    initCarousels();
    initSessionFilter();
    initScheduleTabs();
    initFaq();
    initShare();
  });
})();
