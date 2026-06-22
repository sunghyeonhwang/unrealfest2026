/* Unreal Fest Seoul 2026 — 단체 등록(ticket-group.php) 클라이언트 로직 (개개인별 선택, 티켓=셀렉트)
 * 멤버 카드(템플릿 복제, 최소4·최대29) / 인원별 티켓·트랙·티셔츠 / 금액 합산 / CSV 업로드 / 검증.
 * 의존: <template id="memTpl">, UFS_MIN_MEMBERS, UFS_MAX_TOTAL. ES5 호환. (innerHTML 미사용)
 */
(function () {
  var membersEl = document.getElementById('gMembers');
  var tpl = document.getElementById('memTpl');
  if (!membersEl || !tpl) return;
  var addBtn = document.getElementById('gAddBtn');
  var gIdx = 0;

  function won(n) { return '₩' + (n || 0).toLocaleString('en-US'); }
  function cards() { return document.querySelectorAll('[data-card]'); }
  function memberRows() { return membersEl.querySelectorAll('[data-gm-row]'); }
  function ticketSel(card) { return card.querySelector('[data-pick-ticket]'); }
  function selOpt(sel) { return (sel && sel.selectedIndex >= 0) ? sel.options[sel.selectedIndex] : null; }

  // 티켓 선택 → 트랙 요일 표시 토글
  function refreshTicket(card) {
    var op = selOpt(ticketSel(card));
    var val = op ? op.value : '';
    var isNone = (val === 'NONE');
    var days = (op && op.getAttribute('data-days')) ? op.getAttribute('data-days').split(',') : [];
    var wraps = card.querySelectorAll('[data-track-wrap]');
    for (var i = 0; i < wraps.length; i++) {
      var d = wraps[i].getAttribute('data-day');
      var on = days.indexOf(d) >= 0;
      wraps[i].style.display = on ? '' : 'none';
      var s = wraps[i].querySelector('select');
      if (s) { s.disabled = !on; if (!on) s.value = ''; }
    }
    var tw = card.querySelector('[data-tshirt-wrap]');
    if (tw) tw.style.display = isNone ? 'none' : '';
  }

  function wireCard(card) {
    var t = ticketSel(card);
    if (t) t.addEventListener('change', function () { refreshTicket(card); recalc(); });
    refreshTicket(card);
  }

  function renumber() {
    var rs = memberRows();
    for (var i = 0; i < rs.length; i++) {
      var no = rs[i].querySelector('[data-gm-no]'); if (no) no.textContent = (i + 2) + '. 참석자';
    }
    var cnt = document.getElementById('gMemCount'); if (cnt) cnt.textContent = '(' + rs.length + '명)';
    recalc();
  }

  function addMember() {
    if (memberRows().length + 1 >= window.UFS_MAX_TOTAL) {
      alert('대표자 포함 최대 ' + window.UFS_MAX_TOTAL + '명까지 등록할 수 있습니다.');
      return null;
    }
    var frag = tpl.content.cloneNode(true);
    var idx = gIdx++;
    var named = frag.querySelectorAll('[name]');
    for (var i = 0; i < named.length; i++) {
      named[i].setAttribute('name', named[i].getAttribute('name').replace('__I__', idx));
    }
    var node = frag.querySelector('[data-gm-row]');
    membersEl.appendChild(frag);
    node.querySelector('[data-gm-del]').addEventListener('click', function () {
      if (memberRows().length <= window.UFS_MIN_MEMBERS) { alert('대표자 외 최소 ' + window.UFS_MIN_MEMBERS + '인이 필요합니다.'); return; }
      node.parentNode.removeChild(node); renumber();
    });
    wireCard(node);
    renumber();
    return node;
  }

  function recalc() {
    var cs = cards(), total = 0, people = 0, nAll = 0, nDay = 0;
    for (var i = 0; i < cs.length; i++) {
      var op = selOpt(ticketSel(cs[i]));
      if (!op || !op.value || op.value === 'NONE') continue;
      people++;
      total += parseInt(op.getAttribute('data-price'), 10) || 0;
      if (op.value === 'NORMAL_ALL') nAll++; else nDay++;
    }
    var set = function (id, v) { var el = document.getElementById(id); if (el) el.textContent = v; };
    set('sumPeople', people + '명');
    set('sumAll', nAll + '명');
    set('sumDay', nDay + '명');
    set('sumTotal', won(total));
  }

  (function bindPay() {
    var box = document.getElementById('gPay'); if (!box) return;
    var labs = box.querySelectorAll('.gpay');
    for (var i = 0; i < labs.length; i++) {
      labs[i].addEventListener('click', function () {
        for (var j = 0; j < labs.length; j++) {
          labs[j].classList.remove('border-[#00C1D5]', 'bg-[rgba(0,79,89,0.2)]');
          labs[j].classList.add('border-[#27272a]', 'bg-[#111115]');
        }
        this.classList.remove('border-[#27272a]', 'bg-[#111115]');
        this.classList.add('border-[#00C1D5]', 'bg-[rgba(0,79,89,0.2)]');
        this.querySelector('input').checked = true;
        document.getElementById('group_paymethod').value = this.getAttribute('data-pay');
      });
    }
  })();

  // CSV: 이름,연락처,직무,관심분야,티켓,Day1 트랙,Day2 트랙,티셔츠
  function ticketCodeFromLabel(s) {
    s = (s || '').toLowerCase();
    if (s.indexOf('양일') >= 0) return 'NORMAL_ALL';
    if (s.indexOf('day 1') >= 0 || s.indexOf('20') >= 0) return 'NORMAL_20';
    if (s.indexOf('day 2') >= 0 || s.indexOf('21') >= 0) return 'NORMAL_21';
    return s.toUpperCase().indexOf('NORMAL') === 0 ? (s.toUpperCase()) : '';
  }
  function setSelectByText(sel, text) {
    if (!sel) return; text = (text || '').trim(); if (!text) return;
    for (var i = 0; i < sel.options.length; i++) {
      if (sel.options[i].text.trim() === text || sel.options[i].value === text) { sel.selectedIndex = i; return; }
    }
  }
  function setSelectByValue(sel, val) {
    if (!sel || !val) return;
    for (var i = 0; i < sel.options.length; i++) { if (sel.options[i].value === val) { sel.selectedIndex = i; return; } }
  }
  (function bindUpload() {
    var inp = document.getElementById('gUpload'); if (!inp) return;
    inp.addEventListener('change', function () {
      var f = inp.files[0]; if (!f) return;
      var rd = new FileReader();
      rd.onload = function (e) {
        var lines = String(e.target.result).replace(/^﻿/, '').split(/\r\n|\n|\r/).filter(function (l) { return l.trim() !== ''; });
        if (!lines.length) return;
        var start = /이름|name/i.test(lines[0]) ? 1 : 0;
        var existing = memberRows();
        for (var k = existing.length - 1; k >= 0; k--) existing[k].parentNode.removeChild(existing[k]);
        var added = 0;
        for (var i = start; i < lines.length; i++) {
          var c = lines[i].split(',');
          var node = addMember(); if (!node) break;
          var q = function (n) { return node.querySelector('[name^="' + n + '"]'); };
          if (q('member_name')) q('member_name').value = (c[0] || '').trim();
          if (q('member_phone')) q('member_phone').value = (c[1] || '').trim().replace(/[^0-9]/g, '');
          setSelectByText(q('member_grade'), c[2]);
          setSelectByText(q('member_ex1'), c[3]);
          setSelectByValue(ticketSel(node), ticketCodeFromLabel(c[4]));
          refreshTicket(node);
          setSelectByText(node.querySelector('[data-day="1"]'), c[5]);
          setSelectByText(node.querySelector('[data-day="2"]'), c[6]);
          var ts = c[7] ? node.querySelector('[name^="member_tshirt"][value="' + c[7].trim() + '"]') : null; if (ts) ts.checked = true;
          added++;
        }
        while (memberRows().length < window.UFS_MIN_MEMBERS) addMember();
        renumber();
        alert(added + '명을 불러왔습니다. 항목을 확인해 주세요.');
        inp.value = '';
      };
      rd.readAsText(f, 'UTF-8');
    });
  })();

  window.gValidate = function () {
    if (!document.getElementById('apply_ci').value) { alert('대표자 본인 인증을 먼저 진행해 주세요.'); return false; }
    var cs = cards();
    for (var i = 0; i < cs.length; i++) {
      var who = (i === 0) ? '대표자' : ((i + 1) + '번 참석자');
      var op = selOpt(ticketSel(cs[i]));
      if (!op || !op.value) { alert(who + '의 티켓을 선택해 주세요.'); return false; }
      if (op.value === 'NONE') continue;
      var days = op.getAttribute('data-days').split(',');
      if (days.indexOf('1') >= 0 && !cs[i].querySelector('[data-day="1"]').value) { alert(who + '의 Day1 트랙을 선택해 주세요.'); return false; }
      if (days.indexOf('2') >= 0 && !cs[i].querySelector('[data-day="2"]').value) { alert(who + '의 Day2 트랙을 선택해 주세요.'); return false; }
      if (!cs[i].querySelector('[data-pick-tshirt] input:checked')) { alert(who + '의 티셔츠를 선택해 주세요.'); return false; }
    }
    var ms = memberRows();
    if (ms.length < window.UFS_MIN_MEMBERS) { alert('대표자 외 최소 ' + window.UFS_MIN_MEMBERS + '인을 입력해 주세요.'); return false; }
    for (var m = 0; m < ms.length; m++) {
      var nm = ms[m].querySelector('[name^="member_name"]').value.trim();
      var ph = ms[m].querySelector('[name^="member_phone"]').value.trim();
      if (!nm || !ph) { alert((m + 2) + '번 참석자의 이름/연락처를 입력해 주세요.'); return false; }
    }
    return true;
  };

  var rep = document.querySelector('[data-rep]'); if (rep) wireCard(rep);
  for (var i = 0; i < window.UFS_MIN_MEMBERS; i++) addMember();
  if (addBtn) addBtn.addEventListener('click', addMember);
  renumber();
})();
