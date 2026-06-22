<?php
/* Unreal Fest Seoul 2026 — 단체 등록 (ticket-group.php) [Phase 1: 입력 폼 / 개개인별 선택]
 * 대표자(본인인증+기존 등록필드) + 멤버(이름·연락처·직무·관심분야 + 티켓·트랙·티셔츠).
 * 단일 컬럼(와이드), 요약 최하단. 제출 → ticket-group-confirm.php. 공통: _ticket_init.php / ticket.js / group.js
 */
require __DIR__ . '/_ticket_init.php';

$GDISC = ufs_group_discount(); // 단체 할인율(%)
$TKT = array(
  array('code'=>'NORMAL_ALL','label'=>'양일권 (8.20~21)','price'=>ufs_group_price('NORMAL_ALL'),'days'=>'1,2'),
  array('code'=>'NORMAL_20', 'label'=>'1일권 · Day 1',   'price'=>ufs_group_price('NORMAL_20'), 'days'=>'1'),
  array('code'=>'NORMAL_21', 'label'=>'1일권 · Day 2',   'price'=>ufs_group_price('NORMAL_21'), 'days'=>'2'),
);
$JOBS  = array('직장인','학생','교육자/교육기관','인디 개발자','프리랜서');
$GRADES= array('비주얼 아트','프로그래밍','프로덕션','엔지니어링','설계','기획','R&D','IT','감독/PD','비즈니스/마케팅','C-level','기타');
$EX1S  = array('게임','영화 & TV','방송 & 라이브 이벤트','애니메이션','건축','자동차','제조/시뮬레이션','소프트웨어 & 툴 개발','VR·AR','교육','기타');
function ufs_opts($arr){ $s=''; foreach($arr as $o){ $s.='<option>'.htmlspecialchars($o,ENT_QUOTES,'UTF-8').'</option>'; } return $s; }

$SEL_CLS = 'w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none';

/* 참석 선택 한 줄: 티켓 · Day1트랙 · Day2트랙 · 티셔츠 (4컬럼). $allowNone=대표자 '미참가' 옵션 */
function ufs_attend_row($nTicket, $nD1, $nD2, $nTshirt, $TKT, $TR, $allowNone = false) {
  global $SEL_CLS;
  echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">';
  // 티켓
  echo '<div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">티켓 <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nTicket).'" data-pick-ticket class="'.$SEL_CLS.'"><option value="">티켓 선택</option>';
  foreach ($TKT as $t) echo '<option value="'.e($t['code']).'" data-price="'.(int)$t['price'].'" data-days="'.e($t['days']).'">'.e($t['label']).' (₩'.number_format($t['price']).')</option>';
  if ($allowNone) echo '<option value="NONE" data-price="0" data-days="">결제만 (비참석 · 등록 인원 제외)</option>';
  echo '</select></div>';
  // Day1
  echo '<div class="space-y-2" data-track-wrap data-day="1"><label class="text-sm font-medium text-[#a1a1aa]">Day1 트랙 <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nD1).'" data-day="1" class="'.$SEL_CLS.'"><option value="">Day1 트랙</option>';
  foreach ($TR[1] as $v=>$l) echo '<option value="'.e($v).'">'.e($l).'</option>';
  echo '</select></div>';
  // Day2
  echo '<div class="space-y-2" data-track-wrap data-day="2"><label class="text-sm font-medium text-[#a1a1aa]">Day2 트랙 <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nD2).'" data-day="2" class="'.$SEL_CLS.'"><option value="">Day2 트랙</option>';
  foreach ($TR[2] as $v=>$l) echo '<option value="'.e($v).'">'.e($l).'</option>';
  echo '</select></div>';
  // 티셔츠
  echo '<div class="space-y-2" data-tshirt-wrap><label class="text-sm font-medium text-[#a1a1aa]">티셔츠 <span class="text-[#00C1D5]">*</span></label>';
  echo '<div class="flex flex-wrap gap-2" data-pick-tshirt>';
  foreach (array('M','L','XL','XXL') as $s) {
    echo '<label class="relative cursor-pointer"><input type="radio" name="'.e($nTshirt).'" value="'.$s.'" class="peer sr-only">';
    echo '<div class="w-12 h-12 border border-[#27272a] bg-[#0e0f14] flex items-center justify-center text-sm font-bold text-[#71717a] peer-checked:border-[#00C1D5] peer-checked:bg-[rgba(0,79,89,0.2)] peer-checked:text-[#00C1D5] transition-all hover:border-white/20">'.$s.'</div></label>';
  }
  echo '</div></div>';
  echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>단체 등록 — Unreal Fest Seoul 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}
/* 빌드된 Tailwind에 없는 5컬럼 — 멤버 한 줄(이름·연락처·직무·관심분야·티켓) */
@media (min-width:1024px){ .gcols5{ grid-template-columns:repeat(5,minmax(0,1fr))!important } }
</style>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">

<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<form name="frm" id="frm" method="post" action="ticket-group-confirm.php" onsubmit="return gValidate()">
<input type="hidden" name="apply_ci" id="apply_ci" value="<?= e($sess_ci) ?>">
<input type="hidden" name="apply_di" id="apply_di" value="<?= e($sess_di) ?>">
<input type="hidden" name="apply_real_type" id="apply_real_type" value="">
<input type="hidden" name="group_paymethod" id="group_paymethod" value="card">

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-5xl mx-auto px-6">
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">단체 등록</h1>
    <p class="text-[#a1a1aa] mb-4">5인 이상 단체로 등록하실 수 있습니다. 대표자 1인은 본인 인증 후 정보를 입력하고, 함께 참석하실 인원(최소 4인, 최대 29인 추가)을 작성해 주세요. 티켓·트랙·티셔츠는 인원별로 각각 선택합니다.</p>
    <?php if ($GDISC > 0): ?>
    <div class="inline-flex items-center gap-2 mb-10 px-4 py-2 bg-[rgba(0,79,89,0.2)] border border-[#00C1D5]/40 text-[#00C1D5] text-sm font-bold">단체 할인 <?= (int)$GDISC ?>% 적용 (정상가 기준)</div>
    <?php else: ?><div class="mb-10"></div><?php endif; ?>

    <div class="space-y-4">

      <?php include __DIR__ . '/_ticket_agree.php'; ?>

      <!-- 대표자 본인 인증 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> 대표자 본인 인증</h2>
        <p class="text-sm text-[#a1a1aa] mb-5">대표자 본인 확인을 위해 인증해 주세요. <span id="authState" class="ml-2 font-bold"></span></p>
        <div class="flex flex-wrap gap-4">
          <a href="#n" onclick="jsSubmit();return false;" class="px-6 py-3 bg-[#00C1D5] text-black font-bold hover:bg-[#00a8ba] transition-all">휴대폰 본인 인증</a>
          <a href="#n" onclick="jsSubmitPin();return false;" class="px-6 py-3 bg-transparent text-[#a1a1aa] font-bold border border-[#27272a] hover:border-white/20 hover:text-white transition-all">아이핀 본인 인증</a>
        </div>
      </div>

      <!-- 대표자 정보 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5">대표자 정보</h2>
        <div class="grid md:grid-cols-3 gap-6 mb-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이름 <span class="text-[#00C1D5]">*</span></label>
            <input type="text" name="apply_user_name" id="apply_user_name" value="<?= e($sess_name) ?>" placeholder="본인인증 시 자동입력" readonly class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일 <span class="text-[#00C1D5]">*</span></label>
            <input type="email" name="apply_user_email" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처 <span class="text-[#00C1D5]">*</span></label>
            <input type="tel" name="apply_user_phone" value="<?= e($sess_tel) ?>" placeholder="01034567890" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
        </div>
        <div class="grid md:grid-cols-2 gap-6 mb-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직업 <span class="text-[#00C1D5]">*</span></label>
            <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해 주세요</option><?= ufs_opts($JOBS) ?></select></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">회사명/소속 <span class="text-[#00C1D5]">*</span></label>
            <input type="text" name="apply_user_company" placeholder="에픽게임즈" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">부서 <span class="text-[#00C1D5]">*</span></label>
            <input type="text" name="apply_user_depart" placeholder="개발팀" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직무 <span class="text-[#00C1D5]">*</span></label>
            <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해 주세요</option><?= ufs_opts($GRADES) ?></select></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">산업/관심 분야 <span class="text-[#00C1D5]">*</span></label>
            <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해 주세요</option><?= ufs_opts($EX1S) ?></select></div>
        </div>
      </div>

      <!-- 참석자: 대표자 본인 선택 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8" data-card data-rep>
        <div class="text-sm font-bold text-[#00C1D5] mb-4">1. 대표자 참석 선택</div>
        <?php ufs_attend_row('rep_ticket','rep_day1','rep_day2','rep_tshirt',$TKT,$UFS_TRACKS,true); ?>
      </div>

      <!-- 멤버 명단 -->
      <div class="flex items-center justify-between mt-2 mb-1 px-1">
        <div class="text-sm font-bold text-white">함께 참석하는 인원 <span id="gMemCount" class="text-[#00C1D5]">(0명)</span></div>
        <div class="flex gap-2" data-grp-tools>
          <a href="downloads/group_template.csv" download class="px-3 py-2 text-xs font-bold border border-[#27272a] text-[#a1a1aa] hover:border-white/20 hover:text-white transition-all">양식 다운로드</a>
          <label class="px-3 py-2 text-xs font-bold border border-[#27272a] text-[#a1a1aa] hover:border-white/20 hover:text-white transition-all cursor-pointer">양식 업로드<input type="file" id="gUpload" accept=".csv" class="hidden"></label>
        </div>
      </div>
      <p class="text-xs text-[#71717a] mb-3 px-1">※ 함께 참석하는 인원의 <b class="text-[#a1a1aa]">직업·회사명</b>은 대표자와 동일하게 자동 등록됩니다.</p>
      <div id="gMembers" class="space-y-4"></div>
      <button type="button" id="gAddBtn" class="mt-4 w-full py-3 border border-dashed border-[#27272a] text-[#a1a1aa] hover:border-[#00C1D5] hover:text-[#00C1D5] transition-all text-sm font-bold">+ 인원 추가</button>

      <!-- 결제 수단 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mt-4">
        <h2 class="text-lg font-bold text-white mb-5">결제 수단</h2>
        <div class="space-y-3" id="gPay">
          <label class="gpay flex items-center gap-3 p-4 border border-[#00C1D5] bg-[rgba(0,79,89,0.2)] cursor-pointer" data-pay="card">
            <input type="radio" name="gpay" value="card" checked class="accent-[#00C1D5] w-4 h-4">
            <span class="text-white font-medium text-sm">신용카드</span><span class="text-xs text-[#71717a]">PG 결제창에서 일괄 결제</span></label>
          <label class="gpay flex items-center gap-3 p-4 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20" data-pay="bank">
            <input type="radio" name="gpay" value="bank" class="accent-[#00C1D5] w-4 h-4">
            <span class="text-white font-medium text-sm">무통장 입금</span><span class="text-xs text-[#71717a]">계좌·금액·기한 안내(LMS) 후 입금</span></label>
        </div>
      </div>

      <!-- 등록 요약 (최하단) -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mt-4">
        <h2 class="text-lg font-bold text-white mb-5">등록 요약</h2>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between items-center gap-4"><span class="text-[#71717a]">총 인원 (대표자 포함)</span><span id="sumPeople" class="font-bold text-right">1명</span></div>
          <div class="flex justify-between items-center gap-4"><span class="text-[#71717a]">양일권</span><span id="sumAll" class="font-bold text-right">0명</span></div>
          <div class="flex justify-between items-center gap-4"><span class="text-[#71717a]">1일권 (합계)</span><span id="sumDay" class="font-bold text-right">0명</span></div>
        </div>
        <div class="border-t border-[#27272a] mt-4 pt-4 flex justify-between gap-4 items-end"><span class="text-[#71717a]">총 결제 금액</span><span id="sumTotal" class="text-3xl font-black text-[#00C1D5]">₩0</span></div>
        <button type="submit" class="mt-6 w-full py-4 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors">등록 정보 확인</button>
        <p class="text-xs text-[#71717a] mt-3 leading-relaxed">대표자 본인 인증 후 진행됩니다. 무통장 입금 선택 시 대표자 연락처로 계좌·금액·입금 기한이 안내됩니다.</p>
      </div>

    </div>
  </div>
</div>
</form>

<!-- 멤버 카드 템플릿 (JS가 복제; __I__ = 인덱스 토큰) -->
<template id="memTpl">
  <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8" data-card data-gm-row>
    <div class="flex items-center justify-between mb-4">
      <span class="text-sm font-bold text-[#00C1D5]" data-gm-no></span>
      <button type="button" class="text-[#71717a] hover:text-[#ff8674] text-xl leading-none" data-gm-del title="삭제">&times;</button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이름 <span class="text-[#00C1D5]">*</span></label>
        <input type="text" name="member_name[__I__]" placeholder="이름" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일 <span class="text-[#00C1D5]">*</span></label>
        <input type="email" name="member_email[__I__]" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처 <span class="text-[#00C1D5]">*</span></label>
        <input type="tel" name="member_phone[__I__]" placeholder="01012345678" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">부서 <span class="text-[#00C1D5]">*</span></label>
        <input type="text" name="member_depart[__I__]" placeholder="개발팀" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직무 <span class="text-[#00C1D5]">*</span></label>
        <select name="member_grade[__I__]" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택</option><?= ufs_opts($GRADES) ?></select></div>
      <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">관심 분야 <span class="text-[#00C1D5]">*</span></label>
        <select name="member_ex1[__I__]" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택</option><?= ufs_opts($EX1S) ?></select></div>
    </div>
    <?php ufs_attend_row('member_ticket[__I__]','member_day1[__I__]','member_day2[__I__]','member_tshirt[__I__]',$TKT,$UFS_TRACKS); ?>
  </div>
</template>

<!-- 본인인증 팝업 타깃 (대표자) -->
<form name="form1" id="form1" method="post"></form>
<form name="kcbResultForm" id="kcbResultForm">
  <input type="hidden" name="CP_CD" value=""><input type="hidden" name="TX_SEQ_NO" value=""><input type="hidden" name="RSLT_CD" value="">
  <input type="hidden" name="RSLT_MSG" value=""><input type="hidden" name="RETURN_MSG" value=""><input type="hidden" name="RSLT_NAME" value="">
  <input type="hidden" name="RSLT_BIRTHDAY" value=""><input type="hidden" name="RSLT_SEX_CD" value=""><input type="hidden" name="RSLT_NTV_FRNR_CD" value="">
  <input type="hidden" name="DI" value=""><input type="hidden" name="CI" value=""><input type="hidden" name="CI_UPDATE" value="">
  <input type="hidden" name="TEL_COM_CD" value=""><input type="hidden" name="TEL_NO" value="">
</form>

<script>
window.UFS_MIN_MEMBERS = 4;
window.UFS_MAX_TOTAL   = 30;
</script>
<script src="<?= asset_v('assets/js/ticket.js') ?>"></script>
<script src="<?= asset_v('assets/js/group.js') ?>"></script>
</body>
</html>
