<?php
/* Unreal Fest Seoul 2026 — 오프라인 등록 (ticket.php)
 * 디자인: src/app/pages/TicketPurchase.tsx 1:1 포팅
 * 로직: 2025 application_step1(본인인증)→_applicaiton_pay_ajax(INSERT)→step2(INICIS) 포팅
 * PHP 7.0 호환. 본인인증/결제 백엔드는 apply_pay.php(다음 단계)로 연결.
 */
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$type = ($type === 'day1') ? 'DAY1' : (($type === 'day2') ? 'DAY2' : 'ALL');
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
require __DIR__ . '/_assets.php';
include_once "../common.php";
// 트랙 잔여 (오프라인 정원 — 온라인은 무제한)
$trackRemain = array();
$_tk = sql_query("SELECT name,date1 FROM 2026_event_ticket");
if ($_tk) { while ($x = $_tk->fetch_assoc()) {
    $reg = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE apply_temp_yn='N' AND apply_pay_status<>0 AND apply_track LIKE '%".sql_real_escape_string($x['name'])."%'");
    $trackRemain[$x['name']] = (int)$x['date1'] - ($reg ? (int)$reg['c'] : 0);
}}
// 본인인증 결과(세션) — ../common.php 연동 시 채워짐. 미연동 환경 폴백.
$sess_ci = isset($_SESSION['CI']) ? $_SESSION['CI'] : '';
$sess_di = isset($_SESSION['DI']) ? $_SESSION['DI'] : '';
$sess_name = isset($_SESSION['RSLT_NAME']) ? $_SESSION['RSLT_NAME'] : '';
$sess_tel = isset($_SESSION['TEL_NO']) ? $_SESSION['TEL_NO'] : '';
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>오프라인 등록 — Unreal Fest Seoul 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">

<!-- 상단 바 -->
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.html"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.html" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<form name="frm" id="frm" method="post" action="apply_pay.php" onsubmit="return validateForm()">
<input type="hidden" name="apply_ci" id="apply_ci" value="<?= e($sess_ci) ?>">
<input type="hidden" name="apply_di" id="apply_di" value="<?= e($sess_di) ?>">
<input type="hidden" name="apply_real_type" id="apply_real_type" value="">
<input type="hidden" name="apply_product_code" id="apply_product_code" value="">
<input type="hidden" name="apply_product_name" id="apply_product_name" value="">
<input type="hidden" name="apply_product_price" id="apply_product_price" value="">
<input type="hidden" name="apply_track" id="apply_track" value="">

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-7xl mx-auto px-6">
    <a href="index.html#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">언리얼 페스트 2026 서울 오프라인 등록</h1>
    <p class="text-[#a1a1aa] mb-10">아래 정보를 입력하고 티켓을 선택해주세요.</p>

    <div class="grid lg:grid-cols-12 gap-8 items-start">
      <!-- 좌측 폼 -->
      <div class="lg:col-span-7 xl:col-span-8 space-y-4">

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
              <span class="text-sm text-[#a1a1aa]">에픽 라운지 이용약관 동의 및 개인정보보호정책 확인<span class="ml-1 text-xs text-[#00C1D5]">(필수)</span></span>
            </label>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_mkt" class="agree-item mt-0.5 accent-[#00C1D5]">
              <span class="text-sm text-[#a1a1aa]">광고 수신 동의<span class="ml-1 text-xs text-[#71717a]">(선택)</span></span>
            </label>
          </div>
        </div>

        <!-- 티켓 선택 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">티켓 선택</h2>
          <div class="grid md:grid-cols-3 gap-4 mb-8" id="ticketGroup">
            <?php
            $tickets = array(
              array('code'=>'ALL','name'=>'양일권 (8.20~21)','price'=>120000,'desc'=>'Day 1 + Day 2 전체 참석','sub'=>'양일권'),
              array('code'=>'DAY1','name'=>'Day 1 단일권 (8.20)','price'=>60000,'desc'=>'Day 1만 참석','sub'=>'Day 1 단일권'),
              array('code'=>'DAY2','name'=>'Day 2 단일권 (8.21)','price'=>60000,'desc'=>'Day 2 참석','sub'=>'Day 2 단일권'),
            );
            foreach ($tickets as $t): ?>
            <label class="ticket-card relative p-5 border cursor-pointer transition-all border-[#27272a] hover:border-white/20"
                   data-code="<?= e($t['code']) ?>" data-price="<?= $t['price'] ?>" data-name="<?= e($t['name']) ?>" data-sub="<?= e($t['sub']) ?>">
              <input type="radio" name="ticket" value="<?= e($t['code']) ?>" class="sr-only" <?= $t['code']===$type?'checked':'' ?>>
              <div class="text-xl font-black text-white mb-1">₩<?= number_format($t['price']) ?></div>
              <div class="text-sm font-bold text-[#a1a1aa] mb-2"><?= e($t['name']) ?></div>
              <div class="text-xs text-[#71717a]"><?= e($t['desc']) ?></div>
              <div class="tk-check absolute top-3 right-3 hidden"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg></div>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="bg-[#111115] p-5 border border-[#27272a]">
            <h4 class="text-sm font-bold text-[#a1a1aa] mb-3">혜택</h4>
            <div class="grid sm:grid-cols-2 gap-2 text-sm text-[#a1a1aa]">
              <?php foreach (array('전체 세션 참여','한정판 굿즈 제공','Q&A 참여','전시 및 체험존 이용','이벤트 및 경품 참여') as $b): ?>
              <div class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#00C1D5]"></span><?= e($b) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- 본인 인증 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> 본인 인증</h2>
          <p class="text-sm text-[#a1a1aa] mb-5">본인 확인을 위해 아래 인증 방법 중 하나를 선택해주세요. <span id="authState" class="ml-2 font-bold"></span></p>
          <div class="flex flex-wrap gap-4">
            <a href="#n" onclick="jsSubmit();return false;" class="px-6 py-3 bg-[#00C1D5] text-black font-bold hover:bg-[#00a8ba] transition-all">휴대폰 본인 인증</a>
            <a href="#n" onclick="jsSubmitPin();return false;" class="px-6 py-3 bg-transparent text-[#a1a1aa] font-bold border border-[#27272a] hover:border-white/20 hover:text-white transition-all">아이핀 본인 인증</a>
          </div>
        </div>

        <!-- 기본 정보 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">기본 정보</h2>
          <div class="grid md:grid-cols-3 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이름 *</label>
              <input type="text" name="apply_user_name" id="apply_user_name" value="<?= e($sess_name) ?>" placeholder="본인인증 시 자동입력" readonly class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일 *</label>
              <input type="email" name="apply_user_email" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처 *</label>
              <input type="tel" name="apply_user_phone" value="<?= e($sess_tel) ?>" placeholder="010-1234-5678" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
        </div>

        <!-- 소속 및 관심 분야 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">소속 및 관심 분야</h2>
          <div class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직업 *</label>
                <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">선택해주세요</option><option>직장인</option><option>학생</option><option>교육자/교육기관</option><option>인디 개발자</option><option>프리랜서</option>
                </select></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">회사명/소속 *</label>
                <input type="text" name="apply_user_company" placeholder="에픽게임즈" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">부서</label>
                <input type="text" name="apply_user_depart" placeholder="개발팀" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직무 *</label>
                <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">선택해주세요</option><option>비주얼 아트</option><option>프로그래밍</option><option>프로덕션</option><option>엔지니어링</option><option>설계</option><option>기획</option><option>R&D</option><option>IT</option><option>감독/PD</option><option>비즈니스/마케팅</option><option>C-level</option><option>기타</option>
                </select></div>
              <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">산업/관심 분야 *</label>
                <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">
                  <option value="">선택해주세요</option><option>게임</option><option>영화 &amp; TV</option><option>방송 &amp; 라이브 이벤트</option><option>애니메이션</option><option>건축</option><option>자동차</option><option>제조/시뮬레이션</option><option>소프트웨어 &amp; 툴 개발</option><option>VR·AR</option><option>교육</option><option>기타</option>
                </select></div>
            </div>
          </div>
        </div>

        <!-- 티셔츠 + 트랙 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <div class="mb-8">
            <h2 class="text-lg font-bold text-white mb-2">티셔츠 사이즈 선택 *</h2>
            <p class="text-xs text-[#71717a] mb-4">오프라인 참가자에게 티셔츠 등이 포함된 키트가 지급되며 사이즈 교환은 불가합니다.</p>
            <div class="flex flex-wrap gap-3">
              <?php foreach (array('M','L','XL','3XL') as $size): ?>
              <label class="relative cursor-pointer">
                <input type="radio" name="tshirt" value="<?= $size ?>" class="peer sr-only">
                <div class="w-14 h-14 border border-[#27272a] bg-[#0e0f14] flex items-center justify-center text-sm font-bold text-[#71717a] peer-checked:border-[#00C1D5] peer-checked:bg-[rgba(0,79,89,0.2)] peer-checked:text-[#00C1D5] transition-all hover:border-white/20"><?= $size ?></div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div id="day1box" class="mb-6">
            <h3 class="text-sm font-bold text-white mb-3">Day 1. 8월 20일(목) 트랙 선택 *</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
              <?php
              $d1 = array('DAY1_TR1'=>'게임: 프로그래밍','DAY1_TR2'=>'게임: 아트','DAY1_TR3'=>'미디어 & 엔터테인먼트','DAY1_TR4'=>'산업 & 시뮬레이션');
              foreach ($d1 as $v=>$l): $full = isset($trackRemain[$v]) && $trackRemain[$v] <= 0; ?>
              <label class="trk <?= $full?'trk-full opacity-40 cursor-not-allowed':'cursor-pointer hover:border-white/20' ?> p-3 border text-center text-sm font-medium transition-all border-[#27272a] text-[#71717a]">
                <input type="radio" name="day1track" value="<?= $v ?>" class="sr-only" <?= $full?'disabled':'' ?>><?= e($l) ?><?php if($full): ?> <span class="text-[#ff8674] text-xs">(마감)</span><?php endif; ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div id="day2box">
            <h3 class="text-sm font-bold text-white mb-3">Day 2. 8월 21일(금) 트랙 선택 *</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
              <?php
              $d2 = array('DAY2_TR1'=>'게임: 프로그래밍','DAY2_TR2'=>'게임: 아트','DAY2_TR3'=>'미디어 & 엔터테인먼트','DAY2_TR4'=>'산업 & 시뮬레이션');
              foreach ($d2 as $v=>$l): $full = isset($trackRemain[$v]) && $trackRemain[$v] <= 0; ?>
              <label class="trk <?= $full?'trk-full opacity-40 cursor-not-allowed':'cursor-pointer hover:border-white/20' ?> p-3 border text-center text-sm font-medium transition-all border-[#27272a] text-[#71717a]">
                <input type="radio" name="day2track" value="<?= $v ?>" class="sr-only" <?= $full?'disabled':'' ?>><?= e($l) ?><?php if($full): ?> <span class="text-[#ff8674] text-xs">(마감)</span><?php endif; ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- 우측 결제 사이드바 -->
      <div class="lg:col-span-5 xl:col-span-4 self-start sticky top-28">
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 lg:p-8 space-y-6">
          <h3 class="text-lg font-bold text-white">주문 요약</h3>
          <div class="pb-5 border-b border-[#27272a]">
            <div class="text-[#00C1D5] font-bold text-sm mb-1" id="sumSub">양일권</div>
            <div class="flex justify-between items-center"><span class="text-sm text-[#a1a1aa]">티켓 금액</span><span class="text-sm text-[#a1a1aa]" id="sumPrice">₩120,000</span></div>
            <div class="flex justify-between items-center mt-1"><span class="text-sm text-[#a1a1aa]">부가세 (VAT)</span><span class="text-sm text-[#a1a1aa]">포함</span></div>
          </div>
          <div class="flex justify-between items-end"><span class="text-[#a1a1aa] font-medium">총 결제 금액</span><span class="text-3xl font-black text-white" id="sumTotal">₩120,000</span></div>
          <div class="space-y-2">
            <label class="flex items-center gap-3 p-3 border border-[#00C1D5] bg-[rgba(0,79,89,0.2)] cursor-pointer"><input type="radio" name="payment" value="Card" checked class="accent-[#00C1D5] w-4 h-4"><svg class="w-4 h-4 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg><span class="text-white font-medium text-sm">신용/체크카드</span></label>
            <label class="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20"><input type="radio" name="payment" value="kakaopay" class="accent-[#00C1D5] w-4 h-4"><span class="w-4 h-4 rounded bg-[#FEE500] text-black flex items-center justify-center font-black text-[8px]">P</span><span class="text-white font-medium text-sm">카카오페이</span></label>
            <label class="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20"><input type="radio" name="payment" value="naverpay" class="accent-[#00C1D5] w-4 h-4"><span class="w-4 h-4 rounded bg-[#03C75A] text-white flex items-center justify-center font-bold text-[10px]">N</span><span class="text-white font-medium text-sm">네이버페이</span></label>
            <label class="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20"><input type="radio" name="payment" value="tosspay" class="accent-[#00C1D5] w-4 h-4"><span class="w-4 h-4 rounded bg-[#0064FF] text-white flex items-center justify-center font-bold text-[8px]">T</span><span class="text-white font-medium text-sm">토스페이</span></label>
          </div>
          <div class="text-xs text-[#71717a] space-y-1"><p>• 8월 13일 23:59까지 환불 가능</p><p>• 이후 취소/노쇼: 환불 불가</p></div>
          <label class="flex items-start gap-2 cursor-pointer"><input type="checkbox" id="agree_refund" class="mt-0.5 accent-[#00C1D5]"><span class="text-xs text-[#a1a1aa]">취소/환불 규정에 동의합니다. (필수)</span></label>
          <button type="submit" class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all">
            <span id="payBtnLabel">₩120,000 결제하기</span>
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          </button>
          <a href="myticket.php" class="block w-full text-center text-sm text-[#71717a] hover:text-white py-3 transition-colors">등록 확인 / 취소</a>
        </div>
      </div>
    </div>
  </div>
</div>
</form>

<?php include __DIR__ . '/_pf_footer.php'; ?>

<!-- 본인인증 팝업 타깃용 (2025 real/ 재사용) -->
<form name="form1" id="form1" method="post"></form>
<!-- 서버 phone_popup3.php/ipin_popup3.php가 결과를 주입하는 폼 (14개 필드 전부 필요) -->
<form name="kcbResultForm" id="kcbResultForm">
  <input type="hidden" name="CP_CD" value="">
  <input type="hidden" name="TX_SEQ_NO" value="">
  <input type="hidden" name="RSLT_CD" value="">
  <input type="hidden" name="RSLT_MSG" value="">
  <input type="hidden" name="RETURN_MSG" value="">
  <input type="hidden" name="RSLT_NAME" value="">
  <input type="hidden" name="RSLT_BIRTHDAY" value="">
  <input type="hidden" name="RSLT_SEX_CD" value="">
  <input type="hidden" name="RSLT_NTV_FRNR_CD" value="">
  <input type="hidden" name="DI" value="">
  <input type="hidden" name="CI" value="">
  <input type="hidden" name="CI_UPDATE" value="">
  <input type="hidden" name="TEL_COM_CD" value="">
  <input type="hidden" name="TEL_NO" value="">
</form>

<script>
// 티켓 선택 → 요약/가격/트랙 표시 갱신 (TicketPurchase.tsx state 복제)
var tickets = {ALL:{price:120000,sub:'양일권'},DAY1:{price:60000,sub:'Day 1 단일권'},DAY2:{price:60000,sub:'Day 2 단일권'}};
function won(n){return '₩'+n.toLocaleString();}
function selectTicket(code){
  document.querySelectorAll('.ticket-card').forEach(function(c){
    var on = c.getAttribute('data-code')===code;
    c.classList.toggle('border-[#00C1D5]',on); c.classList.toggle('bg-[rgba(0,79,89,0.2)]',on);
    c.classList.toggle('border-[#27272a]',!on);
    c.querySelector('input').checked = on;
    var chk=c.querySelector('.tk-check'); if(chk) chk.classList.toggle('hidden',!on);
  });
  var t=tickets[code];
  document.getElementById('sumSub').textContent=t.sub;
  document.getElementById('sumPrice').textContent=won(t.price);
  document.getElementById('sumTotal').textContent=won(t.price);
  document.getElementById('payBtnLabel').textContent=won(t.price)+' 결제하기';
  document.getElementById('day1box').style.display=(code==='ALL'||code==='DAY1')?'':'none';
  document.getElementById('day2box').style.display=(code==='ALL'||code==='DAY2')?'':'none';
  var pcode=(code==='ALL'?'NORMAL_ALL':(code==='DAY1'?'NORMAL_20':'NORMAL_21'));
  document.getElementById('apply_product_code').value=pcode;
  document.getElementById('apply_product_name').value=t.sub;
  document.getElementById('apply_product_price').value=t.price;
}
document.querySelectorAll('.ticket-card').forEach(function(c){
  c.addEventListener('click',function(){selectTicket(c.getAttribute('data-code'));});
});
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
function toggleAllAgree(cb){ document.querySelectorAll('.agree-item').forEach(function(i){i.checked=cb.checked;}); }
function checkAgree(){
  if(!document.querySelector('input[name="agree_req"]').checked){
    alert('본인인증 전에 필수 약관에 동의해주세요.');
    return false;
  }
  return true;
}
function jsSubmit(){
  if(!checkAgree()) return;
  document.getElementById('apply_real_type').value='tel';
  var f=document.getElementById('form1');
  f.action='../real/phone_popup2.php'; f.target='auth_popup';
  window.open('about:blank','auth_popup','width=430,height=640,scrollbars=yes');
  f.submit();
}
function jsSubmitPin(){
  if(!checkAgree()) return;
  document.getElementById('apply_real_type').value='ipin';
  var f=document.getElementById('form1');
  f.action='../real/ipin_popup2.php'; f.target='kcbPop';
  window.open('about:blank','kcbPop','width=450,height=550,scrollbars=yes');
  f.submit();
}
// 본인인증: phone_popup3.php가 opener.document.frm.apply_ci/apply_user_name/apply_user_phone 주입.
// 팝업이 직접 onAuthDone을 못 부르므로, 복귀 focus + 1초 폴링으로 인증상태 반영.
function refreshAuth(){
  var ci=document.getElementById('apply_ci').value;
  if(ci){
    var as=document.getElementById('authState');
    if(as){ as.textContent='✓ 인증 완료'; as.className='ml-2 font-bold text-[#00C1D5]'; }
  }
}
// 서버 popup3가 kcbResultForm에 결과 주입 후 호출하는 훅.
window.handleKcbAuthResult = function(){
  var f=document.forms['kcbResultForm'];
  if(!f) return;
  var rslt=f.RSLT_CD?f.RSLT_CD.value:'';
  if(rslt && rslt!=='B000' && rslt!=='T000'){ // 실패
    alert('본인인증에 실패했습니다. 다시 시도해주세요.'); return;
  }
  var name=f.RSLT_NAME?f.RSLT_NAME.value:'';
  var ci=f.CI?f.CI.value:'';
  var di=f.DI?f.DI.value:'';
  var tel=f.TEL_NO?f.TEL_NO.value:'';
  document.getElementById('apply_ci').value=ci;
  document.getElementById('apply_di').value=di;
  var nameEl=document.querySelector('input[name="apply_user_name"]');
  if(nameEl) nameEl.value=name;
  var telEl=document.querySelector('input[name="apply_user_phone"]');
  if(telEl && tel) telEl.value=tel;
  refreshAuth();
  window._justAuthed=true;       // 인증 완료 플래그
  setTimeout(focusEmail, 300);   // 폴백 (focus 이벤트가 늦거나 안 오는 경우)
};
// 인증 직후 이메일 입력칸으로 자동 포커스/스크롤
function focusEmail(){
  if(!window._justAuthed) return;
  window._justAuthed=false;
  var em=document.querySelector('input[name="apply_user_email"]');
  if(em){ em.scrollIntoView({behavior:'smooth', block:'center'}); em.focus(); }
}
// 팝업이 닫히고 본 창으로 복귀하면 상태 갱신 + 이메일 포커스
window.addEventListener('focus', function(){ refreshAuth(); focusEmail(); });
setInterval(refreshAuth, 1000);
refreshAuth();
function collectTrack(){
  var arr=[];
  var d1=document.querySelector('input[name="day1track"]:checked');
  var d2=document.querySelector('input[name="day2track"]:checked');
  if(d1&&document.getElementById('day1box').style.display!=='none')arr.push(d1.value);
  if(d2&&document.getElementById('day2box').style.display!=='none')arr.push(d2.value);
  document.getElementById('apply_track').value=arr.join(',');
}
function validateForm(){
  collectTrack();
  if(!document.getElementById('apply_ci').value){alert('본인인증이 필요합니다.');return false;}
  if(!document.querySelector('input[name="apply_user_email"]').value){alert('이메일을 입력해주세요.');return false;}
  if(!document.querySelector('input[name="apply_user_phone"]').value){alert('연락처를 입력해주세요.');return false;}
  if(!document.querySelector('input[name="apply_user_company"]').value){alert('회사명/소속을 입력해주세요.');return false;}
  if(!document.querySelector('input[name="agree_req"]').checked){alert('필수 약관에 동의해주세요.');return false;}
  if(!document.getElementById('agree_refund').checked){alert('취소/환불 규정에 동의해주세요.');return false;}
  return true;
}
selectTicket(<?= json_encode($type) ?>);
</script>
</body>
</html>
