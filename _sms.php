<?php
/* Unreal Fest Seoul 2026 — SMS/MMS 발송 모듈 (_sms.php)
 * 포팅: 2025 unrealfest2025/inisis_pc/INIstdpay_pc_return.php 문자발송 블록.
 * DirectSend API (https://directsend.co.kr) 사용. PHP 7.0 호환.
 *
 * 사용:
 *   require_once __DIR__.'/_sms.php';
 *   ufs_send_qr_mms($name, $phone, $apply_no, $product_code);  // 오프라인 결제완료 (QR jpg 첨부)
 *   ufs_send_text_sms($name, $phone, $title, $message);        // 온라인/가상계좌 (텍스트)
 */

// ── 발송 스위치 ── true: 실제 발송. (2026-06-11 테스트 중 문자 확인용으로 ON)
//    INICIS 테스트모드와 무관하게 독립 동작. 끄려면 false.
if (!defined('UFS_SMS_LIVE'))     define('UFS_SMS_LIVE',     true);
if (!defined('UFS_SMS_SENDER'))   define('UFS_SMS_SENDER',   '023263701');       // 발신번호 (02-326-3701)
if (!defined('UFS_SMS_USERNAME')) define('UFS_SMS_USERNAME', 'griff16');          // DirectSend 계정
if (!defined('UFS_SMS_KEY'))      define('UFS_SMS_KEY',      'BaIpwA1FNBOYszC');  // DirectSend API key
if (!defined('UFS_SMS_BASE_URL')) define('UFS_SMS_BASE_URL', 'https://epiclounge.co.kr/v3/unrealfest2026'); // QR 첨부 절대경로 기준

/* DirectSend POST 공통 실행 — 성공 시 응답, 실패 시 false. (발송 실패가 등록을 막지 않도록 예외 없이 반환) */
if (!function_exists('ufs_directsend_post')) {
function ufs_directsend_post($postvars, $tag = '') {
    if (!UFS_SMS_LIVE) { return 'SMS_SKIPPED_TEST_MODE'; } // 가오픈/테스트: 실제 발송 안 함
    if (!function_exists('curl_init')) { return false; }
    $url = "https://directsend.co.kr/index.php/api_v2/sms_change_word";
    $headers = array("cache-control: no-cache", "content-type: application/json; charset=utf-8");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);   // 2025 동작 코드와 동일 (빈 응답/SSL 회피)
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $send = 'curl_' . 'exec';
    $response = $send($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    // 발송 결과 로깅 (관리자 결제 로그에서 [SMS] 로 확인) — 성공/실패 모두
    if (function_exists('sql_query')) {
        $logbody = $err ? ('CURL_ERR('.$err.'): '.$errmsg) : ('http='.$httpcode.' len='.strlen((string)$response).' body='.trim((string)$response));
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('0','[SMS ".str_replace("'","`",(string)$tag)."] ".str_replace("'","`",$logbody)."',now())");
    }
    return !$err ? $response : false;
}
}

/* JSON 문자열 안전 이스케이프 (DirectSend는 수동 JSON 조립 → 따옴표/개행/역슬래시 처리) */
if (!function_exists('ufs_sms_json_escape')) {
function ufs_sms_json_escape($s) {
    $s = str_replace('\\', '\\\\', (string)$s);
    $s = str_replace('"', '\\"', $s);
    $s = str_replace("\r", '', $s);
    $s = str_replace("\n", '\\n', $s);
    return $s;
}
}

/* 상품 코드별 체크인 일시 문구 */
if (!function_exists('ufs_sms_call_date')) {
function ufs_sms_call_date($product_code) {
    if ($product_code === 'NORMAL_20') { return '8월 20일(목) 오전 9:00'; }
    if ($product_code === 'NORMAL_21') { return '8월 21일(금) 오전 9:30'; }
    return "8월 20일(목) 오전 9:00\n      8월 21일(금) 오전 9:30"; // 양일권 등 기본
}
}

/* 상품 코드별 티켓 종류 라벨 */
if (!function_exists('ufs_sms_product_label')) {
function ufs_sms_product_label($product_code) {
    if ($product_code === 'NORMAL_20') { return '1일권(8월 20일)'; }
    if ($product_code === 'NORMAL_21') { return '1일권(8월 21일)'; }
    return '양일권(8월 20일~21일)';
}
}

/* 오프라인 결제완료 — QR jpg 첨부 MMS */
if (!function_exists('ufs_send_qr_mms')) {
function ufs_send_qr_mms($name, $phone, $apply_no, $product_code) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if ($phone === '') { return false; }
    $apply_no = preg_replace('/[^0-9]/', '', (string)$apply_no);

    $call_date = ufs_sms_call_date($product_code);
    $product_label = ufs_sms_product_label($product_code);
    $title = "언리얼 페스트 서울 2026";
    $message = "<언리얼 페스트 서울 2026> 오프라인 등록이 완료되었습니다.\n"
             . "행사장 내 셀프 체크인 기기에서 QR코드를 스캔한 후 간편하게 입장하세요.\n\n"
             . "티켓: " . $product_label . "\n"
             . "일시: " . $call_date . "\n"
             . "장소: 웨스틴 서울 파르나스 (지하 1층 하모니 볼룸)\n\n"
             . "자세한 내용은 홈페이지를 참고하세요.\n"
             . "https://epiclounge.co.kr/unrealfest2026/\n\n"
             . "- 언리얼 페스트 사무국";
    $message = str_replace(' ', ' ', $message); // 유니코드 공백문자 치환 (2025 동일)

    $receiver = '[{"name":"' . ufs_sms_json_escape($name) . '","mobile":"' . $phone . '"}]';
    $attaches = json_encode(array(array('attc' => UFS_SMS_BASE_URL . '/qrdata/' . $apply_no . '.jpg')));

    $postvars = '{'
        . '"title":"' . ufs_sms_json_escape($title) . '"'
        . ', "message":"' . ufs_sms_json_escape($message) . '"'
        . ', "sender":"' . UFS_SMS_SENDER . '"'
        . ', "username":"' . UFS_SMS_USERNAME . '"'
        . ', "receiver":' . $receiver
        . ', "key":"' . UFS_SMS_KEY . '"'
        . ', "attaches":' . $attaches
        . '}';
    return ufs_directsend_post($postvars, 'qr-mms');
}
}

/* 단체 등록완료 — 멤버 개인별 QR jpg 첨부 MMS (무통장 입금확인/카드 결제완료 시 등록자 전원 발송) */
if (!function_exists('ufs_send_group_qr_mms')) {
function ufs_send_group_qr_mms($name, $phone, $apply_no, $product_code, $company) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if ($phone === '') { return false; }
    $apply_no = preg_replace('/[^0-9]/', '', (string)$apply_no);
    if ($apply_no === '') { return false; } // QR 없으면 MMS 불가 (호출부에서 텍스트로 대체)

    $call_date = ufs_sms_call_date($product_code);
    $product_label = ufs_sms_product_label($product_code);
    $title = "언리얼 페스트 서울 2026";
    $message = "<언리얼 페스트 서울 2026> 단체 등록이 완료되었습니다.\n"
             . $name . "님, 행사 참가 등록이 확정되었습니다.\n"
             . "첨부된 QR코드를 행사장 셀프 체크인 기기에서 스캔한 후 간편하게 입장하세요.\n\n"
             . "단체 대표: " . $company . "\n"
             . "티켓: " . $product_label . "\n"
             . "일시: " . $call_date . "\n"
             . "장소: 웨스틴 서울 파르나스 (지하 1층 하모니 볼룸)\n\n"
             . "자세한 내용은 홈페이지를 참고하세요.\n"
             . "https://epiclounge.co.kr/unrealfest2026/\n\n"
             . "- 언리얼 페스트 사무국";
    $message = str_replace(' ', ' ', $message); // 유니코드 공백문자 치환 (2025 동일)

    $receiver = '[{"name":"' . ufs_sms_json_escape($name) . '","mobile":"' . $phone . '"}]';
    $attaches = json_encode(array(array('attc' => UFS_SMS_BASE_URL . '/qrdata/' . $apply_no . '.jpg')));

    $postvars = '{'
        . '"title":"' . ufs_sms_json_escape($title) . '"'
        . ', "message":"' . ufs_sms_json_escape($message) . '"'
        . ', "sender":"' . UFS_SMS_SENDER . '"'
        . ', "username":"' . UFS_SMS_USERNAME . '"'
        . ', "receiver":' . $receiver
        . ', "key":"' . UFS_SMS_KEY . '"'
        . ', "attaches":' . $attaches
        . '}';
    return ufs_directsend_post($postvars, 'group-qr-mms');
}
}

/* 텍스트 SMS — 온라인 등록완료 / 가상계좌 입금안내 등 (첨부 없음) */
if (!function_exists('ufs_send_text_sms')) {
function ufs_send_text_sms($name, $phone, $title, $message, $tag = 'text') {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if ($phone === '') { return false; }
    $message = str_replace(' ', ' ', $message);
    $receiver = '[{"name":"' . ufs_sms_json_escape($name) . '","mobile":"' . $phone . '"}]';
    $postvars = '{'
        . '"title":"' . ufs_sms_json_escape($title) . '"'
        . ', "message":"' . ufs_sms_json_escape($message) . '"'
        . ', "sender":"' . UFS_SMS_SENDER . '"'
        . ', "username":"' . UFS_SMS_USERNAME . '"'
        . ', "receiver":' . $receiver
        . ', "key":"' . UFS_SMS_KEY . '"'
        . '}';
    return ufs_directsend_post($postvars, $tag);
}
}

/* 온라인 무료 등록완료 안내 SMS */
if (!function_exists('ufs_send_online_sms')) {
function ufs_send_online_sms($name, $phone) {
    $title = "언리얼 페스트 서울 2026";
    $message = "<언리얼 페스트 서울 2026> 온라인 등록이 완료되었습니다.\n\n"
             . "행사 당일 첫 세션 30분 전, 등록하신 이메일과 카카오 알림톡(또는 문자)으로 시청 링크가 발송됩니다.\n"
             . "링크를 받지 못하신 경우, 행사 홈페이지에서 등록 정보 확인 후 시청하실 수 있습니다.\n\n"
             . "일시: 8월 20일(목)~21일(금) 오전 10:30\n\n"
             . "자세한 내용은 홈페이지를 참고하세요.\n"
             . "https://epiclounge.co.kr/unrealfest2026/\n\n"
             . "- 언리얼 페스트 사무국";
    return ufs_send_text_sms($name, $phone, $title, $message, 'online');
}
}

/* 가상계좌 발급 입금안내 SMS */
if (!function_exists('ufs_send_vbank_sms')) {
function ufs_send_vbank_sms($name, $phone, $bank_num, $amount, $product_code) {
    $title = "언리얼 페스트 서울 2026";
    $won = number_format((int)$amount);
    $message = "<언리얼 페스트 서울 2026> 가상계좌가 발급되었습니다.\n"
             . "아래 계좌로 입금하시면 등록이 최종 확정되며, QR코드가 문자로 발송됩니다.\n\n"
             . "입금계좌: " . $bank_num . "\n"
             . "입금금액: " . $won . "원\n\n"
             . "자세한 내용은 홈페이지를 참고하세요.\n"
             . "https://epiclounge.co.kr/unrealfest2026/\n\n"
             . "- 언리얼 페스트 사무국";
    return ufs_send_text_sms($name, $phone, $title, $message, 'vbank');
}
}
