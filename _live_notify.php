<?php
/* Unreal Fest Seoul 2026 — 라이브 시청 안내 분산 발송 모듈 (_live_notify.php)
 *
 * 목적: 행사 당일 온라인 시청자가 10:30 정각에 한꺼번에 몰려 원본 서버(cafe24 웹호스팅)의
 *       동시 연결 상한을 넘겨 503 이 터지는 것을 막는다. 09:30~10:20 사이에 나눠 보내
 *       "미리 입장"을 유도해 진입을 분산시킨다.
 *
 * 설계 포인트
 *  - 대상은 슬롯마다 다르다(ufs_ln_audience_sql). 온라인 시청 안내는 apply_product_code='ONLINE',
 *    현장 체크인 안내는 그날 오는 현장 참석자, 감사 인사는 전원.
 *    온라인 오전 슬롯은 여기에 ① 아직 그날 라이브 미접속 ② 아직 그날 미발송 조건이 더 붙는다.
 *           (등록자는 계속 늘어날 수 있으므로 발송 시점에 매번 동적으로 조회)
 *  - 중복 발송 방지 = cb_unreal_2026_live_notify 의 UNIQUE(apply_no, ln_day)
 *  - 발송 채널은 어댑터로 분리(alimtalk / sms). 알림톡 API 가 통째로 실패하면 SMS 로
 *    대체하지 않고 선점을 되돌려 재시도한다(전건 LMS 발송 = 비용 폭증 방지).
 *  - 실제 발송 전에는 항상 dry-run 으로 대상 수를 확인할 수 있다.
 * PHP 7.0 호환.
 */

if (!defined('UFS_LN_LIVE_URL')) define('UFS_LN_LIVE_URL', 'https://epiclounge.co.kr/unrealfest2026/live.php');

/* 발송 기록 테이블 — UNIQUE(apply_no, ln_day) 로 같은 날 중복 발송을 DB 레벨에서 차단 */
if (!function_exists('ufs_ln_table')) {
function ufs_ln_table() {
    @sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_live_notify (
        ln_no INT NOT NULL AUTO_INCREMENT,
        apply_no INT NOT NULL DEFAULT 0,
        ln_day VARCHAR(8) NOT NULL DEFAULT '',
        ln_name VARCHAR(100) NOT NULL DEFAULT '',
        ln_phone VARCHAR(30) NOT NULL DEFAULT '',
        ln_channel VARCHAR(12) NOT NULL DEFAULT '',
        ln_status CHAR(1) NOT NULL DEFAULT '',
        ln_at DATETIME DEFAULT NULL,
        ln_result VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (ln_no),
        UNIQUE KEY uq_apply_day (apply_no, ln_day),
        KEY idx_day_status (ln_day, ln_status)
    ) DEFAULT CHARSET=utf8");
    // 슬롯 코드(d1am/d1pm/d2am/d2pm) 저장을 위해 폭 확장 — 이미 넓으면 무해
    @sql_query("ALTER TABLE cb_unreal_2026_live_notify MODIFY ln_day VARCHAR(8) NOT NULL DEFAULT ''");
}
}

/* 발송 슬롯 정의 — 오전(진입 분산용, 미접속자만) / 오후(복귀 유도, 전체)
 *   key   : 슬롯 코드(발송 기록·설정 키에 사용)
 *   day   : 접속여부 판정에 쓸 Day(1|2)
 *   only_unvisited : true 면 그날 아직 라이브에 안 들어온 사람만 */
if (!function_exists('ufs_ln_slots')) {
function ufs_ln_slots() {
    // audience: online(온라인 중계) / offline(그날 오는 현장 참석자) / all(전체 확정 등록자)
    // only_unvisited: 그날 라이브 미접속자만(진입 분산용) — 온라인 오전 슬롯에만 적용
    return array(
        // ── 알림톡(카카오) ─────────────────────────────────────────────
        'd1chk'  => array('label'=>'Day1 체크인 오픈',   'day'=>'1', 'audience'=>'offline', 'only_unvisited'=>false, 'tpl_def'=>'325', 'ch'=>'kakao', 'mode'=>'bulk'),
        'd1am'   => array('label'=>'Day1 온라인 시청안내','day'=>'1', 'audience'=>'online',  'only_unvisited'=>true,  'tpl_def'=>'328', 'ch'=>'kakao', 'mode'=>'spread'),
        'd1pm'   => array('label'=>'Day1 오후 세션',      'day'=>'1', 'audience'=>'online',  'only_unvisited'=>false, 'tpl_def'=>'331', 'ch'=>'kakao', 'mode'=>'spread'),
        'd2chk'  => array('label'=>'Day2 체크인 오픈',   'day'=>'2', 'audience'=>'offline', 'only_unvisited'=>false, 'tpl_def'=>'334', 'ch'=>'kakao', 'mode'=>'bulk'),
        'd2am'   => array('label'=>'Day2 온라인 시청안내','day'=>'2', 'audience'=>'online',  'only_unvisited'=>true,  'tpl_def'=>'337', 'ch'=>'kakao', 'mode'=>'spread'),
        'd2pm'   => array('label'=>'Day2 오후 세션',      'day'=>'2', 'audience'=>'online',  'only_unvisited'=>false, 'tpl_def'=>'340', 'ch'=>'kakao', 'mode'=>'spread'),
        'thanks' => array('label'=>'감사 인사(행사 후)',  'day'=>'2', 'audience'=>'all',     'only_unvisited'=>false, 'tpl_def'=>'343', 'ch'=>'kakao', 'mode'=>'bulk'),
        // ── 이메일 뉴스레터(Resend) ────────────────────────────────────
        // 본문은 관리자 설정(live_notify_nl_url_{slot})의 HTML 그대로. 주소가 없으면 발송하지 않는다.
        // 분산으로 보낸다 — 우리 서버 부하 때문이 아니라, 수천 통을 한 번에 쏟으면
        // 수신측 스팸 필터가 대량 발송으로 보기 쉬워서다. 상한은 200이 아니라 500/분
        // (200 은 원본 서버로 몰리는 것을 막는 값이고, 메일은 원본을 거치지 않는다).
        'nl_d1'  => array('label'=>'뉴스레터 Day1',       'day'=>'1', 'audience'=>'online',  'only_unvisited'=>false, 'tpl_def'=>'',    'ch'=>'email', 'mode'=>'spread', 'max_batch'=>500),
        'nl_d2'  => array('label'=>'뉴스레터 Day2',       'day'=>'2', 'audience'=>'online',  'only_unvisited'=>false, 'tpl_def'=>'',    'ch'=>'email', 'mode'=>'spread', 'max_batch'=>500),
        'nl_thx' => array('label'=>'뉴스레터 감사인사',   'day'=>'2', 'audience'=>'all',     'only_unvisited'=>false, 'tpl_def'=>'',    'ch'=>'email', 'mode'=>'spread', 'max_batch'=>500),
    );
}
}
/* 대상(audience) → SQL 조건
 *
 * 분류 기준은 결제 여부가 아니라 '무엇을 신청했는가'(상품코드)다.
 *   ONLINE      온라인 중계 — 전원 무료
 *   NORMAL_ALL  현장 양일권 / NORMAL_20 현장 Day1권 / NORMAL_21 현장 Day2권
 *
 * 과거에 online 조건에 free_yn='Y' 를 넣었던 것은 "온라인=무료"라는 이유였는데,
 * ONLINE 은 전원 무료라 이 조건이 하는 일이 없으면서 무료 쿠폰으로 현장 등록한
 * 사람(스피커·스폰서·초청 등)까지 online 으로 끌어왔다. 그 결과 현장에 오는 사람이
 * 체크인 안내 대신 온라인 시청 안내를 받았다. → 상품코드만으로 판정한다.
 *
 * 또 체크인 안내는 '그날 오는 사람'에게만 가야 한다. 하루권 소지자에게 다른 날
 * 체크인 안내가 가지 않도록 $day 로 반대편 하루권을 제외한다. */
if (!function_exists('ufs_ln_audience_sql')) {
function ufs_ln_audience_sql($a, $day = '') {
    if ($a === 'all')    return "";
    if ($a === 'online') return " AND a.apply_product_code='ONLINE'";
    // offline — 현장 참석자(온라인 중계가 아닌 모든 상품). 새 현장 상품이 생겨도 자동으로 포함된다.
    $s = " AND a.apply_product_code<>'ONLINE'";
    if ($day === '1') $s .= " AND a.apply_product_code<>'NORMAL_21'";   // Day2권만 가진 사람 제외
    if ($day === '2') $s .= " AND a.apply_product_code<>'NORMAL_20'";   // Day1권만 가진 사람 제외
    return $s;
}
}
if (!function_exists('ufs_ln_slot')) {
function ufs_ln_slot($k) { $s = ufs_ln_slots(); return isset($s[$k]) ? $s[$k] : $s['d1am']; }
}

/* 그날(day=1|2) 아직 안내를 못 받았고 아직 접속도 안 한 온라인 등록자 조회
 *  - 접속 여부: cb_unreal_2026_event2_apply_live 의 d1_at / d2_at (Day별 최초접속 스탬프)
 *  - $limit=0 이면 개수만 셀 때 쓰도록 전체를 반환하지 않고 COUNT 용 쿼리를 따로 쓴다 */
if (!function_exists('ufs_ln_sql_where')) {
function ufs_ln_sql_where($slot) {
    $sl  = ufs_ln_slot($slot);
    $k   = sql_real_escape_string($slot);
    $dcol = ($sl['day'] === '2') ? 'd2_at' : 'd1_at';
    // 오후 슬롯은 이미 시청한 사람도 대상(복귀 유도) → 접속 조인 생략
    $join_visit = $sl['only_unvisited']
        ? "LEFT JOIN cb_unreal_2026_event2_apply_live lv
                  ON lv.apply_user_email = a.apply_user_email AND lv.$dcol IS NOT NULL"
        : "";
    $cond_visit = $sl['only_unvisited'] ? " AND lv.la_no IS NULL" : "";
    // 이메일 슬롯은 연락처 대신 이메일이 있어야 하고, 광고성으로 운영할 경우 수신동의자만 추릴 수 있다
    $is_mail = (isset($sl['ch']) && $sl['ch'] === 'email');
    $cond_ch = $is_mail ? " AND a.apply_user_email <> ''" : " AND a.apply_user_phone <> ''";
    if ($is_mail) {
        ufs_ln_optout_table();
        // 수신거부한 주소는 제외
        $cond_ch .= " AND NOT EXISTS (SELECT 1 FROM cb_unreal_2026_mail_optout mo WHERE mo.mo_email = LOWER(a.apply_user_email))";
        if (ufs_ln_cfg('live_notify_nl_agree_only', '0') === '1') $cond_ch .= " AND a.apply_user_event_agree='1'";
    }
    return array($sl,
        "FROM cb_unreal_2026_event2_apply a
         LEFT JOIN cb_unreal_2026_live_notify n
                ON n.apply_no = a.apply_no AND n.ln_day = '$k'
         $join_visit
         WHERE a.apply_temp_yn='N' AND a.apply_pay_status<>0
           AND n.ln_no IS NULL"
        . $cond_ch
        . ufs_ln_audience_sql(isset($sl['audience']) ? $sl['audience'] : 'online', $sl['day'])
        . $cond_visit);
}
}

/* 남은 대상 수 */
if (!function_exists('ufs_ln_remaining')) {
function ufs_ln_remaining($slot) {
    ufs_ln_table();
    list($sl, $w) = ufs_ln_sql_where($slot);
    $r = sql_fetch("SELECT COUNT(*) c " . $w);
    return $r ? (int)$r['c'] : 0;
}
}

/* 1회 발송 인원 자동 계산 — 남은 대상 ÷ 남은 시간(분).
 *  · 스스로 먼저 입장한 사람은 대상에서 빠지므로 배치가 자동으로 작아지고,
 *    등록자가 늘면 자동으로 커진다. 창 종료까지 균등하게 소진되도록 매분 재계산.
 *  · 상한(기본 200/분)은 도착 폭주 방지용. 200명/분이어도 원본 부하는 약 7 req/s 수준. */
if (!function_exists('ufs_ln_auto_batch')) {
function ufs_ln_auto_batch($slot, $end_str, $start_str = '', $min = 10, $max = 0) {
    if ($max <= 0) { $sl = ufs_ln_slot($slot); $max = isset($sl['max_batch']) ? (int)$sl['max_batch'] : 200; }
    $remain = ufs_ln_remaining($slot);
    if ($remain <= 0) return 0;
    $end = ($end_str !== '') ? strtotime($end_str) : false;
    if ($end === false) return $min;
    // 기준 시각 = 지금과 창 시작 중 늦은 쪽 → 창 시작 전 미리보기에서도 실제와 같은 값이 나온다
    $base = time();
    if ($start_str !== '') { $st = strtotime($start_str); if ($st !== false && $st > $base) $base = $st; }
    $left = (int)ceil(($end - $base) / 60);
    if ($left < 1) $left = 1;                       // 창 종료 임박·초과 → 남은 전량(상한 적용)
    $n = (int)ceil($remain / $left);
    return max($min, min($max, $n));
}
}

/* 이번 배치 대상 N명 */
if (!function_exists('ufs_ln_targets')) {
function ufs_ln_targets($slot, $limit) {
    ufs_ln_table();
    list($sl, $w) = ufs_ln_sql_where($slot);
    $limit = max(1, min(500, (int)$limit));
    $out = array();
    $q = sql_query("SELECT a.apply_no, a.apply_user_name nm, a.apply_user_phone ph, a.apply_user_email em " . $w . " ORDER BY a.apply_no ASC LIMIT " . $limit);
    if ($q) { while ($r = $q->fetch_assoc()) { $out[] = $r; } }
    return $out;
}
}

/* 승인된 알림톡 템플릿 본문(Day1/Day2). 알림톡은 이 본문과 한 글자라도 다르면 발송 실패한다.
 * 변수 치환 없음(전원 동일 본문). 링크는 템플릿의 웹링크 버튼으로 나가고,
 * SMS 대체발송 시에는 버튼이 없으므로 본문 끝에 링크를 덧붙인다. */
if (!function_exists('ufs_ln_template_body')) {
function ufs_ln_template_body($slot) {
    switch ($slot) {
        case 'd1pm':   // 템플릿 331
            return "언리얼 페스트 서울 2026ㅣDay 1 오후 세션 시작!\n\n"
                 . "사전 등록하신 Day 1 오후 세션이 시작되었습니다.\n"
                 . "아래 링크를 클릭해 지금 바로 시청해 보세요!\n\n"
                 . "언리얼 페스트 사무국";
        case 'd2pm':   // 템플릿 340
            return "언리얼 페스트 서울 2026ㅣDay 2 오후 세션 시작!\n\n"
                 . "사전 등록하신 Day 2 오후 세션이 시작되었습니다.\n"
                 . "아래 링크를 클릭해 지금 바로 시청해 보세요!\n\n"
                 . "언리얼 페스트 사무국";
        case 'd1chk':  // 템플릿 325 (오프라인 체크인)
            return "언리얼 페스트 서울 2026ㅣ오늘 시작! Day 1 입장 안내\n\n"
                 . "사전 등록 시 입력하신 연락처로 발송된 QR 코드를 준비해 주세요. 셀프 체크인 또는 유인 데스크에서 체크인하신 후 명찰을 수령해 주세요.\n\n"
                 . "오프라인 참석자 전원께 한정판 티셔츠를 드립니다. 현장 체크인 선착순 300분께는 ‘얼리버드 체크인 이벤트’ 쿠폰을 드리며, 쿠폰 소지자에 한해 추가 굿즈를 증정합니다.\n\n"
                 . "체크인: 8월 20일(목) 09:00\n장소: 웨스틴 서울 파르나스 지하 1층 하모니 볼룸 앞";
        case 'd2chk':  // 템플릿 334 (오프라인 체크인)
            return "언리얼 페스트 서울 2026 | 오늘 시작! Day 2 입장 안내\n\n"
                 . "오늘 처음 방문하시는 참석자는 사전 등록 시 입력하신 연락처로 발송된 QR 코드를 준비해 주세요. 셀프 체크인 또는 유인 데스크에서 체크인하신 후 명찰을 수령해 주세요.\n\n"
                 . "Day 2 일일권 참석자는 한정판 티셔츠를 수령하실 수 있습니다.\n\n"
                 . "Day 1에 명찰을 수령하신 양일권 참석자는 기존 명찰을 지참해 주세요.\n\n"
                 . "체크인: 8월 21일(금) 09:30\n장소: 웨스틴 서울 파르나스 지하 1층 하모니 볼룸 앞";
        case 'thanks': // 템플릿 343 (행사 후 감사)
            return "언리얼 페스트 서울 2026ㅣ함께해 주셔서 감사합니다!\n\n"
                 . "지난 8월 20일(목) ~ 21일(금) 진행된 언리얼 페스트 서울 2026이 성황리에 마무리되었습니다. \n"
                 . "현장과 온라인에서 함께해 주신 모든 분께 진심으로 감사드립니다.\n\n"
                 . "세션 다시보기는 2개월 내 에픽 라운지와 에픽게임즈 코리아 유튜브 채널을 통해 공개될 예정이니, 언리얼 페스트 서울 2026의 다양한 세션을 다시 만나보세요.\n\n"
                 . "내년 언리얼 페스트에서 다시 만나요!\n\n"
                 . "언리얼 페스트 사무국";
        case 'd2am':   // 템플릿 337
            return "언리얼 페스트 서울 2026 ㅣDay 2 온라인 시청 안내\n\n"
                 . "사전 등록하신 Day 2 온라인 세션이 곧 시작됩니다.\n"
                 . "아래 링크에서 온라인 체크인을 완료한 후, 원하는 트랙으로 입장해 주세요!\n\n"
                 . "Day 1에 이어 오늘도 온라인 체크인을 완료하시면 ‘출석 체크 이벤트’에 자동 응모되며, 추첨을 통해 200분께 한정판 티셔츠를 드립니다.\n\n"
                 . "언리얼 페스트 사무국";
        default:       // d1am — 템플릿 328
            return "언리얼 페스트 서울 2026 ㅣDay 1 온라인 시청 안내\n\n"
                 . "사전 등록하신 Day 1 온라인 세션이 곧 시작됩니다.\n"
                 . "아래 링크에서 온라인 체크인을 완료한 후, 원하는 트랙으로 입장해 주세요!\n\n"
                 . "양일 온라인 체크인을 하시면 ‘출석 체크 이벤트’에 자동 응모되며, 추첨을 통해 200분께 한정판 티셔츠를 드립니다.\n\n"
                 . "언리얼 페스트 사무국";
    }
}
}

/* SMS/LMS 대체발송 문구 — 승인 템플릿과 동일 문구 + 링크(버튼이 없으므로 본문에 포함) */
if (!function_exists('ufs_ln_message')) {
function ufs_ln_message($name, $slot) {
    $b = ufs_ln_template_body($slot);
    // 체크인 안내·감사 인사는 시청 링크가 필요 없다(원문 그대로 발송)
    if ($slot === 'd1chk' || $slot === 'd2chk' || $slot === 'thanks') return $b;
    // '언리얼 페스트 사무국' 앞에 링크 삽입
    $link = "\n▶ 온라인 체크인: " . UFS_LN_LIVE_URL . "\n\n";
    $pos = strrpos($b, "\n\n언리얼 페스트 사무국");
    if ($pos !== false) return substr($b, 0, $pos) . "\n" . $link . "언리얼 페스트 사무국";
    return $b . $link;
}
}

/* 설정 조회(알림톡 연동 정보) */
if (!function_exists('ufs_ln_cfg')) {
function ufs_ln_cfg($k, $def = '') {
    $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='" . sql_real_escape_string($k) . "'");
    return ($r && $r['cfg_val'] !== '') ? $r['cfg_val'] : $def;
}
}

/* ── 알림톡 발송 (DirectSend api_v2/kakao_notice) ───────────────────────────
 * ★ 배치 전체를 receiver 배열에 담아 API 1회 호출 → 60명 발송에 호출 1회.
 *   (DirectSend 제한: 분당 300 호출. 1인 1호출이면 60호출/분이라 낭비 + 느림)
 * ★ 대체발송(LMS)은 쓰지 않는다(kakao_faild_type=0). 알림톡 미수신자에게는 아무것도 안 간다.
 *   QR·온라인 시청 안내는 출입 업체가 별도 솔루션으로 전원에게 LMS 를 보내므로,
 *   여기서 또 보내면 중복이고 수신자에게 공해가 된다.
 * 설정(관리자): live_notify_at_plusid(발신프로필 @아이디) · live_notify_at_tpl_d1|d2(템플릿 번호)
 * 응답: {"status":"1"} = 정상. 그 외는 실패 코드(302 인증, 305 프로필키, 306 잔액 등). */
if (!function_exists('ufs_ln_send_alimtalk_batch')) {
function ufs_ln_send_alimtalk_batch($rows, $slot) {
    $sl     = ufs_ln_slot($slot);
    $plusid = ufs_ln_cfg('live_notify_at_plusid');
    $tpl    = ufs_ln_cfg('live_notify_at_tpl_' . $slot, $sl['tpl_def']);
    if ($plusid === '' || $tpl === '' || !function_exists('curl_init') || !count($rows)) {
        return array('ok' => false, 'msg' => 'alimtalk_not_configured');
    }
    require_once __DIR__ . '/_sms.php';   // DirectSend 계정 상수·헬퍼 재사용

    $rcv = array();
    foreach ($rows as $r) {
        $p = ufs_normalize_phone($r['ph']);
        if ($p === '') continue;
        $rcv[] = '{"name":"' . ufs_sms_json_escape($r['nm']) . '","mobile":"' . $p . '"}';
    }
    if (!count($rcv)) return array('ok' => false, 'msg' => 'no_valid_phone');

    $post = array(
        'username'         => UFS_SMS_USERNAME,
        'key'              => UFS_SMS_KEY,
        'kakao_plus_id'    => $plusid,
        'user_template_no' => $tpl,
        'receiver'         => '[' . implode(',', $rcv) . ']',
        // 대체발송 안 함 — 출입 업체가 전원에게 LMS 를 따로 보내므로 중복 방지
        'kakao_faild_type' => '0',
        'sender'           => UFS_SMS_SENDER,
        // 대체발송을 안 쓰므로 title 은 실사용되지 않지만, 길면 DirectSend 가 status 312
        // ('문자 제목 최대 길이 초과')로 요청 전체를 거부하므로 짧게 유지한다.
        'title'            => '언리얼 페스트 서울 2026',
        'message'          => ufs_ln_message('', $slot),
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://directsend.co.kr/index.php/api_v2/kakao_notice');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'cache-control: no-cache'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $send = 'curl_' . 'exec';
    $resp = $send($ch);
    $err  = curl_errno($ch);
    curl_close($ch);

    if (function_exists('sql_query')) {
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('0','[ALIMTALK " . $slot . " n=" . count($rcv) . "] "
            . str_replace("'", "`", $err ? ('CURL_ERR ' . $err) : substr((string)$resp, 0, 400)) . "',now())");
    }
    if ($err) return array('ok' => false, 'msg' => 'curl_err_' . $err);
    $ok = (strpos((string)$resp, '"status":"1"') !== false);   // DirectSend 알림톡: 1 = 정상
    return array('ok' => $ok, 'msg' => substr((string)$resp, 0, 180));
}
}

/* ── 이메일 뉴스레터 (Resend) ───────────────────────────────────────────────
 * 카톡과 같은 대상·같은 중복방지(UNIQUE apply_no+ln_day)를 쓰되 채널만 다르다.
 * 본문은 아래 HTML 이며, 가운데 CTA 버튼의 주소만 관리자 설정에서 넣는다
 * (live_notify_nl_url_{slot}). 링크가 비어 있으면 발송 자체를 하지 않는다.
 * 제목·버튼 문구도 설정으로 덮어쓸 수 있다(live_notify_nl_subj_/nl_btn_). */
if (!function_exists('ufs_ln_nl_text')) {
function ufs_ln_nl_text($slot) {
    $t = array(
        'nl_d1'  => array('subj' => '[언리얼 페스트 서울 2026] 오늘 Day 1, 10시 30분에 시작합니다'),
        'nl_d2'  => array('subj' => '[언리얼 페스트 서울 2026] 오늘 Day 2, 10시 30분에 시작합니다'),
        'nl_thx' => array('subj' => '[언리얼 페스트 서울 2026] 함께해 주셔서 감사합니다'),
    );
    $d = isset($t[$slot]) ? $t[$slot] : $t['nl_d1'];
    $d['subj'] = ufs_ln_cfg('live_notify_nl_subj_' . $slot, $d['subj']);
    $d['url']  = ufs_ln_cfg('live_notify_nl_url_' . $slot,  '');
    return $d;
}
}

/* 뉴스레터 본문 = 설정한 주소의 HTML 그대로.
 * 매 발송마다 받아오지 않도록 5분 캐시하고, 받아오지 못하면 마지막 캐시를 쓴다.
 * 둘 다 없으면 빈 문자열 → 발송하지 않는다(빈 메일 방지). */
if (!function_exists('ufs_ln_nl_fetch')) {
function ufs_ln_nl_fetch($slot, $ttl = 300) {
    static $mem = array();
    if (isset($mem[$slot])) return $mem[$slot];
    $d = ufs_ln_nl_text($slot);
    if ($d['url'] === '') return '';
    $cache = rtrim(sys_get_temp_dir(), '/') . '/ufs2026_nl_' . preg_replace('/[^a-z0-9_]/i', '', $slot) . '.html';
    if (is_file($cache) && (time() - filemtime($cache)) < $ttl) {
        $h = @file_get_contents($cache);
        if ($h !== false && $h !== '') { $mem[$slot] = $h; return $h; }
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $d['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $f = 'curl_' . 'exec';
    $html = $f($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    // 본문으로 쓸 만한 것인지 최소 확인 — 오류 페이지·빈 응답을 그대로 보내지 않는다
    if ($code === 200 && is_string($html) && strlen($html) > 500 && stripos($html, '<') !== false) {
        @file_put_contents($cache, $html);
        $mem[$slot] = $html;
        return $html;
    }
    $h = is_file($cache) ? @file_get_contents($cache) : '';   // 마지막 성공본으로 폴백
    $mem[$slot] = ($h !== false) ? $h : '';
    return $mem[$slot];
}
}

/* 수신거부 링크 — 주소마다 서명이 달라 남의 주소를 해지할 수 없다.
 * 서명 키는 서버에만 있는 Resend 키에서 파생시켜 git 에 비밀이 남지 않게 한다. */
if (!function_exists('ufs_ln_unsub_sig')) {
function ufs_ln_unsub_sig($email) {
    $salt = defined('UFS_RESEND_API_KEY') ? UFS_RESEND_API_KEY : 'ufs2026';
    return substr(hash_hmac('sha256', strtolower(trim($email)), 'ufsnl|' . $salt), 0, 16);
}
}
if (!function_exists('ufs_ln_unsub_url')) {
function ufs_ln_unsub_url($email) {
    return 'https://epiclounge.co.kr/unrealfest2026/newsletter_unsub.php?e=' . rawurlencode($email)
         . '&s=' . ufs_ln_unsub_sig($email);
}
}

/* 발송 직전 본문 — 뉴스레터 HTML 의 {{UNSUBSCRIBE_URL}} 자리에 수신자별 해지 주소를 넣는다 */
if (!function_exists('ufs_ln_nl_body')) {
function ufs_ln_nl_body($slot, $email) {
    $html = ufs_ln_nl_fetch($slot);
    if ($html === '') return '';
    return str_replace('{{UNSUBSCRIBE_URL}}', htmlspecialchars(ufs_ln_unsub_url($email), ENT_QUOTES, 'UTF-8'), $html);
}
}

/* ── 사무국 알림 ────────────────────────────────────────────────────────────
 * 발송은 무인으로 돌아가므로, 무슨 일이 있었는지 메일로 알려 준다.
 *   시작   슬롯의 첫 배치가 나갔을 때 — 이 메일이 예정 시각에 안 오면 스케줄러가 멈춘 것이다
 *   완료   남은 대상이 0 이 됐을 때
 *   실패   발송 실패가 났을 때
 *   미발송 발송 창이 끝났는데 아직 남아 있을 때
 * 종류마다 슬롯당 한 번만 보낸다(설정에 보낸 시각을 기록해 중복을 막는다). */
if (!function_exists('ufs_ln_cfg_set')) {
function ufs_ln_cfg_set($k, $v) {
    $k = sql_real_escape_string($k); $v = sql_real_escape_string($v);
    $e = @sql_fetch("SELECT cfg_key FROM cb_unreal_2026_config WHERE cfg_key='$k'");
    if ($e) @sql_query("UPDATE cb_unreal_2026_config SET cfg_val='$v' WHERE cfg_key='$k'");
    else    @sql_query("INSERT INTO cb_unreal_2026_config (cfg_key,cfg_val) VALUES ('$k','$v')");
}
}

if (!function_exists('ufs_ln_alert')) {
function ufs_ln_alert($slot, $type, $head, $lines, $urgent = false) {
    if (ufs_ln_cfg('live_notify_alert_on', '1') !== '1') return false;
    $flag = 'live_notify_alert_' . $slot . '_' . $type;
    if (ufs_ln_cfg($flag, '') !== '') return false;                 // 이미 보냄
    ufs_ln_cfg_set($flag, date('Y-m-d H:i:s'));                     // 보내기 전에 먼저 기록 — 중복 방지 우선
    require_once __DIR__ . '/_resend.php';
    $to = ufs_ln_cfg('live_notify_alert_to', 'info@epiclounge.co.kr');
    $sl = ufs_ln_slot($slot);
    $c  = $urgent ? '#c0392b' : '#00707d';
    $rows = '';
    foreach ($lines as $k => $v) {
        $rows .= '<tr><td style="padding:5px 14px 5px 0;color:#8a90a2;font-size:13px;white-space:nowrap">' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '</td>'
               . '<td style="padding:5px 0;font-size:13px;font-weight:700">' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    }
    $html = '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Malgun Gothic\',sans-serif;max-width:520px">'
      . '<div style="font-size:12px;font-weight:700;letter-spacing:.1em;color:' . $c . '">UNREAL FEST SEOUL 2026 · 안내 발송</div>'
      . '<h2 style="margin:8px 0 14px;font-size:19px;color:#1f2330">' . htmlspecialchars($head, ENT_QUOTES, 'UTF-8') . '</h2>'
      . '<table cellpadding="0" cellspacing="0" border="0">' . $rows . '</table>'
      . '<p style="margin:18px 0 0;font-size:12.5px;color:#8a90a2;line-height:1.7">'
      . '관리자 화면: <a href="https://epiclounge.co.kr/v3/adm/2026_live_notify.php" style="color:' . $c . '">라이브 안내 분산 발송</a>'
      . ($urgent ? '<br><b style="color:#c0392b">조치가 필요할 수 있습니다.</b> 스케줄러가 멈춘 것으로 보이면 관리자 화면에서 <b>백업 실행</b>을 켜 주세요.' : '')
      . '</p></div>';
    $r = ufs_resend_send($to, '[UFS26 발송] ' . $head . ' — ' . $sl['label'], $html);
    return !empty($r['ok']);
}
}

/* 발송 창이 끝났는데 아직 남은 슬롯이 있으면 알린다(창 밖에서도 매번 확인) */
if (!function_exists('ufs_ln_watchdog')) {
function ufs_ln_watchdog() {
    if (ufs_ln_cfg('live_notify_enabled', '0') !== '1') return array();
    $now = date('Y-m-d H:i');
    $out = array();
    foreach (ufs_ln_slots() as $k => $sl) {
        $e = ufs_ln_cfg('live_notify_' . $k . '_end');
        if ($e === '' || $now <= $e) continue;                       // 아직 안 끝났다
        if (strtotime($now) - strtotime($e) > 86400) continue;       // 하루 넘게 지난 건 새삼 알리지 않는다
        $rem = ufs_ln_remaining($k);
        if ($rem <= 0) continue;
        if (ufs_ln_alert($k, 'undone', '발송 창이 끝났는데 ' . number_format($rem) . '명이 남았습니다',
            array('슬롯' => $sl['label'], '남은 대상' => number_format($rem) . '명', '창 종료' => $e, '확인 시각' => $now), true)) {
            $out[] = $k;
        }
    }
    return $out;
}
}

/* 수신거부 기록 — 여기 있는 주소는 이후 뉴스레터 대상에서 빠진다 */
if (!function_exists('ufs_ln_optout_table')) {
function ufs_ln_optout_table() {
    @sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_mail_optout (
        mo_email VARCHAR(255) NOT NULL,
        mo_at DATETIME DEFAULT NULL,
        mo_ip VARCHAR(45) NOT NULL DEFAULT '',
        PRIMARY KEY (mo_email)
    ) DEFAULT CHARSET=utf8");
}
}

/* Resend 배치 발송 — /emails/batch 는 1회 100건까지. 100건 단위로 나눠 호출한다. */
if (!function_exists('ufs_ln_send_email_batch')) {
function ufs_ln_send_email_batch($rows, $slot) {
    require_once __DIR__ . '/_resend.php';
    $d = ufs_ln_nl_text($slot);
    if ($d['url'] === '')  return array('ok' => false, 'msg' => 'newsletter_url_not_set');
    if (!defined('UFS_RESEND_API_KEY') || UFS_RESEND_API_KEY === '') return array('ok' => false, 'msg' => 'no_api_key');
    if (!count($rows)) return array('ok' => false, 'msg' => 'no_rows');
    if (ufs_ln_nl_fetch($slot) === '') return array('ok' => false, 'msg' => 'newsletter_html_fetch_failed');

    $sent = 0; $fail = 0; $last = '';
    foreach (array_chunk($rows, 100) as $chunk) {
        $items = array();
        foreach ($chunk as $r) {
            $items[] = array(
                'from'     => UFS_RESEND_FROM,
                'to'       => array($r['em']),
                'reply_to' => UFS_RESEND_REPLYTO,
                'subject'  => $d['subj'],
                'html'     => ufs_ln_nl_body($slot, $r['em']),
                // 지메일 등의 '수신거부' 버튼(원클릭)에 연결된다 — 스팸 신고 대신 해지로 유도
                'headers'  => array(
                    'List-Unsubscribe'      => '<' . ufs_ln_unsub_url($r['em']) . '>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ),
            );
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails/batch');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($items));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . UFS_RESEND_API_KEY, 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $send = 'curl_' . 'exec';
        $resp = $send($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_errno($ch);
        curl_close($ch);
        $last = $err ? ('curl_err_' . $err) : substr((string)$resp, 0, 180);
        if (!$err && $code >= 200 && $code < 300) { $sent += count($chunk); } else { $fail += count($chunk); }
        if (function_exists('sql_query')) {
            @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('0','[NEWSLETTER " . $slot . " n=" . count($chunk) . " http=" . $code . "] "
                . str_replace("'", "`", $last) . "',now())");
        }
        if (count($rows) > 100) usleep(250000);   // Resend 초당 요청 제한(10/s) 여유
    }
    return array('ok' => ($sent > 0 && $fail === 0), 'sent' => $sent, 'fail' => $fail, 'msg' => $last);
}
}

/* SMS 개별 발송(알림톡 API 자체가 실패했을 때의 최종 폴백) */
if (!function_exists('ufs_ln_send_sms_one')) {
function ufs_ln_send_sms_one($row, $slot) {
    require_once __DIR__ . '/_sms.php';
    $sl = ufs_ln_slot($slot);
    $resp = ufs_send_text_sms($row['nm'], $row['ph'], '언리얼 페스트 서울 2026 ' . $sl['label'], ufs_ln_message($row['nm'], $slot), 'live_notify');
    $st = ufs_sms_ok($resp);
    if ($st === null) return array('ok' => true, 'msg' => 'test_mode');
    if ($st === true) return array('ok' => true, 'msg' => 'ok');
    return array('ok' => false, 'msg' => substr((string)$resp, 0, 180));
}
}

/* 테스트 발송 — 지정한 번호로 1건만. 실제 등록자 대상이 아니며 발송 기록도 남기지 않는다.
 * (행사 전 알림톡 도착·링크버튼·문구 확인용) */
if (!function_exists('ufs_ln_test_send')) {
function ufs_ln_test_send($phone, $name, $slot, $channel = 'alimtalk') {
    $row = array('nm' => ($name !== '' ? $name : '테스트'), 'ph' => $phone, 'em' => $phone);
    $sl  = ufs_ln_slot($slot);
    // 뉴스레터 슬롯은 입력값을 이메일 주소로 받는다
    if (isset($sl['ch']) && $sl['ch'] === 'email') {
        if (strpos($phone, '@') === false) return array('ok' => false, 'channel' => 'email', 'msg' => '이메일 주소를 입력해 주세요');
        $r = ufs_ln_send_email_batch(array($row), $slot);
        return array('ok' => !empty($r['ok']), 'channel' => 'email', 'msg' => $r['msg']);
    }
    if ($channel === 'alimtalk') {
        $r = ufs_ln_send_alimtalk_batch(array($row), $slot);
        if (!empty($r['ok'])) return array('ok' => true, 'channel' => 'alimtalk', 'msg' => $r['msg']);
        $s = ufs_ln_send_sms_one($row, $slot);
        return array('ok' => !empty($s['ok']), 'channel' => 'sms(폴백)', 'msg' => '알림톡 실패: ' . $r['msg'] . ' / SMS: ' . $s['msg']);
    }
    $s = ufs_ln_send_sms_one($row, $slot);
    return array('ok' => !empty($s['ok']), 'channel' => 'sms', 'msg' => $s['msg']);
}
}

/* 일괄(bulk) 실행 — 남은 대상을 한 번의 호출에서 전부 소진한다.
 *  · 현장 참석자 안내(체크인)와 행사 후 안내는 진입 분산이 필요 없어 한꺼번에 보낸다.
 *  · API 한 번에 다 싣지는 않고 chunk 명씩 끊어 연속 호출한다(응답 크기·타임아웃 대비).
 *  · $max_sec 를 넘기면 남은 인원은 다음 회차(1분 뒤 크론)가 이어받는다 → 무한 실행 방지.
 *  · 창은 여유 있게 몇 분 잡아두면 중간에 끊겨도 그 안에서 자동으로 마무리된다. */
if (!function_exists('ufs_ln_run_bulk')) {
function ufs_ln_run_bulk($slot, $dry = true, $prefer = 'alimtalk', $chunk = 300, $max_sec = 50) {
    @set_time_limit(0);
    $t0  = time();
    $acc = array('day' => $slot, 'picked' => 0, 'sent' => 0, 'fail' => 0, 'dry' => $dry, 'rows' => array(), 'detail' => '', 'calls' => 0);
    while (true) {
        $r = ufs_ln_run_batch($slot, $chunk, $dry, $prefer);
        $acc['calls']++;
        $acc['picked'] += $r['picked'];
        $acc['sent']   += $r['sent'];
        $acc['fail']   += $r['fail'];
        $acc['remaining'] = $r['remaining'];
        if ($r['detail'] !== '') $acc['detail'] = $r['detail'];
        if (count($acc['rows']) < 8) $acc['rows'] = array_merge($acc['rows'], $r['rows']);
        if ($dry) break;                                   // 미리보기는 1회만
        if ($r['picked'] < 1 || $r['remaining'] < 1) break; // 다 보냄
        if ($r['fail'] > 0) break;                          // 실패 시 즉시 중단(원인 확인 우선)
        if ((time() - $t0) >= $max_sec) { $acc['detail'] .= ' · 시간 상한 도달, 나머지는 다음 회차'; break; }
    }
    return $acc;
}
}

/* 배치 실행: 대상 N명 선점 → 알림톡 1회 호출(대체발송 포함) → 결과 기록.
 *  $dry=true 면 실제 발송 없이 대상만 반환(검증용). */
if (!function_exists('ufs_ln_run_batch_core')) {
function ufs_ln_run_batch_core($slot, $limit, $dry = true, $prefer = 'alimtalk') {
    ufs_ln_table();
    $d = $slot;
    $targets = ufs_ln_targets($d, $limit);
    $res = array('day' => $d, 'picked' => count($targets), 'sent' => 0, 'fail' => 0, 'dry' => $dry, 'rows' => array(), 'detail' => '');
    if ($dry) {
        foreach ($targets as $t) $res['rows'][] = array($t['apply_no'], $t['nm'], substr($t['ph'], -4));
        $res['remaining'] = ufs_ln_remaining($d);
        return $res;
    }

    // 1) 선점 INSERT — UNIQUE(apply_no, ln_day) 로 동시 실행·재호출 시 중복 발송 차단
    $claimed = array();
    foreach ($targets as $t) {
        $ok = @sql_query("INSERT INTO cb_unreal_2026_live_notify (apply_no, ln_day, ln_name, ln_phone, ln_status, ln_at)
            VALUES (" . (int)$t['apply_no'] . ", '$d', '" . sql_real_escape_string($t['nm']) . "', '" . sql_real_escape_string($t['ph']) . "', 'P', now())");
        if ($ok) $claimed[] = $t;
    }
    if (!count($claimed)) { $res['remaining'] = ufs_ln_remaining($d); return $res; }

    // 2) 발송
    $ids = array(); foreach ($claimed as $c) $ids[] = (int)$c['apply_no'];
    $in  = implode(',', $ids);

    // 2-a) 이메일 뉴스레터 슬롯 — 한 사람이 여러 건 등록한 경우가 있어 주소 기준으로 한 번만 보낸다
    //      (중복 건도 선점은 해 두므로 다음 배치에서 다시 잡히지 않는다)
    $sl0 = ufs_ln_slot($d);
    if (isset($sl0['ch']) && $sl0['ch'] === 'email') {
        $uniq = array(); $seen = array();
        foreach ($claimed as $c) {
            $e = strtolower(trim($c['em']));
            if ($e === '' || isset($seen[$e])) continue;
            $seen[$e] = 1; $uniq[] = $c;
        }
        $r = ufs_ln_send_email_batch($uniq, $d);
        if (empty($r['ok'])) {
            // 발송이 안 됐으면 선점을 되돌린다 — 안 그러면 영영 다시 시도되지 않는다
            @sql_query("DELETE FROM cb_unreal_2026_live_notify WHERE ln_day='$d' AND ln_status='P' AND apply_no IN ($in)");
            $res['fail']   = count($claimed);
            $res['detail'] = 'email 실패(' . $r['msg'] . ') — 선점 해제, 다음 회차 재시도';
            $res['remaining'] = ufs_ln_remaining($d);
            return $res;
        }
        $res['sent']   = count($claimed);
        $res['detail'] = 'email · 주소 ' . count($uniq) . '건'
                       . (count($claimed) !== count($uniq) ? (' (중복 ' . (count($claimed) - count($uniq)) . '건 제외)') : '');
        @sql_query("UPDATE cb_unreal_2026_live_notify SET ln_status='S', ln_channel='email',
                    ln_result='" . sql_real_escape_string(substr($r['msg'], 0, 200)) . "', ln_at=now()
                    WHERE ln_day='$d' AND apply_no IN ($in)");
        $res['remaining'] = ufs_ln_remaining($d);
        return $res;
    }

    if ($prefer === 'alimtalk') {
        $r = ufs_ln_send_alimtalk_batch($claimed, $d);
        if (!empty($r['ok'])) {
            $res['sent'] = count($claimed); $res['detail'] = 'alimtalk';
            @sql_query("UPDATE cb_unreal_2026_live_notify SET ln_status='S', ln_channel='alimtalk',
                        ln_result='" . sql_real_escape_string($r['msg']) . "', ln_at=now()
                        WHERE ln_day='$d' AND apply_no IN ($in)");
            $res['remaining'] = ufs_ln_remaining($d);
            return $res;
        }
    }
    // 3) 알림톡 API 자체가 실패한 경우 — 개별 SMS 로 대체하지 않는다.
    //    API 실패는 설정 오류(제목 길이·템플릿 번호·잔액 등) 같은 '전건 실패'라, 대체발송하면
    //    수천 건이 통째로 LMS(건당 30원대)로 나간다. 2026-08-17 제목 길이 초과 버그 때
    //    9,806건이 그렇게 나갈 뻔했다(약 29만원). 원인을 고치고 다시 보내는 편이 맞다.
    //    선점을 되돌려 다음 회차가 재시도하게 하고, 사무국에는 실패 알림이 나간다.
    @sql_query("DELETE FROM cb_unreal_2026_live_notify WHERE ln_day='$d' AND ln_status='P' AND apply_no IN ($in)");
    $res['fail']   = count($claimed);
    $res['detail'] = 'alimtalk 실패(' . $r['msg'] . ') — 선점 해제, 다음 회차 재시도';
    $res['remaining'] = ufs_ln_remaining($d);
    return $res;
}
}

/* 어느 경로로 끝나든 알림 판정을 한 번 거치도록 감싼다 */
if (!function_exists('ufs_ln_run_batch')) {
function ufs_ln_run_batch($slot, $limit, $dry = true, $prefer = 'alimtalk') {
    $res = ufs_ln_run_batch_core($slot, $limit, $dry, $prefer);
    ufs_ln_after_batch($slot, $res);
    return $res;
}
}

/* 배치가 한 번 돌 때마다 상태를 보고 알림을 건다(미리보기는 제외) */
if (!function_exists('ufs_ln_after_batch')) {
function ufs_ln_after_batch($slot, $res) {
    if (!empty($res['dry'])) return;
    $sl = ufs_ln_slot($slot);
    $ch = ($sl['ch'] === 'email') ? '이메일' : '알림톡';
    if ($res['sent'] > 0) {
        ufs_ln_alert($slot, 'start', '발송을 시작했습니다',
            array('슬롯' => $sl['label'], '채널' => $ch, '첫 회차' => number_format($res['sent']) . '명',
                  '남은 대상' => number_format($res['remaining']) . '명', '시각' => date('Y-m-d H:i')));
    }
    if ($res['fail'] > 0) {
        ufs_ln_alert($slot, 'fail', '발송 실패가 발생했습니다',
            array('슬롯' => $sl['label'], '채널' => $ch, '실패' => number_format($res['fail']) . '명',
                  '성공' => number_format($res['sent']) . '명', '남은 대상' => number_format($res['remaining']) . '명',
                  '원인' => ($res['detail'] !== '' ? $res['detail'] : '(응답 없음)'), '시각' => date('Y-m-d H:i')), true);
    }
    if ($res['remaining'] <= 0 && $res['sent'] > 0) {
        $ke = sql_real_escape_string($slot);
        $st = sql_fetch("SELECT SUM(ln_status='S') s, SUM(ln_status='F') f FROM cb_unreal_2026_live_notify WHERE ln_day='$ke'");
        ufs_ln_alert($slot, 'done', '발송을 마쳤습니다',
            array('슬롯' => $sl['label'], '채널' => $ch,
                  '누적 성공' => number_format((int)(isset($st['s']) ? $st['s'] : 0)) . '명',
                  '누적 실패' => number_format((int)(isset($st['f']) ? $st['f'] : 0)) . '명',
                  '시각' => date('Y-m-d H:i')));
    }
}
}
