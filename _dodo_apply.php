<?php
/* Unreal Fest Seoul 2026 — Dodo 결제 확정 처리(공용) (_dodo_apply.php)
 * ufs_dodo_finalize_apply($apply_no, $payment_id, $amount) : 대기(temp) 등록건 → 결제완료 확정.
 * 웹훅(dodo_webhook.php)과 완료페이지(ticket-en-complete.php) 양쪽에서 호출. 멱등(재호출 안전).
 * 완료 = 확정 UPDATE + QR 생성 + 영문 이메일. PHP 7.0.
 */
require_once __DIR__ . '/_group_apply.php';   // ufs_group_make_qr
require_once __DIR__ . '/_resend.php';         // ufs_resend_send
require_once __DIR__ . '/_dodo_mail.php';      // ufs_dodo_confirm_mail

if (!function_exists('ufs_dodo_finalize_apply')) {
function ufs_dodo_finalize_apply($apply_no, $payment_id = '', $amount = null) {
    $apply_no = (int)$apply_no;
    if ($apply_no <= 0) return array('ok'=>false, 'msg'=>'invalid apply_no');
    $row = sql_fetch("SELECT * FROM cb_unreal_2026_event2_apply WHERE apply_no=".$apply_no);
    if (!$row) return array('ok'=>false, 'msg'=>'row not found');

    // 이미 확정된 건 → 멱등 반환 (이메일/QR 재발송 안 함)
    if ($row['pay_complete'] === 'Y' && (int)$row['apply_pay_status'] !== 0) {
        return array('ok'=>true, 'already'=>true, 'row'=>$row);
    }
    // 취소된 건은 되살리지 않음
    if ((int)$row['apply_pay_status'] === 0 && $row['pay_complete'] === 'N' && $row['apply_temp_yn'] === 'N') {
        return array('ok'=>false, 'msg'=>'already cancelled', 'row'=>$row);
    }

    $pid  = sql_real_escape_string((string)$payment_id);
    $amt  = ($amount !== null && is_numeric($amount)) ? (int)$amount : (int)$row['apply_product_price'];
    $good = sql_real_escape_string((string)$row['apply_product_name']);

    // 확정 UPDATE (INICIS 경로와 동일 상태값: pay_status=10, complete=Y, temp=N)
    sql_query("UPDATE cb_unreal_2026_event2_apply SET
        free_yn='N', apply_pay_status=10, pay_complete='Y', apply_temp_yn='N',
        pay_paymethod='dodo', pay_tid='".$pid."', pay_moid='".$pid."',
        pay_goodname='".$good."', pay_totprice='".$amt."'
        WHERE apply_no=".$apply_no." AND pay_complete<>'Y'");

    // QR 생성 (apply_password 기반 — 대기 INSERT 시 저장됨)
    if (function_exists('ufs_group_make_qr')) {
        @ufs_group_make_qr($apply_no, $row['apply_password']);
    }

    // 영문 완료 이메일 (비차단)
    $mail_ok = false;
    if (function_exists('ufs_dodo_confirm_mail') && function_exists('ufs_resend_send')) {
        $row['apply_pay_status'] = 10; $row['pay_complete'] = 'Y';   // 메일 본문 최신화
        $m = ufs_dodo_confirm_mail($row);
        $res = @ufs_resend_send($row['apply_user_email'], $m['subject'], $m['html'], '', $m['text']);
        $mail_ok = !empty($res['ok']);
    }

    $row2 = sql_fetch("SELECT * FROM cb_unreal_2026_event2_apply WHERE apply_no=".$apply_no);
    return array('ok'=>true, 'finalized'=>true, 'mail'=>$mail_ok, 'row'=>$row2);
}}
