<?php
/* Unreal Fest Seoul 2026 — 초청장 발송 등록 (ticket-invite.php) [M2: KO · 무료경로]
 * 초청 코드(?code=) 게이트 → 무인증 등록폼(대표 + 동반, 최대 sc_quota) → 무료(100%) 즉시완료 + QR.
 *   - 부분할인(50~99%)은 M3(단체결제 재사용) — 지금은 제출 시 안내만.
 * 데이터층 = _invite_apply.php(리뷰 완료). 오케스트레이션(코드검증·중복차단·소진→삽입·롤백)은 이 파일.
 * 4개국어는 M4에서 data/i18n_ticket.php 로 확장. PHP 7.0 호환.
 */
require __DIR__ . '/_ticket_init.php';        // common.php, e(), asset_v(), 가격/트랙(_pricing 로드=정상가 소스), $UFS_TRACKS, $trackRemain
require_once __DIR__ . '/_invite_apply.php';  // ufs_invite_* 헬퍼

function gp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }
function garr($k){ return (isset($_POST[$k]) && is_array($_POST[$k])) ? $_POST[$k] : array(); }

$PRODDAYS  = array('NORMAL_ALL'=>array('1','2'),'NORMAL_20'=>array('1'),'NORMAL_21'=>array('2'));
$PRODNAME  = array('NORMAL_ALL'=>'양일권 (8.20~21)','NORMAL_20'=>'1일권 · Day 1','NORMAL_21'=>'1일권 · Day 2');
$JOBS  = array('직장인','학생','교육자/교육기관','인디 개발자','프리랜서');
$GRADES= array('비주얼 아트','프로그래밍','프로덕션','엔지니어링','설계','기획','R&D','IT','감독/PD','비즈니스/마케팅','C-level','기타');
$EX1S  = array('게임','영화 & TV','방송 & 라이브 이벤트','애니메이션','건축','자동차','제조/시뮬레이션','소프트웨어 & 툴 개발','VR·AR','교육','기타');
$SEL_CLS = 'w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none';
function inv_opts($arr){ $s=''; foreach($arr as $o){ $s.='<option>'.htmlspecialchars($o,ENT_QUOTES,'UTF-8').'</option>'; } return $s; }

$TKT = array(
  array('code'=>'NORMAL_ALL','label'=>'양일권 (8.20~21)','orig'=>(int)ufs_ticket_orig('NORMAL_ALL'),'days'=>'1,2'),
  array('code'=>'NORMAL_20', 'label'=>'1일권 · Day 1',   'orig'=>(int)ufs_ticket_orig('NORMAL_20'), 'days'=>'1'),
  array('code'=>'NORMAL_21', 'label'=>'1일권 · Day 2',   'orig'=>(int)ufs_ticket_orig('NORMAL_21'), 'days'=>'2'),
);

/* 참석 선택 한 줄(티켓·Day1·Day2·티셔츠) — 초청용(NONE 없음) */
function inv_attend_row($nTicket,$nD1,$nD2,$nTshirt,$TKT,$TR){
  global $SEL_CLS,$trackRemain;
  $remain = is_array($trackRemain) ? $trackRemain : array();
  $opt = function($v,$l) use ($remain){ $full=(isset($remain[$v])&&(int)$remain[$v]<=0); return '<option value="'.e($v).'"'.($full?' disabled':'').'>'.e($l).($full?' (마감)':'').'</option>'; };
  echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">';
  echo '<div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">티켓 <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nTicket).'" data-pick-ticket class="'.$SEL_CLS.'"><option value="">티켓 선택</option>';
  foreach ($TKT as $t) echo '<option value="'.e($t['code']).'" data-orig="'.(int)$t['orig'].'" data-days="'.e($t['days']).'">'.e($t['label']).'</option>';
  echo '</select></div>';
  echo '<div class="space-y-2" data-track-wrap data-day="1"><label class="text-sm font-medium text-[#a1a1aa]">Day1 트랙 <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nD1).'" data-day="1" class="'.$SEL_CLS.'"><option value="">Day1 트랙</option>';
  foreach ($TR[1] as $v=>$l) echo $opt($v,$l);
  echo '</select></div>';
  echo '<div class="space-y-2" data-track-wrap data-day="2"><label class="text-sm font-medium text-[#a1a1aa]">Day2 트랙 <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nD2).'" data-day="2" class="'.$SEL_CLS.'"><option value="">Day2 트랙</option>';
  foreach ($TR[2] as $v=>$l) echo $opt($v,$l);
  echo '</select></div>';
  echo '<div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">티셔츠 <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nTshirt).'" class="'.$SEL_CLS.'"><option value="">선택</option>';
  foreach (array('M','L','XL','XXL') as $s) echo '<option>'.$s.'</option>';
  echo '</select></div>';
  echo '</div>';
}

// ── 코드 해석 (POST 우선, 없으면 GET)
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$code = $isPost ? gp('code') : (isset($_GET['code']) ? trim($_GET['code']) : '');
$chk  = ($code !== '') ? ufs_invite_code_check($code) : null;
$err  = '';

// 이전 입력 복원 헬퍼(오류 재렌더용) — member_name[0] 같은 배열 표기도 지원
function old($k,$d=''){
  $v = null;
  if (preg_match('/^([a-z_]+)\[(\d+)\]$/', $k, $m)) {
    if (isset($_POST[$m[1]]) && is_array($_POST[$m[1]]) && isset($_POST[$m[1]][$m[2]])) $v = $_POST[$m[1]][$m[2]];
  } elseif (isset($_POST[$k])) {
    $v = $_POST[$k];
  }
  return htmlspecialchars(trim($v !== null ? (string)$v : (string)$d), ENT_QUOTES, 'UTF-8');
}

// ── POST 처리
if ($isPost) {
  if (!$chk || !$chk['ok']) {
    $err = $chk ? $chk['msg'] : '초청 코드를 확인해 주세요.';
  } else {
    $row = $chk['row']; $discount = (int)$chk['discount']; $free = ($discount >= 100); $remain = (int)$chk['remain'];

    // 대표(초청 당사자) — 항상 참석
    $rep = array(
      'role'=>'rep','name'=>gp('apply_user_name'),'email'=>gp('apply_user_email'),'phone'=>gp('apply_user_phone'),
      'job'=>gp('apply_user_job'),'company'=>gp('apply_user_company'),'depart'=>gp('apply_user_depart'),
      'grade'=>gp('apply_user_grade'),'ex1'=>gp('apply_user_ex1'),
      'ticket'=>gp('rep_ticket'),'day1'=>gp('rep_day1'),'day2'=>gp('rep_day2'),'tshirt'=>gp('rep_tshirt'),
    );
    $attendees = array($rep);
    // 동반자(선택) — 이름 없으면 skip. 직업·회사=대표 자동상속
    $mN=garr('member_name'); $mE=garr('member_email'); $mP=garr('member_phone');
    $mDe=garr('member_depart'); $mGr=garr('member_grade'); $mEx=garr('member_ex1');
    $mT=garr('member_ticket'); $mD1=garr('member_day1'); $mD2=garr('member_day2'); $mTs=garr('member_tshirt');
    foreach (array_keys($mN) as $k) {
      if (trim($mN[$k]) === '') continue;
      $attendees[] = array('role'=>'member','name'=>trim($mN[$k]),
        'email'=>isset($mE[$k])?trim($mE[$k]):'', 'phone'=>isset($mP[$k])?trim($mP[$k]):'',
        'job'=>$rep['job'],'company'=>$rep['company'],
        'depart'=>isset($mDe[$k])?trim($mDe[$k]):'','grade'=>isset($mGr[$k])?trim($mGr[$k]):'','ex1'=>isset($mEx[$k])?trim($mEx[$k]):'',
        'ticket'=>isset($mT[$k])?trim($mT[$k]):'','day1'=>isset($mD1[$k])?trim($mD1[$k]):'','day2'=>isset($mD2[$k])?trim($mD2[$k]):'','tshirt'=>isset($mTs[$k])?trim($mTs[$k]):'');
    }

    // 검증
    if (gp('agree_req') !== 'Y') $err = '필수 약관에 동의해 주세요.';
    elseif ($rep['name']==='' || $rep['email']==='' || $rep['phone']==='' || $rep['company']==='' || $rep['depart']==='' || $rep['job']==='' || $rep['grade']==='' || $rep['ex1']==='') $err = '대표자(초청 당사자) 정보를 모두 입력해 주세요.';
    if ($err==='' && count($attendees) > $remain) $err = '이 코드로 등록 가능한 인원('.$remain.'명)을 초과했습니다.';
    if ($err==='') {
      $seen = array();
      foreach ($attendees as $i=>$a) {
        $lbl = ($a['role']==='rep') ? '대표자' : ($i.'번 동반자');
        if (!isset($PRODNAME[$a['ticket']])) { $err=$lbl.'의 티켓을 선택해 주세요.'; break; }
        $days = $PRODDAYS[$a['ticket']];
        if (in_array('1',$days,true) && ($a['day1']==='' || !isset($UFS_TRACKS[1][$a['day1']]))) { $err=$lbl.'의 Day1 트랙을 선택해 주세요.'; break; }
        if (in_array('2',$days,true) && ($a['day2']==='' || !isset($UFS_TRACKS[2][$a['day2']]))) { $err=$lbl.'의 Day2 트랙을 선택해 주세요.'; break; }
        if (!in_array($a['tshirt'],array('M','L','XL','XXL'),true)) { $err=$lbl.'의 티셔츠를 선택해 주세요.'; break; }
        if ($a['role']==='member' && ($a['email']==='' || $a['phone']==='')) { $err=$lbl.'의 이메일/연락처를 입력해 주세요.'; break; }
        $emk = strtolower($a['email']);
        if (isset($seen[$emk])) { $err='동일한 이메일이 중복되었습니다: '.e($a['email']); break; }
        $seen[$emk] = 1;
        if (ufs_invite_email_dup($a['email'])) { $err=$a['email'].' 은(는) 이미 등록된 이메일입니다.'; break; }
      }
    }

    // 무료(100%) 경로 — 부분할인은 M3
    if ($err==='' && !$free) {
      $err = '부분할인(결제) 초청 등록은 곧 오픈됩니다. (준비 중)';
    }
    if ($err==='' && $free) {
      $n = count($attendees);
      // 리뷰 계약: 소진(consume) 성공 후에만 INSERT (선-예약)
      if (!ufs_invite_consume($code, $n)) {
        $err = '초청 잔여 매수가 부족합니다. 새로고침 후 다시 시도해 주세요.';
      } else {
        $inserted = array(); $repNo = 0; $fail = false;
        foreach ($attendees as $a) {
          $a['price'] = 0; // 무료
          $no = ufs_invite_insert_member($a, $code, 10, true);
          if ($no > 0) { $inserted[] = $no; if ($a['role']==='rep') $repNo = $no; }
          else { $fail = true; break; }
        }
        if ($fail || $repNo === 0) {
          // 롤백: 삽입행 취소 + 소진 원복(리뷰 계약: 부분성공 방지·잔여 누수 방지)
          foreach ($inserted as $no) sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0 WHERE apply_no=".(int)$no);
          sql_query("UPDATE cb_unreal_2026_speaker_code SET sc_used=GREATEST(sc_used-".(int)$n.",0) WHERE sc_code='".sql_real_escape_string(strtoupper($code))."'");
          $err = '등록 처리 중 오류가 발생했습니다. 다시 시도해 주세요.';
        } else {
          $tok = md5(str_replace("'","\\'", $attendees[0]['email']));  // 대표 apply_password 토큰(완료페이지 검증)
          header('Location: ticket-invite-complete.php?a='.$repNo.'&t='.$tok);
          exit;
        }
      }
    }
  }
}

// GET/오류 렌더용 값
$valid   = ($chk && $chk['ok']);
$row     = $valid ? $chk['row'] : null;
$discount= $valid ? (int)$chk['discount'] : 100;
$remain  = $valid ? (int)$chk['remain'] : 0;
$free    = ($discount >= 100);
$inviter = $valid ? ($row['sc_inviter'] !== '' ? $row['sc_inviter'] : '에픽게임즈') : '';
$companions = $valid ? max(0, $remain - 1) : 0;   // 동반자 슬롯 수(대표 제외)
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>초청 등록 — Unreal Fest Seoul 2026</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}.gwrap{max-width:56rem;margin-left:auto;margin-right:auto}</style>
<?php include __DIR__ . '/_favicon.php'; ?>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">

<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="gwrap px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="gwrap px-6">
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">초청 등록</h1>

    <?php if ($err !== ''): ?>
      <div class="mt-4 mb-6 px-4 py-3 bg-[rgba(255,134,116,0.12)] border border-[#ff8674]/40 text-[#ff8674] text-sm"><?= e($err) ?></div>
    <?php endif; ?>

    <?php if (!$valid): ?>
      <!-- 코드 게이트 -->
      <p class="text-[#a1a1aa] mb-8">초청 이메일에 포함된 <b class="text-white">초청 코드</b>를 입력해 주세요.</p>
      <form method="get" action="ticket-invite.php" class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 max-w-md">
        <label class="text-sm font-medium text-[#a1a1aa]">초청 코드</label>
        <input type="text" name="code" value="<?= e($code) ?>" placeholder="예: UFS-XXXX-XXXX" class="mt-2 w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm uppercase">
        <button type="submit" class="mt-4 w-full py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors">확인</button>
      </form>
    <?php else: ?>
      <!-- 초청 안내 배지 -->
      <p class="text-[#a1a1aa] mb-4"><b class="text-white"><?= e($inviter) ?></b>의 초청으로 등록합니다. 등록 정보를 입력해 주세요.</p>
      <div class="inline-flex flex-wrap items-center gap-2 mb-8">
        <span class="px-4 py-2 bg-[rgba(0,79,89,0.2)] border border-[#00C1D5]/40 text-[#00C1D5] text-sm font-bold"><?= $free ? '무료 초청 (100%)' : ('초청 할인 '.$discount.'%') ?></span>
        <span class="px-4 py-2 border border-[#27272a] text-[#a1a1aa] text-sm">등록 가능 <?= (int)$remain ?>명</span>
      </div>
      <?php if (!$free): ?>
      <div class="mb-8 px-4 py-3 bg-[rgba(255,255,255,0.04)] border border-[#3f3f46] text-[#a1a1aa] text-sm">부분할인(결제) 초청 등록은 곧 오픈됩니다. 현재는 무료(100%) 초청만 즉시 완료됩니다.</div>
      <?php endif; ?>

      <form name="frm" id="frm" method="post" action="ticket-invite.php" onsubmit="return invValidate()">
      <input type="hidden" name="code" value="<?= e($code) ?>">

      <div class="space-y-4">
        <!-- 약관 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">약관 동의</h2>
          <div class="space-y-3">
            <label class="flex items-center gap-3 p-3 bg-[rgba(0,79,89,0.3)] border border-[rgba(0,193,213,0.3)] cursor-pointer">
              <input type="checkbox" id="agree_all" class="accent-[#00C1D5]"><span class="text-sm font-bold text-white">전체 동의</span></label>
            <div class="h-px bg-[#27272a]"></div>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_req" value="Y" class="agree-item mt-0.5 accent-[#00C1D5]" <?= (old('agree_req')==='Y'?'checked':'') ?>>
              <span class="text-sm text-[#a1a1aa]"><a href="#" class="underline text-[#00C1D5]">이용약관</a> 및 <a href="#" class="underline text-[#00C1D5]">개인정보처리방침</a>에 동의합니다 <span class="ml-1 text-xs text-[#00C1D5]">(필수)</span></span></label>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_mkt" value="Y" class="agree-item mt-0.5 accent-[#00C1D5]" <?= (old('agree_mkt')==='Y'?'checked':'') ?>>
              <span class="text-sm text-[#a1a1aa]">마케팅 정보 수신에 동의합니다 <span class="ml-1 text-xs text-[#71717a]">(선택)</span></span></label>
          </div>
        </div>

        <!-- 대표(초청 당사자) 정보 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">등록자 정보</h2>
          <div class="grid md:grid-cols-3 gap-6 mb-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이름 <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_name" value="<?= old('apply_user_name', $row['sc_name']) ?>" placeholder="이름" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일 <span class="text-[#00C1D5]">*</span></label>
              <input type="email" name="apply_user_email" value="<?= old('apply_user_email', $row['sc_email']) ?>" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처 <span class="text-[#00C1D5]">*</span></label>
              <input type="tel" name="apply_user_phone" value="<?= old('apply_user_phone', $row['sc_phone']) ?>" placeholder="01012345678" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
          <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직업 <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_job" class="<?= $SEL_CLS ?>"><option value="">선택해 주세요</option><?= inv_opts($JOBS) ?></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">회사명/소속 <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_company" value="<?= old('apply_user_company', $row['sc_company']) ?>" placeholder="에픽게임즈" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
          <div class="grid md:grid-cols-3 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">부서 <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_depart" value="<?= old('apply_user_depart') ?>" placeholder="개발팀" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직무 <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_grade" class="<?= $SEL_CLS ?>"><option value="">선택해 주세요</option><?= inv_opts($GRADES) ?></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">산업/관심 분야 <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_ex1" class="<?= $SEL_CLS ?>"><option value="">선택해 주세요</option><?= inv_opts($EX1S) ?></select></div>
          </div>
        </div>

        <!-- 대표 참석 선택 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8" data-card>
          <div class="text-sm font-bold text-[#00C1D5] mb-4">1. 등록자 참석 선택</div>
          <?php inv_attend_row('rep_ticket','rep_day1','rep_day2','rep_tshirt',$TKT,$UFS_TRACKS); ?>
        </div>

        <?php if ($companions > 0): ?>
        <!-- 동반자(선택) -->
        <p class="text-xs text-[#71717a] px-1">※ 동반자의 <b class="text-[#a1a1aa]">직업·회사명</b>은 등록자와 동일하게 자동 등록됩니다. 동반 없이 1인만 등록하려면 비워 두세요.</p>
        <?php for ($i=0; $i<$companions; $i++): ?>
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8" data-card>
          <div class="text-sm font-bold text-[#00C1D5] mb-4"><?= $i+2 ?>. 동반자 <span class="text-[#71717a] font-normal">(선택)</span></div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이름</label>
              <input type="text" name="member_name[<?= $i ?>]" value="<?= old('member_name['.$i.']') ?>" placeholder="이름" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일</label>
              <input type="email" name="member_email[<?= $i ?>]" value="<?= old('member_email['.$i.']') ?>" placeholder="email@example.com" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처</label>
              <input type="tel" name="member_phone[<?= $i ?>]" value="<?= old('member_phone['.$i.']') ?>" placeholder="01012345678" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">부서</label>
              <input type="text" name="member_depart[<?= $i ?>]" value="<?= old('member_depart['.$i.']') ?>" placeholder="개발팀" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직무</label>
              <select name="member_grade[<?= $i ?>]" class="<?= $SEL_CLS ?>"><option value="">선택</option><?= inv_opts($GRADES) ?></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">관심 분야</label>
              <select name="member_ex1[<?= $i ?>]" class="<?= $SEL_CLS ?>"><option value="">선택</option><?= inv_opts($EX1S) ?></select></div>
          </div>
          <?php inv_attend_row('member_ticket['.$i.']','member_day1['.$i.']','member_day2['.$i.']','member_tshirt['.$i.']',$TKT,$UFS_TRACKS); ?>
        </div>
        <?php endfor; ?>
        <?php endif; ?>

        <!-- 요약 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5">등록 요약</h2>
          <div class="flex justify-between items-end"><span class="text-[#71717a]">총 결제 금액</span><span class="text-3xl font-black text-[#00C1D5]" id="sumTotal"><?= $free ? '무료' : '₩0' ?></span></div>
          <button type="submit" class="mt-6 w-full py-4 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors"><?= $free ? '등록 완료' : '등록 정보 확인' ?></button>
          <p class="text-xs text-[#71717a] mt-3 leading-relaxed">등록 완료 후 QR과 조회 링크가 제공됩니다. 이미 등록된 이메일은 재등록할 수 없습니다.</p>
        </div>
      </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/_pf_footer.php'; ?>

<?php if ($valid): ?>
<script>
(function(){
  // 티켓별 트랙(Day1/Day2) 표시 토글: data-days 에 따라 필요한 트랙만 노출
  function syncTracks(card){
    var sel = card.querySelector('[data-pick-ticket]'); if(!sel) return;
    var opt = sel.options[sel.selectedIndex];
    var days = opt ? (opt.getAttribute('data-days')||'') : '';
    card.querySelectorAll('[data-track-wrap]').forEach(function(w){
      var d = w.getAttribute('data-day');
      var need = days.split(',').indexOf(d) !== -1;
      w.style.display = need ? '' : 'none';
      var s = w.querySelector('select'); if(s && !need){ s.value=''; }
    });
  }
  document.querySelectorAll('[data-card]').forEach(function(card){
    var sel = card.querySelector('[data-pick-ticket]');
    if(sel){ sel.addEventListener('change', function(){ syncTracks(card); }); syncTracks(card); }
  });
  // 전체 동의
  var all=document.getElementById('agree_all');
  if(all) all.addEventListener('change', function(){ document.querySelectorAll('.agree-item').forEach(function(c){ c.checked=all.checked; }); });
})();
function invValidate(){
  var req=document.querySelector('input[name=agree_req]');
  if(req && !req.checked){ alert('필수 약관에 동의해 주세요.'); return false; }
  return true;
}
</script>
<?php endif; ?>
</body>
</html>
