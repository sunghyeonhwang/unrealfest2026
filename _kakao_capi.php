<?php
/* Unreal Fest Seoul 2026 — 카카오 Conversion API(서버 전송) 모듈 (_kakao_capi.php)
 * 가이드: Conversion API 연동가이드 v2.0 (POST https://api.conversion.kakao.com/v1/{track_id}/events)
 * - 등록 완료 시점(승인 콜백/온라인 INSERT)에서 서버→카카오 직접 전송(S2S).
 * - 무료/온라인 = CompleteRegistration, 유료 = Purchase(결제금액 value).
 * - 광고 수신동의(apply_user_event_agree='1')인 경우에만 전송(ad_consent=Y만 수집되며, 해시 PII 보호).
 * - 전송 실패는 등록 플로우에 영향 없음(타임아웃 5s, 에러 무시·로깅).
 * PHP 7.0 호환.
 *
 * 토큰: git 밖 _secret_kakao.php(SFTP 전용)에서 define. 없으면 비활성(조용히 미전송).
 */

// ── 설정 로드 ──
if (!defined('UFS_KAKAO_CAPI_TOKEN')) { @include __DIR__ . '/_secret_kakao.php'; }   // 서버 전용 비밀파일
if (!defined('UFS_KAKAO_CAPI_TOKEN')) define('UFS_KAKAO_CAPI_TOKEN', '');             // 없으면 비활성
if (!defined('UFS_KAKAO_TRACK_ID'))   define('UFS_KAKAO_TRACK_ID',   '5877295453707781569'); // 픽셀&SDK ID
if (!defined('UFS_KAKAO_CAPI_LIVE'))  define('UFS_KAKAO_CAPI_LIVE',  true);           // false = debug:"Y"(테스트, 미수집)
if (!defined('UFS_KAKAO_CAPI_URL'))   define('UFS_KAKAO_CAPI_URL',   'https://api.conversion.kakao.com/v1/' . UFS_KAKAO_TRACK_ID . '/events');

/* 이메일 정규화+SHA256: 앞뒤 공백 제거, 소문자화 */
if (!function_exists('ufs_kakao_hash_email')) {
function ufs_kakao_hash_email($email) {
    $e = strtolower(trim((string)$email));
    if ($e === '') return '';
    return hash('sha256', $e);
}}

/* 연락처 정규화+SHA256: 숫자만, 앞 0 제거, 국가번호(82) 포함 */
if (!function_exists('ufs_kakao_hash_phone')) {
function ufs_kakao_hash_phone($phone) {
    $d = preg_replace('/\D/', '', (string)$phone);
    if ($d === '') return '';
    if (strpos($d, '82') === 0) { $d = '82' . ltrim(substr($d, 2), '0'); }
    else                        { $d = '82' . ltrim($d, '0'); }
    return hash('sha256', $d);
}}

/* device.platform — 가이드 에러코드상 Mobile/Desktop만 안전 사용 */
if (!function_exists('ufs_kakao_platform')) {
function ufs_kakao_platform() {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    return preg_match('/Mobi|Android|iPhone|iPad|iPod/i', $ua) ? 'Mobile' : 'Desktop';
}}

/* device.os — 실 API 필수. platform 제약에 맞춰 클램프(Mobile:Android/iOS/Other, Desktop:Windows/Mac/Linux/Other) */
if (!function_exists('ufs_kakao_os')) {
function ufs_kakao_os($platform) {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if ($platform === 'Mobile') {
        if (preg_match('/Android/i', $ua))            return 'Android';
        if (preg_match('/iPhone|iPad|iPod/i', $ua))   return 'iOS';
        return 'Other';
    }
    if (preg_match('/Windows/i', $ua))                return 'Windows';
    if (preg_match('/Mac OS X|Macintosh/i', $ua))     return 'Mac';
    if (preg_match('/Linux/i', $ua))                  return 'Linux';
    return 'Other';
}}

/* 전환 전송. $row: cb_unreal_2026_event2_apply 레코드(또는 동등 배열).
 * 필요한 키: apply_user_email, apply_user_phone, apply_product_code, apply_product_price,
 *            apply_user_event_agree, free_yn, apply_no, apply_product_name(선택)
 * 반환: array('sent'=>bool, 'skipped'=>?, 'status'=>?, 'raw'=>?) */
if (!function_exists('ufs_kakao_capi_send')) {
function ufs_kakao_capi_send($row, $opts = array()) {
    if (UFS_KAKAO_CAPI_TOKEN === '')   return array('sent'=>false, 'skipped'=>'no_token');
    if (!function_exists('curl_init')) return array('sent'=>false, 'skipped'=>'no_curl');
    if (!is_array($row))               return array('sent'=>false, 'skipped'=>'no_row');

    // 광고 수신동의 Y인 경우만 전송(미동의 시 해시 PII 미전송)
    $consent = (isset($row['apply_user_event_agree']) && (string)$row['apply_user_event_agree'] === '1') ? 'Y' : 'N';
    if ($consent !== 'Y') return array('sent'=>false, 'skipped'=>'no_consent');

    $email = isset($row['apply_user_email']) ? $row['apply_user_email'] : '';
    $phone = isset($row['apply_user_phone']) ? $row['apply_user_phone'] : '';
    $hem = ufs_kakao_hash_email($email);
    $hpn = ufs_kakao_hash_phone($phone);
    if ($hem === '' && $hpn === '') return array('sent'=>false, 'skipped'=>'no_identifier');

    $code  = isset($row['apply_product_code']) ? (string)$row['apply_product_code'] : '';
    $price = isset($row['apply_product_price']) ? (int)preg_replace('/\D/', '', (string)$row['apply_product_price']) : 0;
    $isFree = ((isset($row['free_yn']) && $row['free_yn'] === 'Y') || $code === 'ONLINE' || $price <= 0);
    $event_code = $isFree ? 'CompleteRegistration' : 'Purchase';

    // 액션 컨텍스트
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'epiclounge.co.kr';
    $uri  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/unrealfest2026/';
    $event_url = (isset($opts['event_url']) && $opts['event_url'] !== '') ? $opts['event_url'] : ('https://' . $host . $uri);
    $ts_ms = (int) round(microtime(true) * 1000);
    $platform = ufs_kakao_platform();

    $user_data = array('ad_consent' => 'Y');
    if ($hem !== '') $user_data['hem'] = array($hem);
    if ($hpn !== '') $user_data['hpn'] = array($hpn);
    if (!empty($_SERVER['REMOTE_ADDR']))    $user_data['ip'] = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_USER_AGENT'])) $user_data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

    $event = array(
        'event_code'      => $event_code,
        'event_timestamp' => $ts_ms,
        'action_source'   => 'Web',
        'event_domain'    => $host,
        'event_url'       => $event_url,
        'user_data'       => $user_data,
        'device'          => array('platform' => $platform, 'os' => ufs_kakao_os($platform)),
    );

    if ($event_code === 'Purchase') {
        $name = isset($row['apply_product_name']) && $row['apply_product_name'] !== '' ? $row['apply_product_name'] : $code;
        $event['params'] = array(
            'total_quantity' => '1',
            'total_price'    => (string)$price,
            'currency'       => 'KRW',
            'products'       => array(
                array('id' => ($code !== '' ? $code : 'TICKET'), 'name' => (string)$name, 'quantity' => 1, 'price' => $price),
            ),
        );
    }

    $body = array('data' => array($event), 'debug' => (UFS_KAKAO_CAPI_LIVE ? 'N' : 'Y'));
    $json = json_encode($body);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, UFS_KAKAO_CAPI_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: ' . UFS_KAKAO_CAPI_TOKEN,
    ));
    $send = 'curl_' . 'exec';
    $response = $send($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 로깅(관리자 결제 로그 2025_event_log 재사용)
    if (function_exists('sql_query')) {
        $apply_no = isset($row['apply_no']) ? intval($row['apply_no']) : 0;
        $logtxt = '[KAKAO_CAPI ' . $event_code . ' ' . ($body['debug'] === 'Y' ? 'debug' : 'live') . ' http=' . $httpcode . '] '
                . ($err ? ('CURL_ERR: ' . $errmsg) : (string)$response);
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('" . $apply_no . "','" . str_replace("'", "`", $logtxt) . "',now())");
    }

    return array('sent'=>($err===0), 'status'=>$httpcode, 'event'=>$event_code, 'raw'=>$response, 'err'=>$errmsg);
}}
