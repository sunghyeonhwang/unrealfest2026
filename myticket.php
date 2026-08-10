<?php
/* Unreal Fest Seoul 2026 — 등록 확인/수정 (myticket.php)
 * 조회(이메일+연락처) → 정보(QR+상세) → 수정 / 취소.
 * KO/EN 이중언어(?lang=en, 쿠키 지속). 해외(Dodo) 등록자용 영문 전환 버튼(헤더). PHP 7.0.
 */
require __DIR__ . '/_ticket_init.php';   // common.php + e() + asset_v() + $trackRemain + $UFS_TRACKS
function pp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }

// ── 언어 (GET/POST lang > 쿠키 > ko). 출력 전에 쿠키 갱신 ──
$lang = 'ko';
if (isset($_REQUEST['lang'])) { $lang = ($_REQUEST['lang'] === 'en') ? 'en' : 'ko'; }
elseif (isset($_COOKIE['ufs_mt_lang']) && $_COOKIE['ufs_mt_lang'] === 'en') { $lang = 'en'; }
@setcookie('ufs_mt_lang', $lang, time()+3600*24*30, '/');

$L = array(
  'ko' => array(
    'home'=>'홈으로','back'=>'돌아가기',
    'title_view'=>'등록 정보','sub_view'=>'등록하신 정보를 확인하고 수정 또는 취소할 수 있습니다.',
    'saved'=>'수정 내용이 저장되었습니다.',
    'qr_present'=>'현장 체크인 시 QR코드를 제시해주세요','qr_pending'=>'QR 코드는 결제 완료 후 생성됩니다.',
    'attendee'=>'참가자 정보','immutable'=>'이름·이메일·연락처는 본인인증 정보로 변경할 수 없습니다.',
    'name'=>'이름','email'=>'이메일','phone'=>'연락처',
    'affiliation'=>'소속 및 관심 분야','job'=>'직업','company'=>'회사명/소속','depart'=>'부서','grade'=>'직무','ex1'=>'산업/관심 분야',
    'reg_info'=>'등록 정보','reg_type'=>'등록 유형','offline'=>'오프라인','online_free'=>'온라인 무료',
    'ticket'=>'티켓','pay_amount'=>'결제 금액','list_price'=>'정가','earlybird50'=>'얼리버드 50%',
    'day1track'=>'Day 1 트랙','day2track'=>'Day 2 트랙','tshirt'=>'티셔츠',
    'mkt_agree'=>'광고 수신 동의','agreed'=>'동의','status'=>'상태',
    'st_done'=>'등록 완료','st_wait'=>'입금 대기','st_check'=>'확인 필요',
    'btn_edit'=>'수정하기','btn_cancel'=>'등록 취소하기','cert_btn'=>'참가확인증 다운로드',
    'title_lookup'=>'등록 확인','sub_lookup'=>'등록 정보를 조회하고 수정 또는 취소할 수 있습니다.',
    'lookup_head'=>'등록 시 사용한 이메일과 연락처를 입력해 주세요.',
    'ph_email'=>'등록 시 사용한 이메일','ph_phone'=>'01012345678','btn_lookup'=>'조회하기',
    'err_both'=>'이메일과 연락처를 모두 입력해주세요.','err_notfound'=>'등록 정보를 찾을 수 없습니다. 이메일과 연락처를 확인해주세요.',
    'title_edit'=>'등록 정보 수정','sub_edit'=>'이름·이메일·연락처는 본인인증 정보로 변경할 수 없습니다.',
    'select'=>'선택해 주세요','tshirt_size'=>'티셔츠 사이즈',
    'day1_label'=>'Day 1 트랙 (8.20 목)','day2_label'=>'Day 2 트랙 (8.21 금)',
    'mkt_opt'=>'광고 수신 동의 (선택)','btn_discard'=>'수정 취소하기','btn_save'=>'저장하기','full'=>'마감','nearfull'=>'마감 임박',
    'title_cancelled'=>'등록이 취소되었습니다',
    'cancelled_paid'=>'유료 등록 건의 환불은 영업일 기준 최대 5일 이내 처리됩니다.',
    'cancelled_free'=>'온라인 등록은 별도의 환불 절차가 없습니다. 다시 시청을 원하시면 행사 페이지에서 재등록해 주세요.',
    'ok'=>'확인',
    'confirm_paid'=>'등록을 취소하시겠습니까?\\n취소 후 재등록 시 오프라인 티켓이 매진되어 구매가 어려울 수 있습니다.',
    'confirm_free'=>'등록을 취소하시겠습니까?',
    'a_refundfail'=>'환불 처리에 실패했습니다.','a_reason'=>'사유','a_office'=>'사무국(02-326-3701)으로 문의해주세요.',
    'a_cancelfail'=>'취소 처리에 실패했습니다.','a_cancel_office'=>'취소·환불은 사무국(02-326-3701 / info@epiclounge.co.kr)으로 요청해 주세요.',
    'a_refund_over'=>'얼리버드 취소/환불 가능 기간이 종료되어 취소가 불가능합니다.\\n고객센터(02-326-3701 / info@epiclounge.co.kr)로 문의해 주세요',
    'refund_over_note'=>'얼리버드 취소/환불 가능 기간이 종료되어 취소가 불가능합니다. 고객센터(02-326-3701 / info@epiclounge.co.kr)로 문의해 주세요',
    'a_refund_over_reg'=>'취소/환불 가능 기간이 종료되어 취소가 불가능합니다.\\n고객센터(02-326-3701 / info@epiclounge.co.kr)로 문의해 주세요',
    'refund_over_note_reg'=>'취소/환불 가능 기간이 종료되어 취소가 불가능합니다. 고객센터(02-326-3701 / info@epiclounge.co.kr)로 문의해 주세요',
    'toggle'=>'EN','doc_title'=>'등록 확인 — Unreal Fest Seoul 2026',
  ),
  'en' => array(
    'home'=>'Home','back'=>'Back',
    'title_view'=>'Your registration','sub_view'=>'Review your registration, then edit or cancel it.',
    'saved'=>'Your changes have been saved.',
    'qr_present'=>'Present this QR code at on-site check-in.','qr_pending'=>'Your QR code will be generated after payment is completed.',
    'attendee'=>'Attendee information','immutable'=>'Name, email and phone cannot be changed.',
    'name'=>'Name','email'=>'Email','phone'=>'Phone',
    'affiliation'=>'Affiliation & interests','job'=>'Occupation','company'=>'Company / Organization','depart'=>'Department','grade'=>'Role','ex1'=>'Industry',
    'reg_info'=>'Registration details','reg_type'=>'Type','offline'=>'Offline','online_free'=>'Online (free)',
    'ticket'=>'Ticket','pay_amount'=>'Amount paid','list_price'=>'List price','earlybird50'=>'Early Bird 50%',
    'day1track'=>'Day 1 track','day2track'=>'Day 2 track','tshirt'=>'T-shirt',
    'mkt_agree'=>'Marketing consent','agreed'=>'Agreed','status'=>'Status',
    'st_done'=>'Confirmed','st_wait'=>'Awaiting payment','st_check'=>'Needs review',
    'btn_edit'=>'Edit','btn_cancel'=>'Cancel registration','cert_btn'=>'Download certificate',
    'title_lookup'=>'Find my registration','sub_lookup'=>'Look up your registration to edit or cancel it.',
    'lookup_head'=>'Enter the email and phone you used to register.',
    'ph_email'=>'Email used at registration','ph_phone'=>'Phone number','btn_lookup'=>'Look up',
    'err_both'=>'Please enter both email and phone.','err_notfound'=>'We couldn\'t find your registration. Please check your email and phone.',
    'title_edit'=>'Edit registration','sub_edit'=>'Name, email and phone cannot be changed.',
    'select'=>'Select','tshirt_size'=>'T-shirt size',
    'day1_label'=>'Day 1 track (Aug 20, Thu)','day2_label'=>'Day 2 track (Aug 21, Fri)',
    'mkt_opt'=>'Marketing consent (optional)','btn_discard'=>'Discard','btn_save'=>'Save','full'=>'Full','nearfull'=>'Almost full',
    'title_cancelled'=>'Registration cancelled',
    'cancelled_paid'=>'Paid registrations are refunded within up to 5 business days.',
    'cancelled_free'=>'Online registration has no separate refund process. Please register again from the event page if needed.',
    'ok'=>'OK',
    'confirm_paid'=>'Cancel your registration?\\nIf you re-register later, offline tickets may be sold out.',
    'confirm_free'=>'Cancel your registration?',
    'a_refundfail'=>'Refund failed.','a_reason'=>'Reason','a_office'=>'Please contact the office (+82-2-326-3701).',
    'a_cancelfail'=>'Cancellation failed.','a_cancel_office'=>'Please contact the office (+82-2-326-3701 / info@epiclounge.co.kr) for cancellation and refund.',
    'a_refund_over'=>'The early-bird cancellation/refund period has ended.\\nPlease contact our customer center (+82-2-326-3701 / info@epiclounge.co.kr).',
    'refund_over_note'=>'The early-bird cancellation/refund period has ended. Please contact our customer center (+82-2-326-3701 / info@epiclounge.co.kr).',
    'a_refund_over_reg'=>'The cancellation/refund period has ended.\\nPlease contact our customer center (+82-2-326-3701 / info@epiclounge.co.kr).',
    'refund_over_note_reg'=>'The cancellation/refund period has ended. Please contact our customer center (+82-2-326-3701 / info@epiclounge.co.kr).',
    'toggle'=>'한국어','doc_title'=>'My registration — Unreal Fest Seoul 2026',
  ),
);
function t($k){ global $L, $lang; return isset($L[$lang][$k]) ? $L[$lang][$k] : (isset($L['ko'][$k]) ? $L['ko'][$k] : $k); }

// 트랙 코드 → 라벨 (lang 인지)
function mt_track_en($code){
    $m = array('DAY1_TR1'=>'Game: Programming','DAY1_TR2'=>'Game: Art','DAY1_TR3'=>'Media & Entertainment','DAY1_TR4'=>'Cross-Industries',
               'DAY2_TR1'=>'Game: Programming','DAY2_TR2'=>'Game: Art','DAY2_TR3'=>'Media & Entertainment','DAY2_TR4'=>'Manufacturing & Simulation');
    return isset($m[$code]) ? $m[$code] : $code;
}
function ufs_track_label_code($code, $UFS_TRACKS, $lang='ko'){
    foreach ($UFS_TRACKS as $d=>$ts) {
        if (isset($ts[$code])) return (($lang==='en') ? mt_track_en($code) : $ts[$code]).' (Day '.$d.')';
    }
    return ($lang==='en') ? mt_track_en($code) : $code;
}
// 수정폼 트랙 드롭다운 (마감 트랙 disabled, 단 본인 현재 트랙 유지)
function ufs_track_select($day, $tracks, $trackRemain, $current, $lang='ko'){
    $field = ($day === 1) ? 'day1track' : 'day2track';
    echo '<select name="'.$field.'" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none">';
    echo '<option value="">'.e(t('select')).'</option>';
    foreach ($tracks as $v=>$l) {
        $rem  = isset($trackRemain[$v]) ? $trackRemain[$v] : null;
        $full = ($rem !== null && $rem <= 0);
        $near = (!$full && $rem !== null && $rem > 0 && $rem <= 10);   // 마감 임박(잔여 10석 이하) — 정확한 수는 미노출
        $sel  = ($v === $current);
        $dis  = ($full && !$sel) ? ' disabled' : '';
        $lab  = ($lang==='en') ? mt_track_en($v) : $l;
        $tag  = $full ? (' ('.t('full').')') : ($near ? (' ('.t('nearfull').')') : '');
        echo '<option value="'.e($v).'"'.($sel?' selected':'').$dis.'>'.e($lab).$tag.'</option>';
    }
    echo '</select>';
}

// 환불 마감 판정 (정책 A) — 유료건만. 얼리버드 구매(등록시각≤얼리버드마감)=7/27 23:59, 정상가=8/18 23:59. 무료/온라인=마감없음.
function ufs_refund_deadline_ts($row){
    if (!$row || $row['free_yn']==='Y' || $row['apply_product_code']==='ONLINE') return 0;
    $reg = (isset($row['apply_reg_datetime']) && $row['apply_reg_datetime'] && strpos((string)$row['apply_reg_datetime'],'0000')!==0) ? strtotime($row['apply_reg_datetime']) : 0;
    $eb_end = function_exists('ufs_earlybird_end_ts') ? ufs_earlybird_end_ts() : strtotime('2026-07-27 23:59:59 +0900');
    $is_eb_ticket = ($reg > 0 && $reg <= $eb_end);
    return $is_eb_ticket ? $eb_end : strtotime('2026-08-18 23:59:59 +0900');
}
// 얼리버드 구매 티켓 여부 (마감 안내 문구 분기용)
function ufs_refund_is_eb_ticket($row){
    if (!$row || $row['free_yn']==='Y' || $row['apply_product_code']==='ONLINE') return false;
    $reg = (isset($row['apply_reg_datetime']) && $row['apply_reg_datetime'] && strpos((string)$row['apply_reg_datetime'],'0000')!==0) ? strtotime($row['apply_reg_datetime']) : 0;
    $eb_end = function_exists('ufs_earlybird_end_ts') ? ufs_earlybird_end_ts() : strtotime('2026-07-27 23:59:59 +0900');
    return ($reg > 0 && $reg <= $eb_end);
}

$row = null; $error = ''; $mode = 'lookup'; $saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = pp('email'); $phone = pp('phone');
    $action = pp('action');
    $em = sql_real_escape_string($email); $ph = sql_real_escape_string($phone);
    $ph_digits = sql_real_escape_string(preg_replace('/[^0-9]/', '', $phone)); // 하이픈 무관 조회용 (숫자만)
    if ($email === '' || $phone === '') {
        $error = t('err_both');
    } else {
        $row = sql_fetch("select * from cb_unreal_2026_event2_apply where apply_user_email = '$em' and REPLACE(REPLACE(apply_user_phone,'-',''),' ','') = '$ph_digits' and apply_temp_yn = 'N' and apply_pay_status <> 0 order by apply_no desc limit 1");
        if (!$row) {
            $error = t('err_notfound');
        } else {
            $is_paid_row = $row['free_yn'] === 'N' && $row['apply_product_code'] !== 'ONLINE';
            if ($action === 'cancel') {
                // 환불 마감 강제(정책 A) — 얼리버드 티켓 7/28~, 정상가 티켓 8/19~ 셀프취소 차단 → 고객센터 안내
                $__dl = ufs_refund_deadline_ts($row);
                if ($__dl > 0 && time() > $__dl) {
                    $__ak = ufs_refund_is_eb_ticket($row) ? 'a_refund_over' : 'a_refund_over_reg';
                    exit('<script>alert("'.t($__ak).'");history.back();</script>');
                }
                if (trim((string)$row['apply_group_code']) !== '') {
                    // ── 단체 구성원 취소: 부분환불 경로. 그룹 공용 TID 전액환불 금지(다른 인원까지 환불되는 사고 방지). ──
                    require_once __DIR__ . '/_group_apply.php';
                    $gc = ufs_group_member_cancel((int)$row['apply_no']);
                    if (empty($gc['ok'])) {
                        $gm = isset($gc['msg']) ? preg_replace('/["\\\\\r\n]/', ' ', $gc['msg']) : '';
                        if (!empty($gc['manual'])) {
                            exit('<script>alert("'.$gm.'\n'.t('a_cancel_office').'");history.back();</script>');
                        }
                        exit('<script>alert("'.t('a_cancelfail').($gm!==''?('\n'.t('a_reason').': '.$gm):'').'\n'.t('a_office').'");history.back();</script>');
                    }
                    $mode = 'cancelled'; $cancelled_paid = true; $row = null;
                } else {
                    // 유료 결제건이면 자동 환불 시도 (Dodo=Dodo환불 / 그외=INICIS; 운영모드에서만 실제)
                    $paid_cancel = ($row['free_yn']==='N' && $row['apply_product_code']!=='ONLINE' && trim((string)$row['pay_tid'])!=='');
                    if ($paid_cancel) {
                        if (isset($row['pay_paymethod']) && $row['pay_paymethod']==='paypal') {
                            require_once __DIR__.'/_paypal.php';   // 해외 PayPal 결제 → PayPal 전액환불(capture_id=pay_tid)
                            $rf = ufs_pp_refund($row['pay_tid'], '회원요청 취소', $row['apply_no']);
                        } else if (isset($row['pay_paymethod']) && $row['pay_paymethod']==='dodo') {
                            require_once __DIR__.'/_dodo.php';
                            $rf = ufs_dodo_refund($row['pay_tid'], '회원요청 취소', $row['apply_no']);
                        } else {
                            require_once __DIR__.'/_refund.php';
                            $rf = ufs_inicis_refund($row['pay_tid'], isset($row['pay_paymethod'])?$row['pay_paymethod']:'', '회원요청 취소', $row['apply_no']);
                        }
                        // 환불 성공(ok) 또는 이미 환불됨(already) → 등록 취소 진행. 그 외만 차단.
                        if (empty($rf['skipped']) && empty($rf['ok']) && empty($rf['already'])) {
                            $rf_reason = isset($rf['msg']) ? preg_replace('/[\"\\\\\r\n]/', ' ', $rf['msg']) : '';
                            exit('<script>alert("'.t('a_refundfail').($rf_reason!==''?('\n'.t('a_reason').': '.$rf_reason):'').'\n'.t('a_office').'");history.back();</script>');
                        }
                    }
                    sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status = 0, refund_date = now() WHERE apply_no = '".intval($row['apply_no'])."'");
                    // 쿠폰 사용횟수 복원(-1) — 완료건에 쿠폰이 적용됐던 경우만
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
                            if (isset($trackRemain[$new]) && $trackRemain[$new] <= 0) {
                                $cap_err = ($lang==='en')
                                    ? ('The selected track ('.ufs_track_label_code($new,$UFS_TRACKS,'en').') is full.')
                                    : ('선택하신 트랙('.ufs_track_label_code($new,$UFS_TRACKS).')의 정원이 마감되었습니다.');
                            }
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
$refund_dl = $row ? ufs_refund_deadline_ts($row) : 0;
$refund_blocked = ($refund_dl > 0 && time() > $refund_dl);   // 마감 지남 → 셀프취소 차단(고객센터)
$refund_note_key = ($row && ufs_refund_is_eb_ticket($row)) ? 'refund_over_note' : 'refund_over_note_reg';  // 얼리버드/정상가 문구 분기
$qr_jpg = ($row && $is_paid && file_exists(__DIR__."/qrdata/".$row['apply_no'].".jpg")) ? "qrdata/".$row['apply_no'].".jpg" : '';
// 참가확인증(오프라인만) — 발급 가능일: 8/21권=8/21부터, 그 외(양일·8/20권)=8/20부터. 미리보기 ?certpv=ufscert2026
$cert_avail_date = ($row && $row['apply_product_code']==='NORMAL_21') ? '2026-08-21' : '2026-08-20';
$cert_avail_disp = ($cert_avail_date==='2026-08-21') ? array('ko'=>'8월 21일','en'=>'Aug 21') : array('ko'=>'8월 20일','en'=>'Aug 20');
$cert_preview = (isset($_GET['certpv']) && $_GET['certpv']==='ufscert2026');
$cert_ok = ($is_paid && (date('Y-m-d') >= $cert_avail_date || $cert_preview));
// 현재 트랙 분해 (view/edit 공용)
$cur_d1=''; $cur_d2='';
if ($row) { foreach (explode(',', $row['apply_track']) as $t) { $t=trim($t); if (strpos($t,'DAY1')===0) $cur_d1=$t; else if (strpos($t,'DAY2')===0) $cur_d2=$t; } }
// 정가/실결제 (얼리버드 50% 할인 표기용)
$orig_price = 0; $paid_price = 0;
if ($row) {
  $_c = $row['apply_product_code'];
  $orig_price = ($_c === 'NORMAL_ALL') ? 120000 : ((($_c === 'NORMAL_20') || ($_c === 'NORMAL_21')) ? 60000 : 0);
  $paid_price = (int)$row['apply_product_price'];
}
// 옵션 목록 (lang별) — 값 매칭: KO 등록자=한글값, EN(해외)등록자=영문값
$OPTS = array(
  'ko' => array(
    'job'   => array('직장인','학생','교육자/교육기관','인디 개발자','프리랜서'),
    'grade' => array('비주얼 아트','프로그래밍','프로덕션','엔지니어링','설계','기획','R&D','IT','감독/PD','비즈니스/마케팅','C-level','기타'),
    'ex1'   => array('게임','영화 & TV','방송 & 라이브 이벤트','애니메이션','건축','자동차','제조/시뮬레이션','소프트웨어 & 툴 개발','VR·AR','교육','기타'),
  ),
  'en' => array(
    'job'   => array('Professional','Student','Educator / Institution','Indie developer','Freelancer'),
    'grade' => array('Visual Art','Programming','Production','Engineering','Design','Planning','R&D','IT','Director / PD','Business / Marketing','C-level','Other'),
    'ex1'   => array('Games','Film & TV','Broadcast & Live Events','Animation','Architecture','Automotive','Manufacturing / Simulation','Software & Tools','VR / AR','Education','Other'),
  ),
);
$OPT_JOB = $OPTS[$lang]['job']; $OPT_GRADE = $OPTS[$lang]['grade']; $OPT_EX1 = $OPTS[$lang]['ex1'];
function ufs_opt($list,$cur){ foreach($list as $o){ echo '<option'.($o===$cur?' selected':'').'>'.e($o).'</option>'; } }
$other_lang = ($lang === 'en') ? 'ko' : 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="dark"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title><?= e(t('doc_title')) ?></title>
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
    <div class="flex items-center gap-4">
      <?php if ($row): /* view/edit: 언어 전환 시 현재 조회건 유지(POST) */ ?>
      <form method="post" style="display:inline">
        <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
        <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
        <input type="hidden" name="action" value="<?= $mode==='edit'?'edit':'view' ?>">
        <input type="hidden" name="lang" value="<?= $other_lang ?>">
        <button type="submit" class="text-sm text-[#a1a1aa] hover:text-white border border-[#27272a] rounded px-2.5 py-1"><?= e(t('toggle')) ?></button>
      </form>
      <?php else: ?>
      <a href="?lang=<?= $other_lang ?>" class="text-sm text-[#a1a1aa] hover:text-white border border-[#27272a] rounded px-2.5 py-1"><?= e(t('toggle')) ?></a>
      <?php endif; ?>
      <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white"><?= e(t('home')) ?></a>
    </div>
  </div>
</header>

<main class="pt-32 pb-24 min-h-screen">
  <div class="max-w-2xl mx-auto px-6">
  <?php if ($mode === 'cancelled'): ?>
    <div class="text-center">
      <h1 class="text-3xl font-bold mb-3"><?= e(t('title_cancelled')) ?></h1>
      <?php if (!empty($cancelled_paid)): ?>
      <p class="text-[#a1a1aa] mb-10"><?= e(t('cancelled_paid')) ?></p>
      <?php else: ?>
      <p class="text-[#a1a1aa] mb-10"><?= e(t('cancelled_free')) ?></p>
      <?php endif; ?>
      <a href="myticket.php" class="clip-btn inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-4 font-bold"><?= e(t('ok')) ?></a>
    </div>

  <?php elseif ($mode === 'lookup'): ?>
    <!-- 조회 -->
    <a href="index.php#register" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-6 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> <?= e(t('back')) ?></a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight"><?= e(t('title_lookup')) ?></h1>
    <p class="text-[#a1a1aa] mb-8"><?= e(t('sub_lookup')) ?></p>

    <!-- 조회 폼 -->
    <form method="post" class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
      <input type="hidden" name="lang" value="<?= $lang ?>">
      <h2 class="text-lg font-bold text-white mb-5"><?= e(t('lookup_head')) ?></h2>
      <?php if ($error): ?><p class="text-[#ff8674] text-sm mb-4"><?= e($error) ?></p><?php endif; ?>
      <div class="space-y-5">
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('email')) ?> <span class="text-[#00C1D5]">*</span></label><input type="email" name="email" placeholder="<?= e(t('ph_email')) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('phone')) ?> <span class="text-[#00C1D5]">*</span></label><input type="tel" name="phone" placeholder="<?= e(t('ph_phone')) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
      </div>
      <button type="submit" class="mt-6 w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold flex items-center justify-center gap-2 transition-all"><?= e(t('btn_lookup')) ?> <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></button>
    </form>

  <?php elseif ($mode === 'edit'): ?>
    <!-- 수정 -->
    <form method="post" class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8" action="myticket.php">
      <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
      <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="lang" value="<?= $lang ?>">
      <a href="myticket.php" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-6 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> <?= e(t('back')) ?></a>
      <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight"><?= e(t('title_edit')) ?></h1>
      <p class="text-[#a1a1aa] mb-8"><?= e(t('sub_edit')) ?></p>
      <?php if ($error): ?><p class="text-[#ff8674] text-sm mb-4"><?= e($error) ?></p><?php endif; ?>
      <!-- 변경 불가 정보 (읽기 전용) -->
      <div class="bg-[#111115] border border-[#27272a] p-5 mb-6 space-y-2 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('name')) ?></span><span class="font-bold"><?= e($row['apply_user_name']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('email')) ?></span><span class="break-all text-right"><?= e($row['apply_user_email']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('phone')) ?></span><span class="text-right"><?= e($row['apply_user_phone']) ?></span></div>
      </div>
      <div class="space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('job')) ?></label>
            <select name="apply_user_job" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value=""><?= e(t('select')) ?></option><?php ufs_opt($OPT_JOB, $row['apply_user_job']); ?></select></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('company')) ?></label>
            <input type="text" name="apply_user_company" value="<?= e($row['apply_user_company']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('depart')) ?></label>
            <input type="text" name="apply_user_depart" value="<?= e($row['apply_user_depart']) ?>" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('grade')) ?></label>
            <select name="apply_user_grade" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value=""><?= e(t('select')) ?></option><?php ufs_opt($OPT_GRADE, $row['apply_user_grade']); ?></select></div>
          <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('ex1')) ?></label>
            <select name="apply_user_ex1" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white outline-none focus:border-[#00C1D5] text-sm appearance-none"><option value=""><?= e(t('select')) ?></option><?php ufs_opt($OPT_EX1, $row['apply_user_ex1']); ?></select></div>
        </div>
        <?php if ($is_paid): ?>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('tshirt_size')) ?></label>
          <div class="flex flex-wrap gap-3">
            <?php foreach (array('M','L','XL','XXL') as $size): ?>
            <label class="relative cursor-pointer"><input type="radio" name="tshirt" value="<?= $size ?>" class="peer sr-only" <?= $row['apply_tshirt']===$size?'checked':'' ?>>
              <div class="w-14 h-14 border border-[#27272a] bg-[#0e0f14] flex items-center justify-center text-sm font-bold text-[#71717a] peer-checked:border-[#00C1D5] peer-checked:bg-[rgba(0,79,89,0.2)] peer-checked:text-[#00C1D5] transition-all hover:border-white/20"><?= $size ?></div></label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php if ($row['apply_product_code']==='NORMAL_ALL' || $row['apply_product_code']==='NORMAL_20'): ?>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('day1_label')) ?></label><?php ufs_track_select(1, $UFS_TRACKS[1], $trackRemain, $cur_d1, $lang); ?></div>
        <?php endif; ?>
        <?php if ($row['apply_product_code']==='NORMAL_ALL' || $row['apply_product_code']==='NORMAL_21'): ?>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]"><?= e(t('day2_label')) ?></label><?php ufs_track_select(2, $UFS_TRACKS[2], $trackRemain, $cur_d2, $lang); ?></div>
        <?php endif; ?>
        <?php endif; ?>
        <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="agree_mkt" class="accent-[#00C1D5]" <?= $row['apply_user_event_agree']==='1'?'checked':'' ?>><span class="text-sm text-[#a1a1aa]"><?= e(t('mkt_opt')) ?></span></label>
      </div>
      <div class="flex gap-3 mt-8">
        <a href="myticket.php" class="flex-1 text-center border border-[#27272a] text-[#a1a1aa] py-3 font-bold hover:text-white hover:border-white/20 transition-colors"><?= e(t('btn_discard')) ?></a>
        <button type="submit" class="flex-1 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold transition-all"><?= e(t('btn_save')) ?></button>
      </div>
    </form>

  <?php else: /* view */ ?>
    <!-- 정보 -->
    <a href="myticket.php" class="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-6 text-sm"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> <?= e(t('back')) ?></a>
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight"><?= e(t('title_view')) ?></h1>
    <p class="text-[#a1a1aa] mb-10"><?= e(t('sub_view')) ?></p>
    <?php if ($saved): ?><div class="bg-[rgba(0,193,213,0.08)] border border-[rgba(0,193,213,0.3)] text-[#9adbe8] text-sm px-4 py-3 mb-6"><?= e(t('saved')) ?></div><?php endif; ?>

    <?php if ($is_paid): ?>
    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4 text-center">
      <?php if ($qr_jpg): ?>
        <p class="text-sm text-[#a1a1aa] mb-4"><?= e(t('qr_present')) ?></p>
        <div class="bg-white p-5 inline-block clip-tr-16"><img src="<?= asset_v($qr_jpg) ?>" alt="check-in QR" class="w-60 h-60"></div>
        <?php $qr_rel=$qr_jpg; include __DIR__.'/_qr_actions.php'; ?>
      <?php else: ?>
        <p class="text-sm text-[#a1a1aa]"><?= e(t('qr_pending')) ?></p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-1"><?= e(t('attendee')) ?></h2>
      <p class="text-xs text-[#71717a] mb-5"><?= e(t('immutable')) ?></p>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('name')) ?></span><span class="font-bold text-right"><?= e($row['apply_user_name']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('email')) ?></span><span class="text-right break-all"><?= e($row['apply_user_email']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('phone')) ?></span><span class="text-right"><?= e($row['apply_user_phone']) ?></span></div>
      </div>
    </div>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-5"><?= e(t('affiliation')) ?></h2>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('job')) ?></span><span class="text-right"><?= e($row['apply_user_job']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('company')) ?></span><span class="text-right"><?= e($row['apply_user_company']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('depart')) ?></span><span class="text-right"><?= e($row['apply_user_depart']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('grade')) ?></span><span class="text-right"><?= e($row['apply_user_grade']) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('ex1')) ?></span><span class="text-right"><?= e($row['apply_user_ex1']) ?></span></div>
      </div>
    </div>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-5"><?= e(t('reg_info')) ?></h2>
      <style>@media (min-width:640px){.tkt-br{display:none}}</style>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('reg_type')) ?></span><span class="font-bold text-[#00C1D5]"><?= $is_paid ? e(t('offline')) : e(t('online_free')) ?></span></div>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('ticket')) ?></span><span class="font-bold text-right"><?= str_replace('2026 ', '2026·<br class="tkt-br">', e($row['apply_product_name'])) ?></span></div>
        <?php if ($is_paid): ?>
        <div class="flex justify-between gap-4 items-start"><span class="text-[#71717a]"><?= e(t('pay_amount')) ?></span>
          <span class="text-right">
            <span class="font-bold text-[#00C1D5]">₩<?= e(number_format($paid_price)) ?></span>
            <?php if ($orig_price > $paid_price): ?><br><span class="text-xs text-[#71717a]"><?= e(t('list_price')) ?> <span class="line-through">₩<?= e(number_format($orig_price)) ?></span> · <?= e(t('earlybird50')) ?></span><?php endif; ?>
          </span>
        </div>
        <?php if ($cur_d1): ?><div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('day1track')) ?></span><span class="text-right"><?= e(ufs_track_label_code($cur_d1,$UFS_TRACKS,$lang)) ?></span></div><?php endif; ?>
        <?php if ($cur_d2): ?><div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('day2track')) ?></span><span class="text-right"><?= e(ufs_track_label_code($cur_d2,$UFS_TRACKS,$lang)) ?></span></div><?php endif; ?>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('tshirt')) ?></span><span class="text-right"><?= e($row['apply_tshirt']) ?></span></div>
        <?php endif; ?>
        <?php if ($row['apply_user_event_agree']==='1'): ?><div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('mkt_agree')) ?></span><span class="text-right"><?= e(t('agreed')) ?></span></div><?php endif; ?>
        <div class="flex justify-between gap-4"><span class="text-[#71717a]"><?= e(t('status')) ?></span><span class="font-bold"><?= ((int)$row['apply_pay_status'] === 10) ? e(t('st_done')) : (((int)$row['apply_pay_status'] === 1) ? e(t('st_wait')) : e(t('st_check'))) ?></span></div>
      </div>
    </div>

    <?php if ($cert_ok): ?>
    <form method="post" action="cert.php<?= $cert_preview ? '?certpv=ufscert2026' : '' ?>" target="_blank" class="mb-4">
      <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
      <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
      <button type="submit" class="w-full bg-[#111115] border border-[rgba(0,193,213,0.4)] text-white py-3.5 font-bold hover:border-[#00C1D5] transition-all flex items-center justify-center gap-2">
        <svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
        <?= e(t('cert_btn')) ?>
      </button>
    </form>
    <?php elseif ($is_paid): ?>
    <p class="text-xs text-[#71717a] mb-4 text-center"><?= $lang==='en' ? 'Your certificate of participation will be available from '.e($cert_avail_disp['en']).'.' : '참가확인증은 '.e($cert_avail_disp['ko']).'부터 다운로드하실 수 있습니다.' ?></p>
    <?php endif; ?>

    <div class="flex gap-3 mt-6">
      <form method="post" class="flex-1">
        <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
        <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="lang" value="<?= $lang ?>">
        <button type="submit" class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold transition-all"><?= e(t('btn_edit')) ?></button>
      </form>
      <?php if ($refund_blocked): ?>
      <div class="flex-1 border border-[#27272a] text-[#71717a] py-3 font-bold text-center opacity-60" title="<?= e(t($refund_note_key)) ?>"><?= e(t('btn_cancel')) ?></div>
      <?php else: ?>
      <form method="post" class="flex-1" onsubmit="return confirm('<?= $is_paid ? t('confirm_paid') : t('confirm_free') ?>');">
        <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
        <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
        <input type="hidden" name="action" value="cancel">
        <input type="hidden" name="lang" value="<?= $lang ?>">
        <button type="submit" class="w-full border border-[#27272a] text-[#71717a] py-3 font-bold hover:text-[#a1a1aa] hover:border-white/20 transition-all"><?= e(t('btn_cancel')) ?></button>
      </form>
      <?php endif; ?>
    </div>
    <?php if ($refund_blocked): ?>
    <p class="text-xs text-[#71717a] mt-3 leading-relaxed"><?= e(t($refund_note_key)) ?></p>
    <?php endif; ?>
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
