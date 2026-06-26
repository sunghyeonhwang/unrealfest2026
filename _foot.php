<?php
// Unreal Fest Seoul 2026 — 공통 Footer + 스크립트. React Footer.tsx 1:1.
// _head.php 에서 연 <main> 을 여기서 닫는다. $is_home/$nav 는 _head.php 에서 설정됨.
if (!isset($nav)) { $nav = ufs_nav_links(); }
if (!isset($is_home)) { $is_home = false; }
?>
</main>

<!-- ===== Footer ===== -->
<footer id="footer" class="bg-black pt-20 pb-10 border-t border-white/10 text-sm">
  <div class="max-w-7xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">

      <!-- Col 1: logo + socials + 사업자 정보 -->
      <div class="col-span-1 md:col-span-2">
        <div class="flex items-center gap-2 mb-8 group">
          <img src="./white_logo.svg" alt="Unreal Fest 2026" class="h-8">
        </div>
        <div class="flex gap-4">
          <a href="https://www.youtube.com/@unrealenginekr" target="_blank" rel="noopener noreferrer" aria-label="YouTube" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:border-[#00C1D5] transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>
          </a>
          <a href="https://cafe.naver.com/unrealenginekr" target="_blank" rel="noopener noreferrer" aria-label="Naver Cafe" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:border-[#00C1D5] transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16.273 12.845L7.376 0H0v24h7.727V11.155L16.624 24H24V0h-7.727v12.845z"/></svg>
          </a>
          <a href="https://pf.kakao.com/_xfdmNb" target="_blank" rel="noopener noreferrer" aria-label="KakaoTalk" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:border-[#00C1D5] transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.477 3 2 6.463 2 10.691c0 2.818 1.867 5.29 4.682 6.678-.177.63-.64 2.284-.733 2.64-.117.443.162.436.34.317.14-.093 2.23-1.516 3.132-2.132.52.078 1.053.12 1.579.12 5.523 0 10-3.463 10-7.623C22 6.463 17.523 3 12 3z"/></svg>
          </a>
        </div>
        <!-- 사업자 정보 (전자상거래법 표시) -->
        <div class="mt-8 text-xs leading-relaxed text-slate-500 space-y-1">
          <p>법인명(상호): 주식회사 그리프 &nbsp;·&nbsp; 대표이사: 황성현 &nbsp;·&nbsp; 사업자등록번호: 859-88-00263 <a href="https://www.ftc.go.kr/bizCommPop.do?wrkr_no=8598800263" target="_blank" rel="noopener noreferrer" class="underline hover:text-white transition-colors">확인</a></p>
          <p>통신판매업: 2018-서울송파-0571 &nbsp;·&nbsp; 개인정보보호책임자: 오승훈</p>
          <p>주소: 서울 성동구 광나루로8길 31, SK V1 CENTER2, 1102-1103호</p>
          <p>고객센터: 02-326-3701 &nbsp;·&nbsp; 대표 이메일: <a href="mailto:info@epiclounge.co.kr" class="underline hover:text-white transition-colors">info@epiclounge.co.kr</a></p>
        </div>
      </div>

      <!-- Col 2: Unreal Fest Seoul nav -->
      <div>
        <h4 class="text-white font-bold mb-6 tracking-widest text-xs uppercase">Unreal Fest Seoul</h4>
        <ul class="space-y-4 text-slate-400">
          <?php foreach ($nav as $n): ?>
            <li>
              <?php if ($is_home): ?>
                <button type="button" data-scroll="<?= e($n['id']) ?>" class="hover:text-[#00C1D5] transition-colors"><?= e($n['name']) ?></button>
              <?php else: ?>
                <a href="index.php#<?= e($n['id']) ?>" class="hover:text-[#00C1D5] transition-colors"><?= e($n['name']) ?></a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Col 3: Epic Lounge -->
      <div>
        <h4 class="text-white font-bold mb-6 tracking-widest text-xs uppercase">Epic Lounge</h4>
        <ul class="space-y-4 text-slate-400">
          <?php foreach (ufs_footer_epic_links() as $label => $url): ?>
            <li>
              <a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors flex items-center gap-1 group">
                <?= e($label) ?>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 opacity-50 group-hover:opacity-100"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>

    <!-- Bottom bar -->
    <div class="pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-4 text-slate-500">
      <p>© 2026 Epic Games, Inc. All Rights Reserved. Unreal 및 그 로고의 저작권은 Epic Games, Inc. 에 있으며, 이는 미국 및 그 외 국가에 모두 해당됩니다.</p>
      <div class="flex gap-6">
        <?php foreach (ufs_footer_legal_links() as $label => $url): ?>
          <a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</footer>

<?php include __DIR__ . '/_sns_fixed.php'; ?>

<script src="<?= e(asset_v('assets/js/app.js')) ?>" defer></script>
</body>
</html>
