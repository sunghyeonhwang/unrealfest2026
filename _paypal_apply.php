<?php
/* Unreal Fest Seoul 2026 — PayPal 결제 확정 처리(공용) (_paypal_apply.php)
 * ufs_paypal_finalize_apply($apply_no, $capture_id, $usd_amount) : 대기(temp) 등록건 → 결제완료 확정.
 * 캡처 후(return) + 웹훅 양쪽에서 호출. 멱등. 확정 = UPDATE + QR + 영문 이메일(USD 표기). PHP 7.0.
 */
require_once __DIR__ . '/_group_apply.php';   // ufs_group_make_qr
require_once __DIR__ . '/_resend.php';         // ufs_resend_send
require_once __DIR__ . '/_dodo_mail.php';      // ufs_dodo_confirm_mail (금액 override 지원)
if (is_file(__DIR__ . '/_coupon.php')) require_once __DIR__ . '/_coupon.php';   // ufs_coupon_use (부분할인 쿠폰 사용횟수)

if (!function_exists('ufs_paypal_finalize_apply')) {
function ufs_paypal_finalize_apply($apply_no, $capture_id = '', $usd_amount = '') {
    $apply_no = (int)$apply_no;
    if ($apply_no <= 0) return array('ok'=>false, 'msg'=>'invalid apply_no');
    $row = sql_fetch("SELECT * FROM cb_unreal_2026_event2_apply WHERE apply_no=".$apply_no);
    if (!$row) return array('ok'=>false, 'msg'=>'row not found');

    // 이미 확정 → 멱등 반환(이메일/QR 재발송 안 함)
    if ($row['pay_complete'] === 'Y' && (int)$row['apply_pay_status'] !== 0) {
        return array('ok'=>true, 'already'=>true, 'row'=>$row);
    }
    // 취소된 건은 되살리지 않음
    if ((int)$row['apply_pay_status'] === 0 && $row['pay_complete'] === 'N' && $row['apply_temp_yn'] === 'N') {
        return array('ok'=>false, 'msg'=>'already cancelled', 'row'=>$row);
    }

    $cap  = sql_real_escape_string((string)$capture_id);
    $usd  = sql_real_escape_string((string)$usd_amount);
    $good = sql_real_escape_string((string)$row['apply_product_name']);

    // 확정 UPDATE (상태값은 INICIS/Dodo와 동일: pay_status=10, complete=Y, temp=N).
    // pay_tid=캡처ID, pay_paymethod='paypal', pay_totprice=USD 실결제. apply_product_price(KRW 정가)는 유지.
    sql_query("UPDATE cb_unreal_2026_event2_apply SET
        free_yn='N', apply_pay_status=10, pay_complete='Y', apply_temp_yn='N',
        pay_paymethod='paypal', pay_tid='".$cap."', pay_moid='".$cap."',
        pay_goodname='".$good."', pay_totprice='".$usd."'
        WHERE apply_no=".$apply_no." AND pay_complete<>'Y'");

    // 부분할인 쿠폰 사용횟수 +1 (1회만: 이 요청의 UPDATE가 실제로 pay_complete를 뒤집었을 때만 — return/웹훅 동시 방지)
    global $g5;
    $aff = (isset($g5['connect_db']) && function_exists('mysqli_affected_rows')) ? mysqli_affected_rows($g5['connect_db']) : 1;
    if ($aff > 0 && function_exists('ufs_coupon_use') && !empty($row['apply_coupon_code']) && (int)$row['apply_coupon_pct'] > 0) {
        ufs_coupon_use($row['apply_coupon_code']);
    }

    // QR 생성 (apply_password 기반 — 대기 INSERT 시 저장됨)
    if (function_exists('ufs_group_make_qr')) {
        @ufs_group_make_qr($apply_no, $row['apply_password']);
    }

    // 영문 완료 이메일 (USD 금액 표기, 비차단)
    $mail_ok = false;
    if (function_exists('ufs_dodo_confirm_mail') && function_exists('ufs_resend_send')) {
        $row['apply_pay_status'] = 10; $row['pay_complete'] = 'Y';
        $amount_label = ($usd_amount !== '') ? ('$'.$usd_amount.' USD') : '';
        $m = ufs_dodo_confirm_mail($row, $amount_label);
        $res = @ufs_resend_send($row['apply_user_email'], $m['subject'], $m['html'], '', $m['text']);
        $mail_ok = !empty($res['ok']);
    }

    $row2 = sql_fetch("SELECT * FROM cb_unreal_2026_event2_apply WHERE apply_no=".$apply_no);
    return array('ok'=>true, 'finalized'=>true, 'mail'=>$mail_ok, 'row'=>$row2);
}}
