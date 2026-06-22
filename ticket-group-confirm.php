<?php
/* Unreal Fest Seoul 2026 — 단체 등록 확인/저장 (ticket-group-confirm.php) [Phase 2]
 * ticket-group.php POST 수신 → 서버 검증 → 확인 화면 → (action=register) DB 저장 → 결제(Phase3) 연결.
 * 신규 테이블: cb_unreal_2026_group / cb_unreal_2026_group_member. PHP 7.0 호환.
 */
require __DIR__ . '/_ticket_init.php'; // common.php + e() + $UFS_TRACKS + _pricing

function gp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }
function garr($k){ return (isset($_POST[$k]) && is_array($_POST[$k])) ? $_POST[$k] : array(); }

$PRODNAME = array('NORMAL_ALL'=>'양일권 (8.20~21)','NORMAL_20'=>'1일권 · Day 1','NORMAL_21'=>'1일권 · Day 2');
$PRODDAYS = array('NORMAL_ALL'=>array('1','2'),'NORMAL_20'=>array('1'),'NORMAL_21'=>array('2'));

function track_label($code, $UFS_TRACKS){
    if ($code === '') return '';
    foreach ($UFS_TRACKS as $d=>$ts){ if (isset($ts[$code])) return $ts[$code]; }
    return $code;
}
// 서버 쿠폰 검증 → 할인율(%) 또는 0
function coupon_percent($code){
    $code = strtoupper(trim($code)); if ($code==='') return 0;
    $r = @sql_fetch("SELECT * FROM cb_unreal_2026_coupon WHERE cp_code='".sql_real_escape_string($code)."' LIMIT 1");
    if (!$r || $r['cp_active']!=='Y') return 0;
    if (!empty($r['cp_expire']) && $r['cp_expire']!=='0000-00-00' && $r['cp_expire'] < date('Y-m-d')) return 0;
    if ((int)$r['cp_max']>0 && (int)$r['cp_used']>=(int)$r['cp_max']) return 0;
    return (int)$r['cp_percent'];
}

// ── 입력 수집
$rep = array(
    'name'=>gp('apply_user_name'), 'email'=>gp('apply_user_email'), 'phone'=>gp('apply_user_phone'),
    'job'=>gp('apply_user_job'), 'company'=>gp('apply_user_company'), 'depart'=>gp('apply_user_depart'),
    'grade'=>gp('apply_user_grade'), 'ex1'=>gp('apply_user_ex1'), 'ci'=>gp('apply_ci'), 'di'=>gp('apply_di'),
    'ticket'=>gp('rep_ticket'), 'day1'=>gp('rep_day1'), 'day2'=>gp('rep_day2'), 'tshirt'=>gp('rep_tshirt'),
);
$paymethod = (gp('group_paymethod')==='bank') ? 'bank' : 'card';
$coupon_code = strtoupper(gp('coupon_code'));

$mName=garr('member_name'); $mEmail=garr('member_email'); $mPhone=garr('member_phone');
$mDepart=garr('member_depart'); $mGrade=garr('member_grade'); $mEx1=garr('member_ex1');
$mTicket=garr('member_ticket'); $mD1=garr('member_day1'); $mD2=garr('member_day2'); $mTshirt=garr('member_tshirt');

// ── 참석자 목록 구성 (대표자 결제만(NONE)=비참석 → 명단/인원 제외)
$attendees = array();
$rep_attend = ($rep['ticket']!=='' && $rep['ticket']!=='NONE') ? 'Y' : 'N';
if ($rep_attend==='Y') {
    $attendees[] = array('role'=>'rep','name'=>$rep['name'],'email'=>$rep['email'],'phone'=>$rep['phone'],
        'job'=>$rep['job'],'company'=>$rep['company'],'depart'=>$rep['depart'],'grade'=>$rep['grade'],'ex1'=>$rep['ex1'],
        'ticket'=>$rep['ticket'],'day1'=>$rep['day1'],'day2'=>$rep['day2'],'tshirt'=>$rep['tshirt']);
}
foreach (array_keys($mName) as $k) {
    $nm = trim($mName[$k]);
    if ($nm==='') continue;
    $attendees[] = array('role'=>'member','name'=>$nm,
        'email'=>isset($mEmail[$k])?trim($mEmail[$k]):'', 'phone'=>isset($mPhone[$k])?trim($mPhone[$k]):'',
        'job'=>$rep['job'], 'company'=>$rep['company'],   // 멤버 직업·회사명=대표자값 자동
        'depart'=>isset($mDepart[$k])?trim($mDepart[$k]):'', 'grade'=>isset($mGrade[$k])?trim($mGrade[$k]):'', 'ex1'=>isset($mEx1[$k])?trim($mEx1[$k]):'',
        'ticket'=>isset($mTicket[$k])?trim($mTicket[$k]):'', 'day1'=>isset($mD1[$k])?trim($mD1[$k]):'', 'day2'=>isset($mD2[$k])?trim($mD2[$k]):'', 'tshirt'=>isset($mTshirt[$k])?trim($mTshirt[$k]):'');
}
$member_count = 0; foreach ($attendees as $a){ if ($a['role']==='member') $member_count++; }

// ── 검증
$err = '';
if ($rep['ci']==='') $err = '대표자 본인 인증이 필요합니다.';
elseif ($rep['name']==='' || $rep['email']==='' || $rep['phone']==='' || $rep['company']==='' || $rep['depart']==='' || $rep['job']==='' || $rep['grade']==='' || $rep['ex1']==='') $err = '대표자 정보를 모두 입력해 주세요.';
elseif ($member_count < 4) $err = '대표자 외 최소 4인을 입력해 주세요.';
elseif (count($attendees) < 1) $err = '참석 인원이 없습니다.';
if ($err==='') {
    foreach ($attendees as $i=>$a) {
        if (!isset($PRODNAME[$a['ticket']])) { $err = ($a['role']==='rep'?'대표자':($i.'번 참석자')).'의 티켓이 올바르지 않습니다.'; break; }
        $days = $PRODDAYS[$a['ticket']];
        if (in_array('1',$days,true) && ($a['day1']==='' || !isset($UFS_TRACKS[1][$a['day1']]))) { $err='참석자 트랙(Day1) 선택을 확인해 주세요.'; break; }
        if (in_array('2',$days,true) && ($a['day2']==='' || !isset($UFS_TRACKS[2][$a['day2']]))) { $err='참석자 트랙(Day2) 선택을 확인해 주세요.'; break; }
        if (!in_array($a['tshirt'],array('M','L','XL','XXL'),true)) { $err='참석자 티셔츠 선택을 확인해 주세요.'; break; }
        if ($a['role']==='member' && ($a['email']==='' || $a['phone']==='')) { $err=$i.'번 참석자의 이메일/연락처를 입력해 주세요.'; break; }
    }
}

// ── 금액(서버 재계산): 유효할인 = max(단체할인, 쿠폰) — 중복 안 됨
$gdisc = ufs_group_discount();
$cpct  = coupon_percent($coupon_code);
$eff   = max($gdisc, $cpct);
$disc_src = ($cpct > $gdisc && $cpct>0) ? 'coupon' : ($gdisc>0 ? 'group' : '');
$sumOrig=0; $total=0;
foreach ($attendees as &$a){ $o=ufs_ticket_orig($a['ticket']); $p=(int)(round(($o*(100-$eff)/100)/100)*100); $a['price']=$p; $sumOrig+=$o; $total+=$p; }
unset($a);

// ── 등록 처리
$done = false; $grp_code = '';
if ($err==='' && gp('action')==='register') {
    sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_group (grp_no INT UNSIGNED NOT NULL AUTO_INCREMENT, grp_code VARCHAR(40) NOT NULL DEFAULT '', rep_name VARCHAR(60), rep_email VARCHAR(120), rep_phone VARCHAR(30), rep_job VARCHAR(60), rep_company VARCHAR(120), rep_depart VARCHAR(80), rep_grade VARCHAR(60), rep_ex1 VARCHAR(80), rep_ci VARCHAR(120), rep_di VARCHAR(120), rep_attend CHAR(1) DEFAULT 'Y', paymethod VARCHAR(10), coupon_code VARCHAR(40), discount_pct INT DEFAULT 0, total_amount INT DEFAULT 0, headcount INT DEFAULT 0, pay_status VARCHAR(20) DEFAULT 'pending', reg DATETIME, PRIMARY KEY(grp_no), KEY k_code(grp_code)) DEFAULT CHARSET=utf8");
    sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_group_member (gm_no INT UNSIGNED NOT NULL AUTO_INCREMENT, grp_no INT NOT NULL, role VARCHAR(10), name VARCHAR(60), email VARCHAR(120), phone VARCHAR(30), job VARCHAR(60), company VARCHAR(120), depart VARCHAR(80), grade VARCHAR(60), ex1 VARCHAR(80), ticket VARCHAR(20), day1 VARCHAR(20), day2 VARCHAR(20), tshirt VARCHAR(10), price INT DEFAULT 0, PRIMARY KEY(gm_no), KEY k_grp(grp_no)) DEFAULT CHARSET=utf8");

    $grp_code = 'G'.date('ymdHis').rand(100,999);
    $f = function($v){ return "'".sql_real_escape_string($v)."'"; };
    sql_query("INSERT INTO cb_unreal_2026_group (grp_code,rep_name,rep_email,rep_phone,rep_job,rep_company,rep_depart,rep_grade,rep_ex1,rep_ci,rep_di,rep_attend,paymethod,coupon_code,discount_pct,total_amount,headcount,pay_status,reg) VALUES (".
        $f($grp_code).",".$f($rep['name']).",".$f($rep['email']).",".$f($rep['phone']).",".$f($rep['job']).",".$f($rep['company']).",".$f($rep['depart']).",".$f($rep['grade']).",".$f($rep['ex1']).",".$f($rep['ci']).",".$f($rep['di']).",".$f($rep_attend).",".$f($paymethod).",".$f($coupon_code).",".(int)$eff.",".(int)$total.",".(int)count($attendees).",'pending',now())");
    $grp_no = (int)sql_insert_id();
    foreach ($attendees as $a) {
        sql_query("INSERT INTO cb_unreal_2026_group_member (grp_no,role,name,email,phone,job,company,depart,grade,ex1,ticket,day1,day2,tshirt,price) VALUES (".
            (int)$grp_no.",".$f($a['role']).",".$f($a['name']).",".$f($a['email']).",".$f($a['phone']).",".$f($a['job']).",".$f($a['company']).",".$f($a['depart']).",".$f($a['grade']).",".$f($a['ex1']).",".$f($a['ticket']).",".$f($a['day1']).",".$f($a['day2']).",".$f($a['tshirt']).",".(int)$a['price'].")");
    }
    $done = true;
}
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>단체 등록 확인 — Unreal Fest Seoul 2026</title>
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>
<div class="pt-32 pb-24 min-h-screen">
  <div class="max-w-5xl mx-auto px-6">

  <?php if ($err !== ''): ?>
    <h1 class="text-2xl font-bold mb-4">입력 확인이 필요합니다</h1>
    <div class="bg-[rgba(250,70,22,0.1)] border border-[#FA4616]/40 text-[#ff8674] p-4 mb-6"><?= e($err) ?></div>
    <a href="javascript:history.back()" class="inline-block px-6 py-3 bg-[#27272a] hover:bg-[#3f3f46] text-white font-bold">← 돌아가서 수정</a>

  <?php elseif ($done): ?>
    <h1 class="text-2xl md:text-3xl font-bold mb-2">단체 등록이 접수되었습니다</h1>
    <p class="text-[#a1a1aa] mb-8">접수번호 <b class="text-[#00C1D5]"><?= e($grp_code) ?></b> · 총 <?= count($attendees) ?>명 · 결제 금액 ₩<?= number_format($total) ?></p>
    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-6">
      <p class="text-[#a1a1aa] leading-relaxed">
        <?php if ($paymethod==='card'): ?>
        선택하신 <b class="text-white">신용카드</b> 결제 단계는 현재 준비 중입니다(Phase 3). 접수 내역은 저장되었으며, 결제 연동 완료 후 안내드립니다.
        <?php else: ?>
        선택하신 <b class="text-white">무통장 입금</b> 안내(계좌·금액·기한 + 통장/사업자등록증)는 현재 준비 중입니다(Phase 3). 접수 내역은 저장되었으며, 대표자 연락처로 입금 안내가 발송될 예정입니다.
        <?php endif; ?>
      </p>
    </div>
    <a href="index.php" class="inline-block px-6 py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold">홈으로</a>

  <?php else: ?>
    <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white mb-6 text-sm">← 수정하기</a>
    <h1 class="text-3xl font-bold mb-2 tracking-tight">등록 정보 확인</h1>
    <p class="text-[#a1a1aa] mb-8">아래 내용을 확인하신 후 등록을 진행해 주세요. 등록 후 결제 단계로 이동합니다.</p>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-4">대표자</h2>
      <div class="grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">이름</span><span><?= e($rep['name']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">연락처</span><span><?= e($rep['phone']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">이메일</span><span><?= e($rep['email']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">회사/소속</span><span><?= e($rep['company']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">참석 여부</span><span><?= $rep_attend==='Y' ? '참석' : '결제만 (비참석)' ?></span></div>
      </div>
    </div>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-4">참석자 (<?= count($attendees) ?>명)</h2>
      <div class="overflow-x-auto">
      <table class="w-full text-sm whitespace-nowrap">
        <thead><tr class="text-[#71717a] border-b border-[#27272a]"><th class="text-left py-2 pr-4">이름</th><th class="text-left py-2 pr-4">구분</th><th class="text-left py-2 pr-4">티켓</th><th class="text-left py-2 pr-4">Day1</th><th class="text-left py-2 pr-4">Day2</th><th class="text-left py-2 pr-4">티셔츠</th><th class="text-right py-2">금액</th></tr></thead>
        <tbody>
        <?php foreach ($attendees as $a): ?>
          <tr class="border-b border-[#1b1b20]">
            <td class="py-2 pr-4"><?= e($a['name']) ?></td>
            <td class="py-2 pr-4 text-[#a1a1aa]"><?= $a['role']==='rep'?'대표자':'멤버' ?></td>
            <td class="py-2 pr-4"><?= e($PRODNAME[$a['ticket']]) ?></td>
            <td class="py-2 pr-4 text-[#a1a1aa]"><?= e(track_label($a['day1'],$UFS_TRACKS)) ?: '-' ?></td>
            <td class="py-2 pr-4 text-[#a1a1aa]"><?= e(track_label($a['day2'],$UFS_TRACKS)) ?: '-' ?></td>
            <td class="py-2 pr-4 text-[#a1a1aa]"><?= e($a['tshirt']) ?></td>
            <td class="py-2 text-right">₩<?= number_format($a['price']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-6">
      <div class="space-y-2 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">정상가 합계</span><span class="text-[#a1a1aa]">₩<?= number_format($sumOrig) ?></span></div>
        <?php if ($eff>0): ?><div class="flex justify-between gap-4"><span class="text-[#71717a]">적용 할인 (<?= $disc_src==='coupon'?'쿠폰':'단체' ?> <?= (int)$eff ?>%<?= $disc_src==='coupon'?' · '.e($coupon_code):'' ?>)</span><span class="text-[#00C1D5]">-₩<?= number_format($sumOrig-$total) ?></span></div><?php endif; ?>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">결제 수단</span><span><?= $paymethod==='card'?'신용카드':'무통장 입금' ?></span></div>
      </div>
      <div class="border-t border-[#27272a] mt-4 pt-4 flex justify-between items-end"><span class="text-[#71717a]">총 결제 금액</span><span class="text-3xl font-black text-[#00C1D5]">₩<?= number_format($total) ?></span></div>
    </div>

    <form method="post" action="ticket-group-confirm.php">
      <?php
      foreach (array('apply_user_name','apply_user_email','apply_user_phone','apply_user_job','apply_user_company','apply_user_depart','apply_user_grade','apply_user_ex1','apply_ci','apply_di','rep_ticket','rep_day1','rep_day2','rep_tshirt','group_paymethod','coupon_code') as $hf) {
        echo '<input type="hidden" name="'.e($hf).'" value="'.e(gp($hf)).'">';
      }
      foreach (array('member_name'=>$mName,'member_email'=>$mEmail,'member_phone'=>$mPhone,'member_depart'=>$mDepart,'member_grade'=>$mGrade,'member_ex1'=>$mEx1,'member_ticket'=>$mTicket,'member_day1'=>$mD1,'member_day2'=>$mD2,'member_tshirt'=>$mTshirt) as $fn=>$arr) {
        foreach ($arr as $k=>$v) echo '<input type="hidden" name="'.e($fn).'['.e($k).']" value="'.e($v).'">';
      }
      ?>
      <input type="hidden" name="action" value="register">
      <button type="submit" class="w-full py-4 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors">등록하고 결제 진행</button>
    </form>
  <?php endif; ?>

  </div>
</div>
</body>
</html>
