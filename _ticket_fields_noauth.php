<?php
/* Unreal Fest Seoul 2026 — 티켓 페이지 폼 섹션(무인증판) (_ticket_fields_noauth.php)
 * _ticket_fields.php에서 [본인 인증] 블록 제거 + 이름 수동입력(readonly 해제).
 * 100% 무료 쿠폰 등록(ticket-coupon.php 무인증 모드) 전용. 나머지(기본정보/소속/티셔츠)는 동일.
 */
?>
<!-- 기본 정보 (본인인증 없이 수동 입력) -->
<div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
  <h2 class="text-lg font-bold text-white mb-5">기본 정보</h2>
  <div class="grid md:grid-cols-3 gap-6">
    <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이름 <span class="text-[#00C1D5]">*</span></label>
      <input type="text" name="apply_user_name" id="apply_user_name" value="<?= e($sess_name) ?>" placeholder="이름 입력" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
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
  <p class="text-xs text-[#71717a] mb-4">현장 재고 상황에 따라 선택하신 티셔츠 사이즈가 제공되지 않을 수 있습니다.</p>
  <div class="flex flex-wrap gap-3">
    <?php foreach (array('M','L','XL','XXL') as $size): ?>
    <label class="relative cursor-pointer">
      <input type="radio" name="tshirt" value="<?= $size ?>" class="peer sr-only">
      <div class="w-14 h-14 border border-[#27272a] bg-[#0e0f14] flex items-center justify-center text-sm font-bold text-[#71717a] peer-checked:border-[#00C1D5] peer-checked:bg-[rgba(0,79,89,0.2)] peer-checked:text-[#00C1D5] transition-all hover:border-white/20"><?= $size ?></div>
    </label>
    <?php endforeach; ?>
  </div>
</div>
