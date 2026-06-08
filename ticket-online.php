<?php
/* Unreal Fest Seoul 2026 — 온라인 무료 등록 (ticket-online.php)
 * 디자인: src/app/pages/TicketOnline.tsx. 로직: 2025 _applicaiton_online_ajax(무료 INSERT).
 * 결제 없음. 자체 POST 처리 → INSERT(free_yn=Y, temp_yn=N) → 완료. PHP 7.0 호환.
 */
include_once "../common.php";
require __DIR__ . '/_assets.php';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ── POST 처리: 무료 등록 INSERT ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function pp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }
    $name = pp('apply_user_name'); $email = pp('apply_user_email'); $phone = pp('apply_user_phone');
    $job = pp('apply_user_job'); $company = pp('apply_user_company');
    $grade = pp('apply_user_grade'); $ex1 = pp('apply_user_ex1');
    $agree = (pp('agree_mkt') !== '') ? '1' : '0';
    if ($name === '' || $email === '' || $phone === '') {
        exit('<script>alert("이름/이메일/연락처를 입력해주세요.");history.back();</script>');
    }
    $em = sql_real_escape_string($email); $ph = sql_real_escape_string($phone);
    $dup = sql_fetch("select count(*) as cnt from cb_unreal_2026_event2_apply where apply_user_email = '$em' and apply_temp_yn = 'N'");
    if ($dup && $dup['cnt'] > 0) { exit('<script>alert("이미 등록된 이메일입니다. 등록 확인 페이지에서 확인해주세요.");location.href="myticket.php";</script>'); }
    $pw = md5(str_replace("'","\\'",$email));
    $sql = "INSERT INTO cb_unreal_2026_event2_apply
      (apply_user_name, apply_user_email, apply_user_phone, apply_user_job, apply_user_company,
       apply_user_grade, apply_user_ex1, apply_product_code, apply_product_name, apply_product_price,
       apply_user_event_agree, apply_password, apply_temp_yn, apply_pay_status, pay_complete, free_yn, apply_reg_datetime)
      VALUES (
       '".sql_real_escape_string(strip_tags($name))."', '".sql_real_escape_string(strip_tags($email))."',
       '".sql_real_escape_string(strip_tags($phone))."', '".sql_real_escape_string(strip_tags($job))."',
       '".sql_real_escape_string(strip_tags($company))."', '".sql_real_escape_string(strip_tags($grade))."',
       '".sql_real_escape_string(strip_tags($ex1))."', 'ONLINE', '온라인 무료', '0',
       '".sql_real_escape_string($agree)."', '".sql_real_escape_string($pw)."', 'N', 10, 'Y', 'Y', now())";
    sql_query($sql);
    $row = sql_query("SELECT LAST_INSERT_ID() as idx")->fetch_array();
    header("Location: ticket-complete.php?online=1&k=".rawurlencode(base64_encode($row['idx'])));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko" class="dark"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>온라인 등록 — Unreal Fest Seoul 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.html"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.html" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<form method="post" onsubmit="return validateForm()">
<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-3xl mx-auto px-6">
    <a href="index.html#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">언리얼 페스트 2026 서울 온라인 등록</h1>
    <p class="text-[#a1a1aa] mb-10">온라인으로 Unreal Fest Seoul 2026의 주요 세션을 시청할 수 있습니다.</p>

    <div class="bg-[rgba(0,193,213,0.05)] border border-[rgba(0,193,213,0.2)] p-6 mb-6">
      <h3 class="text-base font-bold text-white mb-3">온라인 시청 안내</h3>
      <ul class="text-sm text-[#a1a1aa] space-y-1.5">
        <li>• 키노트 및 주요 세션 실시간 스트리밍</li>
        <li>• 행사 종료 후 1달 내 다시보기 제공</li>
        <li>• 발표자 동의에 따라 일부 세션만 중계될 수 있습니다</li>
        <li class="text-[#71717a]">• Q&amp;A 참여 및 현장 프로그램은 제공되지 않습니다</li>
      </ul>
    </div>

    <div class="space-y-4">
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> 약관 동의</h2>
        <div class="space-y-3">
          <label class="flex items-center gap-3 p-3 bg-[rgba(0,79,89,0.3)] border border-[rgba(0,193,213,0.3)] cursor-pointer">
            <input type="checkbox" id="agree_all" onchange="var c=this.checked;document.querySelectorAll('.agree-item').forEach(function(i){i.checked=c});" class="accent-[#00C1D5]">
            <span class="text-sm font-bold text-white">전체 동의</span>
          </label>
          <div class="h-px bg-[#27272a]"></div>
          <label class="flex items-start gap-3 px-3 py-2 cursor-pointer"><input type="checkbox" name="agree_req" class="agree-item mt-0.5 accent-[#00C1D5]"><span class="text-sm text-[#a1a1aa]">에픽 라운지 이용약관 동의 및 개인정보보호정책 확인<span class="ml-1 text-xs text-[#00C1D5]">(필수)</span></span></label>
          <label class="flex items-start gap-3 px-3 py-2 cursor-pointer"><input type="checkbox" name="agree_mkt" class="agree-item mt-0.5 accent-[#00C1D5]"><span class="text-sm text-[#a1a1aa]">광고 수신 동의<span class="ml-1 text-xs text-[#71717a]">(선택)</span></span></label>
        </div>
      </div>

      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5">기본 정보</h2>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이름 *</label><input type="text" name="apply_user_name" placeholder="홍길동" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일 *</label><input type="email" name="apply_user_email" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처 *</label><input type="tel" name="apply_user_phone" placeholder="010-1234-5678" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
        </div>
      </div>

      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5">소속 및 관심 분야</h2>
        <div class="space-y-6">
          <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직업 *</label>
              <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해주세요</option><option>직장인</option><option>학생</option><option>교육자/교육기관</option><option>인디 개발자</option><option>프리랜서</option></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">회사명/소속</label><input type="text" name="apply_user_company" placeholder="에픽게임즈" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
          <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직무</label>
              <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해주세요</option><option>비주얼 아트</option><option>프로그래밍</option><option>프로덕션</option><option>엔지니어링</option><option>기획</option><option>비즈니스/마케팅</option><option>기타</option></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">산업/관심 분야 *</label>
              <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해주세요</option><option>게임</option><option>영화 &amp; TV</option><option>방송 &amp; 라이브 이벤트</option><option>애니메이션</option><option>건축</option><option>자동차</option><option>제조/시뮬레이션</option><option>소프트웨어 &amp; 툴 개발</option><option>VR·AR</option><option>교육</option><option>기타</option></select></div>
          </div>
        </div>
      </div>

      <button type="submit" class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all">무료 등록하기 <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></button>
      <a href="index.html#register" class="block w-full text-center text-sm text-[#71717a] hover:text-white py-3 transition-colors">취소</a>
    </div>
  </div>
</div>
</form>
<?php include __DIR__ . '/_pf_footer.php'; ?>
<script>
function validateForm(){
  if(!document.querySelector('input[name="apply_user_name"]').value){alert('이름을 입력해주세요.');return false;}
  if(!document.querySelector('input[name="apply_user_email"]').value){alert('이메일을 입력해주세요.');return false;}
  if(!document.querySelector('input[name="apply_user_phone"]').value){alert('연락처를 입력해주세요.');return false;}
  if(!document.querySelector('input[name="agree_req"]').checked){alert('필수 약관에 동의해주세요.');return false;}
  return true;
}
</script>
</body></html>
