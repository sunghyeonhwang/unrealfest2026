<?php
/* Unreal Fest Seoul 2026 — 초청장 발송 등록 백엔드 (_invite_apply.php) [M1]
 * 초청 코드(cb_unreal_2026_speaker_code) 게이트 → 무인증 등록 → event2_apply 반영 + QR.
 *   - 100%(무료): status=10 즉시 INSERT + QR + sc_used 증가.
 *   - 50~99%(부분할인): 결제 경유(M3, ticket-group-pay* 재사용) — 이 파일은 코드검증/집계/무료반영까지.
 * 재사용: _group_apply.php 의 ufs_group_make_qr(범용 QR). event2_apply 컬럼셋은 ufs_group_insert_member 미러링.
 * common.php(sql_*) 로드 전제. PHP 7.0 호환. DB 직접접근 없음 → CREATE/ALTER IF NOT EXISTS 패턴.
 *
 * POST 계약(ticket-invite.php, M2에서 생성):
 *   code, lang,
 *   apply_user_name/email/phone/job/company/depart/grade/ex1, rep_ticket, rep_day1, rep_day2, rep_tshirt,
 *   member_name[], member_email[], member_phone[], member_depart[], member_grade[], member_ex1[],
 *   member_ticket[], member_day1[], member_day2[], member_tshirt[]
 *   (직업·회사는 멤버=대표 자동상속, 단체와 동일)
 */
require_once __DIR__ . '/_group_apply.php';   // ufs_group_make_qr, ufs_group_prodname

/* ── 스키마 보장 ── */
if (!function_exists('ufs_invite_schema')) {
function ufs_invite_schema() {
    sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_speaker_code (
      sc_no        INT UNSIGNED NOT NULL AUTO_INCREMENT,
      sc_code      VARCHAR(40)  NOT NULL DEFAULT '',
      sc_src       VARCHAR(12)  NOT NULL DEFAULT 'speaker',
      sc_ref_id    INT          NOT NULL DEFAULT 0,
      sc_name      VARCHAR(100) NOT NULL DEFAULT '',
      sc_email     VARCHAR(200) NOT NULL DEFAULT '',
      sc_phone     VARCHAR(50)  NOT NULL DEFAULT '',
      sc_company   VARCHAR(200) NOT NULL DEFAULT '',
      sc_lang      VARCHAR(5)   NOT NULL DEFAULT 'ko',
      sc_quota     INT          NOT NULL DEFAULT 2,
      sc_used      INT          NOT NULL DEFAULT 0,
      sc_discount  INT          NOT NULL DEFAULT 100,
      sc_inviter   VARCHAR(100) NOT NULL DEFAULT '에픽게임즈',
      sc_active    CHAR(1)      NOT NULL DEFAULT 'Y',
      sc_sent_at   DATETIME     DEFAULT NULL,
      sc_memo      VARCHAR(255) NOT NULL DEFAULT '',
      sc_reg_datetime DATETIME  DEFAULT NULL,
      PRIMARY KEY (sc_no),
      UNIQUE KEY uq_sc_code (sc_code)
    ) DEFAULT CHARSET=utf8");
    @sql_query("ALTER TABLE cb_unreal_2026_event2_apply ADD COLUMN apply_speaker_code VARCHAR(40) DEFAULT ''");
    @sql_query("ALTER TABLE cb_unreal_2026_event2_apply ADD COLUMN apply_invite_oid VARCHAR(40) DEFAULT ''"); // 부분할인 결제 배치 묶음(M3)
}
}

/* ── 코드 조회(정규화) ── 반환: row 또는 null */
if (!function_exists('ufs_invite_code_fetch')) {
function ufs_invite_code_fetch($code) {
    $code = strtoupper(trim((string)$code));
    if ($code === '') return null;
    ufs_invite_schema();
    $r = sql_fetch("SELECT * FROM cb_unreal_2026_speaker_code WHERE sc_code='".sql_real_escape_string($code)."' LIMIT 1");
    return $r ? $r : null;
}
}

/* ── 코드 유효성 + 잔여 매수 ── 반환: array(ok, remain, discount, row, msg) */
if (!function_exists('ufs_invite_code_check')) {
function ufs_invite_code_check($code) {
    $base = array('ok'=>false, 'remain'=>0, 'discount'=>100, 'row'=>null, 'msg'=>'');
    $r = ufs_invite_code_fetch($code);
    if (!$r)                       return array_merge($base, array('msg'=>'유효하지 않은 초청 코드입니다.'));
    $base['row'] = $r;
    $disc = (int)$r['sc_discount']; if ($disc < 50) $disc = 50; if ($disc > 100) $disc = 100;
    $base['discount'] = $disc;
    if ($r['sc_active'] !== 'Y')   return array_merge($base, array('msg'=>'사용이 중지된 초청 코드입니다.'));
    $remain = (int)$r['sc_quota'] - (int)$r['sc_used'];
    $base['remain'] = ($remain > 0) ? $remain : 0;
    if ($remain <= 0)              return array_merge($base, array('msg'=>'이미 모두 등록된 초청 코드입니다.'));
    return array_merge($base, array('ok'=>true, 'remain'=>$remain));
}
}

/* ── 정상가(기준가) → 할인 적용가(반올림) ── */
if (!function_exists('ufs_invite_price')) {
function ufs_invite_price($pcode, $discount) {
    $d = (int)$discount; if ($d < 50) $d = 50; if ($d > 100) $d = 100;   // code_check와 동일 클램프(단일 정책)
    $base = function_exists('ufs_ticket_orig') ? (int)ufs_ticket_orig($pcode) : 0; // 정상가(얼리버드 무관). 호출부는 _ticket_init(=_pricing) 로드 전제
    $pay  = (int)round($base * (100 - $d) / 100);
    if ($pay < 0)     $pay = 0;      // 방어: 음수 결제액 금지
    if ($pay > $base) $pay = $base;  // 방어: 정상가 초과 금지
    return $pay;
}
}

/* ── 이메일 중복(이미 등록) 차단 ── 반환: true=중복 */
if (!function_exists('ufs_invite_email_dup')) {
function ufs_invite_email_dup($email) {
    $email = trim((string)$email); if ($email === '') return false;
    $r = sql_fetch("SELECT COUNT(*) AS c FROM cb_unreal_2026_event2_apply
                    WHERE apply_user_email='".sql_real_escape_string($email)."'
                      AND apply_temp_yn='N' AND apply_pay_status<>0");
    return ($r && (int)$r['c'] > 0);
}
}

/* ── 참석자 1명 INSERT (status: 1=홀드 / 10=완료). 반환 apply_no ──
 *   $a: name,email,phone,job,company,depart,grade,ex1,ticket,day1,day2,tshirt,price
 *   $code: 초청코드(apply_speaker_code), $free: 100% 무료 여부(free_yn) */
if (!function_exists('ufs_invite_insert_member')) {
function ufs_invite_insert_member($a, $code, $status, $free, $oid = '') {
    ufs_invite_schema();   // apply_speaker_code/apply_invite_oid 컬럼/테이블 존재 보장(사전 code_check 미경유 방어)
    $f = function($v){ return sql_real_escape_string(strip_tags((string)$v)); };
    $track = implode(',', array_filter(array(trim($a['day1']), trim($a['day2']))));
    $pw    = md5(str_replace("'","\\'", $a['email']));
    $pname = ufs_group_prodname($a['ticket']);
    $paycomplete = ((int)$status === 10) ? 'Y' : 'N';
    $free_yn = $free ? 'Y' : 'N';
    $sql = "INSERT INTO cb_unreal_2026_event2_apply
      (apply_user_name,apply_user_email,apply_user_phone,apply_user_job,apply_user_company,
       apply_user_depart,apply_user_grade,apply_user_ex1,apply_product_code,apply_product_name,
       apply_product_price,apply_tshirt,apply_track,apply_user_event_agree,apply_password,
       apply_ci,apply_di,apply_temp_yn,apply_pay_status,pay_complete,free_yn,apply_speaker_code,apply_invite_oid,apply_reg_datetime)
      VALUES ('".$f($a['name'])."','".$f($a['email'])."','".$f($a['phone'])."','".$f($a['job'])."','".$f($a['company'])."',
       '".$f($a['depart'])."','".$f($a['grade'])."','".$f($a['ex1'])."','".$f($a['ticket'])."','".sql_real_escape_string($pname)."',
       '".(int)$a['price']."','".$f($a['tshirt'])."','".sql_real_escape_string($track)."','0','".sql_real_escape_string($pw)."',
       '','','N',".(int)$status.",'".$paycomplete."','".$free_yn."','".sql_real_escape_string(strtoupper($code))."','".sql_real_escape_string($oid)."',now())";
    sql_query($sql);
    $row = sql_query("SELECT LAST_INSERT_ID() as idx");
    $apply_no = ($row) ? (int)$row->fetch_array()['idx'] : 0;
    if ($apply_no > 0 && (int)$status === 10) {
        ufs_group_make_qr($apply_no, $pw);   // 홀드(status 1)는 QR 미생성 → 결제완료 반영 시 생성
    }
    return $apply_no;
}
}

/* ── 부분할인 결제(M3): 배치(oid) 금액/인원/대표 집계 ── */
if (!function_exists('ufs_invite_oid_summary')) {
function ufs_invite_oid_summary($oid) {
    $oid = trim($oid);
    if ($oid === '') return null;
    // 홀드(1) 또는 완료(10) 행의 합계 = 이 결제 배치의 총액(행이 진실의 원천)
    $r = sql_fetch("SELECT COALESCE(SUM(apply_product_price),0) AS amt, COUNT(*) AS cnt,
                           MIN(apply_no) AS rep_no, MAX(apply_pay_status) AS st
                    FROM cb_unreal_2026_event2_apply
                    WHERE apply_invite_oid='".sql_real_escape_string($oid)."' AND apply_pay_status IN (1,10)");
    return $r;
}
}

/* ── 결제 승인 → 배치 홀드행(status 1) 일괄 승급 10 + QR 생성 ── 반환 승급 건수 */
if (!function_exists('ufs_invite_reflect_oid')) {
function ufs_invite_reflect_oid($oid) {
    $oid = trim($oid);
    if ($oid === '') return 0;
    $rs = sql_query("SELECT apply_no, apply_user_email FROM cb_unreal_2026_event2_apply
                     WHERE apply_invite_oid='".sql_real_escape_string($oid)."' AND apply_pay_status=1 ORDER BY apply_no");
    $n = 0;
    if ($rs) while ($r = $rs->fetch_assoc()) {
        $no = (int)$r['apply_no'];
        // 승급은 status=1 인 건만(경합/중복 콜백 방어)
        sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=10, pay_complete='Y' WHERE apply_no=".$no." AND apply_pay_status=1");
        $pw = md5(str_replace("'","\\'", $r['apply_user_email']));
        ufs_group_make_qr($no, $pw);
        $n++;
    }
    return $n;
}
}

/* ── 결제 실패/취소 → 배치 홀드행 해제(status 0) + sc_used 원복 ── */
if (!function_exists('ufs_invite_release_oid')) {
function ufs_invite_release_oid($oid, $code, $n) {
    $oid = trim($oid);
    if ($oid === '') return false;
    // 홀드(1)만 해제 — 이미 결제완료(10)된 건은 건드리지 않음
    sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0 WHERE apply_invite_oid='".sql_real_escape_string($oid)."' AND apply_pay_status=1");
    $rc = sql_fetch("SELECT ROW_COUNT() AS rc");
    $released = ($rc ? (int)$rc['rc'] : 0);
    if ($released > 0 && $code !== '') {
        // 실제 해제된 건수만큼만 sc_used 원복(과다 원복 방지)
        sql_query("UPDATE cb_unreal_2026_speaker_code SET sc_used = GREATEST(sc_used - ".$released.", 0)
                   WHERE sc_code='".sql_real_escape_string(strtoupper($code))."'");
    }
    return true;
}
}

/* ── sc_used 원자적 증가(동시성 방어). $n명 만큼, 잔여 초과 시 실패 ──
 *   반환: true=증가 성공 / false=잔여부족·경합 */
if (!function_exists('ufs_invite_consume')) {
function ufs_invite_consume($code, $n) {
    $code = strtoupper(trim((string)$code)); $n = (int)$n;
    if ($n <= 0) return false;
    sql_query("UPDATE cb_unreal_2026_speaker_code
               SET sc_used = sc_used + ".$n."
               WHERE sc_code='".sql_real_escape_string($code)."'
                 AND sc_active='Y' AND sc_used + ".$n." <= sc_quota");
    $rc = sql_fetch("SELECT ROW_COUNT() AS rc");
    return ($rc && (int)$rc['rc'] === 1);
}
}
