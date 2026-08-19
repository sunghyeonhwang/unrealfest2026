<?php
/* Unreal Fest Seoul 2026 — 등록 마감 게이트 (_reg_gate.php)
 *
 * 2026-08-21 17:00 부터 신규 등록을 받지 않는다(유료·무료·온라인·단체·초청 전부).
 * 행사가 8/21 16:50 에 끝나므로 그 이후의 등록은 의미가 없고, 정산·명단 확정을 위해 닫는다.
 *
 * 두 겹으로 막는다.
 *   ① 폼 페이지  — 입력 화면 대신 마감 안내를 보여준다(ufs_reg_closed_page)
 *   ② 처리 엔드포인트 — 폼을 우회해 직접 POST 해도 서버에서 거절한다(ufs_reg_gate_or_die)
 *   버튼만 숨기면 우회가 가능하므로 ②가 실질적인 차단선이다.
 *
 * 마감 시각은 cb_unreal_2026_config[reg_close_at] 로 조정할 수 있다('Y-m-d H:i' 또는 빈값=기본).
 * 빈값이거나 형식이 틀리면 기본값(2026-08-21 17:00)을 쓴다 — 설정 실수로 등록이 조기 차단되지 않게.
 *
 * 프리뷰(ufs_is_preview)는 통과시킨다 — 마감 후에도 내부 점검이 가능해야 한다.
 * PHP 7.0 호환.
 */

if (!function_exists('ufs_reg_close_ts')) {
function ufs_reg_close_ts() {
    static $ts = null;
    if ($ts !== null) return $ts;
    $ts = strtotime('2026-08-21 17:00:00 +0900');
    if (function_exists('sql_fetch')) {
        $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='reg_close_at'");
        if ($r && trim($r['cfg_val']) !== '') {
            $t = strtotime(trim($r['cfg_val']) . ' +0900');
            if ($t !== false && $t > 0) $ts = $t;
        }
    }
    return $ts;
}
}

/* 관리자 즉시 마감 스위치 — 정원(현장 1,680명)에 도달하면 시각과 무관하게 바로 닫는다.
 * cb_unreal_2026_config[reg_closed_now] = '1' 이면 마감. 관리자 '온라인 라이브 설정'에서 켠다. */
if (!function_exists('ufs_reg_closed_manual')) {
function ufs_reg_closed_manual() {
    if (!function_exists('sql_fetch')) return false;
    $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='reg_closed_now'");
    return ($r && trim($r['cfg_val']) === '1');
}
}

/* 쿠폰 등록은 마감 후에도 열어 둔다.
 * 초청·스폰서 쿠폰 소지자는 이미 자리를 배정받은 사람이라 정원 마감과 성격이 다르다.
 * 판정: 전용 페이지(ticket-coupon*.php) 또는 그 폼이 보내는 coupon_flow=1. */
if (!function_exists('ufs_reg_is_coupon_flow')) {
function ufs_reg_is_coupon_flow() {
    if (isset($_POST['coupon_flow']) && $_POST['coupon_flow'] === '1') return true;
    if (isset($_GET['coupon_flow']) && $_GET['coupon_flow'] === '1') return true;
    $p = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';
    return (strpos($p, 'ticket-coupon') === 0);
}
}

if (!function_exists('ufs_reg_closed')) {
function ufs_reg_closed() {
    if (function_exists('ufs_is_preview') && ufs_is_preview()) return false;
    if (ufs_reg_is_coupon_flow()) return false;          // 쿠폰 등록은 항상 허용
    if (ufs_reg_closed_manual()) return true;            // 관리자가 즉시 마감
    return time() > ufs_reg_close_ts();
}
}

/* ── 현장(오프라인) 총 정원 ────────────────────────────────────────────────
 * 트랙별 좌석과 별개로 '사람 수' 상한이 필요하다. 양일권 1명이 Day1·Day2 좌석을 각각
 * 쓰기 때문에, 트랙 정원만으로는 총 인원을 정확히 묶을 수 없다.
 * 기본 1,690명. cb_unreal_2026_config[reg_max_offline] 로 조정한다(0 이면 상한 없음).
 *
 * ⚠️ 온라인 등록에는 적용하지 않는다 — 온라인은 좌석 개념이 없어 무제한이다.
 *    그래서 전역 마감(ufs_reg_closed)과 분리해 ufs_reg_closed_offline() 로만 쓴다. */
if (!function_exists('ufs_reg_max_offline')) {
function ufs_reg_max_offline() {
    static $n = null;
    if ($n !== null) return $n;
    $n = 1690;
    if (function_exists('sql_fetch')) {
        $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='reg_max_offline'");
        if ($r && trim($r['cfg_val']) !== '' && ctype_digit(trim($r['cfg_val']))) $n = (int)trim($r['cfg_val']);
    }
    return $n;
}
}

if (!function_exists('ufs_reg_offline_count')) {
function ufs_reg_offline_count() {
    static $c = null;
    if ($c !== null) return $c;
    $c = 0;
    if (function_exists('sql_fetch')) {
        $r = @sql_fetch("SELECT COUNT(*) c FROM cb_unreal_2026_event2_apply
                         WHERE apply_temp_yn='N' AND apply_pay_status<>0 AND apply_product_code<>'ONLINE'");
        if ($r) $c = (int)$r['c'];
    }
    return $c;
}
}

if (!function_exists('ufs_reg_offline_full')) {
function ufs_reg_offline_full() {
    if (function_exists('ufs_is_preview') && ufs_is_preview()) return false;
    if (ufs_reg_is_coupon_flow()) return false;          // 쿠폰은 정원과 무관
    $max = ufs_reg_max_offline();
    if ($max <= 0) return false;                          // 0 = 상한 없음
    return ufs_reg_offline_count() >= $max;
}
}

/* 현장 등록 경로에서 쓰는 마감 판정 = 전역 마감 또는 현장 정원 도달 */
if (!function_exists('ufs_reg_closed_offline')) {
function ufs_reg_closed_offline() {
    return ufs_reg_closed() || ufs_reg_offline_full();
}
}

/* 단체 등록 마감 — cb_unreal_2026_config[reg_group_closed]='1' 이면 닫는다.
 * 개인 등록과 따로 두는 이유: 단체는 견적·세금계산서 등 후속 절차가 있어
 * 마감 시점을 개인보다 앞당기는 경우가 많다.
 * ⚠️ 관리자 무인증 등록(ticket-group-admin.php)에는 적용하지 않는다 — 현장 워크인용. */
if (!function_exists('ufs_reg_group_closed')) {
function ufs_reg_group_closed() {
    if (function_exists('ufs_is_preview') && ufs_is_preview()) return false;
    if (function_exists('sql_fetch')) {
        $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='reg_group_closed'");
        if ($r && trim($r['cfg_val']) === '1') return true;
    }
    return ufs_reg_closed_offline();   // 전역·정원 마감이면 단체도 당연히 마감
}
}

/* 처리 엔드포인트용 — 마감이면 여기서 끊는다.
 * $json=true 면 AJAX 응답 형식으로 돌려준다(폼 페이지의 비동기 제출 대응). */
if (!function_exists('ufs_reg_gate_or_die')) {
function ufs_reg_gate_or_die($json = false, $offline = false) {
    // $offline=true 면 현장 정원(1,690명)도 함께 본다. 온라인 등록은 false 로 호출해야 한다.
    if (!($offline ? ufs_reg_closed_offline() : ufs_reg_closed())) return;
    $msg = (ufs_reg_closed_manual() || ufs_reg_offline_full())
        ? '등록이 마감되었습니다. (정원 마감)'
        : '등록이 마감되었습니다. (2026년 8월 21일 17:00 마감)';
    if ($json) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('result' => 'fail', 'ok' => 0, 'msg' => $msg, 'message' => $msg));
        exit;
    }
    if (!headers_sent()) header('Content-Type: text/html; charset=utf-8');
    echo '<script>alert(' . json_encode($msg, JSON_UNESCAPED_UNICODE) . ');location.href="/unrealfest2026/";</script>';
    exit;
}
}

/* 폼 페이지용 — 입력 화면 대신 보여줄 마감 안내. 출력 후 종료한다. */
if (!function_exists('ufs_reg_closed_page')) {
function ufs_reg_closed_page($title = '등록 마감') {
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
    }
    $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    // heredoc 안에서는 PHP 태그가 평가되지 않으므로 문구를 먼저 만들어 변수로 넣는다
    $reason = (ufs_reg_closed_manual() || ufs_reg_offline_full())
        ? '준비된 좌석이 모두 마감되어 신규 등록을 받지 않습니다.'
        : '2026년 8월 21일 17시부로 신규 등록을 받지 않습니다.';
    echo <<<HTML
<!doctype html>
<html lang="ko"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<title>{$t} · Unreal Fest Seoul 2026</title>
<style>
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
    background:radial-gradient(900px 500px at 78% -8%,rgba(0,193,213,.12),transparent 60%),#08080a;
    color:#eaeaef;font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;
    word-break:keep-all;box-sizing:border-box}
  .b{max-width:460px;text-align:center}
  .k{font-size:12px;font-weight:700;letter-spacing:.14em;color:#00FFC8;margin-bottom:14px}
  h1{margin:0 0 14px;font-size:23px;font-weight:800;line-height:1.4}
  p{margin:0;font-size:14.5px;line-height:1.85;color:#a9a9b8}
  a{display:inline-block;margin-top:28px;background:#00C1D5;color:#06060a;text-decoration:none;
    padding:13px 26px;border-radius:8px;font-weight:800;font-size:14.5px}
  .s{margin-top:22px;font-size:12.5px;color:#6e6e80;line-height:1.8}
</style></head><body>
  <div class="b">
    <div class="k">UNREAL FEST SEOUL 2026</div>
    <h1>등록이 마감되었습니다</h1>
    <p>{$reason}<br>관심 가져 주셔서 감사합니다.</p>
    <a href="/unrealfest2026/">행사 홈으로</a>
    <div class="s">이미 등록하신 분은 <a href="/unrealfest2026/myticket.php" style="background:none;color:#8a8a9c;padding:0;margin:0;font-weight:700;text-decoration:underline">내 티켓 확인</a>에서 조회하실 수 있습니다.<br>
      문의: <a href="mailto:info@epiclounge.co.kr" style="background:none;color:#8a8a9c;padding:0;margin:0;font-weight:700;text-decoration:underline">info@epiclounge.co.kr</a></div>
  </div>
</body></html>
HTML;
    exit;
}
}
