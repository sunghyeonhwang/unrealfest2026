<?php
/* Unreal Fest Seoul 2026 — 단체 등록 → 개인 등록(apply) 반영 + QR (_group_apply.php)
 * 좌석 홀드(②안):
 *   - ufs_group_hold($grp_no)    : 무통장 접수(확인) 시 멤버를 apply에 입금대기(status=1)로 INSERT → 정원 임시 점유(좌석 홀드). QR 없음.
 *   - ufs_group_reflect($grp_no) : 결제완료(카드 승인/무통장 입금확인) 시 → 홀드행은 status=10 승급+QR, 홀드 없으면 INSERT(status=10)+QR.
 *   - ufs_group_release($grp_no) : 관리자 수동취소 → 홀드/반영된 apply행을 status=0 으로 해제(정원 반환).
 * common.php(sql_*) 로드 전제. PHP 7.0 호환.
 */
if (!function_exists('ufs_group_make_qr')) {
function ufs_group_make_qr($apply_no, $pw) {
    $dir = __DIR__ . "/qrdata";
    @mkdir($dir, 0755);
    $qrlib = __DIR__ . "/../unrealfest2025/phpqrcode/qrlib.php";
    if (!file_exists($qrlib)) return;
    include_once $qrlib;
    $png = $dir."/".$apply_no.".png";
    $jpg = $dir."/".$apply_no.".jpg";
    QRcode::png($pw, $png, 0, 7, 2);
    if (file_exists($png) && function_exists('imagecreatefrompng')) {
        $p = imagecreatefrompng($png);
        if ($p) { $j = imagecreatetruecolor(imagesx($p), imagesy($p)); imagecopy($j,$p,0,0,0,0,imagesx($p),imagesy($p)); imagejpeg($j,$jpg,100); imagedestroy($p); imagedestroy($j); }
    }
}
}

if (!function_exists('ufs_group_apply_cols')) {
function ufs_group_apply_cols() {
    // 단체 반영에 필요한 컬럼 보강(존재하면 무시)
    @sql_query("ALTER TABLE cb_unreal_2026_event2_apply ADD COLUMN apply_group_code VARCHAR(40) DEFAULT ''");
    @sql_query("ALTER TABLE cb_unreal_2026_group_member ADD COLUMN apply_no INT DEFAULT 0");
    @sql_query("ALTER TABLE cb_unreal_2026_group ADD COLUMN applied_yn CHAR(1) DEFAULT 'N'");
    @sql_query("ALTER TABLE cb_unreal_2026_group ADD COLUMN held_yn CHAR(1) DEFAULT 'N'");
}
}

if (!function_exists('ufs_group_prodname')) {
function ufs_group_prodname($code) {
    $PN = array(
        'NORMAL_ALL' => '언리얼 페스트 서울 2026 양일권(8월 20일~21일)',
        'NORMAL_20'  => '언리얼 페스트 서울 2026 1일권(8월 20일)',
        'NORMAL_21'  => '언리얼 페스트 서울 2026 1일권(8월 21일)',
    );
    return isset($PN[$code]) ? $PN[$code] : $code;
}
}

// 멤버 1명을 apply에 INSERT (status: 1=입금대기 홀드 / 10=결제완료). 반환 apply_no.
if (!function_exists('ufs_group_insert_member')) {
function ufs_group_insert_member($g, $m, $status) {
    $f = function($v){ return sql_real_escape_string(strip_tags((string)$v)); };
    $track = implode(',', array_filter(array(trim($m['day1']), trim($m['day2']))));
    $pw    = md5(str_replace("'","\\'", $m['email']));
    $pname = ufs_group_prodname($m['ticket']);
    $paycomplete = ((int)$status === 10) ? 'Y' : 'N';
    $sql = "INSERT INTO cb_unreal_2026_event2_apply
      (apply_user_name,apply_user_email,apply_user_phone,apply_user_job,apply_user_company,
       apply_user_depart,apply_user_grade,apply_user_ex1,apply_product_code,apply_product_name,
       apply_product_price,apply_tshirt,apply_track,apply_user_event_agree,apply_password,
       apply_ci,apply_di,apply_temp_yn,apply_pay_status,pay_complete,free_yn,apply_group_code,apply_reg_datetime)
      VALUES ('".$f($m['name'])."','".$f($m['email'])."','".$f($m['phone'])."','".$f($m['job'])."','".$f($m['company'])."',
       '".$f($m['depart'])."','".$f($m['grade'])."','".$f($m['ex1'])."','".$f($m['ticket'])."','".sql_real_escape_string($pname)."',
       '".(int)$m['price']."','".$f($m['tshirt'])."','".sql_real_escape_string($track)."','0','".sql_real_escape_string($pw)."',
       '','','N',".(int)$status.",'".$paycomplete."','N','".sql_real_escape_string($g['grp_code'])."',now())";
    sql_query($sql);
    $row = sql_query("SELECT LAST_INSERT_ID() as idx");
    $apply_no = ($row) ? (int)$row->fetch_array()['idx'] : 0;
    if ($apply_no > 0) sql_query("UPDATE cb_unreal_2026_group_member SET apply_no=".$apply_no." WHERE gm_no=".(int)$m['gm_no']);
    return $apply_no;
}
}

// 무통장 접수(확인) 시 좌석 홀드 — 멤버를 입금대기(status=1)로 apply에 INSERT. QR 미생성.
if (!function_exists('ufs_group_hold')) {
function ufs_group_hold($grp_no) {
    $grp_no = (int)$grp_no;
    $g = sql_fetch("SELECT * FROM cb_unreal_2026_group WHERE grp_no=".$grp_no);
    if (!$g) return false;
    ufs_group_apply_cols();
    if (isset($g['held_yn']) && $g['held_yn'] === 'Y') return true; // 중복 홀드 방지
    $ms = sql_query("SELECT * FROM cb_unreal_2026_group_member WHERE grp_no=".$grp_no." ORDER BY gm_no");
    if ($ms) while ($m = $ms->fetch_assoc()) {
        if ((int)$m['apply_no'] > 0) continue; // 이미 홀드/반영된 멤버
        ufs_group_insert_member($g, $m, 1); // 입금대기
    }
    sql_query("UPDATE cb_unreal_2026_group SET held_yn='Y' WHERE grp_no=".$grp_no);
    return true;
}
}

// 결제완료 → apply 반영 + QR. 홀드행은 승급(1→10), 미홀드(카드)는 INSERT(status=10).
if (!function_exists('ufs_group_reflect')) {
function ufs_group_reflect($grp_no) {
    $grp_no = (int)$grp_no;
    $g = sql_fetch("SELECT * FROM cb_unreal_2026_group WHERE grp_no=".$grp_no);
    if (!$g) return false;
    ufs_group_apply_cols();
    if (isset($g['applied_yn']) && $g['applied_yn'] === 'Y') return true; // 중복 반영 방지

    $ms = sql_query("SELECT * FROM cb_unreal_2026_group_member WHERE grp_no=".$grp_no." ORDER BY gm_no");
    if ($ms) while ($m = $ms->fetch_assoc()) {
        $apply_no = (int)$m['apply_no'];
        if ($apply_no > 0) {
            // 홀드된 행 → 결제완료로 승급
            sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=10, pay_complete='Y' WHERE apply_no=".$apply_no);
        } else {
            $apply_no = ufs_group_insert_member($g, $m, 10);
        }
        if ($apply_no > 0) {
            $pw = md5(str_replace("'","\\'", $m['email']));
            ufs_group_make_qr($apply_no, $pw);
        }
    }
    sql_query("UPDATE cb_unreal_2026_group SET applied_yn='Y' WHERE grp_no=".$grp_no);
    return true;
}
}

// 관리자 수동취소 → 홀드/반영된 apply행을 status=0(취소)으로 해제. 정원 반환.
if (!function_exists('ufs_group_release')) {
function ufs_group_release($grp_no) {
    $grp_no = (int)$grp_no;
    $g = sql_fetch("SELECT * FROM cb_unreal_2026_group WHERE grp_no=".$grp_no);
    if (!$g) return false;
    $n = 0;
    $ms = sql_query("SELECT * FROM cb_unreal_2026_group_member WHERE grp_no=".$grp_no." ORDER BY gm_no");
    if ($ms) while ($m = $ms->fetch_assoc()) {
        if ((int)$m['apply_no'] > 0) {
            sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0 WHERE apply_no=".(int)$m['apply_no']);
            $n++;
        }
    }
    return $n;
}
}
