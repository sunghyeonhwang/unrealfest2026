<?php
/* Unreal Fest Seoul 2026 — 티켓 페이지 우측 결제 사이드바 (_ticket_sidebar.php)
 * 표시 금액(sumSub/sumPrice/sumTotal/payBtnLabel)은 ticket.js selectTicket()이 onload에 갱신.
 */
?>
<div class="lg:col-span-5 xl:col-span-4 self-start sticky top-28">
  <div class="bg-[#0e0f14] border border-[#27272a] p-6 lg:p-8 space-y-6">
    <h3 class="text-lg font-bold text-white">주문 요약</h3>
    <div class="pb-5 border-b border-[#27272a]">
      <div class="text-[#00C1D5] font-bold text-sm mb-1" id="sumSub">&nbsp;</div>
      <div class="flex justify-between items-center"><span class="text-sm text-[#a1a1aa]">티켓 금액</span><span class="text-sm text-[#a1a1aa]" id="sumPrice">&nbsp;</span></div>
      <div class="flex justify-between items-center mt-1" id="sumDiscountRow"><span class="text-sm text-[#00C1D5]">얼리버드 할인 (50%)</span><span class="text-sm font-bold text-[#00C1D5]" id="sumDiscount">&nbsp;</span></div>
      <div class="flex justify-between items-center mt-1"><span class="text-sm text-[#a1a1aa]">부가세 (VAT)</span><span class="text-sm text-[#a1a1aa]">포함</span></div>
    </div>
    <div class="flex justify-between items-end"><span class="text-[#a1a1aa] font-medium">총 결제 금액</span><span class="text-3xl font-black text-white" id="sumTotal">&nbsp;</span></div>
    <div class="space-y-2">
      <label class="flex items-center gap-3 p-3 border border-[#00C1D5] bg-[rgba(0,79,89,0.2)] cursor-pointer"><input type="radio" name="payment" value="Card" checked class="accent-[#00C1D5] w-4 h-4"><svg class="w-4 h-4 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg><span class="text-white font-medium text-sm">신용/체크카드</span></label>
      <label class="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20"><input type="radio" name="payment" value="kakaopay" class="accent-[#00C1D5] w-4 h-4"><span class="w-4 h-4 rounded bg-[#FEE500] text-black flex items-center justify-center font-black text-[8px]">P</span><span class="text-white font-medium text-sm">카카오페이</span></label>
      <label class="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20"><input type="radio" name="payment" value="naverpay" class="accent-[#00C1D5] w-4 h-4"><span class="w-4 h-4 rounded bg-[#03C75A] text-white flex items-center justify-center font-bold text-[10px]">N</span><span class="text-white font-medium text-sm">네이버페이</span></label>
      <label class="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20"><input type="radio" name="payment" value="tosspay" class="accent-[#00C1D5] w-4 h-4"><span class="w-4 h-4 rounded bg-[#0064FF] text-white flex items-center justify-center font-bold text-[8px]">T</span><span class="text-white font-medium text-sm">토스페이</span></label>
    </div>
    <div class="text-xs text-[#71717a] space-y-1"><p>• 얼리버드 티켓은 2026년 7월 13일 23:59까지 취소 및 환불이 가능하며, 7월 14일부터는 취소 및 환불이 불가합니다.</p></div>
    <label class="flex items-start gap-2 cursor-pointer"><input type="checkbox" id="agree_refund" class="mt-0.5 accent-[#00C1D5]"><span class="text-xs text-[#a1a1aa]">취소 및 환불 규정에 동의합니다. (필수)</span></label>
    <button type="submit" class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all">
      <span id="payBtnLabel">결제하기</span>
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
    </button>
    <!-- <a href="myticket.php" class="block w-full text-center text-sm text-[#71717a] hover:text-white py-3 transition-colors">등록 확인 / 취소</a> -->
  </div>
</div>
