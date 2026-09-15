<?php
/* Unreal Fest Seoul 2026 — 다시보기(VOD) 공용 헬퍼 (replay/_common_replay.php)
 *
 * index.php / pdf.php 가 공유한다. 기획: replay/REPLAY_PLANNING.md
 *  - 데이터: 기존 세션 cb_unreal_2026_agenda + 다시보기 전용 cb_unreal_2026_replay_vod
 *  - 설정:   cb_unreal_2026_config (replay_enabled / replay_start / replay_end / replay_notice / replay_pdf_domain)
 *  - 로그:   cb_unreal_2026_replay_log (체크인 성공·실패, 상세 열람, 재생, PDF)
 * 테이블 생성은 관리자(adm/2026_replay_vod.php)가 담당하고, 여기서는 로그 테이블만 지연 생성한다.
 * PHP 7.0 호환. charset=utf8 (실서버 제약).
 */

if (!function_exists('rv_e')) {
function rv_e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

/* 설정 읽기 — cb_unreal_2026_config (라이브/뉴스레터와 같은 키-값 테이블) */
if (!function_exists('rv_cfg')) {
function rv_cfg($k, $def = '') {
    $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='" . sql_real_escape_string($k) . "'");
    return ($r && $r['cfg_val'] !== '') ? $r['cfg_val'] : $def;
}
}

/* 접속 IP — Cloudflare 뒤에 있으므로 CF 헤더 우선 (newsletter_unsub.php 와 동일) */
if (!function_exists('rv_ip')) {
function rv_ip() {
    $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP']
        : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    return substr($ip, 0, 45);
}
}

/* 이메일 마스킹 — 실패 로그에 입력 전체를 남기지 않는다(기획 15.1) */
if (!function_exists('rv_mask_email')) {
function rv_mask_email($email) {
    $email = trim((string)$email);
    if ($email === '' || strpos($email, '@') === false) return substr($email, 0, 2) . '***';
    list($lp, $dm) = explode('@', $email, 2);
    return substr($lp, 0, 2) . str_repeat('*', max(1, strlen($lp) - 2)) . '@' . $dm;
}
}

/* 로그 테이블(지연 생성) — 체크인·콘텐츠 이용 기록 겸 체크인 속도제한 판정 소스 */
if (!function_exists('rv_log_table')) {
function rv_log_table() {
    static $done = false; if ($done) return; $done = true;
    @sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_replay_log (
        rl_no INT UNSIGNED NOT NULL AUTO_INCREMENT,
        rl_type VARCHAR(12) NOT NULL DEFAULT '',
        rl_apply_no INT NOT NULL DEFAULT 0,
        rl_agno INT NOT NULL DEFAULT 0,
        rl_email VARCHAR(150) NOT NULL DEFAULT '',
        rl_ip VARCHAR(45) NOT NULL DEFAULT '',
        rl_ua VARCHAR(180) NOT NULL DEFAULT '',
        rl_at DATETIME DEFAULT NULL,
        PRIMARY KEY (rl_no),
        KEY idx_type_at (rl_type, rl_at),
        KEY idx_ip (rl_ip, rl_type, rl_at),
        KEY idx_agno (rl_agno, rl_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
}
}

/* 로그 기록 — 타입: gate_ok / gate_fail / view / play / pdf / pdf_fail */
if (!function_exists('rv_log')) {
function rv_log($type, $apply_no = 0, $agno = 0, $email = '') {
    rv_log_table();
    $t  = sql_real_escape_string(substr($type, 0, 12));
    $em = sql_real_escape_string(substr($email, 0, 150));
    $ip = sql_real_escape_string(rv_ip());
    $ua = sql_real_escape_string(substr(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', 0, 180));
    @sql_query("INSERT INTO cb_unreal_2026_replay_log (rl_type, rl_apply_no, rl_agno, rl_email, rl_ip, rl_ua, rl_at)
                VALUES ('$t', " . (int)$apply_no . ", " . (int)$agno . ", '$em', '$ip', '$ua', NOW())");
}
}

/* 관리자 여부 — 공개측에서 기존 관리자 세션으로 게이트 우회(기획 4.4) */
if (!function_exists('rv_is_adm')) {
function rv_is_adm() {
    global $member, $config;
    return (isset($member['mb_id']) && $member['mb_id'] !== '' && (
        ((int)(isset($member['mb_level']) ? $member['mb_level'] : 0) >= 10) ||
        (isset($config['cf_admin']) && $member['mb_id'] === $config['cf_admin'])));
}
}

/* 다시보기 인증 상태 — 24시간 지나면 만료(기획 12.2) */
if (!function_exists('rv_auth_ok')) {
function rv_auth_ok() {
    if (empty($_SESSION['ufs_replay_ok'])) return false;
    $at = isset($_SESSION['ufs_replay_at']) ? (int)$_SESSION['ufs_replay_at'] : 0;
    if ($at <= 0 || (time() - $at) > 86400) {
        unset($_SESSION['ufs_replay_ok'], $_SESSION['ufs_replay_name'], $_SESSION['ufs_replay_no'], $_SESSION['ufs_replay_email'], $_SESSION['ufs_replay_at']);
        return false;
    }
    return true;
}
}

/* 서비스 제공 상태 — 'on'(운영) / 'off'(비활성) / 'before'(시작 전) / 'ended'(종료) */
if (!function_exists('rv_service_state')) {
function rv_service_state() {
    if (rv_cfg('replay_enabled', '0') !== '1') return 'off';
    $now = date('Y-m-d H:i');
    $s = rv_cfg('replay_start', ''); $e = rv_cfg('replay_end', '');
    if ($s !== '' && $now < $s) return 'before';
    if ($e !== '' && $now > $e) return 'ended';
    return 'on';
}
}

/* Vimeo 임베드 주소 — 비공개 해시(h=)가 있으면 붙인다. dnt=1(추적 최소화) */
if (!function_exists('rv_vimeo_embed')) {
function rv_vimeo_embed($id, $hash = '') {
    $id = preg_replace('/[^0-9]/', '', (string)$id);
    if ($id === '') return '';
    $u = 'https://player.vimeo.com/video/' . $id . '?dnt=1&title=0&byline=0&portrait=0&badge=0';
    if ($hash !== '' && preg_match('/^[a-f0-9]+$/i', $hash)) $u .= '&h=' . $hash;
    return $u;
}
}
