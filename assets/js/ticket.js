/* Unreal Fest Seoul 2026 — 티켓 등록 공통 스크립트 (ticket.js)
 * ticket-all.php / ticket-day.php 공유. 본인인증 훅 + 티켓선택/가격갱신 + 트랙선택 + 폼검증.
 * 페이지는 .ticket-card[data-code,data-price,data-sub,data-pcode,data-days] 마크업과
 * 마지막에 selectTicket(초기코드) 호출만 제공하면 됨. data-days: "1,2"|"1"|"2".
 */
function won(n){ return '₩' + n.toLocaleString(); }
function _t(id){ return document.getElementById(id); }
function setText(id,v){ var el=_t(id); if(el) el.textContent=v; }
function setVal(id,v){ var el=_t(id); if(el) el.value=v; }
function toggleBox(id,show){ var el=_t(id); if(el) el.style.display = show ? '' : 'none'; }

// 티켓 선택 → 요약/가격/트랙박스/hidden 갱신
function selectTicket(code){
  var sel=null;
  document.querySelectorAll('.ticket-card').forEach(function(c){
    var on = c.getAttribute('data-code')===code;
    c.classList.toggle('border-[#00C1D5]',on);
    c.classList.toggle('bg-[rgba(0,79,89,0.2)]',on);
    c.classList.toggle('border-[#27272a]',!on);
    var inp=c.querySelector('input'); if(inp) inp.checked=on;
    var chk=c.querySelector('.tk-check'); if(chk) chk.classList.toggle('hidden',!on);
    if(on) sel=c;
  });
  if(!sel) return;
  var price=parseInt(sel.getAttribute('data-price'),10)||0;        // 할인가(실결제)
  var orig=parseInt(sel.getAttribute('data-orig'),10)||price;      // 정가
  var disc=orig-price;                                             // 할인액
  var sub=sel.getAttribute('data-sub')||'';
  var pcode=sel.getAttribute('data-pcode')||'';
  var days=(sel.getAttribute('data-days')||'').split(',');
  setText('sumSub',sub);
  setText('sumPrice',won(orig));
  setText('sumDiscount','-'+won(disc));
  setText('sumTotal',won(price));
  setText('payBtnLabel',won(price)+' 결제하기');
  toggleBox('sumDiscountRow', disc>0);
  setVal('apply_product_code',pcode);
  setVal('apply_product_name',sub);
  setVal('apply_product_price',price);
  toggleBox('day1box', days.indexOf('1')>=0);
  toggleBox('day2box', days.indexOf('2')>=0);
}

// 트랙 선택 UI
function bindTracks(){
  document.querySelectorAll('.trk').forEach(function(l){
    l.addEventListener('click',function(){
      if(l.classList.contains('trk-full')) return; // 마감 트랙 선택 불가
      var name=l.querySelector('input').name;
      document.querySelectorAll('.trk input[name="'+name+'"]').forEach(function(i){
        var lab=i.closest('.trk');
        lab.classList.remove('border-[#00C1D5]','bg-[rgba(0,79,89,0.2)]','text-[#9adbe8]');
        lab.classList.add('border-[#27272a]','text-[#71717a]');
      });
      l.classList.add('border-[#00C1D5]','bg-[rgba(0,79,89,0.2)]','text-[#9adbe8]');
      l.classList.remove('border-[#27272a]','text-[#71717a]');
    });
  });
}
function bindTicketCards(){
  document.querySelectorAll('.ticket-card').forEach(function(c){
    c.addEventListener('click',function(){ selectTicket(c.getAttribute('data-code')); });
  });
}

// 결제수단 라디오 선택 → 해당 label 하이라이트 이동
function bindPayment(){
  var radios=document.querySelectorAll('input[name="payment"]');
  if(!radios.length) return;
  function upd(){
    radios.forEach(function(r){
      var lab=r.closest('label'); if(!lab) return;
      if(r.checked){
        lab.classList.add('border-[#00C1D5]','bg-[rgba(0,79,89,0.2)]');
        lab.classList.remove('border-[#27272a]','bg-[#111115]');
      } else {
        lab.classList.remove('border-[#00C1D5]','bg-[rgba(0,79,89,0.2)]');
        lab.classList.add('border-[#27272a]','bg-[#111115]');
      }
    });
  }
  radios.forEach(function(r){ r.addEventListener('change', upd); });
  upd();
}

// 약관
function toggleAllAgree(cb){ document.querySelectorAll('.agree-item').forEach(function(i){ i.checked=cb.checked; }); }
function checkAgree(){
  if(!document.querySelector('input[name="agree_req"]').checked){
    alert('본인인증 전에 필수 약관에 동의해주세요.');
    return false;
  }
  return true;
}

// 본인인증 팝업 호출
function jsSubmit(){
  if(!checkAgree()) return;
  setVal('apply_real_type','tel');
  var f=_t('form1');
  f.action='../real/phone_popup2.php'; f.target='auth_popup';
  window.open('about:blank','auth_popup','width=430,height=640,scrollbars=yes');
  f.submit();
}
function jsSubmitPin(){
  if(!checkAgree()) return;
  setVal('apply_real_type','ipin');
  var f=_t('form1');
  f.action='../real/ipin_popup2.php'; f.target='kcbPop';
  window.open('about:blank','kcbPop','width=450,height=550,scrollbars=yes');
  f.submit();
}
function refreshAuth(){
  var ciEl=_t('apply_ci');
  if(ciEl && ciEl.value){
    var as=_t('authState');
    if(as){ as.textContent='✓ 인증 완료'; as.className='ml-2 font-bold text-[#00C1D5]'; }
  }
}
// 서버 popup3가 kcbResultForm 주입 후 호출하는 훅
window.handleKcbAuthResult = function(){
  var f=document.forms['kcbResultForm'];
  if(!f) return;
  var rslt=f.RSLT_CD?f.RSLT_CD.value:'';
  if(rslt && rslt!=='B000' && rslt!=='T000'){ alert('본인인증에 실패했습니다. 다시 시도해주세요.'); return; }
  var name=f.RSLT_NAME?f.RSLT_NAME.value:'';
  var ci=f.CI?f.CI.value:'';
  var di=f.DI?f.DI.value:'';
  var tel=f.TEL_NO?f.TEL_NO.value:'';
  setVal('apply_ci',ci);
  setVal('apply_di',di);
  var nameEl=document.querySelector('input[name="apply_user_name"]');
  if(nameEl) nameEl.value=name;
  var telEl=document.querySelector('input[name="apply_user_phone"]');
  if(telEl && tel) telEl.value=tel;
  refreshAuth();
  window._justAuthed=true;
  setTimeout(focusEmail, 300);
};
function focusEmail(){
  if(!window._justAuthed) return;
  window._justAuthed=false;
  var em=document.querySelector('input[name="apply_user_email"]');
  if(em){ em.scrollIntoView({behavior:'smooth', block:'center'}); em.focus(); }
}

// 트랙 수집 (표시중인 박스만)
function collectTrack(){
  var arr=[];
  var d1=document.querySelector('input[name="day1track"]:checked');
  var d2=document.querySelector('input[name="day2track"]:checked');
  var b1=_t('day1box'), b2=_t('day2box');
  if(d1 && b1 && b1.style.display!=='none') arr.push(d1.value);
  if(d2 && b2 && b2.style.display!=='none') arr.push(d2.value);
  setVal('apply_track', arr.join(','));
}

function ufsFail(el, msg){
  alert(msg);
  if(el){ try{ el.scrollIntoView({behavior:'smooth', block:'center'}); el.focus({preventScroll:true}); }catch(e){} }
  return false;
}
function _q(sel){ return document.querySelector(sel); }
function validateForm(){
  collectTrack();
  if(!_t('apply_ci').value) return ufsFail(_t('authState'), '본인인증을 먼저 진행해주세요.');
  var f;
  if(!(f=_q('input[name="apply_user_email"]')).value.trim()) return ufsFail(f, '이메일을 입력해주세요.');
  if(!(f=_q('input[name="apply_user_phone"]')).value.trim()) return ufsFail(f, '연락처를 입력해주세요.');
  if(!(f=_q('select[name="apply_user_job"]')).value) return ufsFail(f, '직업을 선택해주세요.');
  if(!(f=_q('input[name="apply_user_company"]')).value.trim()) return ufsFail(f, '회사명/소속을 입력해주세요.');
  if(!(f=_q('input[name="apply_user_depart"]')).value.trim()) return ufsFail(f, '부서를 입력해주세요.');
  if(!(f=_q('select[name="apply_user_grade"]')).value) return ufsFail(f, '직무를 선택해주세요.');
  if(!(f=_q('select[name="apply_user_ex1"]')).value) return ufsFail(f, '산업/관심 분야를 선택해주세요.');
  if(!_q('input[name="tshirt"]:checked')) return ufsFail(_q('input[name="tshirt"]'), '티셔츠 사이즈를 선택해주세요.');
  var b1=_t('day1box'), b2=_t('day2box');
  if(b1 && b1.style.display!=='none' && !_q('input[name="day1track"]:checked')) return ufsFail(b1, 'Day 1 트랙을 선택해주세요.');
  if(b2 && b2.style.display!=='none' && !_q('input[name="day2track"]:checked')) return ufsFail(b2, 'Day 2 트랙을 선택해주세요.');
  if(!_q('input[name="agree_req"]').checked) return ufsFail(_q('input[name="agree_req"]'), '필수 약관에 동의해주세요.');
  if(!_t('agree_refund').checked) return ufsFail(_t('agree_refund'), '취소/환불 규정에 동의해주세요.');
  return true;
}

// INICIS 결제창이 건 스크롤 잠금(body overflow/position) 복원
// — 결제 취소로 등록폼 복귀 시 스크롤이 막히는 문제 방지 (새 로드 + bfcache 모두 대응)
function ufsResetScroll(){
  document.documentElement.style.overflow='';
  document.body.style.overflow='';
  document.body.style.position='';
  document.body.style.top='';
  document.body.style.width='';
}

// 초기화
(function(){
  bindTicketCards();
  bindTracks();
  bindPayment();
  window.addEventListener('focus', function(){ refreshAuth(); focusEmail(); ufsResetScroll(); });
  window.addEventListener('pageshow', ufsResetScroll);
  setInterval(refreshAuth, 1000);
  refreshAuth();
  ufsResetScroll();
})();
