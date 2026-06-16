<?php
/* Unreal Fest Seoul 2026 — 티켓 페이지 공통 폼 섹션 (_ticket_fields.php)
 * 본인 인증 / 기본 정보 / 소속·관심 / 티셔츠.
 * 호출 전 e(), $sess_name, $sess_tel 정의 필요 (_ticket_init.php).
 * 순서: [약관(_ticket_agree.php)] → [티켓 선택(페이지별)] → 본 파일 → [트랙(페이지별)].
 */
?>
<!-- 본인 인증 -->
<div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
  <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> 본인 인증</h2>
  <p class="text-sm text-[#a1a1aa] mb-5">본인 확인을 위해 아래 인증 방법 중 하나를 선택해 주세요. <span id="authState" class="ml-2 font-bold"></span></p>
  <div class="flex flex-wrap gap-4">
    <a href="#n" onclick="jsSubmit();return false;" class="px-6 py-3 bg-[#00C1D5] text-black font-bold hover:bg-[#00a8ba] transition-all">휴대폰 본인 인증</a>
    <a href="#n" onclick="jsSubmitPin();return false;" class="px-6 py-3 bg-transparent text-[#a1a1aa] font-bold border border-[#27272a] hover:border-white/20 hover:text-white transition-all">아이핀 본인 인증</a>
  </div>
</div>

<!-- 기본 정보 -->
<div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
  <h2 class="text-lg font-bold text-white mb-5">기본 정보</h2>
  <div class="grid md:grid-cols-3 gap-6">
    <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이름 <span class="text-[#00C1D5]">*</span></label>
      <input type="text" name="apply_user_name" id="apply_user_name" value="<?= e($sess_name) ?>" placeholder="본인인증 시 자동입력" readonly class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none text-sm"></div>
    <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일 <span class="text-[#00C1D5]">*</span></label>
      <input type="email" name="apply_user_email" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
    <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처 <span class="text-[#00C1D5]">*</span></label>
      <input type="tel" name="apply_user_phone" value="<?= e($sess_tel) ?>" placeholder="01034567890" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
  </div>
</div>

<!-- 소속 및 관심 분야 -->
<div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
  <h2 class="text-lg font-bold text-white mb-5">소속 및 관심 분야</h2>
  <div class="space-y-6">
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직업 <span class="text-[#00C1D5]">*</span></label>
        <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
          <option value="">선택해 주세요</option><option>직장인</option><option>학생</option><option>교육자/교육기관</option><option>인디 개발자</option><option>프리랜서</option>
        </select></div>
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">회사명/소속 <span class="text-[#00C1D5]">*</span></label>
        <input type="text" name="apply_user_company" placeholder="에픽게임즈" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">부서 <span class="text-[#00C1D5]">*</span></label>
        <input type="text" name="apply_user_depart" placeholder="개발팀" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직무 <span class="text-[#00C1D5]">*</span></label>
        <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
          <option value="">선택해 주세요</option><option>비주얼 아트</option><option>프로그래밍</option><option>프로덕션</option><option>엔지니어링</option><option>설계</option><option>기획</option><option>R&D</option><option>IT</option><option>감독/PD</option><option>비즈니스/마케팅</option><option>C-level</option><option>기타</option>
        </select></div>
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">산업/관심 분야 <span class="text-[#00C1D5]">*</span></label>
        <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
          <option value="">선택해 주세요</option><option>게임</option><option>영화 &amp; TV</option><option>방송 &amp; 라이브 이벤트</option><option>애니메이션</option><option>건축</option><option>자동차</option><option>제조/시뮬레이션</option><option>소프트웨어 &amp; 툴 개발</option><option>VR·AR</option><option>교육</option><option>기타</option>
        </select></div>
    </div>
  </div>
</div>

<!-- 티셔츠 -->
<div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
  <h2 class="text-lg font-bold text-white mb-2">티셔츠 사이즈 선택 <span class="text-[#00C1D5]">*</span></h2>
  <p class="text-xs text-[#71717a] mb-4">오프라인 참가자에게 지급되며 사이즈 교환은 불가합니다.</p>
  <div class="flex flex-wrap gap-3">
    <?php foreach (array('M','L','XL','XXL') as $size): ?>
    <label class="relative cursor-pointer">
      <input type="radio" name="tshirt" value="<?= $size ?>" class="peer sr-only">
      <div class="w-14 h-14 border border-[#27272a] bg-[#0e0f14] flex items-center justify-center text-sm font-bold text-[#71717a] peer-checked:border-[#00C1D5] peer-checked:bg-[rgba(0,79,89,0.2)] peer-checked:text-[#00C1D5] transition-all hover:border-white/20"><?= $size ?></div>
    </label>
    <?php endforeach; ?>
  </div>
</div>
