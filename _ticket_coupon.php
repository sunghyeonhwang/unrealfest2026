<?php
/* Unreal Fest Seoul 2026 — 개인 등록 쿠폰 입력 파티셜 (_ticket_coupon.php)
 * ticket-all.php / ticket-day.php 좌측 폼 최하단에 include. 토글 ON(ufs_coupon_enabled)일 때만 렌더.
 * 서버 최종 적용은 apply_pay.php(coupon_code 재검증). 여기 JS는 표시(사이드바 총액) 미리보기용.
 */
if (!function_exists('ufs_coupon_enabled') || !ufs_coupon_enabled()) return;
?>
<!-- 쿠폰 입력 (개인) -->
<div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
  <h2 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
    <svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
    쿠폰 <span class="text-xs font-normal text-[#71717a]">(선택)</span>
  </h2>
  <p class="text-xs text-[#71717a] mb-4">보유하신 쿠폰 코드가 있다면 입력 후 적용해 주세요.</p>
  <div class="flex gap-2">
    <input type="text" name="coupon_code" id="coupon_code" placeholder="쿠폰 코드 (예: UE2026...)" autocomplete="off"
           class="flex-1 bg-[#09090b] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm uppercase">
    <button type="button" onclick="ufsApplyCoupon()" class="px-6 py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-bold text-sm transition-all whitespace-nowrap">적용</button>
    <button type="button" onclick="ufsClearCoupon(true)" class="px-4 py-3 border border-[#27272a] text-[#a1a1aa] hover:text-white text-sm transition-all whitespace-nowrap">해제</button>
  </div>
  <div id="coupon_result" class="mt-3 text-sm" style="display:none"></div>
</div>
<script>
/* 쿠폰 적용 + 사이드바 총액 반영 + 티켓 변경 시 재적용 (DOM 안전 생성) */
var _ufsCouponPct = 0;
function _ufsSelPrice(){ var s=document.querySelector('input[name="ticket"]:checked'); var c=s?s.closest('.ticket-card'):document.querySelector('.ticket-card'); return c?(parseInt(c.getAttribute('data-price'),10)||0):0; }
function _ufsWon(n){ return '₩'+(n||0).toLocaleString(); }
function _ufsSpan(cls,t){ var s=document.createElement('span'); s.className=cls; s.textContent=t; return s; }
function _ufsDiscounted(price){ return Math.round(price*(100-_ufsCouponPct)/100/100)*100; }
function _ufsSidebar(){
  var price=_ufsSelPrice();
  var total=document.getElementById('sumTotal');
  var row=document.getElementById('ufsCouponRow');
  if(_ufsCouponPct>0 && total){
    var discounted=_ufsDiscounted(price);
    var anchor=total.closest('div');
    if(!row){ row=document.createElement('div'); row.id='ufsCouponRow'; row.className='flex justify-between items-center mt-1'; anchor.parentNode.insertBefore(row,anchor); }
    row.textContent='';
    row.appendChild(_ufsSpan('text-sm text-[#00C1D5]','쿠폰 할인 ('+_ufsCouponPct+'%)'));
    row.appendChild(_ufsSpan('text-sm font-bold text-[#00C1D5]','-'+_ufsWon(price-discounted)));
    total.textContent=_ufsWon(discounted);
    var lbl=document.getElementById('payBtnLabel'); if(lbl) lbl.textContent=_ufsWon(discounted)+' 결제하기';
  } else if(row){ row.remove(); }
}
function ufsClearCoupon(reset){
  _ufsCouponPct=0;
  var box=document.getElementById('coupon_result'); if(box){ box.style.display='none'; box.textContent=''; }
  if(reset){ var i=document.getElementById('coupon_code'); if(i) i.value=''; }
  var row=document.getElementById('ufsCouponRow'); if(row) row.remove();
  var sel=document.querySelector('input[name="ticket"]:checked'); if(sel && typeof selectTicket==='function'){ selectTicket(sel.value); }  // 총액 원복
}
function ufsApplyCoupon(){
  var el=document.getElementById('coupon_code');
  var code=(el.value||'').trim().toUpperCase(); el.value=code;
  var box=document.getElementById('coupon_result'); box.style.display=''; box.className='mt-3 text-sm';
  if(!code){ _ufsCouponPct=0; _ufsSidebar(); box.style.color='#f87171'; box.textContent='쿠폰 코드를 입력해 주세요.'; return; }
  box.style.color='#a1a1aa'; box.textContent='확인 중…';
  fetch('group-coupon-check.php?code='+encodeURIComponent(code)).then(function(r){ return r.json(); }).then(function(d){
    if(!d.ok){ _ufsCouponPct=0; _ufsSidebar(); box.style.display=''; box.style.color='#f87171'; box.textContent=d.msg||'유효하지 않은 쿠폰입니다.'; return; }
    _ufsCouponPct=parseInt(d.percent,10)||0;
    _ufsSidebar();
    var price=_ufsSelPrice(), discounted=_ufsDiscounted(price);
    box.style.display=''; box.style.color='#00C1D5'; box.textContent='';
    var b1=document.createElement('b'); b1.textContent=code; box.appendChild(b1);
    box.appendChild(document.createTextNode(' · '+_ufsCouponPct+'% 할인 — '+_ufsWon(price)+' → '));
    var b2=document.createElement('b'); b2.textContent=_ufsWon(discounted); box.appendChild(b2);
  }).catch(function(){ box.style.display=''; box.style.color='#f87171'; box.textContent='쿠폰 확인 중 오류가 발생했습니다.'; });
}
document.addEventListener('change', function(e){ if(e.target && e.target.name==='ticket'){ setTimeout(_ufsSidebar, 0); } });
</script>
<?php /* 파일 끝 */ ?>
