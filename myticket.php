<?php
/* Unreal Fest Seoul 2026 — 등록 확인/수정 (myticket.php)
 * 조회(이메일+연락처) → 정보(QR+상세) → 수정 / 취소.
 * PHP 7.0 호환. 공통: _ticket_init.php (e/asset_v/$trackRemain/$UFS_TRACKS).
 */
require __DIR__ . '/_ticket_init.php';   // common.php + e() + asset_v() + $trackRemain + $UFS_TRACKS
function pp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }

// 트랙 코드 → 라벨
function ufs_track_label_code($code, $UFS_TRACKS){
    foreach ($UFS_TRACKS as $d=>$ts) { if (isset($ts[$code])) return $ts[$code].' (Day '.$d.')'; }
    return $code;
}
// 수정폼 트랙 드롭다운 (마감 트랙은 disabled, 단 본인 현재 트랙은 유지)
function ufs_track_select($day, $tracks, $trackRemain, $current){
    $field = ($day === 1) ? 'day1track' : 'day2track';
    echo '<select name="'.$field.'" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">';
    echo '<option value="">선택해 주세요</option>';
    foreach ($tracks as $v=>$l) {
        $full = isset($trackRemain[$v]) && $trackRemain[$v] <= 0;
        $sel  = ($v === $current);
        $dis  = ($full && !$sel) ? ' disabled' : '';
        echo '<option value="'.e($v).'"'.($sel?' selected':'').$dis.'>'.e($l).($full?' (마감)':'').'</option>';
    }
    echo '</select>';
}

$row = null; $error = ''; $mode = 'lookup'; $saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = pp('email'); $phone = pp('phone');
    $action = pp('action');
    $em = sql_real_escape_string($email); $ph = sql_real_escape_string($phone);
    $ph_digits = sql_real_escape_string(preg_replace('/[^0-9]/', '', $phone)); // 하이픈 무관 조회용 (숫자만)
    if ($email === '' || $phone === '') {
        $error = '이메일과 연락처를 모두 입력해주세요.';
    } else {
        $row = sql_fetch("select * from cb_unreal_2026_event2_apply where apply_user_email = '$em' and REPLACE(REPLACE(apply_user_phone,'-',''),' ','') = '$ph_digits' and apply_temp_yn = 'N' and apply_pay_status <> 0 order by apply_no desc limit 1");
        if (!$row) {
            $error = '등록 정보를 찾을 수 없습니다. 이메일과 연락처를 확인해주세요.';
        } else {
            $is_paid_row = $row['free_yn'] === 'N' && $row['apply_product_code'] !== 'ONLINE';
            if ($action === 'cancel') {
                if (trim((string)$row['apply_group_code']) !== '') {
                    // ── 단체 구성원 취소: 부분환불 경로. 그룹 공용 TID 전액환불 금지(다른 인원까지 환불되는 사고 방지). ──
                    require_once __DIR__ . '/_group_apply.php';
                    $gc = ufs_group_member_cancel((int)$row['apply_no']);
                    if (empty($gc['ok'])) {
                        $gm = isset($gc['msg']) ? preg_replace('/["\\\\\r\n]/', ' ', $gc['msg']) : '';
                        if (!empty($gc['manual'])) {
                            exit('<script>alert("'.$gm.'\n취소·환불은 사무국(02-326-3701 / info@epiclounge.co.kr)으로 요청해 주세요.");history.back();</script>');
                        }
                        exit('<script>alert("취소 처리에 실패했습니다.'.($gm!==''?('\n사유: '.$gm):'').'\n사무국(02-326-3701)으로 문의해 주세요.");history.back();</script>');
                    }
                    $mode = 'cancelled'; $cancelled_paid = true; $row = null;
                } else {
                    // 유료 결제건이면 INICIS 자동 환불 시도 (운영모드에서만 실제 환불; 테스트는 상태만)
                    $paid_cancel = ($row['free_yn']==='N' && $row['apply_product_code']!=='ONLINE' && trim((string)$row['pay_tid'])!=='');
                    if ($paid_cancel) {
                        require_once __DIR__.'/_refund.php';
                        $rf = ufs_inicis_refund($row['pay_tid'], isset($row['pay_paymethod'])?$row['pay_paymethod']:'', '회원요청 취소', $row['apply_no']);
                        // 환불 성공(ok) 또는 이미 환불됨(already=기 취소거래) → 등록 취소 진행. 그 외만 차단.
                        if (empty($rf['skipped']) && empty($rf['ok']) && empty($rf['already'])) {
                            $rf_reason = isset($rf['msg']) ? preg_replace('/[\"\\\\\r\n]/', ' ', $rf['msg']) : '';
                            exit('<script>alert("환불 처리에 실패했습니다.'.($rf_reason!==''?('\n사유: '.$rf_reason):'').'\n사무국(02-326-3701)으로 문의해주세요.");history.back();</script>');
                        }
                    }
                    sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status = 0, refund_date = now() WHERE apply_no = '".intval($row['apply_no'])."'");
                    // 쿠폰 사용횟수 복원(-1) — 완료건에 쿠폰이 적용됐던 경우만(결제완료 시 +1 했던 것 되돌림)
                    if ($row['pay_complete'] === 'Y' && !empty($row['apply_coupon_code'])) {
                        @sql_query("UPDATE cb_unreal_2026_coupon SET cp_used=GREATEST(cp_used-1,0) WHERE cp_code='".sql_real_escape_string($row['apply_coupon_code'])."'");
                    }
                    $mode = 'cancelled'; $cancelled_paid = $is_paid_row; $row = null;
                }
            } else if ($action === 'edit') {
                $mode = 'edit';
            } else if ($action === 'update') {
                // 현재 트랙
                $cur_d1=''; $cur_d2='';
                foreach (explode(',', $row['apply_track']) as $t) { $t=trim($t); if (strpos($t,'DAY1')===0) $cur_d1=$t; else if (strpos($t,'DAY2')===0) $cur_d2=$t; }
                // 입력값
                $u_job=pp('apply_user_job'); $u_company=pp('apply_user_company'); $u_depart=pp('apply_user_depart');
                $u_grade=pp('apply_user_grade'); $u_ex1=pp('apply_user_ex1'); $u_tshirt=pp('tshirt');
                $u_agree=(pp('agree_mkt')!=='')?'1':'0';
                $d1=pp('day1track'); $d2=pp('day2track');
                // 상품별 트랙 조합
                $code=$row['apply_product_code']; $tracks=array();
                if ($code==='NORMAL_ALL') { if($d1)$tracks[]=$d1; if($d2)$tracks[]=$d2; }
                else if ($code==='NORMAL_20') { if($d1)$tracks[]=$d1; }
                else if ($code==='NORMAL_21') { if($d2)$tracks[]=$d2; }
                $track_str=implode(',',$tracks);
                // 변경된 트랙 정원 체크 (오프라인만)
                $cap_err='';
                if ($is_paid_row) {
                    foreach (array($d1=>$cur_d1, $d2=>$cur_d2) as $new=>$old) {
                        if ($new!=='' && $new!==$old) {
                            $tke=sql_real_escape_string($new);
                            if (isset($trackRemain[$new]) && $trackRemain[$new] <= 0) { $cap_err='선택하신 트랙('.ufs_track_label_code($new,$UFS_TRACKS).')의 정원이 마감되었습니다.'; }
                        }
                    }
                }
                if ($cap_err!=='') {
                    $error=$cap_err; $mode='edit';
                } else {
                    $sets = "apply_user_job='".sql_real_escape_string(strip_tags($u_job))."',"
                          . "apply_user_company='".sql_real_escape_string(strip_tags($u_company))."',"
                          . "apply_user_depart='".sql_real_escape_string(strip_tags($u_depart))."',"
                          . "apply_user_grade='".sql_real_escape_string(strip_tags($u_grade))."',"
                          . "apply_user_ex1='".sql_real_escape_string(strip_tags($u_ex1))."',"
                          . "apply_user_event_agree='".sql_real_escape_string($u_agree)."'";
                    if ($is_paid_row) {
                        $sets .= ",apply_tshirt='".sql_real_escape_string(strip_tags($u_tshirt))."'"
                              .  ",apply_track='".sql_real_escape_string($track_str)."'";
                    }
                    sql_query("UPDATE cb_unreal_2026_event2_apply SET $sets WHERE apply_no='".intval($row['apply_no'])."' AND apply_user_email='$em' AND apply_user_phone='$ph'");
                    $row = sql_fetch("select * from cb_unreal_2026_event2_apply where apply_no='".intval($row['apply_no'])."'");
                    $mode = 'view'; $saved = true;
                }
            } else {
                $mode = 'view';
            }
        }
    }
}

$is_paid = $row && $row['free_yn'] === 'N' && $row['apply_product_code'] !== 'ONLINE';
$qr_jpg = ($row && $is_paid && file_exists(__DIR__."/qrdata/".$row['apply_no'].".jpg")) ? "qrdata/".$row['apply_no'].".jpg" : '';
// 현재 트랙 분해 (view/edit 공용)
$cur_d1=''; $cur_d2='';
if ($row) { foreach (explode(',', $row['apply_track']) as $t) { $t=trim($t); if (strpos($t,'DAY1')===0) $cur_d1=$t; else if (strpos($t,'DAY2')===0) $cur_d2=$t; } }
// 정가/실결제 (얼리버드 50% 할인 표기용) — 정가는 상품코드 기준
$orig_price = 0; $paid_price = 0;
if ($row) {
  $_c = $row['apply_product_code'];
  $orig_price = ($_c === 'NORMAL_ALL') ? 120000 : ((($_c === 'NORMAL_20') || ($_c === 'NORMAL_21')) ? 60000 : 0);
  $paid_price = (int)$row['apply_product_price'];
}
// 옵션 목록
$OPT_JOB   = array('직장인','학생','교육자/교육기관','인디 개발자','프리랜서');
$OPT_GRADE = array('비주얼 아트','프로그래밍','프로덕션','엔지니어링','설계','기획','R&D','IT','감독/PD','비즈니스/마케팅','C-level','기타');
$OPT_EX1   = array('게임','영화 & TV','방송 & 라이브 이벤트','애니메이션','건축','자동차','제조/시뮬레이션','소프트웨어 & 툴 개발','VR·AR','교육','기타');
function ufs_opt($list,$cur){ foreach($list as $o){ echo '<option'.($o===$cur?' selected':'').'>'.e($o).'</option>'; } }
?>
<!DOCTYPE html>
<html lang="ko" class="dark"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>등록 확인 — Unreal Fest Seoul 2026</title>
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
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<main class="pt-32 pb-24 min-h-screen">
  <div class="max-w-2xl mx-auto px-6">
  <?php if ($mode === 'cancelled'): ?>
    <div class="text-center">
      <h1 class="text-3xl font-bold mb-3">등록이 취소되었습니다</h1>
      <?php if (!empty($cancelled_paid)): ?>
      <p class="text-[#a1a1aa] mb-10">유료 등록 건의 환불은 영업일 기준 최대 5일 이내 처리됩니다.</p>
      <?php else: ?>
      <p class="text-[#a1a1aa] mb-10">온라인 등록은 별도의 환불 절차가 없습니다. 다시 시청을 원하시면 행사 페이지에서 재등록해 주세요.</p>
      <?php endif; ?>
      <a href="myticket.php" class="clip-btn inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-4 font-bold">확인</a>
    </div>

  <?php elseif ($mode === 'lookup'): ?>
    <!-- 조회 -->
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-6 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 확인</h1>
    <p class="text-[#a1a1aa] mb-8">등록 정보를 조회하고 수정 또는 취소할 수 있습니다.</p>

    <?php if (false): // 안내 박스 비표시 (주석처리) ?>
    <!-- 안내 -->
    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-4">안내</h2>
      <ul class="space-y-2 text-sm text-[#a1a1aa]">
        <li class="flex gap-2"><span class="text-[#71717a]">•</span><span>등록 정보 확인 후 수정 혹은 등록 취소가 가능합니다.</span></li>
        <li class="flex gap-2"><span class="text-[#71717a]">•</span><span>온라인 등록 후 오프라인 등록을 원하시면 기존 등록 취소 후 티켓 구매/결제 진행을 완료해야 합니다. (중복 불가)</span></li>
        <li class="flex gap-2"><span class="text-[#71717a]">•</span><span>1인당 티켓은 1매만 가능합니다.</span></li>
        <li class="flex gap-2 text-[#00C1D5]"><span>•</span><span><?= e(ufs_refund_notice()) ?></span></li>
      </ul>
    </div>
    <?php endif; ?>

    <!-- 조회 폼 -->
    <form method="post" class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
      <h2 class="text-lg font-bold text-white mb-5">등록 시 사용한 이메일과 연락처를 입력해 주세요.</h2>
      <?php if ($error): ?><p class="text-[#ff8674] text-sm mb-4"><?= e($error) ?></p><?php endif; ?>
      <div class="space-y-5">
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일 <span class="text-[#00C1D5]">*</span></label><input type="email" name="email" placeholder="등록 시 사용한 이메일" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처 <span class="text-[#00C1D5]">*</span></label><input type="tel" name="phone" placeholder="01012345678" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
      </div>
      <button type="submit" class="mt-6 w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold flex items-center justify-center gap-2 transition-all">조회하기 <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></button>
    </form>
    <!-- <a href="index.php#register" class="block w-full text-center text-sm text-[#71717a] hover:text-white py-4 transition-colors">취소</a> -->

  <?php elseif ($mode === 'edit'): ?>
    <!-- 수정 -->
    <form method="post" class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8" action="myticket.php">
      <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
      <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
      <input type="hidden" name="action" value="update">
      <a href="myticket.php" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-6 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
      <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 정보 수정</h1>
      <p class="text-[#a1a1aa] mb-8">이름·이메일·연락처는 본인인증 정보로 변경할 수 없습니다.</p>
      <?php if ($error): ?><p class="text-[#ff8674] text-sm mb-4"><?= e($error) ?></p><?php endif; ?>
      <!-- 변경 불가 정보 (읽기 전용) -->
      <div class="bg-[#111115] border border-[#27272a] p-5 mb-6 space-y-2 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">이름</span><span class="font-bold"><?= e($row['apply_user_name']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">이메일</span><span class="break-all text-right"><?= e($row['apply_user_email']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">연락처</span><span class="text-right"><?= e($row['apply_user_phone']) ?></span></div>
      </div>
      <div class="space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직업</label>
            <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해 주세요</option><?php ufs_opt($OPT_JOB, $row['apply_user_job']); ?></select></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">회사명/소속</label>
            <input type="text" name="apply_user_company" value="<?= e($row['apply_user_company']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">부서</label>
            <input type="text" name="apply_user_depart" value="<?= e($row['apply_user_depart']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">직무</label>
            <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해 주세요</option><?php ufs_opt($OPT_GRADE, $row['apply_user_grade']); ?></select></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">산업/관심 분야</label>
            <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value="">선택해 주세요</option><?php ufs_opt($OPT_EX1, $row['apply_user_ex1']); ?></select></div>
        </div>
        <?php if ($is_paid): ?>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">티셔츠 사이즈</label>
          <div class="flex flex-wrap gap-3">
            <?php foreach (array('M','L','XL','XXL') as $size): ?>
            <label class="relative cursor-pointer"><input type="radio" name="tshirt" value="<?= $size ?>" class="peer sr-only" <?= $row['apply_tshirt']===$size?'checked':'' ?>>
              <div class="w-14 h-14 border border-[#27272a] bg-[#0e0f14] flex items-center justify-center text-sm font-bold text-[#71717a] peer-checked:border-[#00C1D5] peer-checked:bg-[rgba(0,79,89,0.2)] peer-checked:text-[#00C1D5] transition-all hover:border-white/20"><?= $size ?></div></label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php if ($row['apply_product_code']==='NORMAL_ALL' || $row['apply_product_code']==='NORMAL_20'): ?>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Day 1 트랙 (8.20 목)</label><?php ufs_track_select(1, $UFS_TRACKS[1], $trackRemain, $cur_d1); ?></div>
        <?php endif; ?>
        <?php if ($row['apply_product_code']==='NORMAL_ALL' || $row['apply_product_code']==='NORMAL_21'): ?>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">Day 2 트랙 (8.21 금)</label><?php ufs_track_select(2, $UFS_TRACKS[2], $trackRemain, $cur_d2); ?></div>
        <?php endif; ?>
        <?php endif; ?>
        <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="agree_mkt" class="accent-[#00C1D5]" <?= $row['apply_user_event_agree']==='1'?'checked':'' ?>><span class="text-sm text-[#a1a1aa]">광고 수신 동의 (선택)</span></label>
      </div>
      <div class="flex gap-3 mt-8">
        <a href="myticket.php" class="flex-1 text-center border border-[#27272a] text-[#a1a1aa] py-3 font-bold hover:text-white hover:border-white/20 transition-colors">수정 취소하기</a>
        <button type="submit" class="flex-1 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold transition-all">저장하기</button>
      </div>
    </form>

  <?php else: /* view */ ?>
    <!-- 정보 -->
    <a href="myticket.php" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-6 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> 돌아가기</a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 정보</h1>
    <p class="text-[#a1a1aa] mb-10">등록하신 정보를 확인하고 수정 또는 취소할 수 있습니다.</p>
    <?php if ($saved): ?><div class="bg-[rgba(0,193,213,0.08)] border border-[rgba(0,193,213,0.3)] text-[#9adbe8] text-sm px-4 py-3 mb-6">수정 내용이 저장되었습니다.</div><?php endif; ?>

    <?php if ($is_paid): ?>
    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4 text-center">
      <?php if ($qr_jpg): ?>
        <p class="text-sm text-[#a1a1aa] mb-4">현장 체크인 시 QR코드를 제시해주세요</p>
        <div class="bg-white p-5 inline-block clip-tr-16"><img src="<?= asset_v($qr_jpg) ?>" alt="체크인 QR" class="w-60 h-60"></div>
        <?php $qr_rel=$qr_jpg; include __DIR__.'/_qr_actions.php'; ?>
      <?php else: ?>
        <p class="text-sm text-[#a1a1aa]">QR 코드는 결제 완료 후 생성됩니다.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-1">참가자 정보</h2>
      <p class="text-xs text-[#71717a] mb-5">이름·이메일·연락처는 본인인증 정보로 변경할 수 없습니다.</p>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">이름</span><span class="font-bold text-right"><?= e($row['apply_user_name']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">이메일</span><span class="text-right break-all"><?= e($row['apply_user_email']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">연락처</span><span class="text-right"><?= e($row['apply_user_phone']) ?></span></div>
      </div>
    </div>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-5">소속 및 관심 분야</h2>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">직업</span><span class="text-right"><?= e($row['apply_user_job']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">회사명/소속</span><span class="text-right"><?= e($row['apply_user_company']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">부서</span><span class="text-right"><?= e($row['apply_user_depart']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">직무</span><span class="text-right"><?= e($row['apply_user_grade']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">산업/관심 분야</span><span class="text-right"><?= e($row['apply_user_ex1']) ?></span></div>
      </div>
    </div>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-5">등록 정보</h2>
      <style>@media (min-width:640px){.tkt-br{display:none}}</style>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">등록 유형</span><span class="font-bold text-[#00C1D5]"><?= $is_paid ? '오프라인' : '온라인 무료' ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">티켓</span><span class="font-bold text-right"><?= str_replace('2026 ', '2026<br class="tkt-br">', e($row['apply_product_name'])) ?></span></div>
        <?php if ($is_paid): ?>
        <div class="flex justify-between gap-4 items-start"><span class="text-[#71717a]">결제 금액</span>
          <span class="text-right">
            <span class="font-bold text-[#00C1D5]">₩<?= e(number_format($paid_price)) ?></span>
            <?php if ($orig_price > $paid_price): ?><br><span class="text-xs text-[#71717a]">정가 <span class="line-through">₩<?= e(number_format($orig_price)) ?></span> · 얼리버드 50%</span><?php endif; ?>
          </span>
        </div>
        <?php if ($cur_d1): ?><div class="flex justify-between gap-4"><span class="text-[#71717a]">Day 1 트랙</span><span class="text-right"><?= e(ufs_track_label_code($cur_d1,$UFS_TRACKS)) ?></span></div><?php endif; ?>
        <?php if ($cur_d2): ?><div class="flex justify-between gap-4"><span class="text-[#71717a]">Day 2 트랙</span><span class="text-right"><?= e(ufs_track_label_code($cur_d2,$UFS_TRACKS)) ?></span></div><?php endif; ?>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">티셔츠</span><span class="text-right"><?= e($row['apply_tshirt']) ?></span></div>
        <?php endif; ?>
        <?php if ($row['apply_user_event_agree']==='1'): ?><div class="flex justify-between gap-4"><span class="text-[#71717a]">광고 수신 동의</span><span class="text-right">동의</span></div><?php endif; ?>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]">상태</span><span class="font-bold"><?= ((int)$row['apply_pay_status'] === 10) ? '등록 완료' : (((int)$row['apply_pay_status'] === 1) ? '입금 대기' : '확인 필요') ?></span></div>
      </div>
    </div>

    <div class="flex gap-3 mt-6">
      <form method="post" class="flex-1">
        <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
        <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
        <input type="hidden" name="action" value="edit">
        <button type="submit" class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold transition-all">수정하기</button>
      </form>
      <form method="post" class="flex-1" onsubmit="return confirm('<?= $is_paid ? '등록을 취소하시겠습니까?\n취소 후 재등록 시 오프라인 티켓이 매진되어 구매가 어려울 수 있습니다.' : '등록을 취소하시겠습니까?' ?>');">
        <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
        <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
        <input type="hidden" name="action" value="cancel">
        <button type="submit" class="w-full border border-[#27272a] text-[#71717a] py-3 font-bold hover:text-[#a1a1aa] hover:border-white/20 transition-all">등록 취소하기</button>
      </form>
    </div>
  <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/_pf_footer.php'; ?>
<script>
// 조회 연락처 입력 — 숫자만 유지(하이픈 등 제거). 등록 시 하이픈 없이 저장되므로 매칭 일치.
(function(){
  var p=document.querySelector('input[type="tel"][name="phone"]');
  if(!p) return;
  p.addEventListener('input',function(){
    this.value=this.value.replace(/[^0-9]/g,'').slice(0,11);
  });
})();
</script>
</body></html>
