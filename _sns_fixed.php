<?php /* 우측 고정 공유바 + 맨 위로 버튼 (2025 #sns_fixed 대응). 동작은 app.js initSnsFixed(). */ ?>
<div class="fixed right-3 md:right-5 top-1/2 -translate-y-1/2 z-40 flex flex-col gap-2.5" data-sns-fixed>
  <!-- 페이스북 공유 -->
  <button type="button" data-share-fb aria-label="페이스북 공유" class="w-11 h-11 rounded-full bg-[#09090b]/80 backdrop-blur border border-white/10 flex items-center justify-center text-slate-300 hover:text-white hover:border-[#00C1D5] transition-colors">
    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M24 12.07C24 5.41 18.63 0 12 0S0 5.41 0 12.07c0 6.02 4.39 11.02 10.13 11.93v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.09 24 18.1 24 12.07Z"/></svg>
  </button>
  <!-- X(트위터) 공유 -->
  <button type="button" data-share-x aria-label="X 공유" class="w-11 h-11 rounded-full bg-[#09090b]/80 backdrop-blur border border-white/10 flex items-center justify-center text-slate-300 hover:text-white hover:border-[#00C1D5] transition-colors">
    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.66l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z"/></svg>
  </button>
  <!-- 링크 복사 -->
  <button type="button" data-share-copy aria-label="링크 복사" class="w-11 h-11 rounded-full bg-[#09090b]/80 backdrop-blur border border-white/10 flex items-center justify-center text-slate-300 hover:text-white hover:border-[#00C1D5] transition-colors">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
  </button>
  <!-- 맨 위로 (스크롤 시 노출) -->
  <button type="button" data-to-top aria-label="맨 위로" class="w-11 h-11 rounded-full bg-[#00C1D5] text-[#09090b] flex items-center justify-center hover:bg-[#00a8ba] transition-all opacity-0 pointer-events-none">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>
  </button>
</div>
