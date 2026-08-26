<?php
/* Unreal Fest Seoul 2026 — 오프라인 참석자 설문 안내 LMS (_svsms.php)
 *
 * 2026-08-26 중복발송 사고 이후 다시 쓴 버전. 일회성 대량 발송의 표준 형태로 남긴다.
 *
 * ── 사고 경위 ────────────────────────────────────────────────────────────
 * 초판은 대상 전원을 GET 한 번으로 처리했다. 1,667명 발송에 53초가 걸렸는데
 * 라이브 재시도 Worker 의 원본 대기 상한이 8초라, Worker 가 같은 GET 을 두 번 더
 * 던져 원본에서 3개 프로세스가 동시에 돌았다. 선점을 ON DUPLICATE KEY UPDATE 로
 * 해 둔 탓에 뒤늦게 시작한 프로세스도 "아직 Y 가 아니다"라며 그대로 다시 보냈다.
 * 결과: 1인 평균 2.6통, 약 4,300통 발송(회수 불가).
 *
 * ── 세 겹으로 막는다 ────────────────────────────────────────────────────
 *  ① Worker NO_RETRY   — 이 경로는 애초에 재시도하지 않는다(live-retry-worker)
 *  ② GET_LOCK          — 그래도 동시 호출되면 두 번째 프로세스가 즉시 빠진다
 *  ③ 실행토큰 선점      — UPDATE ... WHERE sv_status='N' 로 행을 원자적으로 집는다.
 *                        내가 집은 행(sv_run=내토큰)만 보내므로, 락이 뚫려도 겹치지 않는다.
 * ②만으로도 충분해 보이지만, 락은 커넥션이 끊기면 풀린다. ③이 최종 방어선이다.
 *
 * ── 한 번에 다 보내지 않는다 ────────────────────────────────────────────
 * 호출당 MAX_PER_CALL 명만 처리하고 남은 수를 돌려준다. 8초 안에 끝나므로
 * 어떤 프록시·게이트웨이 타임아웃에도 걸리지 않는다. 호출부가 remaining>0 인 동안 반복한다.
 *
 * 호출: ?k=KEY&mode=seed        대상 적재(N 상태로) — 최초 1회
 *       ?k=KEY&mode=stat        현황
 *       ?k=KEY&mode=test&to=번호&v=all|d1|d2
 *       ?k=KEY&mode=live&go=YES 실발송 1회분(기본 300명). &n= 으로 조절
 * PHP 7.0 호환.
 */
if (($_GET['k'] ?? '') !== 'ufsdiag2026x') { http_response_code(403); exit('no'); }
include_once "../common.php";
require_once __DIR__ . '/_sms.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
@set_time_limit(60);

$TBL = 'cb_unreal_2026_survey_sms';
$MAX_PER_CALL = 300;          // 6회 호출 ≈ 3초. 8초 상한에 여유를 둔다
$CHUNK        = 50;           // DirectSend 1회 호출당 수신자 수

sql_query("CREATE TABLE IF NOT EXISTS $TBL (
  sv_phone VARCHAR(30) NOT NULL, sv_name VARCHAR(60) NOT NULL DEFAULT '',
  sv_variant CHAR(3) NOT NULL DEFAULT '', sv_status CHAR(1) NOT NULL DEFAULT 'N',
  sv_run VARCHAR(16) NOT NULL DEFAULT '', sv_at DATETIME DEFAULT NULL,
  sv_resp VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (sv_phone), KEY idx_pick (sv_status, sv_variant)) DEFAULT CHARSET=utf8");
if (!sql_fetch("SHOW COLUMNS FROM $TBL LIKE 'sv_run'"))
    sql_query("ALTER TABLE $TBL ADD COLUMN sv_run VARCHAR(16) NOT NULL DEFAULT ''");

$D1 = 'https://forms.gle/PWZpQgXZc7kWJghs5';
$D2 = 'https://forms.gle/ERjnH3axVz19Qaws7';
/* ⚠️ 제목은 짧게. 긴 제목은 DirectSend 가 status 105(제목 길이 초과)로 거부한다.
 *    본문 첫 줄에 전체 문구를 넣어 수신자에게는 그대로 보이게 한다. */
$TITLE      = '언리얼 페스트 서울 2026';
$TITLE_FULL = '[언리얼 페스트 서울 2026] 설문조사 참여 안내';

function sv_msg($v) {
    global $D1, $D2, $TITLE_FULL;
    $links = ($v === 'all') ? "8월20일 $D1\n8월21일 $D2"
           : (($v === 'd1') ? "8월20일 $D1" : "8월21일 $D2");
    return "$TITLE_FULL\n\n"
         . "언리얼 페스트 서울 2026에 함께해 주셔서 감사합니다!\n"
         . "더 나은 행사를 위해 여러분의 소중한 의견을 들려주세요. 설문에 참여해 주신 분 중 추첨을 통해 '네이버페이 포인트 1만 원권'을 드립니다.\n\n"
         . "▶ 설문 참여하기\n$links\n\n"
         . "▶ 참여 기간: ~ 8월 31일(월)\n\n"
         . "*당첨자에게는 9월 7일(월) 개별 안내드릴 예정입니다.";
}

/* 대상 = 오프라인 유효 등록자, 연락처 기준 합침.
 * 한 번호가 여러 티켓을 가지면 상품 합집합으로 링크를 정한다 —
 * 양일권이 있거나 20·21일권을 둘 다 가지면 양일 링크(그대로 두면 한 사람에게 여러 통이 간다). */
function sv_targets() {
    $out = array();
    $q = sql_query("SELECT apply_user_phone ph, MIN(apply_user_name) nm,
                           GROUP_CONCAT(DISTINCT apply_product_code) pcs
                    FROM cb_unreal_2026_event2_apply
                    WHERE apply_temp_yn='N' AND apply_pay_status<>0
                      AND apply_product_code<>'ONLINE' AND apply_user_phone<>''
                    GROUP BY apply_user_phone");
    while ($x = $q->fetch_assoc()) {
        $p = ufs_normalize_phone($x['ph']);
        if ($p === '' || strlen($p) < 10) continue;      // 국제번호 등 발송 불가
        $s  = ',' . $x['pcs'] . ',';
        $a  = strpos($s, ',NORMAL_ALL,') !== false;
        $h1 = strpos($s, ',NORMAL_20,')  !== false;
        $h2 = strpos($s, ',NORMAL_21,')  !== false;
        $out[] = array('ph' => $p, 'nm' => $x['nm'],
                       'v'  => ($a || ($h1 && $h2)) ? 'all' : ($h1 ? 'd1' : 'd2'));
    }
    return $out;
}

function sv_send_chunk($pairs, $v) {
    global $TITLE;
    $rcv = array();
    foreach ($pairs as $p)
        $rcv[] = '{"name":"' . ufs_sms_json_escape($p[1]) . '","mobile":"' . $p[0] . '"}';
    $post = '{"title":"' . ufs_sms_json_escape($TITLE) . '"'
          . ',"message":"' . ufs_sms_json_escape(sv_msg($v)) . '"'
          . ',"sender":"' . UFS_SMS_SENDER . '"'
          . ',"username":"' . UFS_SMS_USERNAME . '"'
          . ',"receiver":[' . implode(',', $rcv) . ']'
          . ',"key":"' . UFS_SMS_KEY . '"}';
    return ufs_directsend_post($post, 'survey');
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'stat';

if ($mode === 'seed') {
    $n = 0;
    foreach (sv_targets() as $t) {
        // 이미 있는 행은 건드리지 않는다(발송 이력 보존)
        if (sql_query("INSERT IGNORE INTO $TBL (sv_phone, sv_name, sv_variant, sv_status)
                       VALUES ('" . sql_real_escape_string($t['ph']) . "','" . sql_real_escape_string($t['nm']) . "','" . $t['v'] . "','N')")) $n++;
    }
    $r = sql_fetch("SELECT COUNT(*) c FROM $TBL");
    printf("적재 완료 — 테이블 %d행\n", (int)$r['c']);
    exit;
}

if ($mode === 'stat') {
    $r = sql_fetch("SELECT COUNT(*) t, SUM(sv_status='Y') y, SUM(sv_status='P') p,
                           SUM(sv_status='F') f, SUM(sv_status='N') n FROM $TBL");
    printf("대상 %d명 — 완료 %d · 선점중 %d · 실패 %d · 미발송 %d\n",
        (int)$r['t'], (int)$r['y'], (int)$r['p'], (int)$r['f'], (int)$r['n']);
    $v = sql_query("SELECT sv_variant, sv_status, COUNT(*) c FROM $TBL GROUP BY sv_variant, sv_status ORDER BY sv_variant");
    while ($z = $v->fetch_assoc()) printf("  %-4s %s %4d건\n", $z['sv_variant'], $z['sv_status'], $z['c']);
    $x = sql_fetch("SELECT MIN(sv_at) a, MAX(sv_at) b FROM $TBL WHERE sv_status='Y'");
    if ($x['a']) printf("\n발송 구간 %s ~ %s\n", $x['a'], $x['b']);
    foreach (array('all', 'd1', 'd2') as $vv)
        printf("\n[%s] %d바이트(EUC-KR)\n%s\n", $vv, strlen(@iconv('UTF-8', 'EUC-KR//IGNORE', sv_msg($vv))), sv_msg($vv));
    exit;
}

if ($mode === 'test') {
    $to = ufs_normalize_phone(isset($_GET['to']) ? $_GET['to'] : '');
    $v  = isset($_GET['v']) ? $_GET['v'] : 'all';
    if ($to === '') exit("to 필요\n");
    $resp = ufs_send_text_sms('테스트', $to, $TITLE, sv_msg($v), 'survey-test');
    printf("대상 %s · 유형 %s\n결과 %s\n응답 %s\n", $to, $v,
        (ufs_sms_ok($resp) === true ? '성공' : (ufs_sms_ok($resp) === null ? '테스트모드' : '실패')),
        substr(trim((string)$resp), 0, 300));
    exit;
}

if ($mode === 'live') {
    if ((isset($_GET['go']) ? $_GET['go'] : '') !== 'YES') exit("실발송은 &go=YES 필요\n");

    // ② 단일 실행 보장. 대기 0초 — 겹치면 두 번째는 아무것도 하지 않고 나간다.
    $lk = sql_fetch("SELECT GET_LOCK('ufs_svsms_live', 0) g");
    if (!$lk || (int)$lk['g'] !== 1) exit("다른 발송이 진행 중입니다 — 중복 실행을 차단했습니다.\n");

    $want = isset($_GET['n']) ? max(1, min($MAX_PER_CALL, (int)$_GET['n'])) : $MAX_PER_CALL;
    $run  = substr(md5(uniqid('', true)), 0, 16);
    $sent = 0; $fail = 0; $calls = 0;

    foreach (array('all', 'd1', 'd2') as $v) {
        if ($want - $sent - $fail <= 0) break;
        $take = $want - $sent - $fail;
        // ③ 원자적 선점 — 이 UPDATE 로 집힌 행만 내 것이다
        sql_query("UPDATE $TBL SET sv_status='P', sv_run='$run', sv_at=NOW()
                   WHERE sv_status='N' AND sv_variant='$v' ORDER BY sv_phone LIMIT $take");
        $mine = array();
        $q = sql_query("SELECT sv_phone, sv_name FROM $TBL WHERE sv_run='$run' AND sv_status='P' AND sv_variant='$v'");
        while ($z = $q->fetch_assoc()) $mine[] = array($z['sv_phone'], $z['sv_name']);
        if (!$mine) continue;

        foreach (array_chunk($mine, $CHUNK) as $chunk) {
            $phs = array();
            foreach ($chunk as $c) $phs[] = "'" . sql_real_escape_string($c[0]) . "'";
            $in   = implode(',', $phs);
            $resp = sv_send_chunk($chunk, $v); $calls++;
            $rs   = sql_real_escape_string(substr(trim((string)$resp), 0, 250));
            if (ufs_sms_ok($resp) === true) {
                sql_query("UPDATE $TBL SET sv_status='Y', sv_at=NOW(), sv_resp='$rs' WHERE sv_phone IN ($in)");
                $sent += count($chunk);
            } else {
                // 실패한 청크만 되돌린다 — 성공한 청크까지 풀면 그들이 다시 받는다
                sql_query("UPDATE $TBL SET sv_status='N', sv_run='', sv_resp='$rs' WHERE sv_phone IN ($in)");
                $fail += count($chunk);
                printf("  [%s] 청크 실패 — %s\n", $v, substr((string)$resp, 0, 120));
                break 2;
            }
            usleep(300000);
        }
    }
    $r = sql_fetch("SELECT SUM(sv_status='N') n FROM $TBL");
    sql_query("SELECT RELEASE_LOCK('ufs_svsms_live')");
    printf("이번 호출 — 성공 %d · 실패 %d · API %d회 · 남은 대상 %d명\n", $sent, $fail, $calls, (int)$r['n']);
    exit;
}
echo "mode=seed|stat|test|live\n";
