<?php
/* Unreal Fest Seoul 2026 — 티켓 페이지 약관 동의 섹션 (_ticket_agree.php)
 * 페이지 좌측 최상단(티켓 선택 앞)에 include.
 */
?>
<!-- 약관 동의 -->
<div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
  <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> 약관 동의</h2>
  <div class="space-y-3">
    <label class="flex items-center gap-3 p-3 bg-[rgba(0,79,89,0.3)] border border-[rgba(0,193,213,0.3)] cursor-pointer">
      <input type="checkbox" id="agree_all" onchange="toggleAllAgree(this)" class="accent-[#00C1D5]">
      <span class="text-sm font-bold text-white">전체 동의</span>
    </label>
    <div class="h-px bg-[#27272a]"></div>
    <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
      <input type="checkbox" name="agree_req" class="agree-item mt-0.5 accent-[#00C1D5]">
      <span class="text-sm text-[#a1a1aa]"><button type="button" onclick="event.preventDefault();event.stopPropagation();openLegal('terms');" class="underline text-[#00C1D5] hover:text-white">이용약관</button> 동의 및 <button type="button" onclick="event.preventDefault();event.stopPropagation();openLegal('privacy');" class="underline text-[#00C1D5] hover:text-white">개인정보처리방침</button> 확인<span class="ml-1 text-xs text-[#00C1D5]">(필수)</span></span>
    </label>
    <?php if (!empty($ufs_group_agree)): ?>
    <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
      <input type="checkbox" name="agree_group" class="agree-item mt-0.5 accent-[#00C1D5]">
      <span class="text-sm text-[#a1a1aa]">참가 인원의 개인정보 수집·이용 및 제3자(주최/대행사) 제공에 동의하며, <b class="text-[#cbd5e1]">각 인원으로부터 사전 동의를 받았음</b>을 확인합니다.<span class="ml-1 text-xs text-[#00C1D5]">(필수)</span></span>
    </label>
    <?php endif; ?>
    <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
      <input type="checkbox" name="agree_mkt" class="agree-item mt-0.5 accent-[#00C1D5]">
      <span class="text-sm text-[#a1a1aa]"><button type="button" onclick="event.preventDefault();event.stopPropagation();openLegal('marketing');" class="underline text-[#a1a1aa] hover:text-white">광고 수신 동의</button><span class="ml-1 text-xs text-[#71717a]">(선택)</span></span>
    </label>
  </div>
</div>
<?php include __DIR__ . '/_legal_modal.php'; ?>
