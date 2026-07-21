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
    @sql_query("ALTER TABLE cb_unreal_2026_group_member ADD COLUMN gm_status CHAR(1) DEFAULT 'A'");   // A=활성 / C=취소(1인 부분취소)
    @sql_query("ALTER TABLE cb_unreal_2026_group ADD COLUMN refunded_amount INT DEFAULT 0");          // 부분환불 누계(잔액=total_amount-refunded_amount)
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
            // 홀드된 행 → 결제완료로 승급 (단, 취소된(status=0) 행은 부활 금지)
            sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=10, pay_complete='Y' WHERE apply_no=".$apply_no." AND apply_pay_status<>0");
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

/* 단체 구성원 1명 취소 → (카드)INICIS 부분환불 + apply status=0 + 그룹 집계 동기화.
 *   - 카드 결제 그룹만 자동 부분환불. 무통장(bank)/TID없음 → 자동 불가(manual=true 반환, 변경 없음) → 사무국 처리.
 *   - 환불액 = 그 1인의 apply_product_price(반올림가 그대로). 잔액=total_amount-refunded_amount.
 *   - 마지막 1인(잔액 소진)은 부분취소 대신 전액취소(confirmPrice=0 회피).
 *   - 동시/중복 취소는 gm_status(A→C) + ROW_COUNT() 락으로 방지(sql_affected_rows 헬퍼 부재 → ROW_COUNT 사용).
 * 반환: array('ok'=>bool, 'manual'=>bool, 'refund'=>int, 'msg'=>string) */
if (!function_exists('ufs_group_member_cancel')) {
function ufs_group_member_cancel($apply_no, $admin_manual = false) {
    $apply_no = (int)$apply_no;
    if ($apply_no <= 0) return array('ok'=>false, 'msg'=>'잘못된 요청입니다.');
    ufs_group_apply_cols();

    $r = sql_fetch(
        "SELECT a.apply_no, a.apply_pay_status, a.apply_product_price,
                g.grp_no, g.paymethod, g.pay_tid, g.total_amount, g.refunded_amount
         FROM cb_unreal_2026_event2_apply a
         JOIN cb_unreal_2026_group g ON g.grp_code = a.apply_group_code
         WHERE a.apply_no = ".$apply_no." LIMIT 1");
    if (!$r) return array('ok'=>false, 'msg'=>'단체 등록 정보를 찾을 수 없습니다.');
    if ((int)$r['apply_pay_status'] === 0) return array('ok'=>true, 'refund'=>0, 'msg'=>'이미 취소된 등록입니다.');

    $method = isset($r['paymethod']) ? $r['paymethod'] : '';
    $tid    = trim((string)$r['pay_tid']);
    $auto   = ($method === 'card' && $tid !== '');   // 카드+TID → INICIS 자동 부분환불 가능

    // 자동환불 불가(무통장 등): 자가(myticket)는 안내만(변경 없음), 관리자(admin_manual=true)는 좌석반환+집계(계좌환불은 수동).
    if (!$auto && !$admin_manual) {
        return array('ok'=>false, 'manual'=>true, 'msg'=>'무통장(계좌)으로 결제된 단체 등록은 자동 환불이 어렵습니다.');
    }

    // ── 동시/중복 취소 방지 락: gm_status A→C, 직전 UPDATE의 ROW_COUNT()==1 일 때만 진행 ──
    sql_query("UPDATE cb_unreal_2026_group_member SET gm_status='C' WHERE apply_no=".$apply_no." AND gm_status<>'C'");
    $rc = sql_fetch("SELECT ROW_COUNT() AS rc");
    if (!$rc || (int)$rc['rc'] !== 1) {
        return array('ok'=>false, 'msg'=>'이미 취소되었거나 취소 처리 중입니다.');
    }

    $price   = (int)$r['apply_product_price'];                           // 이 1인 환불액(저장된 반올림가 그대로)
    $grp_no  = (int)$r['grp_no'];
    $balance = (int)$r['total_amount'] - (int)$r['refunded_amount'];     // 현재 결제 잔액
    if ($price > $balance) $price = $balance;                            // 방어: 잔액 초과 금지
    $remain  = $balance - $price;                                        // 부분취소 후 잔액(confirmPrice)

    if ($auto) {
        require_once __DIR__ . '/_refund.php';
        if ($remain <= 0) {
            // 마지막 남은 인원 → 잔액 전액취소(부분취소 confirmPrice=0 회피)
            $rf = ufs_inicis_refund($tid, 'Card', '단체 마지막 구성원 취소', $apply_no);
        } else {
            $rf = ufs_inicis_partial_refund($tid, 'Card', $price, $remain, '단체 구성원 취소', $apply_no);
        }
        // 성공(ok) 또는 이미취소(already) 아니면 실패 → 락 원복 후 중단
        if (empty($rf['skipped']) && empty($rf['ok']) && empty($rf['already'])) {
            sql_query("UPDATE cb_unreal_2026_group_member SET gm_status='A' WHERE apply_no=".$apply_no);
            return array('ok'=>false, 'msg'=>(isset($rf['msg']) ? $rf['msg'] : '환불 처리에 실패했습니다.'));
        }
    }

    // ── 성공: apply 취소 + 그룹 집계 상대감산(동시성 안전). 무통장(admin_manual)은 API 없이 좌석/집계만. ──
    sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0, refund_date=now() WHERE apply_no=".$apply_no." AND apply_pay_status<>0");
    sql_query("UPDATE cb_unreal_2026_group SET refunded_amount = refunded_amount + ".$price." WHERE grp_no=".$grp_no);
    if ($remain <= 0) sql_query("UPDATE cb_unreal_2026_group SET pay_status='refunded' WHERE grp_no=".$grp_no);
    @unlink(__DIR__.'/qrdata/'.$apply_no.'.jpg');
    @unlink(__DIR__.'/qrdata/'.$apply_no.'.png');
    return array('ok'=>true, 'manual'=>!$auto, 'refund'=>$price,
        'msg'=> $auto ? '취소/환불이 완료되었습니다.' : '좌석을 반환했습니다. (무통장은 계좌 환불을 수동으로 진행하세요.)');
}
}
