<?php
/* Unreal Fest Seoul 2026 — 초청장 발송 등록 (ticket-invite.php) [M2/M3/M4]
 * 초청 코드(?code=) 게이트 → 무인증 등록폼(대표 + 동반, 최대 sc_quota) → 무료(100%) 즉시완료 / 부분할인(50~99%) 카드결제.
 * M4: 언어 KO/EN (sc_lang 또는 ?lang=). 데이터층 = _invite_apply.php(리뷰 완료), i18n = data/i18n_invite.php.
 * 오케스트레이션(코드검증·중복차단·소진→삽입·롤백)은 이 파일. PHP 7.0 호환.
 */
require __DIR__ . '/_ticket_init.php';        // common.php, e(), asset_v(), 가격/트랙(_pricing 로드=정상가 소스), $UFS_TRACKS, $trackRemain
require_once __DIR__ . '/_invite_apply.php';  // ufs_invite_* 헬퍼
require_once __DIR__ . '/data/i18n_invite.php'; // KO/EN 사전

function gp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }
function garr($k){ return (isset($_POST[$k]) && is_array($_POST[$k])) ? $_POST[$k] : array(); }

$PRODDAYS  = array('NORMAL_ALL'=>array('1','2'),'NORMAL_20'=>array('1'),'NORMAL_21'=>array('2'));
$PRODNAME  = array('NORMAL_ALL'=>'양일권','NORMAL_20'=>'1일권 Day1','NORMAL_21'=>'1일권 Day2'); // 유효 티켓 코드 판별용(표시 아님)
$SEL_CLS = 'w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none';
function inv_opts($arr){ $s=''; foreach($arr as $o){ $s.='<option>'.htmlspecialchars($o,ENT_QUOTES,'UTF-8').'</option>'; } return $s; }

$TKT = array(
  array('code'=>'NORMAL_ALL','orig'=>(int)ufs_ticket_orig('NORMAL_ALL'),'days'=>'1,2'),
  array('code'=>'NORMAL_20', 'orig'=>(int)ufs_ticket_orig('NORMAL_20'), 'days'=>'1'),
  array('code'=>'NORMAL_21', 'orig'=>(int)ufs_ticket_orig('NORMAL_21'), 'days'=>'2'),
);

/* 참석 선택 한 줄(티켓·Day1·Day2·티셔츠) — 초청용(NONE 없음), 언어별 라벨 */
function inv_attend_row($nTicket,$nD1,$nD2,$nTshirt,$TKT,$TR,$lang,$L){
  global $SEL_CLS,$trackRemain;
  $remain = is_array($trackRemain) ? $trackRemain : array();
  $opt = function($v,$koLabel) use ($remain,$lang,$L){ $full=(isset($remain[$v])&&(int)$remain[$v]<=0); return '<option value="'.e($v).'"'.($full?' disabled':'').'>'.e(ufs_inv_track_label($v,$lang,$koLabel)).($full?' ('.$L['closed'].')':'').'</option>'; };
  echo '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">';
  echo '<div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">'.$L['l_ticket'].' <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nTicket).'" data-pick-ticket class="'.$SEL_CLS.'"><option value="">'.e($L['ticket_ph']).'</option>';
  foreach ($TKT as $t) echo '<option value="'.e($t['code']).'" data-orig="'.(int)$t['orig'].'" data-days="'.e($t['days']).'">'.e(ufs_inv_ticket_label($t['code'],$lang)).'</option>';
  echo '</select></div>';
  echo '<div class="space-y-2" data-track-wrap data-day="1"><label class="text-sm font-medium text-[#a1a1aa]">'.$L['l_day1'].' <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nD1).'" data-day="1" class="'.$SEL_CLS.'"><option value="">'.e($L['day1_ph']).'</option>';
  foreach ($TR[1] as $v=>$l) echo $opt($v,$l);
  echo '</select></div>';
  echo '<div class="space-y-2" data-track-wrap data-day="2"><label class="text-sm font-medium text-[#a1a1aa]">'.$L['l_day2'].' <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nD2).'" data-day="2" class="'.$SEL_CLS.'"><option value="">'.e($L['day2_ph']).'</option>';
  foreach ($TR[2] as $v=>$l) echo $opt($v,$l);
  echo '</select></div>';
  echo '<div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">'.$L['l_tshirt'].' <span class="text-[#00C1D5]">*</span></label>';
  echo '<select name="'.e($nTshirt).'" class="'.$SEL_CLS.'"><option value="">'.e($L['sel_short']).'</option>';
  foreach (array('M','L','XL','XXL') as $s) echo '<option>'.$s.'</option>';
  echo '</select></div>';
  echo '</div>';
}

// ── 코드 해석 (POST 우선, 없으면 GET)
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$code = $isPost ? gp('code') : (isset($_GET['code']) ? trim($_GET['code']) : '');
$chk  = ($code !== '') ? ufs_invite_code_check($code) : null;
$err  = '';

// ── 언어 해석: 명시(?lang/hidden) 우선, 없으면 코드의 sc_lang, 그 외 ko. (KO/EN 만)
$explicitLang = $isPost ? gp('lang') : (isset($_GET['lang']) ? trim($_GET['lang']) : '');
$hasExplicitLang = ($explicitLang !== '');   // 명시 선택 여부(게이트가 기본값을 sc_lang 위로 고정하지 않도록)
$rawLang = $explicitLang;
if ($rawLang === '' && $chk && isset($chk['row']['sc_lang'])) $rawLang = $chk['row']['sc_lang'];
$lang = ufs_inv_lang($rawLang);
$L    = ufs_inv_dict($lang);
$JOBS = ufs_inv_jobs($lang); $GRADES = ufs_inv_grades($lang); $EX1S = ufs_inv_ex1s($lang);

// 코드가 주어졌으나 무효(만료/시작전/중지/소진/오류) → 게이트에 사유 표시(GET·POST 공통)
if ($code !== '' && (!$chk || !$chk['ok'])) {
    $rmap = array('invalid'=>'e_invalid','inactive'=>'e_inactive','soldout'=>'e_soldout','expired'=>'e_expired','notyet'=>'e_notyet');
    $rz = ($chk && isset($chk['reason'])) ? $chk['reason'] : '';
    $err = isset($rmap[$rz]) ? $L[$rmap[$rz]] : $L['e_code'];
}

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
    // 코드 오류 언어화 — code_check의 reason 기반
    $rmap = array('invalid'=>'e_invalid','inactive'=>'e_inactive','soldout'=>'e_soldout','expired'=>'e_expired','notyet'=>'e_notyet');
    $rz = ($chk && isset($chk['reason'])) ? $chk['reason'] : '';
    $err = isset($rmap[$rz]) ? $L[$rmap[$rz]] : $L['e_code'];
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
      $attendees[] = array('role'=>'member','name'=>trim($mN[$k]),'pos'=>((int)$k + 2), // 폼 카드 번호($i+2)와 일치
        'email'=>isset($mE[$k])?trim($mE[$k]):'', 'phone'=>isset($mP[$k])?trim($mP[$k]):'',
        'job'=>$rep['job'],'company'=>$rep['company'],
        'depart'=>isset($mDe[$k])?trim($mDe[$k]):'','grade'=>isset($mGr[$k])?trim($mGr[$k]):'','ex1'=>isset($mEx[$k])?trim($mEx[$k]):'',
        'ticket'=>isset($mT[$k])?trim($mT[$k]):'','day1'=>isset($mD1[$k])?trim($mD1[$k]):'','day2'=>isset($mD2[$k])?trim($mD2[$k]):'','tshirt'=>isset($mTs[$k])?trim($mTs[$k]):'');
    }

    // 검증
    if (gp('agree_req') !== 'Y') $err = $L['e_agree'];
    elseif ($rep['name']==='' || $rep['email']==='' || $rep['phone']==='' || $rep['company']==='' || $rep['depart']==='' || $rep['job']==='' || $rep['grade']==='' || $rep['ex1']==='') $err = $L['e_rep'];
    if ($err==='' && count($attendees) > $remain) $err = sprintf($L['e_over'], $remain);
    if ($err==='') {
      $seen = array();
      foreach ($attendees as $i=>$a) {
        $lbl = ($a['role']==='rep') ? $L['rep_label'] : sprintf($L['companion_n'], $a['pos']);
        if (!isset($PRODNAME[$a['ticket']])) { $err=sprintf($L['e_ticket'],$lbl); break; }
        $days = $PRODDAYS[$a['ticket']];
        if (in_array('1',$days,true) && ($a['day1']==='' || !isset($UFS_TRACKS[1][$a['day1']]))) { $err=sprintf($L['e_day1'],$lbl); break; }
        if (in_array('2',$days,true) && ($a['day2']==='' || !isset($UFS_TRACKS[2][$a['day2']]))) { $err=sprintf($L['e_day2'],$lbl); break; }
        if (!in_array($a['tshirt'],array('M','L','XL','XXL'),true)) { $err=sprintf($L['e_tshirt'],$lbl); break; }
        if ($a['role']==='member' && ($a['email']==='' || $a['phone']==='')) { $err=sprintf($L['e_contact'],$lbl); break; }
        $emk = strtolower($a['email']);
        if (isset($seen[$emk])) { $err=sprintf($L['e_dup_in'], $a['email']); break; }
        $seen[$emk] = 1;
        if (ufs_invite_email_dup($a['email'])) { $err=sprintf($L['e_dup'], $a['email']); break; }
      }
    }

    // 부분할인(50~99%) — 홀드 후 결제 경유(M3)
    if ($err==='' && !$free) {
      $n = count($attendees);
      if (!ufs_invite_consume($code, $n)) {
        $err = $L['e_soldout2'];
      } else {
        $oid = 'INV'.substr(md5($code.'|'.$attendees[0]['email'].'|'.uniqid('', true)), 0, 28); // 결제 배치 토큰(추측 불가)
        $inserted = array(); $fail = false;
        foreach ($attendees as $a) {
          $a['price'] = ufs_invite_price($a['ticket'], $discount); // 부분할인가(정상가×(100-할인)%)
          $no = ufs_invite_insert_member($a, $code, 1, false, $oid); // status 1=홀드(QR 미생성)
          if ($no > 0) { $inserted[] = $no; } else { $fail = true; break; }
        }
        if ($fail || !$inserted) {
          foreach ($inserted as $no) sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0 WHERE apply_no=".(int)$no);
          sql_query("UPDATE cb_unreal_2026_speaker_code SET sc_used=GREATEST(sc_used-".(int)$n.",0) WHERE sc_code='".sql_real_escape_string(strtoupper($code))."'");
          $err = $L['e_insert'];
        } else {
          header('Location: ticket-invite-pay.php?o='.rawurlencode($oid).'&lang='.$lang);
          exit;
        }
      }
    }
    if ($err==='' && $free) {
      $n = count($attendees);
      // 리뷰 계약: 소진(consume) 성공 후에만 INSERT (선-예약)
      if (!ufs_invite_consume($code, $n)) {
        $err = $L['e_soldout2'];
      } else {
        $inserted = array(); $repNo = 0; $fail = false;
        foreach ($attendees as $a) {
          $a['price'] = 0; // 무료
          $no = ufs_invite_insert_member($a, $code, 10, true);
          if ($no > 0) { $inserted[] = $no; if ($a['role']==='rep') $repNo = $no; }
          else { $fail = true; break; }
        }
        if ($fail || $repNo === 0) {
          foreach ($inserted as $no) sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0 WHERE apply_no=".(int)$no);
          sql_query("UPDATE cb_unreal_2026_speaker_code SET sc_used=GREATEST(sc_used-".(int)$n.",0) WHERE sc_code='".sql_real_escape_string(strtoupper($code))."'");
          $err = $L['e_insert'];
        } else {
          $tok = md5(str_replace("'","\\'", $attendees[0]['email']));  // 대표 apply_password 토큰(완료페이지 검증)
          header('Location: ticket-invite-complete.php?a='.$repNo.'&t='.$tok.'&lang='.$lang);
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
<html lang="<?= e($L['html_lang']) ?>" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title><?= e($L['title']) ?> — Unreal Fest Seoul 2026</title>
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
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white"><?= e($L['home']) ?></a>
  </div>
</header>

<div class="pt-32 pb-24 min-h-screen bg-[#09090b]">
  <div class="gwrap px-6">
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight"><?= e($L['title']) ?></h1>

    <?php if ($err !== ''): ?>
      <div class="mt-4 mb-6 px-4 py-3 bg-[rgba(255,134,116,0.12)] border border-[#ff8674]/40 text-[#ff8674] text-sm"><?= e($err) ?></div>
    <?php endif; ?>

    <?php if (!$valid): ?>
      <!-- 코드 게이트 -->
      <p class="text-[#a1a1aa] mb-8"><?= $L['gate_desc'] ?></p>
      <form method="get" action="ticket-invite.php" class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 max-w-md">
        <?php if ($hasExplicitLang): /* 명시 선택 때만 유지 — 미명시면 코드 sc_lang이 적용되도록 */ ?><input type="hidden" name="lang" value="<?= e($lang) ?>"><?php endif; ?>
        <label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['code_label']) ?></label>
        <input type="text" name="code" value="<?= e($code) ?>" placeholder="<?= e($L['code_ph']) ?>" class="mt-2 w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm uppercase">
        <button type="submit" class="mt-4 w-full py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors"><?= e($L['confirm']) ?></button>
      </form>
    <?php else: ?>
      <!-- 초청 안내 배지 -->
      <p class="text-[#a1a1aa] mb-4"><?= sprintf($L['invited_by'], e($inviter)) ?></p>
      <div class="inline-flex flex-wrap items-center gap-2 mb-8">
        <span class="px-4 py-2 bg-[rgba(0,79,89,0.2)] border border-[#00C1D5]/40 text-[#00C1D5] text-sm font-bold"><?= $free ? e($L['free_badge']) : e(sprintf($L['disc_badge'],$discount)) ?></span>
        <span class="px-4 py-2 border border-[#27272a] text-[#a1a1aa] text-sm"><?= e(sprintf($L['remain'],(int)$remain)) ?></span>
      </div>

      <form name="frm" id="frm" method="post" action="ticket-invite.php" onsubmit="return invValidate()">
      <input type="hidden" name="code" value="<?= e($code) ?>">
      <input type="hidden" name="lang" value="<?= e($lang) ?>">

      <div class="space-y-4">
        <!-- 약관 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5"><?= e($L['agree_title']) ?></h2>
          <div class="space-y-3">
            <label class="flex items-center gap-3 p-3 bg-[rgba(0,79,89,0.3)] border border-[rgba(0,193,213,0.3)] cursor-pointer">
              <input type="checkbox" id="agree_all" class="accent-[#00C1D5]"><span class="text-sm font-bold text-white"><?= e($L['agree_all']) ?></span></label>
            <div class="h-px bg-[#27272a]"></div>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_req" value="Y" class="agree-item mt-0.5 accent-[#00C1D5]" <?= (old('agree_req')==='Y'?'checked':'') ?>>
              <span class="text-sm text-[#a1a1aa]"><a href="#" class="underline text-[#00C1D5]"><?= e($L['agree_req_a']) ?></a><?= e($L['agree_req_join']) ?><a href="#" class="underline text-[#00C1D5]"><?= e($L['agree_req_b']) ?></a><?= e($L['agree_req_tail']) ?> <span class="ml-1 text-xs text-[#00C1D5]"><?= e($L['req']) ?></span></span></label>
            <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
              <input type="checkbox" name="agree_mkt" value="Y" class="agree-item mt-0.5 accent-[#00C1D5]" <?= (old('agree_mkt')==='Y'?'checked':'') ?>>
              <span class="text-sm text-[#a1a1aa]"><?= e($L['agree_mkt']) ?> <span class="ml-1 text-xs text-[#71717a]"><?= e($L['opt']) ?></span></span></label>
          </div>
        </div>

        <!-- 대표(초청 당사자) 정보 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5"><?= e($L['reg_info']) ?></h2>
          <div class="grid md:grid-cols-3 gap-6 mb-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_name']) ?> <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_name" value="<?= old('apply_user_name', $row['sc_name']) ?>" placeholder="<?= e($L['ph_name']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_email']) ?> <span class="text-[#00C1D5]">*</span></label>
              <input type="email" name="apply_user_email" value="<?= old('apply_user_email', $row['sc_email']) ?>" placeholder="<?= e($L['ph_email']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_phone']) ?> <span class="text-[#00C1D5]">*</span></label>
              <input type="tel" name="apply_user_phone" value="<?= old('apply_user_phone', $row['sc_phone']) ?>" placeholder="<?= e($L['ph_phone']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
          <div class="grid md:grid-cols-2 gap-6 mb-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_job']) ?> <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_job" class="<?= $SEL_CLS ?>"><option value=""><?= e($L['sel']) ?></option><?= inv_opts($JOBS) ?></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_company']) ?> <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_company" value="<?= old('apply_user_company', $row['sc_company']) ?>" placeholder="<?= e($L['ph_company']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          </div>
          <div class="grid md:grid-cols-3 gap-6">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_depart']) ?> <span class="text-[#00C1D5]">*</span></label>
              <input type="text" name="apply_user_depart" value="<?= old('apply_user_depart') ?>" placeholder="<?= e($L['ph_depart']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_grade']) ?> <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_grade" class="<?= $SEL_CLS ?>"><option value=""><?= e($L['sel']) ?></option><?= inv_opts($GRADES) ?></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_ex1']) ?> <span class="text-[#00C1D5]">*</span></label>
              <select name="apply_user_ex1" class="<?= $SEL_CLS ?>"><option value=""><?= e($L['sel']) ?></option><?= inv_opts($EX1S) ?></select></div>
          </div>
        </div>

        <!-- 대표 참석 선택 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8" data-card>
          <div class="text-sm font-bold text-[#00C1D5] mb-4"><?= e($L['attend']) ?></div>
          <?php inv_attend_row('rep_ticket','rep_day1','rep_day2','rep_tshirt',$TKT,$UFS_TRACKS,$lang,$L); ?>
        </div>

        <?php if ($companions > 0): ?>
        <!-- 동반자(선택) -->
        <p class="text-xs text-[#71717a] px-1"><?= $L['companion_note'] ?></p>
        <?php for ($i=0; $i<$companions; $i++): ?>
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8" data-card>
          <div class="text-sm font-bold text-[#00C1D5] mb-4"><?= $i+2 ?>. <?= e($L['companion']) ?> <span class="text-[#71717a] font-normal"><?= e($L['opt']) ?></span></div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_name']) ?></label>
              <input type="text" name="member_name[<?= $i ?>]" value="<?= old('member_name['.$i.']') ?>" placeholder="<?= e($L['ph_name']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_email']) ?></label>
              <input type="email" name="member_email[<?= $i ?>]" value="<?= old('member_email['.$i.']') ?>" placeholder="<?= e($L['ph_email']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_phone']) ?></label>
              <input type="tel" name="member_phone[<?= $i ?>]" value="<?= old('member_phone['.$i.']') ?>" placeholder="<?= e($L['ph_phone']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_depart']) ?></label>
              <input type="text" name="member_depart[<?= $i ?>]" value="<?= old('member_depart['.$i.']') ?>" placeholder="<?= e($L['ph_depart']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_grade']) ?></label>
              <select name="member_grade[<?= $i ?>]" class="<?= $SEL_CLS ?>"><option value=""><?= e($L['sel_short']) ?></option><?= inv_opts($GRADES) ?></select></div>
            <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e($L['f_ex1']) ?></label>
              <select name="member_ex1[<?= $i ?>]" class="<?= $SEL_CLS ?>"><option value=""><?= e($L['sel_short']) ?></option><?= inv_opts($EX1S) ?></select></div>
          </div>
          <?php inv_attend_row('member_ticket['.$i.']','member_day1['.$i.']','member_day2['.$i.']','member_tshirt['.$i.']',$TKT,$UFS_TRACKS,$lang,$L); ?>
        </div>
        <?php endfor; ?>
        <?php endif; ?>

        <!-- 요약 -->
        <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
          <h2 class="text-lg font-bold text-white mb-5"><?= e($L['summary']) ?></h2>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center gap-4"><span class="text-[#71717a]"><?= e($L['orig_sum']) ?></span><span id="sumOrig" class="text-[#a1a1aa]">₩0</span></div>
            <?php if (!$free): ?><div class="flex justify-between items-center gap-4"><span class="text-[#71717a]"><?= e(sprintf($L['disc_line'],(int)$discount)) ?></span><span id="sumDisc" class="text-[#00C1D5]">-₩0</span></div><?php endif; ?>
          </div>
          <div class="mt-3 flex justify-between items-end gap-4"><span class="text-[#71717a]"><?= e($L['total']) ?></span><span class="text-3xl font-black text-[#00C1D5]" id="sumTotal"><?= $free ? e($L['free_word']) : '₩0' ?></span></div>
          <button type="submit" class="mt-6 w-full py-4 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors"><?= $free ? e($L['btn_free']) : e($L['btn_pay']) ?></button>
          <p class="text-xs text-[#71717a] mt-3 leading-relaxed"><?= $free ? e($L['note_free']) : e($L['note_paid']) ?><?= e($L['note_tail']) ?></p>
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
  var DISCOUNT = <?= (int)$discount ?>, IS_FREE = <?= $free ? 1 : 0 ?>;
  function won(n){ return '₩' + (n||0).toLocaleString('en-US'); }
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
  function recalc(){
    var orig = 0;
    document.querySelectorAll('[data-pick-ticket]').forEach(function(sel){
      var o = sel.options[sel.selectedIndex];
      if(o){ orig += parseInt(o.getAttribute('data-orig')||'0',10)||0; }
    });
    var so = document.getElementById('sumOrig'); if(so) so.textContent = won(orig);
    if(!IS_FREE){
      var pay = Math.round(orig * (100 - DISCOUNT) / 100);
      var sd = document.getElementById('sumDisc'); if(sd) sd.textContent = '-' + won(orig - pay);
      var st = document.getElementById('sumTotal'); if(st) st.textContent = won(pay);
    }
  }
  document.querySelectorAll('[data-card]').forEach(function(card){
    var sel = card.querySelector('[data-pick-ticket]');
    if(sel){ sel.addEventListener('change', function(){ syncTracks(card); recalc(); }); syncTracks(card); }
  });
  recalc();
  var all=document.getElementById('agree_all');
  if(all) all.addEventListener('change', function(){ document.querySelectorAll('.agree-item').forEach(function(c){ c.checked=all.checked; }); });
})();
function invValidate(){
  var req=document.querySelector('input[name=agree_req]');
  if(req && !req.checked){ alert(<?= json_encode($L['e_agree']) ?>); return false; }
  return true;
}
</script>
<?php endif; ?>
</body>
</html>
