<?php
/* Unreal Fest Seoul 2026 — 해외(영문) 등록 결제 시작 핸들러 (apply_pay_en.php)
 * ticket-en.php POST 수신 → 검증 → 대기(temp) 등록건 INSERT → Dodo 체크아웃 생성 → 리다이렉트.
 * 확정은 결제완료 후 웹훅/완료페이지에서 (_dodo_apply.php). 정상가(KRW) 고정. PHP 7.0.
 */
require __DIR__ . '/_ticket_init.php';     // common, $UFS_TRACKS, $trackRemain, ufs_ticket_orig
require_once __DIR__ . '/_paypal.php';     // ufs_pp_create_order (PayPal Orders v2)

$SITE = 'https://epiclounge.co.kr/unrealfest2026/';
$PRODNAME = array('NORMAL_ALL'=>'2-Day Pass (Aug 20-21)','NORMAL_20'=>'1-Day Pass (Aug 20)','NORMAL_21'=>'1-Day Pass (Aug 21)');
$T2P = array('ALL'=>'NORMAL_ALL','DAY1'=>'NORMAL_20','DAY2'=>'NORMAL_21');

function pay_en_fail($msg) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registration</title>'
       . '<style>body{font-family:system-ui,sans-serif;background:#09090b;color:#e4e4e7;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}'
       . '.b{max-width:460px;padding:32px;text-align:center}a{color:#00C1D5}</style></head><body><div class="b">'
       . '<h2 style="color:#fff">We couldn\'t start your payment</h2>'
       . '<p style="color:#a1a1aa">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</p>'
       . '<p><a href="ticket-en.php">← Back to registration</a></p></div></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ticket-en.php'); exit; }

$gp = function($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; };
$name=$gp('apply_user_name'); $email=$gp('apply_user_email'); $phone=$gp('apply_user_phone');
$job=$gp('apply_user_job'); $company=$gp('apply_user_company'); $depart=$gp('apply_user_depart');
$grade=$gp('apply_user_grade'); $ex1=$gp('apply_user_ex1'); $tshirt=$gp('tshirt');
$ticket=$gp('ticket'); $d1=$gp('day1track'); $d2=$gp('day2track');
$agree=$gp('agree_req'); $mkt = ($gp('agree_mkt')!=='') ? '1' : '0';
$pcode = isset($T2P[$ticket]) ? $T2P[$ticket] : '';
$tracks = array();

// ── 검증 ──
if ($agree!=='on' && $agree!=='Y' && $agree!=='1') pay_en_fail('Please agree to the required terms.');
if ($name===''||$email===''||$phone===''||$company===''||$depart===''||$job===''||$grade===''||$ex1===''||$tshirt==='') pay_en_fail('Please complete all required fields.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) pay_en_fail('Please enter a valid email address.');
if ($pcode==='') pay_en_fail('Please select a ticket.');
if ($pcode==='NORMAL_ALL')      { if(!isset($UFS_TRACKS[1][$d1])) pay_en_fail('Please select a Day 1 track.'); if(!isset($UFS_TRACKS[2][$d2])) pay_en_fail('Please select a Day 2 track.'); $tracks=array($d1,$d2); }
elseif ($pcode==='NORMAL_20')   { if(!isset($UFS_TRACKS[1][$d1])) pay_en_fail('Please select a Day 1 track.'); $tracks=array($d1); }
elseif ($pcode==='NORMAL_21')   { if(!isset($UFS_TRACKS[2][$d2])) pay_en_fail('Please select a Day 2 track.'); $tracks=array($d2); }

// ── 중복(완료 등록 이메일) ──
$dup = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE apply_user_email='".sql_real_escape_string($email)."' AND apply_temp_yn='N' AND apply_pay_status<>0");
if ($dup && (int)$dup['c']>0) pay_en_fail('This email is already registered.');

// ── 트랙 정원 ──
foreach ($tracks as $tk) {
    $cap = sql_fetch("SELECT date1 FROM 2026_event_ticket WHERE name='".sql_real_escape_string($tk)."'");
    $capN = $cap ? (int)$cap['date1'] : 0;
    if ($capN > 0) {
        $reg = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE apply_temp_yn='N' AND apply_pay_status<>0 AND apply_track LIKE '%".sql_real_escape_string($tk)."%'");
        if ($reg && (int)$reg['c'] >= $capN) pay_en_fail('The selected track is full. Please choose another track.');
    }
}

// ── 대기(temp) 등록건 INSERT — 정상가(KRW). 확정 전이라 정원/중복 집계에서 제외(temp_yn=Y). ──
$price = function_exists('ufs_ticket_orig') ? (int)ufs_ticket_orig($pcode) : 0;
$f = function($v){ return sql_real_escape_string(strip_tags((string)$v)); };
$pw = md5(str_replace("'","\\'",$email));
$track_str = implode(',', $tracks);
sql_query("INSERT INTO cb_unreal_2026_event2_apply
   (apply_user_name,apply_user_email,apply_user_phone,apply_user_job,apply_user_company,apply_user_depart,apply_user_grade,apply_user_ex1,
    apply_product_code,apply_product_name,apply_product_price,apply_tshirt,apply_track,apply_user_event_agree,apply_coupon_code,apply_coupon_pct,
    apply_password,apply_ci,apply_di,apply_pay_status,pay_complete,free_yn,apply_temp_yn,apply_group_code,pay_paymethod,apply_reg_datetime)
   VALUES ('".$f($name)."','".$f($email)."','".$f($phone)."','".$f($job)."','".$f($company)."','".$f($depart)."','".$f($grade)."','".$f($ex1)."',
    '".$f($pcode)."','".$f($PRODNAME[$pcode])."','".$price."','".$f($tshirt)."','".$f($track_str)."','".$mkt."','',0,
    '".sql_real_escape_string($pw)."','','',0,'N','N','Y','','paypal_pending',now())");
$r = sql_query("SELECT LAST_INSERT_ID() as idx")->fetch_array();
$apply_no = (int)$r['idx'];
if ($apply_no <= 0) pay_en_fail('Registration failed. Please try again.');

// ── PayPal 주문 생성 → 승인 페이지로 리다이렉트 ──
$return_url = $SITE.'ticket-en-complete.php?apply_no='.$apply_no;   // 승인 후 PayPal이 ?token=&PayerID= 부착
$cancel_url = $SITE.'ticket-en.php?paypal=cancel';
$od = ufs_pp_create_order($pcode, $email, $name, $return_url, $cancel_url, $apply_no);
if (empty($od['ok']) || $od['approve_url']==='') {
    // 주문 생성 실패 → 대기건 정리
    sql_query("DELETE FROM cb_unreal_2026_event2_apply WHERE apply_no=".$apply_no." AND apply_temp_yn='Y' AND pay_complete='N'");
    pay_en_fail('Payment could not be started. Please try again later. ('.$od['msg'].')');
}
header('Location: '.$od['approve_url']);
exit;
