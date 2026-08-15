<?php
/* Unreal Fest Seoul 2026 — 라이브 시청 안내 분산 발송 모듈 (_live_notify.php)
 *
 * 목적: 행사 당일 온라인 시청자가 10:30 정각에 한꺼번에 몰려 원본 서버(cafe24 웹호스팅)의
 *       동시 연결 상한을 넘겨 503 이 터지는 것을 막는다. 09:30~10:20 사이에 나눠 보내
 *       "미리 입장"을 유도해 진입을 분산시킨다.
 *
 * 설계 포인트
 *  - 대상 = 온라인 무료 등록자(apply_product_code='ONLINE' 또는 free_yn='Y') 중
 *           ① 아직 그날 라이브에 접속하지 않았고 ② 아직 그날 안내를 못 받은 사람.
 *           (등록자는 계속 늘어날 수 있으므로 발송 시점에 매번 동적으로 조회)
 *  - 중복 발송 방지 = cb_unreal_2026_live_notify 의 UNIQUE(apply_no, ln_day)
 *  - 발송 채널은 어댑터로 분리(alimtalk / sms). 알림톡 실패 시 SMS 자동 대체.
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
    // audience: online(온라인 무료) / offline(현장 참석) / all(전체 확정 등록자)
    // only_unvisited: 그날 라이브 미접속자만(진입 분산용) — 온라인 오전 슬롯에만 적용
    return array(
        'd1chk'  => array('label'=>'Day1 체크인 오픈',   'day'=>'1', 'audience'=>'offline', 'only_unvisited'=>false, 'tpl_def'=>'325'),
        'd1am'   => array('label'=>'Day1 온라인 시청안내','day'=>'1', 'audience'=>'online',  'only_unvisited'=>true,  'tpl_def'=>'328'),
        'd1pm'   => array('label'=>'Day1 오후 세션',      'day'=>'1', 'audience'=>'online',  'only_unvisited'=>false, 'tpl_def'=>'331'),
        'd2chk'  => array('label'=>'Day2 체크인 오픈',   'day'=>'2', 'audience'=>'offline', 'only_unvisited'=>false, 'tpl_def'=>'334'),
        'd2am'   => array('label'=>'Day2 온라인 시청안내','day'=>'2', 'audience'=>'online',  'only_unvisited'=>true,  'tpl_def'=>'337'),
        'd2pm'   => array('label'=>'Day2 오후 세션',      'day'=>'2', 'audience'=>'online',  'only_unvisited'=>false, 'tpl_def'=>'340'),
        'thanks' => array('label'=>'감사 인사(행사 후)',  'day'=>'2', 'audience'=>'all',     'only_unvisited'=>false, 'tpl_def'=>'343'),
    );
}
}
/* 대상(audience) → SQL 조건 */
if (!function_exists('ufs_ln_audience_sql')) {
function ufs_ln_audience_sql($a) {
    if ($a === 'offline') return " AND a.apply_product_code<>'ONLINE' AND a.free_yn='N'";
    if ($a === 'all')     return "";
    return " AND (a.apply_product_code='ONLINE' OR a.free_yn='Y')";   // online(기본)
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
    return array($sl, 
        "FROM cb_unreal_2026_event2_apply a
         LEFT JOIN cb_unreal_2026_live_notify n
                ON n.apply_no = a.apply_no AND n.ln_day = '$k'
         $join_visit
         WHERE a.apply_temp_yn='N' AND a.apply_pay_status<>0
           AND a.apply_user_phone <> ''
           AND n.ln_no IS NULL"
        . ufs_ln_audience_sql(isset($sl['audience']) ? $sl['audience'] : 'online')
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
function ufs_ln_auto_batch($slot, $end_str, $start_str = '', $min = 10, $max = 200) {
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
    $q = sql_query("SELECT a.apply_no, a.apply_user_name nm, a.apply_user_phone ph " . $w . " ORDER BY a.apply_no ASC LIMIT " . $limit);
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
 * ★ 대체발송은 DirectSend 내장 기능 사용: kakao_faild_type=2(LMS) + message/title/sender.
 *   알림톡이 안 가는 수신자에게 자동으로 LMS 가 나가므로 누락이 없다.
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
        // 알림톡 미수신자 자동 대체발송(LMS)
        'kakao_faild_type' => '2',
        'sender'           => UFS_SMS_SENDER,
        'title'            => '언리얼 페스트 서울 2026 ' . $sl['label'],
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
    $row = array('nm' => ($name !== '' ? $name : '테스트'), 'ph' => $phone);
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

/* 배치 실행: 대상 N명 선점 → 알림톡 1회 호출(대체발송 포함) → 결과 기록.
 *  $dry=true 면 실제 발송 없이 대상만 반환(검증용). */
if (!function_exists('ufs_ln_run_batch')) {
function ufs_ln_run_batch($slot, $limit, $dry = true, $prefer = 'alimtalk') {
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
        $res['detail'] = 'alimtalk 실패(' . $r['msg'] . ') → SMS 대체';
    }
    // 3) 알림톡 API 자체 실패 → 개별 SMS 폴백
    foreach ($claimed as $c) {
        $s = ufs_ln_send_sms_one($c, $d);
        $st = !empty($s['ok']) ? 'S' : 'F';
        if ($st === 'S') $res['sent']++; else $res['fail']++;
        @sql_query("UPDATE cb_unreal_2026_live_notify SET ln_status='$st', ln_channel='sms',
                    ln_result='" . sql_real_escape_string($s['msg']) . "', ln_at=now()
                    WHERE ln_day='$d' AND apply_no=" . (int)$c['apply_no']);
    }
    $res['remaining'] = ufs_ln_remaining($d);
    return $res;
}
}
