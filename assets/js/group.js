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
  var couponPct = 0;

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
    if (t) t.addEventListener('change', function () { refreshTicket(card); renumber(); });
    refreshTicket(card);
  }

  function renumber() {
    var rep = document.querySelector('[data-rep]');
    var repOp = rep ? selOpt(ticketSel(rep)) : null;
    var repAttend = !!(repOp && repOp.value && repOp.value !== 'NONE');
    var repHead = document.getElementById('repHead');
    if (repHead) repHead.textContent = repAttend ? '1. 대표자 참석 선택' : '대표자 참석 선택 (결제만 · 인원 제외)';
    var base = repAttend ? 2 : 1;
    var rs = memberRows();
    for (var i = 0; i < rs.length; i++) {
      var no = rs[i].querySelector('[data-gm-no]'); if (no) no.textContent = (i + base) + '. 참석자';
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
      node.parentNode.removeChild(node); renumber(); queueSave();
    });
    wireCard(node);
    renumber();
    return node;
  }

  function recalc() {
    var cs = cards(), people = 0, nAll = 0, nDay = 0, sumOrig = 0, total = 0;
    var gd = window.GROUP_DISCOUNT || 0;
    var eff = Math.max(gd, couponPct || 0);
    for (var i = 0; i < cs.length; i++) {
      var op = selOpt(ticketSel(cs[i]));
      if (!op || !op.value || op.value === 'NONE') continue;
      people++;
      var orig = parseInt(op.getAttribute('data-orig'), 10) || 0;
      sumOrig += orig;
      total += Math.round(orig * (100 - eff) / 100 / 100) * 100;
      if (op.value === 'NORMAL_ALL') nAll++; else nDay++;
    }
    var disc = sumOrig - total;
    var set = function (id, v) { var el = document.getElementById(id); if (el) el.textContent = v; };
    set('sumPeople', people + '명');
    set('sumAll', nAll + '명');
    set('sumDay', nDay + '명');
    set('sumOrig', won(sumOrig));
    set('sumDisc', '-' + won(disc));
    set('sumDiscPct', eff > 0 ? ('(' + (couponPct > gd ? '쿠폰 ' : '단체 ') + eff + '%)') : '');
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
        var pay = this.getAttribute('data-pay');
        document.getElementById('group_paymethod').value = pay;
        var taxSec = document.getElementById('taxSection');
        if (taxSec) {
          if (pay === 'bank') { taxSec.classList.remove('hidden'); }
          else { taxSec.classList.add('hidden'); var t=document.getElementById('taxReq'); if(t) t.checked=false; var tf=document.getElementById('taxFields'); if(tf) tf.classList.add('hidden'); }
        }
      });
    }
  })();

  // 세금계산서 체크 → 필드 토글
  (function bindTax(){
    var t=document.getElementById('taxReq'); var tf=document.getElementById('taxFields'); if(!t||!tf) return;
    t.addEventListener('change', function(){ tf.classList.toggle('hidden', !t.checked); });
  })();

  // 쿠폰 적용
  (function bindCoupon() {
    var btn = document.getElementById('couponBtn'); if (!btn) return;
    var inp = document.getElementById('couponCode');
    var msg = document.getElementById('couponMsg');
    function setMsg(t, color) { msg.textContent = t; msg.style.color = color; }
    btn.addEventListener('click', function () {
      var code = (inp.value || '').trim().toUpperCase();
      if (!code) { setMsg('쿠폰 코드를 입력해 주세요.', '#ff8674'); return; }
      btn.disabled = true; setMsg('확인 중...', '#71717a');
      fetch('group-coupon-check.php?code=' + encodeURIComponent(code)).then(function (r) { return r.json(); }).then(function (j) {
        btn.disabled = false;
        if (j && j.ok) {
          couponPct = parseInt(j.percent, 10) || 0;
          document.getElementById('couponApplied').value = j.code;
          document.getElementById('couponPercent').value = couponPct;
          var gd = window.GROUP_DISCOUNT || 0;
          if (gd >= couponPct) setMsg('쿠폰(' + couponPct + '%)보다 단체 할인(' + gd + '%)이 커서 단체 할인이 적용됩니다.', '#a1a1aa');
          else setMsg(j.msg, '#00C1D5');
        } else {
          couponPct = 0; document.getElementById('couponApplied').value = ''; document.getElementById('couponPercent').value = 0;
          setMsg((j && j.msg) || '쿠폰 확인에 실패했습니다.', '#ff8674');
        }
        recalc(); queueSave();
      }).catch(function () { btn.disabled = false; setMsg('쿠폰 확인 중 오류가 발생했습니다.', '#ff8674'); });
    });
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
  // 트랙 매칭: 라벨로 옵션을 찾되 마감(disabled '(마감)') 이면 선택 안 하고 알림. 반환 {ok, full, notfound}
  function trackResult(sel, text) {
    text = (text || '').trim();
    if (!sel || !text) return { ok: true };
    for (var i = 0; i < sel.options.length; i++) {
      var o = sel.options[i];
      var base = o.text.replace(/\s*\(\s*마감\s*\)\s*$/, '').trim();
      if (o.text.trim() === text || base === text || o.value === text) {
        if (o.disabled) return { ok: false, full: true };
        sel.selectedIndex = i; return { ok: true };
      }
    }
    return { ok: false, notfound: true };
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
        var fullWarn = [];
        for (var i = start; i < lines.length; i++) {
          var c = lines[i].split(',');
          var node = addMember(); if (!node) break;
          var q = function (n) { return node.querySelector('[name^="' + n + '"]'); };
          if (q('member_name')) q('member_name').value = (c[0] || '').trim();
          if (q('member_email')) q('member_email').value = (c[1] || '').trim();
          if (q('member_phone')) q('member_phone').value = (c[2] || '').trim().replace(/[^0-9]/g, '');
          if (q('member_depart')) q('member_depart').value = (c[3] || '').trim();
          setSelectByText(q('member_grade'), c[4]);
          setSelectByText(q('member_ex1'), c[5]);
          setSelectByValue(ticketSel(node), ticketCodeFromLabel(c[6]));
          refreshTicket(node);
          var who = (c[0] || '').trim() || ((added + 1) + '번 참석자');
          var r1 = trackResult(node.querySelector("select[data-day=\"1\"]"), c[7]);
          if (r1.full) fullWarn.push(who + ' · Day1 · ' + (c[7] || '').trim());
          var r2 = trackResult(node.querySelector("select[data-day=\"2\"]"), c[8]);
          if (r2.full) fullWarn.push(who + ' · Day2 · ' + (c[8] || '').trim());
          setSelectByValue(node.querySelector('[data-pick-tshirt]'), (c[9] || '').trim());
          added++;
        }
        while (memberRows().length < window.UFS_MIN_MEMBERS) addMember();
        renumber(); queueSave();
        var msg = added + '명을 불러왔습니다. 항목을 확인해 주세요.';
        if (fullWarn.length) {
          msg += '\n\n⚠ 아래 트랙은 마감되어 자동 선택되지 않았습니다. 다른 트랙을 선택해 주세요:\n- ' + fullWarn.join('\n- ');
        }
        alert(msg);
        inp.value = '';
      };
      rd.readAsText(f, 'UTF-8');
    });
  })();

  // 미입력 칸으로 즉시 이동(스크롤+포커스) + 빨간 테두리 강조 후 false 반환
  function gFail(el, msg) {
    alert(msg);
    if (el) {
      try { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { try { el.scrollIntoView(); } catch (e2) {} }
      try { el.focus({ preventScroll: true }); } catch (e) {}
      if (el.style) {
        el.style.borderColor = '#FA4616';
        var clr = function () { el.style.borderColor = ''; el.removeEventListener('input', clr); el.removeEventListener('change', clr); };
        el.addEventListener('input', clr); el.addEventListener('change', clr);
      }
    }
    return false;
  }
  function gField(name) { var frm = document.getElementById('frm'); return frm ? frm.querySelector('[name="' + name + '"]') : null; }

  window.gValidate = function () {
    // 1) 대표자 본인 인증
    if (!document.getElementById('apply_ci').value) {
      var authEl = document.getElementById('authState') || document.getElementById('apply_user_name');
      return gFail(authEl, '대표자 본인 인증을 먼저 진행해 주세요.');
    }
    // 2) 대표자 입력칸(이름은 본인인증 자동입력이라 제외)
    var repReq = [
      ['apply_user_email', '대표자 이메일을 입력해 주세요.'],
      ['apply_user_phone', '대표자 연락처를 입력해 주세요.'],
      ['apply_user_job', '대표자 직업을 선택해 주세요.'],
      ['apply_user_company', '대표자 회사명/소속을 입력해 주세요.'],
      ['apply_user_depart', '대표자 부서를 입력해 주세요.'],
      ['apply_user_grade', '대표자 직무를 선택해 주세요.'],
      ['apply_user_ex1', '대표자 산업/관심 분야를 선택해 주세요.']
    ];
    for (var r = 0; r < repReq.length; r++) {
      var rf = gField(repReq[r][0]);
      if (rf && !rf.value.trim()) return gFail(rf, repReq[r][1]);
    }
    // 3) 각 참석자 티켓/트랙/티셔츠
    var cs = cards();
    for (var i = 0; i < cs.length; i++) {
      var who = (i === 0) ? '대표자' : ((i + 1) + '번 참석자');
      var tSel = ticketSel(cs[i]);
      var op = selOpt(tSel);
      if (!op || !op.value) return gFail(tSel, who + '의 티켓을 선택해 주세요.');
      if (op.value === 'NONE') continue;
      var days = op.getAttribute('data-days').split(',');
      var d1 = cs[i].querySelector("select[data-day=\"1\"]");
      var d2 = cs[i].querySelector("select[data-day=\"2\"]");
      if (days.indexOf('1') >= 0 && d1 && !d1.value) return gFail(d1, who + '의 Day1 트랙을 선택해 주세요.');
      if (days.indexOf('2') >= 0 && d2 && !d2.value) return gFail(d2, who + '의 Day2 트랙을 선택해 주세요.');
      var tsSel = cs[i].querySelector('[data-pick-tshirt]');
      if (tsSel && !tsSel.value) return gFail(tsSel, who + '의 티셔츠를 선택해 주세요.');
    }
    // 4) 멤버 인원수 + 개인 필드
    var ms = memberRows();
    if (ms.length < window.UFS_MIN_MEMBERS) { return gFail(document.getElementById('gAddBtn'), '대표자 외 최소 ' + window.UFS_MIN_MEMBERS + '인을 입력해 주세요.'); }
    for (var m = 0; m < ms.length; m++) {
      var nmEl = ms[m].querySelector('[name^="member_name"]');
      var emEl = ms[m].querySelector('[name^="member_email"]');
      var phEl = ms[m].querySelector('[name^="member_phone"]');
      if (nmEl && !nmEl.value.trim()) return gFail(nmEl, (m + 2) + '번 참석자의 이름을 입력해 주세요.');
      if (emEl && !emEl.value.trim()) return gFail(emEl, (m + 2) + '번 참석자의 이메일을 입력해 주세요.');
      if (phEl && !phEl.value.trim()) return gFail(phEl, (m + 2) + '번 참석자의 연락처를 입력해 주세요.');
    }
    // 5) 세금계산서 발행 신청 시 필수 항목
    var taxReq = document.getElementById('taxReq');
    if (taxReq && taxReq.checked) {
      var taxFields = [
        ['apply_user_biznum', '사업자등록번호를 입력해 주세요.'],
        ['tax_addr', '사업장 주소를 입력해 주세요.'],
        ['tax_ceo', '(법인) 대표자명을 입력해 주세요.'],
        ['tax_biztype', '업태를 입력해 주세요.'],
        ['tax_bizitem', '종목을 입력해 주세요.']
      ];
      for (var t = 0; t < taxFields.length; t++) {
        var tf = gField(taxFields[t][0]);
        if (tf && !tf.value.trim()) return gFail(tf, taxFields[t][1]);
      }
    }
    // 6) 약관 동의
    var aReq = document.querySelector('input[name="agree_req"]');
    if (aReq && !aReq.checked) return gFail(aReq, '이용약관 및 개인정보처리방침에 동의해 주세요.');
    var aGrp = document.querySelector('input[name="agree_group"]');
    if (aGrp && !aGrp.checked) return gFail(aGrp, '단체 참가 인원의 개인정보 수집·제공 동의가 필요합니다.');
    return true;
  };

  // ── 폼값 유지(localStorage) ─────────────────────────────────────
  // 입력 → 다음 단계(확인/결제)로 갔다가 취소/뒤로 와도 값 보존. 등록 최종완료 시 완료페이지에서 제거.
  var STORE_KEY = 'ufs_group_form';
  function valOf(el) { return el ? el.value : ''; }

  function fillCardSelections(card, t, d1, d2, ts) {
    if (!card) return;
    setSelectByValue(ticketSel(card), t);
    refreshTicket(card);
    setSelectByValue(card.querySelector("select[data-day=\"1\"]"), d1);
    setSelectByValue(card.querySelector("select[data-day=\"2\"]"), d2);
    setSelectByValue(card.querySelector('[data-pick-tshirt]'), ts);
  }

  function snapshot() {
    var d = { rep: {}, members: [] };
    var repNames = ['apply_user_email', 'apply_user_phone', 'apply_user_job', 'apply_user_company', 'apply_user_depart', 'apply_user_grade', 'apply_user_ex1'];
    for (var i = 0; i < repNames.length; i++) { var el = gField(repNames[i]); if (el) d.rep[repNames[i]] = el.value; }
    var repCard = document.querySelector('[data-rep]');
    if (repCard) {
      d.repTicket = valOf(ticketSel(repCard));
      d.repDay1 = valOf(repCard.querySelector("select[data-day=\"1\"]"));
      d.repDay2 = valOf(repCard.querySelector("select[data-day=\"2\"]"));
      d.repTshirt = valOf(repCard.querySelector('[data-pick-tshirt]'));
    }
    var pm = document.getElementById('group_paymethod'); d.paymethod = pm ? pm.value : 'card';
    var tq = document.getElementById('taxReq');
    d.tax = { req: !!(tq && tq.checked), biznum: valOf(gField('apply_user_biznum')), addr: valOf(gField('tax_addr')), ceo: valOf(gField('tax_ceo')), biztype: valOf(gField('tax_biztype')), bizitem: valOf(gField('tax_bizitem')) };
    var cc = document.getElementById('couponCode'), ca = document.getElementById('couponApplied');
    d.coupon = { code: cc ? cc.value : '', applied: ca ? ca.value : '', percent: couponPct || 0 };
    var rows = memberRows();
    for (var r = 0; r < rows.length; r++) {
      var row = rows[r];
      var q = function (n) { return row.querySelector('[name^="' + n + '"]'); };
      d.members.push({
        name: valOf(q('member_name')), email: valOf(q('member_email')), phone: valOf(q('member_phone')),
        depart: valOf(q('member_depart')), grade: valOf(q('member_grade')), ex1: valOf(q('member_ex1')),
        ticket: valOf(ticketSel(row)),
        day1: valOf(row.querySelector("select[data-day=\"1\"]")),
        day2: valOf(row.querySelector("select[data-day=\"2\"]")),
        tshirt: valOf(row.querySelector('[data-pick-tshirt]'))
      });
    }
    return d;
  }

  function restoreForm(raw) {
    if (!raw) return;
    var d; try { d = JSON.parse(raw); } catch (e) { return; }
    if (!d) return;
    if (d.rep) { for (var k in d.rep) { if (!d.rep.hasOwnProperty(k)) continue; var el = gField(k); if (el && !el.readOnly) el.value = d.rep[k]; } }
    fillCardSelections(document.querySelector('[data-rep]'), d.repTicket, d.repDay1, d.repDay2, d.repTshirt);
    if (d.paymethod) { var lab = document.querySelector('.gpay[data-pay="' + d.paymethod + '"]'); if (lab) lab.click(); }
    if (d.tax) {
      var tq = document.getElementById('taxReq');
      if (tq && d.tax.req) { tq.checked = true; var tf = document.getElementById('taxFields'); if (tf) tf.classList.remove('hidden'); }
      var sv = function (n, v) { var e = gField(n); if (e) e.value = v || ''; };
      sv('apply_user_biznum', d.tax.biznum); sv('tax_addr', d.tax.addr); sv('tax_ceo', d.tax.ceo); sv('tax_biztype', d.tax.biztype); sv('tax_bizitem', d.tax.bizitem);
    }
    if (d.coupon) {
      var cc = document.getElementById('couponCode'); if (cc) cc.value = d.coupon.code || '';
      if (d.coupon.percent) { couponPct = parseInt(d.coupon.percent, 10) || 0; var ca = document.getElementById('couponApplied'); if (ca) ca.value = d.coupon.applied || ''; var cp = document.getElementById('couponPercent'); if (cp) cp.value = couponPct; }
    }
    if (d.members && d.members.length) {
      var ex = memberRows(); for (var i = ex.length - 1; i >= 0; i--) ex[i].parentNode.removeChild(ex[i]);
      for (var j = 0; j < d.members.length; j++) {
        var mm = d.members[j]; var node = addMember(); if (!node) break;
        var nm = node.querySelector('[name^="member_name"]'); if (nm) nm.value = mm.name || '';
        var em = node.querySelector('[name^="member_email"]'); if (em) em.value = mm.email || '';
        var ph = node.querySelector('[name^="member_phone"]'); if (ph) ph.value = mm.phone || '';
        var dp = node.querySelector('[name^="member_depart"]'); if (dp) dp.value = mm.depart || '';
        setSelectByValue(node.querySelector('[name^="member_grade"]'), mm.grade);
        setSelectByValue(node.querySelector('[name^="member_ex1"]'), mm.ex1);
        fillCardSelections(node, mm.ticket, mm.day1, mm.day2, mm.tshirt);
      }
      while (memberRows().length < window.UFS_MIN_MEMBERS) addMember();
    }
    renumber();
  }

  var savedRaw = null; try { savedRaw = localStorage.getItem(STORE_KEY); } catch (e) {}
  var formReady = false;
  var saveTimer;
  function saveForm() { if (!formReady) return; try { localStorage.setItem(STORE_KEY, JSON.stringify(snapshot())); } catch (e) {} }
  function queueSave() { if (!formReady) return; clearTimeout(saveTimer); saveTimer = setTimeout(saveForm, 150); }
  var frmEl = document.getElementById('frm');
  if (frmEl) { frmEl.addEventListener('input', queueSave); frmEl.addEventListener('change', queueSave); }

  var rep = document.querySelector('[data-rep]'); if (rep) wireCard(rep);
  for (var i = 0; i < window.UFS_MIN_MEMBERS; i++) addMember();
  if (addBtn) addBtn.addEventListener('click', function () { addMember(); queueSave(); });
  renumber();
  restoreForm(savedRaw);
  formReady = true;
  saveForm();
})();
