<?php
// Unreal Fest Seoul 2026 — 공통 footer (항상 bg-black) + app.js
// Design Ref: design doc §5.3 / dist globalUI footer.
?>
<footer id="footer" class="bg-black border-t border-white/10 mt-24">
  <div class="max-w-7xl mx-auto px-6 py-16 grid grid-cols-1 md:grid-cols-4 gap-10">
    <div class="md:col-span-1">
      <img src="public/white_logo.svg" alt="Unreal Fest Seoul" class="h-8 w-auto mb-5">
      <div class="flex items-center gap-3">
        <a href="#" aria-label="YouTube" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.6 15.6V8.4l6.2 3.6-6.2 3.6z"/></svg>
        </a>
        <a href="#" aria-label="Naver" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16.3 12.6 7.4 0H0v24h7.7V11.4L16.6 24H24V0h-7.7v12.6z"/></svg>
        </a>
        <a href="#" aria-label="KakaoTalk" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.5 3 2 6.5 2 10.8c0 2.8 1.9 5.2 4.7 6.6-.2.7-.7 2.6-.8 3-.1.5.2.5.4.4.2-.1 2.7-1.8 3.8-2.6.6.1 1.3.1 1.9.1 5.5 0 10-3.5 10-7.5S17.5 3 12 3z"/></svg>
        </a>
      </div>
    </div>
    <div>
      <h4 class="text-sm font-bold text-white mb-4">Unreal Fest Seoul</h4>
      <ul class="space-y-2.5 text-sm text-slate-400">
        <li><a href="index.php#overview" class="hover:text-white transition-colors">소개</a></li>
        <li><a href="sessions.php" class="hover:text-white transition-colors">아젠다</a></li>
        <li><a href="index.php#register" class="hover:text-white transition-colors">티켓</a></li>
        <li><a href="index.php#venue" class="hover:text-white transition-colors">행사장 안내</a></li>
      </ul>
    </div>
    <div>
      <h4 class="text-sm font-bold text-white mb-4">Epic Lounge</h4>
      <ul class="space-y-2.5 text-sm text-slate-400">
        <li><a href="https://epiclounge.co.kr" class="hover:text-white transition-colors">에픽 라운지</a></li>
        <li><a href="index.php#faq" class="hover:text-white transition-colors">자주 묻는 질문</a></li>
        <li><a href="index.php#sponsors" class="hover:text-white transition-colors">스폰서</a></li>
      </ul>
    </div>
    <div>
      <h4 class="text-sm font-bold text-white mb-4">Legal</h4>
      <ul class="space-y-2.5 text-sm text-slate-400">
        <li><a href="#" class="hover:text-white transition-colors">이용약관</a></li>
        <li><a href="#" class="hover:text-white transition-colors">개인정보처리방침</a></li>
        <li><a href="#" class="hover:text-white transition-colors">쿠키 정책</a></li>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-6 text-xs text-slate-500">
      © 2026 Epic Games, Inc. 모든 권리 보유. Unreal, Unreal Engine은 에픽게임즈의 상표입니다.
    </div>
  </div>
</footer>

<script src="assets/js/app.js" defer></script>
</body>
</html>
