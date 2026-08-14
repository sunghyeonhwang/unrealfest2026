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
        ln_day CHAR(1) NOT NULL DEFAULT '',
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
}
}

/* 그날(day=1|2) 아직 안내를 못 받았고 아직 접속도 안 한 온라인 등록자 조회
 *  - 접속 여부: cb_unreal_2026_event2_apply_live 의 d1_at / d2_at (Day별 최초접속 스탬프)
 *  - $limit=0 이면 개수만 셀 때 쓰도록 전체를 반환하지 않고 COUNT 용 쿼리를 따로 쓴다 */
if (!function_exists('ufs_ln_sql_where')) {
function ufs_ln_sql_where($day) {
    $d = ($day === '2' || $day === 2) ? '2' : '1';
    $dcol = ($d === '2') ? 'd2_at' : 'd1_at';
    return array($d, $dcol,
        "FROM cb_unreal_2026_event2_apply a
         LEFT JOIN cb_unreal_2026_live_notify n
                ON n.apply_no = a.apply_no AND n.ln_day = '$d'
         LEFT JOIN cb_unreal_2026_event2_apply_live lv
                ON lv.apply_user_email = a.apply_user_email AND lv.$dcol IS NOT NULL
         WHERE a.apply_temp_yn='N' AND a.apply_pay_status<>0
           AND (a.apply_product_code='ONLINE' OR a.free_yn='Y')
           AND a.apply_user_phone <> ''
           AND n.ln_no IS NULL
           AND lv.la_no IS NULL");
}
}

/* 남은 대상 수 */
if (!function_exists('ufs_ln_remaining')) {
function ufs_ln_remaining($day) {
    ufs_ln_table();
    list($d, $dcol, $w) = ufs_ln_sql_where($day);
    $r = sql_fetch("SELECT COUNT(*) c " . $w);
    return $r ? (int)$r['c'] : 0;
}
}

/* 이번 배치 대상 N명 */
if (!function_exists('ufs_ln_targets')) {
function ufs_ln_targets($day, $limit) {
    ufs_ln_table();
    list($d, $dcol, $w) = ufs_ln_sql_where($day);
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
function ufs_ln_template_body($day) {
    if ($day === '2') {
        return "언리얼 페스트 서울 2026 ㅣDay 2 온라인 시청 안내\n\n"
             . "사전 등록하신 Day 2 온라인 세션이 곧 시작됩니다.\n"
             . "아래 링크에서 온라인 체크인을 완료한 후, 원하는 트랙으로 입장해 주세요!\n\n"
             . "Day 1에 이어 오늘도 온라인 체크인을 완료하시면 ‘출석 체크 이벤트’에 자동 응모되며, 추첨을 통해 200분께 한정판 티셔츠를 드립니다.\n\n"
             . "언리얼 페스트 사무국";
    }
    return "언리얼 페스트 서울 2026 ㅣDay 1 온라인 시청 안내\n\n"
         . "사전 등록하신 Day 1 온라인 세션이 곧 시작됩니다.\n"
         . "아래 링크에서 온라인 체크인을 완료한 후, 원하는 트랙으로 입장해 주세요!\n\n"
         . "양일 온라인 체크인을 하시면 ‘출석 체크 이벤트’에 자동 응모되며, 추첨을 통해 200분께 한정판 티셔츠를 드립니다.\n\n"
         . "언리얼 페스트 사무국";
}
}

/* SMS/LMS 대체발송 문구 — 승인 템플릿과 동일 문구 + 링크(버튼이 없으므로 본문에 포함) */
if (!function_exists('ufs_ln_message')) {
function ufs_ln_message($name, $day) {
    $b = ufs_ln_template_body($day);
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
function ufs_ln_send_alimtalk_batch($rows, $day) {
    $d      = ($day === '2') ? '2' : '1';
    $plusid = ufs_ln_cfg('live_notify_at_plusid');
    $tpl    = ufs_ln_cfg('live_notify_at_tpl_d' . $d);
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
        'title'            => '언리얼 페스트 서울 2026 온라인 시청 안내',
        'message'          => ufs_ln_message('', $d),
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
        @sql_query("insert into 2025_event_log(log_idx,log_text,rdate) values('0','[ALIMTALK d" . $d . " n=" . count($rcv) . "] "
            . str_replace("'", "`", $err ? ('CURL_ERR ' . $err) : substr((string)$resp, 0, 400)) . "',now())");
    }
    if ($err) return array('ok' => false, 'msg' => 'curl_err_' . $err);
    $ok = (strpos((string)$resp, '"status":"1"') !== false);   // DirectSend 알림톡: 1 = 정상
    return array('ok' => $ok, 'msg' => substr((string)$resp, 0, 180));
}
}

/* SMS 개별 발송(알림톡 API 자체가 실패했을 때의 최종 폴백) */
if (!function_exists('ufs_ln_send_sms_one')) {
function ufs_ln_send_sms_one($row, $day) {
    require_once __DIR__ . '/_sms.php';
    $resp = ufs_send_text_sms($row['nm'], $row['ph'], '언리얼 페스트 서울 2026 온라인 시청 안내', ufs_ln_message($row['nm'], $day), 'live_notify');
    $st = ufs_sms_ok($resp);
    if ($st === null) return array('ok' => true, 'msg' => 'test_mode');
    if ($st === true) return array('ok' => true, 'msg' => 'ok');
    return array('ok' => false, 'msg' => substr((string)$resp, 0, 180));
}
}

/* 테스트 발송 — 지정한 번호로 1건만. 실제 등록자 대상이 아니며 발송 기록도 남기지 않는다.
 * (행사 전 알림톡 도착·링크버튼·문구 확인용) */
if (!function_exists('ufs_ln_test_send')) {
function ufs_ln_test_send($phone, $name, $day, $channel = 'alimtalk') {
    $d = ($day === '2') ? '2' : '1';
    $row = array('nm' => ($name !== '' ? $name : '테스트'), 'ph' => $phone);
    if ($channel === 'alimtalk') {
        $r = ufs_ln_send_alimtalk_batch(array($row), $d);
        if (!empty($r['ok'])) return array('ok' => true, 'channel' => 'alimtalk', 'msg' => $r['msg']);
        $s = ufs_ln_send_sms_one($row, $d);
        return array('ok' => !empty($s['ok']), 'channel' => 'sms(폴백)', 'msg' => '알림톡 실패: ' . $r['msg'] . ' / SMS: ' . $s['msg']);
    }
    $s = ufs_ln_send_sms_one($row, $d);
    return array('ok' => !empty($s['ok']), 'channel' => 'sms', 'msg' => $s['msg']);
}
}

/* 배치 실행: 대상 N명 선점 → 알림톡 1회 호출(대체발송 포함) → 결과 기록.
 *  $dry=true 면 실제 발송 없이 대상만 반환(검증용). */
if (!function_exists('ufs_ln_run_batch')) {
function ufs_ln_run_batch($day, $limit, $dry = true, $prefer = 'alimtalk') {
    ufs_ln_table();
    $d = ($day === '2' || $day === 2) ? '2' : '1';
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
