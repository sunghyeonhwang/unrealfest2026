/* Unreal Fest Seoul 2026 — 단체 등록(ticket-group.php) 클라이언트 로직
 * 멤버 동적 행(추가/삭제, 최소4·최대29) / 상품·트랙 토글 / 금액 계산 / CSV 업로드 / 검증.
 * 의존: window.UFS_TRACKS, UFS_MIN_MEMBERS, UFS_MAX_TOTAL. ES5 호환.
 * 주: innerHTML 은 신뢰된 정적 템플릿+서버(UFS_TRACKS) 값만 사용. 업로드 CSV 값은 .value 로만 주입.
 */
(function () {
  var membersEl = document.getElementById('gMembers');
  if (!membersEl) return;
  var addBtn = document.getElementById('gAddBtn');
  var product = { code: 'NORMAL_ALL', price: 0, days: [1, 2] };

  function curProductLabel() {
    return { 'NORMAL_ALL': '양일권', 'NORMAL_20': '1일권 · Day 1', 'NORMAL_21': '1일권 · Day 2' }[product.code] || '';
  }
  function won(n) { return '₩' + (n || 0).toLocaleString('en-US'); }

  function fillTrackOptions(sel, day) {
    var m = window.UFS_TRACKS[day] || {};
    var o0 = document.createElement('option'); o0.value = ''; o0.textContent = '선택'; sel.appendChild(o0);
    for (var k in m) {
      if (m.hasOwnProperty(k)) { var o = document.createElement('option'); o.value = k; o.textContent = m[k]; sel.appendChild(o); }
    }
  }

  function makeInput(type, name, ph, cls) {
    var el = document.createElement('input'); el.type = type; el.name = name; el.placeholder = ph; el.className = cls; return el;
  }

  // 멤버 행 생성 (DOM 구성 — 사용자 입력은 value로만)
  function makeRow() {
    var inputCls = 'w-full bg-[#0e0f14] border border-[#27272a] px-3 py-2 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm';
    var selCls = 'w-full bg-[#0e0f14] border border-[#27272a] px-2 py-2 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none';
    var row = document.createElement('div');
    row.className = 'gmember-grid grid gap-2 items-center';
    row.setAttribute('data-gm-row', '');
    var no = document.createElement('div'); no.className = 'text-xs text-[#71717a] text-center'; no.setAttribute('data-gm-no', '');
    var name = makeInput('text', 'member_name[]', '이름', inputCls);
    var phone = makeInput('tel', 'member_phone[]', '01012345678', inputCls);
    var s1 = document.createElement('select'); s1.name = 'member_day1[]'; s1.className = selCls; s1.setAttribute('data-gm-day1', ''); fillTrackOptions(s1, 1);
    var s2 = document.createElement('select'); s2.name = 'member_day2[]'; s2.className = selCls; s2.setAttribute('data-gm-day2', ''); fillTrackOptions(s2, 2);
    var del = document.createElement('button'); del.type = 'button'; del.className = 'text-[#71717a] hover:text-[#ff8674] text-lg leading-none'; del.setAttribute('data-gm-del', ''); del.title = '삭제'; del.textContent = '×';
    row.appendChild(no); row.appendChild(name); row.appendChild(phone); row.appendChild(s1); row.appendChild(s2); row.appendChild(del);
    return row;
  }

  function rows() { return membersEl.querySelectorAll('[data-gm-row]'); }

  function renumber() {
    var rs = rows();
    for (var i = 0; i < rs.length; i++) { rs[i].querySelector('[data-gm-no]').textContent = (i + 1); }
    var cnt = document.getElementById('gMemCount');
    if (cnt) cnt.textContent = '(' + rs.length + '명)';
    applyDayVisibility();
    recalc();
  }

  function addRow() {
    if (rows().length + 1 >= window.UFS_MAX_TOTAL) {
      alert('대표자 포함 최대 ' + window.UFS_MAX_TOTAL + '명까지 등록할 수 있습니다.');
      return null;
    }
    var node = makeRow();
    membersEl.appendChild(node);
    node.querySelector('[data-gm-del]').addEventListener('click', function () {
      if (rows().length <= window.UFS_MIN_MEMBERS) { alert('대표자 외 최소 ' + window.UFS_MIN_MEMBERS + '인이 필요합니다.'); return; }
      node.parentNode.removeChild(node); renumber();
    });
    renumber();
    return node;
  }

  function applyDayVisibility() {
    var d1 = product.days.indexOf(1) >= 0, d2 = product.days.indexOf(2) >= 0;
    var hc1 = document.querySelector('[data-col-day1]'), hc2 = document.querySelector('[data-col-day2]');
    if (hc1) hc1.style.visibility = d1 ? '' : 'hidden';
    if (hc2) hc2.style.visibility = d2 ? '' : 'hidden';
    var rs = rows();
    for (var i = 0; i < rs.length; i++) {
      var s1 = rs[i].querySelector('[data-gm-day1]'), s2 = rs[i].querySelector('[data-gm-day2]');
      s1.style.display = d1 ? '' : 'none'; s1.disabled = !d1; if (!d1) s1.value = '';
      s2.style.display = d2 ? '' : 'none'; s2.disabled = !d2; if (!d2) s2.value = '';
    }
    var rb1 = document.getElementById('day1box'), rb2 = document.getElementById('day2box');
    if (rb1) rb1.style.display = d1 ? '' : 'none';
    if (rb2) rb2.style.display = d2 ? '' : 'none';
  }

  function recalc() {
    var people = rows().length + 1;
    var total = people * product.price;
    var set = function (id, v) { var el = document.getElementById(id); if (el) el.textContent = v; };
    set('sumProd', curProductLabel());
    set('sumUnit', won(product.price));
    set('sumPeople', people + '명');
    set('sumTotal', won(total));
  }

  function bindProduct() {
    var box = document.getElementById('gProduct');
    box.querySelectorAll('.gprod').forEach(function (lab) {
      lab.addEventListener('click', function () {
        box.querySelectorAll('.gprod').forEach(function (x) { x.classList.remove('border-[#00C1D5]', 'bg-[rgba(0,79,89,0.2)]'); x.classList.add('border-[#27272a]'); });
        lab.classList.remove('border-[#27272a]'); lab.classList.add('border-[#00C1D5]', 'bg-[rgba(0,79,89,0.2)]');
        lab.querySelector('input').checked = true;
        product.code = lab.getAttribute('data-code');
        product.price = parseInt(lab.getAttribute('data-price'), 10) || 0;
        product.days = lab.getAttribute('data-days').split(',').map(function (x) { return parseInt(x, 10); });
        document.getElementById('group_product').value = product.code;
        applyDayVisibility(); recalc();
      });
    });
  }

  function bindPay() {
    var box = document.getElementById('gPay');
    box.querySelectorAll('.gpay').forEach(function (lab) {
      lab.addEventListener('click', function () {
        box.querySelectorAll('.gpay').forEach(function (x) { x.classList.remove('border-[#00C1D5]', 'bg-[rgba(0,79,89,0.2)]'); x.classList.add('border-[#27272a]'); });
        lab.classList.remove('border-[#27272a]'); lab.classList.add('border-[#00C1D5]', 'bg-[rgba(0,79,89,0.2)]');
        lab.querySelector('input').checked = true;
        document.getElementById('group_paymethod').value = lab.getAttribute('data-pay');
      });
    });
  }

  function labelToCode(day, label) {
    var m = window.UFS_TRACKS[day] || {}; label = (label || '').trim();
    for (var k in m) { if (m.hasOwnProperty(k) && m[k] === label) return k; }
    return '';
  }
  function bindUpload() {
    var inp = document.getElementById('gUpload');
    if (!inp) return;
    inp.addEventListener('change', function () {
      var f = inp.files[0]; if (!f) return;
      var rd = new FileReader();
      rd.onload = function (e) {
        var lines = String(e.target.result).split(/\r\n|\n|\r/).filter(function (l) { return l.trim() !== ''; });
        if (!lines.length) return;
        var start = /이름|name/i.test(lines[0]) ? 1 : 0;
        membersEl.innerHTML = '';
        var added = 0;
        for (var i = start; i < lines.length; i++) {
          var c = lines[i].split(',');
          var node = addRow(); if (!node) break;
          node.querySelector('input[name="member_name[]"]').value = (c[0] || '').trim();
          node.querySelector('input[name="member_phone[]"]').value = (c[1] || '').trim().replace(/[^0-9]/g, '');
          var s1 = node.querySelector('[data-gm-day1]'), s2 = node.querySelector('[data-gm-day2]');
          if (s1) s1.value = labelToCode(1, c[2]);
          if (s2) s2.value = labelToCode(2, c[3]);
          added++;
        }
        while (rows().length < window.UFS_MIN_MEMBERS) addRow();
        renumber();
        alert(added + '명을 불러왔습니다. 트랙/연락처를 확인해 주세요.');
        inp.value = '';
      };
      rd.readAsText(f, 'UTF-8');
    });
  }

  window.gValidate = function () {
    if (!document.getElementById('apply_ci').value) { alert('대표자 본인 인증을 먼저 진행해 주세요.'); return false; }
    var rs = rows();
    if (rs.length < window.UFS_MIN_MEMBERS) { alert('대표자 외 최소 ' + window.UFS_MIN_MEMBERS + '인을 입력해 주세요.'); return false; }
    var d1 = product.days.indexOf(1) >= 0, d2 = product.days.indexOf(2) >= 0;
    for (var i = 0; i < rs.length; i++) {
      var nm = rs[i].querySelector('input[name="member_name[]"]').value.trim();
      var ph = rs[i].querySelector('input[name="member_phone[]"]').value.trim();
      if (!nm || !ph) { alert((i + 1) + '번 인원의 이름/연락처를 입력해 주세요.'); return false; }
      if (d1 && !rs[i].querySelector('[data-gm-day1]').value) { alert((i + 1) + '번 인원의 Day1 트랙을 선택해 주세요.'); return false; }
      if (d2 && !rs[i].querySelector('[data-gm-day2]').value) { alert((i + 1) + '번 인원의 Day2 트랙을 선택해 주세요.'); return false; }
    }
    return true;
  };

  bindProduct(); bindPay(); bindUpload();
  var first = document.querySelector('.gprod input:checked');
  if (first) first.parentNode.click();
  for (var i = 0; i < window.UFS_MIN_MEMBERS; i++) addRow();
  addBtn.addEventListener('click', addRow);
  renumber();
})();
