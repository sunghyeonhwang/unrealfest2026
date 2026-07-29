<?php
/* Unreal Fest Seoul 2026 — 온라인 무료 등록 (ticket-online.php)
 * 로직: 2025 _applicaiton_online_ajax(무료 INSERT) + KCB 본인인증.
 * KO/EN 언어 토글(?lang=en|ko · 쿠키 ufs_ol_lang · 디폴트 ko). 본인인증(KCB)은 유지 — UI만 영문화.
 */
include_once "../common.php";
require __DIR__ . '/_assets.php';
require_once __DIR__ . '/_sms.php';   // 온라인 등록완료 안내 SMS
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

// ── 언어 (GET ?lang > 쿠키 > ko) ──
$lang = 'ko';
if (isset($_GET['lang'])) { $lang = ($_GET['lang'] === 'en') ? 'en' : 'ko'; @setcookie('ufs_ol_lang', $lang, 0, '/'); $_COOKIE['ufs_ol_lang'] = $lang; }
elseif (isset($_COOKIE['ufs_ol_lang']) && $_COOKIE['ufs_ol_lang'] === 'en') { $lang = 'en'; }

$L = array(
 'ko' => array(
   'title'=>'온라인 등록', 'home'=>'홈으로', 'back'=>'돌아가기',
   'intro'=>'온라인으로 제공되는 세션을 시청하실 수 있습니다. 등록을 위해 아래 정보를 입력해 주세요.',
   'agree_h'=>'약관 동의', 'agree_all'=>'전체 동의',
   'terms'=>'이용약관','privacy'=>'개인정보처리방침','mkt'=>'광고 수신 동의','required'=>'(필수)','optional'=>'(선택)',
   'notice_h'=>'온라인 시청 안내',
   'notice1'=>'현장 전체 세션이 아닌 일부 세션만 온라인으로 제공됩니다.',
   'notice2'=>'Q&amp;A 참여 및 현장 프로그램은 제공되지 않습니다.',
   'notice3'=>'온라인 등록과 오프라인 티켓은 중복 등록할 수 없습니다.',
   'notice4'=>'오프라인 참석을 원하실 경우 온라인 등록을 취소한 후 등록해 주세요.',
   'auth_h'=>'본인 인증','auth_desc'=>'본인 확인을 위해 아래 인증 방법 중 하나를 선택해 주세요.','auth_done'=>'✓ 인증 완료',
   'auth_phone'=>'휴대폰 본인 인증','auth_ipin'=>'아이핀 본인 인증',
   'basic_h'=>'기본 정보','name'=>'이름','email'=>'이메일','phone'=>'연락처','ph_auto'=>'본인인증 시 자동입력',
   'aff_h'=>'소속 및 관심 분야','job'=>'직업','company'=>'회사명/소속','depart'=>'부서','grade'=>'직무','industry'=>'산업/관심 분야',
   'ph_company'=>'에픽게임즈','ph_depart'=>'개발팀','select'=>'선택해 주세요','submit'=>'무료 등록하기','cancel'=>'취소',
   'a_agree_first'=>'본인인증 전에 필수 약관에 동의해주세요.','a_auth_first'=>'본인인증을 먼저 진행해주세요.',
   'a_auth_fail'=>'본인인증에 실패했습니다. 다시 시도해주세요.','a_email'=>'이메일을 입력해주세요.',
   'a_job'=>'직업을 선택해 주세요.','a_company'=>'회사명/소속을 입력해주세요.','a_depart'=>'부서를 입력해주세요.',
   'a_grade'=>'직무를 선택해 주세요.','a_industry'=>'산업/관심 분야를 선택해 주세요.','a_req_agree'=>'필수 약관에 동의해주세요.',
   'a_need_basic'=>'이름/이메일/연락처를 입력해주세요.','a_need_aff'=>'직업·회사명/소속·부서·직무·산업/관심 분야를 모두 입력해주세요.',
   'a_dup'=>'이미 등록된 이메일입니다. 등록 확인 페이지에서 확인해주세요.',
 ),
 'en' => array(
   'title'=>'Online Registration', 'home'=>'Home', 'back'=>'Back',
   'intro'=>'Watch the sessions provided online. Please fill in the details below to register.',
   'agree_h'=>'Agreement', 'agree_all'=>'Agree to all',
   'terms'=>'Terms of Service','privacy'=>'Privacy Policy','mkt'=>'I agree to receive marketing communications','required'=>'(required)','optional'=>'(optional)',
   'notice_h'=>'Online viewing notice',
   'notice1'=>'Only some sessions are provided online, not all on-site sessions.',
   'notice2'=>'Q&amp;A participation and on-site programs are not included.',
   'notice3'=>'Online registration and an offline ticket cannot be held at the same time.',
   'notice4'=>'To attend on-site, please cancel your online registration first, then register.',
   'auth_h'=>'Identity Verification','auth_desc'=>'Please choose one of the verification methods below. (Korean mobile / i-PIN required)','auth_done'=>'✓ Verified',
   'auth_phone'=>'Mobile verification','auth_ipin'=>'i-PIN verification',
   'basic_h'=>'Basic Information','name'=>'Name','email'=>'Email','phone'=>'Phone','ph_auto'=>'Auto-filled after verification',
   'aff_h'=>'Affiliation & Interests','job'=>'Occupation','company'=>'Company / Organization','depart'=>'Department','grade'=>'Job function','industry'=>'Industry / Interest',
   'ph_company'=>'Epic Games','ph_depart'=>'Dev team','select'=>'Please select','submit'=>'Register for free','cancel'=>'Cancel',
   'a_agree_first'=>'Please agree to the required terms before verification.','a_auth_first'=>'Please complete identity verification first.',
   'a_auth_fail'=>'Identity verification failed. Please try again.','a_email'=>'Please enter your email.',
   'a_job'=>'Please select your occupation.','a_company'=>'Please enter your company/organization.','a_depart'=>'Please enter your department.',
   'a_grade'=>'Please select your job function.','a_industry'=>'Please select your industry/interest.','a_req_agree'=>'Please agree to the required terms.',
   'a_need_basic'=>'Please enter your name/email/phone.','a_need_aff'=>'Please complete occupation, company, department, job function, and industry.',
   'a_dup'=>'This email is already registered. Please check on the ticket lookup page.',
 ),
);
$Lc = $L[$lang];
function t($k){ global $Lc; return isset($Lc[$k]) ? $Lc[$k] : $k; }

// 셀렉트 옵션 (값=한글 canonical / 라벨=언어별) — DB 저장값은 한글 유지
$OPT_JOB = array('직장인'=>'Office worker','학생'=>'Student','교육자/교육기관'=>'Educator / Institution','인디 개발자'=>'Indie developer','프리랜서'=>'Freelancer');
$OPT_GRADE = array('비주얼 아트'=>'Visual Art','프로그래밍'=>'Programming','프로덕션'=>'Production','엔지니어링'=>'Engineering','설계'=>'Design','기획'=>'Planning','R&D'=>'R&D','IT'=>'IT','감독/PD'=>'Director / PD','비즈니스/마케팅'=>'Business / Marketing','C-level'=>'C-level','기타'=>'Other');
$OPT_IND = array('게임'=>'Games','영화 & TV'=>'Film &amp; TV','방송 & 라이브 이벤트'=>'Broadcast &amp; Live Events','애니메이션'=>'Animation','건축'=>'Architecture','자동차'=>'Automotive','제조/시뮬레이션'=>'Manufacturing / Simulation','소프트웨어 & 툴 개발'=>'Software &amp; Tools Dev','VR·AR'=>'VR / AR','교육'=>'Education','기타'=>'Other');
function opt_render($opts, $lang){
    $h = '<option value="">'.t('select').'</option>';
    foreach ($opts as $val=>$en) {
        $label = ($lang==='en') ? $en : htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        $h .= '<option value="'.htmlspecialchars($val, ENT_QUOTES, 'UTF-8').'">'.$label.'</option>';
    }
    return $h;
}

// ── POST 처리: 무료 등록 INSERT (본인인증 필수) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function pp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }
    function ol_alert($msg, $back = true){ echo '<script>alert('.json_encode($msg, JSON_UNESCAPED_UNICODE).');'.($back?'history.back();':'location.href="myticket.php";').'</script>'; exit; }
    $name = pp('apply_user_name'); $email = pp('apply_user_email'); $phone = pp('apply_user_phone');
    $job = pp('apply_user_job'); $company = pp('apply_user_company'); $depart = pp('apply_user_depart');
    $grade = pp('apply_user_grade'); $ex1 = pp('apply_user_ex1');
    $ci = pp('apply_ci'); $di = pp('apply_di');
    $agree = (pp('agree_mkt') !== '') ? '1' : '0';
    if ($ci === '')                                                                              ol_alert(t('a_auth_first'));
    if ($name === '' || $email === '' || $phone === '')                                          ol_alert(t('a_need_basic'));
    if ($job === '' || $company === '' || $depart === '' || $grade === '' || $ex1 === '')        ol_alert(t('a_need_aff'));
    $em = sql_real_escape_string($email); $ph = sql_real_escape_string($phone);
    $dup = sql_fetch("select count(*) as cnt from cb_unreal_2026_event2_apply where apply_user_email = '$em' and apply_temp_yn = 'N' and apply_pay_status <> 0");
    if ($dup && $dup['cnt'] > 0) ol_alert(t('a_dup'), false);
    $pw = md5(str_replace("'","\\'",$email));
    $sql = "INSERT INTO cb_unreal_2026_event2_apply
      (apply_user_name, apply_user_email, apply_user_phone, apply_user_job, apply_user_company, apply_user_depart,
       apply_user_grade, apply_user_ex1, apply_product_code, apply_product_name, apply_product_price,
       apply_user_event_agree, apply_password, apply_ci, apply_di, apply_temp_yn, apply_pay_status, pay_complete, free_yn, apply_reg_datetime)
      VALUES (
       '".sql_real_escape_string(strip_tags($name))."', '".sql_real_escape_string(strip_tags($email))."',
       '".sql_real_escape_string(strip_tags($phone))."', '".sql_real_escape_string(strip_tags($job))."',
       '".sql_real_escape_string(strip_tags($company))."', '".sql_real_escape_string(strip_tags($depart))."',
       '".sql_real_escape_string(strip_tags($grade))."',
       '".sql_real_escape_string(strip_tags($ex1))."', 'ONLINE', '온라인 무료', '0',
       '".sql_real_escape_string($agree)."', '".sql_real_escape_string($pw)."',
       '".sql_real_escape_string(strip_tags($ci))."', '".sql_real_escape_string(strip_tags($di))."',
       'N', 10, 'Y', 'Y', now())";
    sql_query($sql);
    $row = sql_query("SELECT LAST_INSERT_ID() as idx")->fetch_array();
    ufs_send_online_sms($name, $phone);
    $ufs_conv_row = array(
        'apply_user_email' => $email, 'apply_user_phone' => $phone,
        'apply_product_code' => 'ONLINE', 'apply_product_price' => 0,
        'apply_user_event_agree' => $agree, 'free_yn' => 'Y', 'apply_no' => $row['idx'],
    );
    require_once __DIR__ . '/_kakao_capi.php';
    @ufs_kakao_capi_send($ufs_conv_row);
    require_once __DIR__ . '/_meta_capi.php';
    @ufs_meta_capi_send($ufs_conv_row);
    header("Location: ticket-complete.php?online=1&lang=".$lang."&k=".rawurlencode(base64_encode($row['idx'])));
    exit;
}
// 본인인증 결과(세션) 폴백
$sess_ci = isset($_SESSION['CI']) ? $_SESSION['CI'] : '';
$sess_di = isset($_SESSION['DI']) ? $_SESSION['DI'] : '';
$sess_name = isset($_SESSION['RSLT_NAME']) ? $_SESSION['RSLT_NAME'] : '';
$sess_tel = isset($_SESSION['TEL_NO']) ? $_SESSION['TEL_NO'] : '';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="dark"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= t('title') ?> — Unreal Fest Seoul 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
<?php include __DIR__ . '/_favicon.php'; ?>
<?php if (defined('_GNUBOARD_')) include __DIR__ . '/../inc/marketing_head.php'; /* 라운지 전역 SEO/마케팅 */ ?>
<?php include __DIR__.'/_wcs.php'; ?>
<?php include __DIR__.'/_adn.php'; ?>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <div class="flex items-center gap-3">
      <div class="flex text-xs border border-[#27272a] rounded overflow-hidden">
        <a href="?lang=ko" class="px-2.5 py-1 <?= $lang==='ko'?'bg-[#00C1D5] text-black font-bold':'text-[#a1a1aa] hover:text-white' ?>">한국어</a>
        <a href="?lang=en" class="px-2.5 py-1 <?= $lang==='en'?'bg-[#00C1D5] text-black font-bold':'text-[#a1a1aa] hover:text-white' ?>">EN</a>
      </div>
      <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white"><?= t('home') ?></a>
    </div>
  </div>
</header>

<form name="frm" id="frm" method="post" onsubmit="return validateForm()">
<input type="hidden" name="apply_ci" id="apply_ci" value="<?= e($sess_ci) ?>">
<input type="hidden" name="apply_di" id="apply_di" value="<?= e($sess_di) ?>">
<input type="hidden" name="apply_real_type" id="apply_real_type" value="">
<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="max-w-3xl mx-auto px-6">
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> <?= t('back') ?></a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight"><?= t('title') ?></h1>
    <p class="text-[#a1a1aa] mb-10"><?= t('intro') ?></p>

    <div class="space-y-4">
      <!-- 약관 동의 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> <?= t('agree_h') ?></h2>
        <div class="space-y-3">
          <label class="flex items-center gap-3 p-3 bg-[rgba(0,79,89,0.3)] border border-[rgba(0,193,213,0.3)] cursor-pointer">
            <input type="checkbox" id="agree_all" onchange="toggleAllAgree(this)" class="accent-[#00C1D5]">
            <span class="text-sm font-bold text-white"><?= t('agree_all') ?></span>
          </label>
          <div class="h-px bg-[#27272a]"></div>
          <?php if ($lang === 'en'): ?>
          <label class="flex items-start gap-3 px-3 py-2 cursor-pointer"><input type="checkbox" name="agree_req" class="agree-item mt-0.5 accent-[#00C1D5]"><span class="text-sm text-[#a1a1aa]">I agree to the <a href="legal-en.php#terms" target="_blank" rel="noopener" class="underline text-[#00C1D5] hover:text-white">Terms of Service</a> and <a href="legal-en.php#privacy" target="_blank" rel="noopener" class="underline text-[#00C1D5] hover:text-white">Privacy Policy</a><span class="ml-1 text-xs text-[#00C1D5]"><?= t('required') ?></span></span></label>
          <label class="flex items-start gap-3 px-3 py-2 cursor-pointer"><input type="checkbox" name="agree_mkt" class="agree-item mt-0.5 accent-[#00C1D5]"><span class="text-sm text-[#a1a1aa]"><?= t('mkt') ?><span class="ml-1 text-xs text-[#71717a]"><?= t('optional') ?></span></span></label>
          <?php else: ?>
          <label class="flex items-start gap-3 px-3 py-2 cursor-pointer"><input type="checkbox" name="agree_req" class="agree-item mt-0.5 accent-[#00C1D5]"><span class="text-sm text-[#a1a1aa]"><button type="button" onclick="event.preventDefault();event.stopPropagation();openLegal('terms');" class="underline text-[#00C1D5] hover:text-white">이용약관</button> 동의 및 <button type="button" onclick="event.preventDefault();event.stopPropagation();openLegal('privacy');" class="underline text-[#00C1D5] hover:text-white">개인정보처리방침</button> 확인<span class="ml-1 text-xs text-[#00C1D5]"><?= t('required') ?></span></span></label>
          <label class="flex items-start gap-3 px-3 py-2 cursor-pointer"><input type="checkbox" name="agree_mkt" class="agree-item mt-0.5 accent-[#00C1D5]"><span class="text-sm text-[#a1a1aa]"><button type="button" onclick="event.preventDefault();event.stopPropagation();openLegal('marketing');" class="underline text-[#a1a1aa] hover:text-white">광고 수신 동의</button><span class="ml-1 text-xs text-[#71717a]"><?= t('optional') ?></span></span></label>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($lang !== 'en') include __DIR__ . '/_legal_modal.php'; ?>

      <!-- 온라인 시청 안내 -->
      <div class="bg-[rgba(0,193,213,0.05)] border border-[rgba(0,193,213,0.2)] p-6">
        <h3 class="text-base font-bold text-white mb-3"><?= t('notice_h') ?></h3>
        <ul class="text-sm text-[#a1a1aa] space-y-1.5">
          <li>• <?= t('notice1') ?></li>
          <li>• <?= t('notice2') ?></li>
          <li>• <?= t('notice3') ?></li>
          <li>• <?= t('notice4') ?></li>
        </ul>
      </div>

      <!-- 본인 인증 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> <?= t('auth_h') ?></h2>
        <p class="text-sm text-[#a1a1aa] mb-5"><?= t('auth_desc') ?> <span id="authState" class="ml-2 font-bold"></span></p>
        <div class="flex flex-wrap gap-4">
          <a href="#n" onclick="jsSubmit();return false;" class="px-6 py-3 bg-[#00C1D5] text-black font-bold hover:bg-[#00a8ba] transition-all"><?= t('auth_phone') ?></a>
          <a href="#n" onclick="jsSubmitPin();return false;" class="px-6 py-3 bg-transparent text-[#a1a1aa] font-bold border border-[#27272a] hover:border-white/20 hover:text-white transition-all"><?= t('auth_ipin') ?></a>
        </div>
      </div>

      <!-- 기본 정보 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5"><?= t('basic_h') ?></h2>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= t('name') ?> <span class="text-[#00C1D5]">*</span></label><input type="text" name="apply_user_name" id="apply_user_name" value="<?= e($sess_name) ?>" placeholder="<?= t('ph_auto') ?>" readonly class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= t('email') ?> <span class="text-[#00C1D5]">*</span></label><input type="email" name="apply_user_email" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= t('phone') ?> <span class="text-[#00C1D5]">*</span></label><input type="tel" name="apply_user_phone" id="apply_user_phone" value="<?= e($sess_tel) ?>" placeholder="<?= t('ph_auto') ?>" readonly class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none text-sm"></div>
        </div>
      </div>

      <!-- 소속 및 관심 분야 -->
      <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
        <h2 class="text-lg font-bold text-white mb-5"><?= t('aff_h') ?></h2>
        <div class="space-y-6">
          <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= t('job') ?> <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><?= opt_render($OPT_JOB, $lang) ?></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= t('company') ?> <span class="text-[#00C1D5]">*</span></label><input type="text" name="apply_user_company" placeholder="<?= t('ph_company') ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
          <div class="grid md:grid-cols-3 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= t('depart') ?> <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_depart" placeholder="<?= t('ph_depart') ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= t('grade') ?> <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><?= opt_render($OPT_GRADE, $lang) ?></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= t('industry') ?> <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><?= opt_render($OPT_IND, $lang) ?></select></div>
          </div>
        </div>
      </div>

      <button type="submit" class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all"><?= t('submit') ?> <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></button>
      <a href="index.php#register" class="block w-full text-center text-sm text-[#71717a] hover:text-white py-3 transition-colors"><?= t('cancel') ?></a>
    </div>
  </div>
</div>
</form>

<!-- 본인인증 팝업 타깃 (2025 real/ 재사용) -->
<form name="form1" id="form1" method="post"></form>
<form name="kcbResultForm" id="kcbResultForm">
  <input type="hidden" name="CP_CD" value=""><input type="hidden" name="TX_SEQ_NO" value=""><input type="hidden" name="RSLT_CD" value="">
  <input type="hidden" name="RSLT_MSG" value=""><input type="hidden" name="RETURN_MSG" value=""><input type="hidden" name="RSLT_NAME" value="">
  <input type="hidden" name="RSLT_BIRTHDAY" value=""><input type="hidden" name="RSLT_SEX_CD" value=""><input type="hidden" name="RSLT_NTV_FRNR_CD" value="">
  <input type="hidden" name="DI" value=""><input type="hidden" name="CI" value=""><input type="hidden" name="CI_UPDATE" value="">
  <input type="hidden" name="TEL_COM_CD" value=""><input type="hidden" name="TEL_NO" value="">
</form>

<?php include __DIR__ . '/_pf_footer.php'; ?>
<script>
var T = <?= json_encode($Lc, JSON_UNESCAPED_UNICODE) ?>;
function _t(id){return document.getElementById(id);}
function toggleAllAgree(cb){document.querySelectorAll('.agree-item').forEach(function(i){i.checked=cb.checked;});}
function checkAgree(){if(!document.querySelector('input[name="agree_req"]').checked){alert(T.a_agree_first);return false;}return true;}
function jsSubmit(){if(!checkAgree())return;_t('apply_real_type').value='tel';var f=_t('form1');f.action='../real/phone_popup2.php';f.target='auth_popup';window.open('about:blank','auth_popup','width=430,height=640,scrollbars=yes');f.submit();}
function jsSubmitPin(){if(!checkAgree())return;_t('apply_real_type').value='ipin';var f=_t('form1');f.action='../real/ipin_popup2.php';f.target='kcbPop';window.open('about:blank','kcbPop','width=450,height=550,scrollbars=yes');f.submit();}
function refreshAuth(){var c=_t('apply_ci');if(c&&c.value){var as=_t('authState');if(as){as.textContent=T.auth_done;as.className='ml-2 font-bold text-[#00C1D5]';}}}
window.handleKcbAuthResult=function(){var f=document.forms['kcbResultForm'];if(!f)return;var r=f.RSLT_CD?f.RSLT_CD.value:'';if(r&&r!=='B000'&&r!=='T000'){alert(T.a_auth_fail);return;}_t('apply_ci').value=f.CI?f.CI.value:'';_t('apply_di').value=f.DI?f.DI.value:'';var nm=document.querySelector('input[name="apply_user_name"]');if(nm)nm.value=f.RSLT_NAME?f.RSLT_NAME.value:'';var tl=_t('apply_user_phone');if(tl){if(f.TEL_NO&&f.TEL_NO.value){tl.value=f.TEL_NO.value;tl.readOnly=true;}else{tl.readOnly=false;}}refreshAuth();window._justAuthed=true;setTimeout(focusEmail,300);};
function focusEmail(){if(!window._justAuthed)return;window._justAuthed=false;var em=document.querySelector('input[name="apply_user_email"]');if(em){em.scrollIntoView({behavior:'smooth',block:'center'});em.focus();}}
window.addEventListener('focus',function(){refreshAuth();focusEmail();});
setInterval(refreshAuth,1000);refreshAuth();
function validateForm(){
  if(!_t('apply_ci').value){alert(T.a_auth_first);return false;}
  if(!document.querySelector('input[name="apply_user_email"]').value.trim()){alert(T.a_email);return false;}
  if(!document.querySelector('input[name="apply_user_phone"]').value.trim()){alert(T.a_auth_first);return false;}
  if(!document.querySelector('select[name="apply_user_job"]').value){alert(T.a_job);return false;}
  if(!document.querySelector('input[name="apply_user_company"]').value.trim()){alert(T.a_company);return false;}
  if(!document.querySelector('input[name="apply_user_depart"]').value.trim()){alert(T.a_depart);return false;}
  if(!document.querySelector('select[name="apply_user_grade"]').value){alert(T.a_grade);return false;}
  if(!document.querySelector('select[name="apply_user_ex1"]').value){alert(T.a_industry);return false;}
  if(!document.querySelector('input[name="agree_req"]').checked){alert(T.a_req_agree);return false;}
  return true;
}
</script>
</body></html>
