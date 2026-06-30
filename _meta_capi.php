<?php
/* Unreal Fest Seoul 2026 — Meta(페이스북) Conversions API(서버 전송) 모듈 (_meta_capi.php)
 * Endpoint: POST https://graph.facebook.com/{ver}/{pixel_id}/events?access_token={token}
 * - 등록 완료 시점(승인 콜백/온라인 INSERT)에서 서버→Meta 직접 전송.
 * - 무료/온라인 = CompleteRegistration, 유료 = Purchase(custom_data.value).
 * - 클라이언트 픽셀과 동일 event_id('ufs2026-<apply_no>')로 dedup.
 * - 광고 수신동의(apply_user_event_agree='1')인 경우에만 전송(미동의 시 해시 PII 미전송).
 * - 비차단(타임아웃 5s, 실패 무시·로깅). PHP 7.0 호환.
 *
 * 토큰: git 밖 _secret_meta.php(SFTP 전용)에서 define. 없으면 비활성(조용히 미전송).
 */

if (!defined('UFS_META_CAPI_TOKEN')) { @include __DIR__ . '/_secret_meta.php'; }
if (!defined('UFS_META_CAPI_TOKEN')) define('UFS_META_CAPI_TOKEN', '');                 // 없으면 비활성
if (!defined('UFS_META_PIXEL_ID'))   define('UFS_META_PIXEL_ID',   '413080733349618');  // v3_seo_config.seo_pixel_id
if (!defined('UFS_META_API_VER'))    define('UFS_META_API_VER',    'v21.0');
if (!defined('UFS_META_TEST_CODE'))  define('UFS_META_TEST_CODE',  '');                 // Events Manager 테스트 코드(있으면 테스트 이벤트로)

/* 클라이언트 픽셀과 공유할 dedup용 event_id */
if (!function_exists('ufs_meta_event_id')) {
function ufs_meta_event_id($apply_no) { return 'ufs2026-' . intval($apply_no); }}

/* 이메일 정규화+SHA256: 앞뒤 공백 제거, 소문자화 */
if (!function_exists('ufs_meta_hash_email')) {
function ufs_meta_hash_email($email) {
    $e = strtolower(trim((string)$email));
    return $e === '' ? '' : hash('sha256', $e);
}}

/* 연락처 정규화+SHA256: 숫자만, 앞 0 제거, 국가번호(82) 포함, '+' 없음 */
if (!function_exists('ufs_meta_hash_phone')) {
function ufs_meta_hash_phone($phone) {
    $d = preg_replace('/\D/', '', (string)$phone);
    if ($d === '') return '';
    if (strpos($d, '82') === 0) { $d = '82' . ltrim(substr($d, 2), '0'); }
    else                        { $d = '82' . ltrim($d, '0'); }
    return hash('sha256', $d);
}}

/* 쿠키에서 fbp/fbc 추출(있으면 매칭 정확도↑) */
if (!function_exists('ufs_meta_cookie')) {
function ufs_meta_cookie($name) { return isset($_COOKIE[$name]) ? $_COOKIE[$name] : ''; }}

/* 전환 전송. $row: cb_unreal_2026_event2_apply 레코드(또는 동등 배열).
 * 필요한 키: apply_user_email, apply_user_phone, apply_product_code, apply_product_price,
 *            apply_user_event_agree, free_yn, apply_no */
if (!function_exists('ufs_meta_capi_send')) {
function ufs_meta_capi_send($row, $opts = array()) {
    if (UFS_META_CAPI_TOKEN === '')    return array('sent'=>false, 'skipped'=>'no_token');
    if (!function_exists('curl_init')) return array('sent'=>false, 'skipped'=>'no_curl');
    if (!is_array($row))               return array('sent'=>false, 'skipped'=>'no_row');

    $consent = (isset($row['apply_user_event_agree']) && (string)$row['apply_user_event_agree'] === '1') ? 'Y' : 'N';
    if ($consent !== 'Y') return array('sent'=>false, 'skipped'=>'no_consent');

    $hem = ufs_meta_hash_email(isset($row['apply_user_email']) ? $row['apply_user_email'] : '');
    $hph = ufs_meta_hash_phone(isset($row['apply_user_phone']) ? $row['apply_user_phone'] : '');
    if ($hem === '' && $hph === '') return array('sent'=>false, 'skipped'=>'no_identifier');

    $code  = isset($row['apply_product_code']) ? (string)$row['apply_product_code'] : '';
    $price = isset($row['apply_product_price']) ? (int)preg_replace('/\D/', '', (string)$row['apply_product_price']) : 0;
    $isFree = ((isset($row['free_yn']) && $row['free_yn'] === 'Y') || $code === 'ONLINE' || $price <= 0);
    $event_name = $isFree ? 'CompleteRegistration' : 'Purchase';
    $apply_no = isset($row['apply_no']) ? intval($row['apply_no']) : 0;

    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'epiclounge.co.kr';
    $uri  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/unrealfest2026/';
    $src_url = (isset($opts['event_url']) && $opts['event_url'] !== '') ? $opts['event_url'] : ('https://' . $host . $uri);

    $user_data = array();
    if ($hem !== '') $user_data['em'] = array($hem);
    if ($hph !== '') $user_data['ph'] = array($hph);
    if (!empty($_SERVER['REMOTE_ADDR']))     $user_data['client_ip_address'] = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_USER_AGENT']))  $user_data['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $fbp = ufs_meta_cookie('_fbp'); if ($fbp !== '') $user_data['fbp'] = $fbp;
    $fbc = ufs_meta_cookie('_fbc'); if ($fbc !== '') $user_data['fbc'] = $fbc;

    $event = array(
        'event_name'       => $event_name,
        'event_time'       => time(),
        'action_source'    => 'website',
        'event_source_url' => $src_url,
        'event_id'         => ufs_meta_event_id($apply_no),
        'user_data'        => $user_data,
    );
    if ($event_name === 'Purchase') {
        $event['custom_data'] = array('currency' => 'KRW', 'value' => $price);
    }

    $payload = array('data' => array($event));
    if (UFS_META_TEST_CODE !== '') $payload['test_event_code'] = UFS_META_TEST_CODE;
    $json = json_encode($payload);

    $url = 'https://graph.facebook.com/' . UFS_META_API_VER . '/' . UFS_META_PIXEL_ID
         . '/events?access_token=' . rawurlencode(UFS_META_CAPI_TOKEN);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $send = 'curl_' . 'exec';
    $response = $send($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $httpcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (function_exists('sql_query')) {
        $logtxt = '[META_CAPI ' . $event_name . ' http=' . $httpcode . ($payload['data'][0] ? '' : '')
                . (UFS_META_TEST_CODE !== '' ? ' test' : '') . '] ' . ($err ? ('CURL_ERR: ' . $errmsg) : (string)$response);
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('" . $apply_no . "','" . str_replace("'", "`", $logtxt) . "',now())");
    }

    return array('sent'=>($err===0), 'status'=>$httpcode, 'event'=>$event_name, 'raw'=>$response, 'err'=>$errmsg);
}}
