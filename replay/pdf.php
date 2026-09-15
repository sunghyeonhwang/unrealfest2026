<?php
/* Unreal Fest Seoul 2026 — 강연자료 PDF 게이트 (replay/pdf.php)
 *
 * 인증 전 HTML 에 Cloudflare PDF 주소를 노출하지 않기 위한 중계 엔드포인트(기획 11.5/12.3).
 * 서버에서 인증·공개 상태·URL 형식을 재확인한 뒤에만 리다이렉트하고, 이용 로그를 남긴다.
 * 공개 고정 URL 인 경우 보호 수준은 '인증 전 미노출'까지다(기획 11.5 명시).
 * PHP 7.0 호환.
 */
include_once "../../common.php";
require_once __DIR__ . '/_common_replay.php';

header('Cache-Control: private, no-store');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow');

function rv_pdf_fail($msg) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<meta name="robots" content="noindex,nofollow"><title>강연자료 — Unreal Fest Seoul 2026</title>'
       . '<style>body{margin:0;background:#08080a;color:#eaeaef;font-family:system-ui,-apple-system,"Apple SD Gothic Neo","Malgun Gothic",sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;box-sizing:border-box}'
       . '.b{max-width:420px;width:100%;background:#0e0e12;border:1px solid #1e1e25;padding:36px 32px;text-align:center}'
       . 'h1{font-size:18px;font-weight:800;margin:0 0 10px}p{font-size:13.5px;color:#8b8b96;line-height:1.8;margin:0}'
       . 'a{display:inline-block;margin-top:22px;color:#00C1D5;font-size:13px;font-weight:700;text-decoration:none}</style></head><body>'
       . '<div class="b"><h1>강연자료</h1><p>' . rv_e($msg) . '</p><a href="./">다시보기로 돌아가기 →</a></div></body></html>';
    exit;
}

$is_adm   = rv_is_adm();
$verified = (rv_auth_ok() || $is_adm);
$state    = rv_service_state();

/* 서버측 재검증(기획 12.1) — 인증, 서비스 기간, 세션·PDF 공개 여부 */
if (!$verified) rv_pdf_fail('다시보기 체크인 후 이용하실 수 있습니다.');
if ($state !== 'on' && !$is_adm) rv_pdf_fail('다시보기 제공 기간이 아닙니다.');

$agno = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
if ($agno <= 0) rv_pdf_fail('강연자료를 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.');

$row = @sql_fetch("SELECT v.rv_pdf_url, v.rv_pdf_public, v.rv_show, a.ag_is_active
    FROM cb_unreal_2026_replay_vod v
    JOIN cb_unreal_2026_agenda a ON a.ag_no = v.rv_agno
    WHERE v.rv_agno = $agno");

$apply_no = !empty($_SESSION['ufs_replay_no']) ? (int)$_SESSION['ufs_replay_no'] : 0;

if (!$row || $row['rv_show'] !== 'Y' || $row['ag_is_active'] !== 'Y'
    || $row['rv_pdf_public'] !== 'Y' || trim($row['rv_pdf_url']) === '') {
    rv_log('pdf_fail', $apply_no, $agno);
    rv_pdf_fail('발표자료가 제공되지 않는 세션입니다.');
}

$url = trim($row['rv_pdf_url']);

/* URL 검증 — HTTPS + (설정 시) 허용 도메인만(기획 11.4) */
$p = @parse_url($url);
$ok_scheme = ($p && isset($p['scheme']) && strtolower($p['scheme']) === 'https' && isset($p['host']));
$ok_domain = true;
$allow = trim(rv_cfg('replay_pdf_domain', ''));   // 쉼표 구분 복수 허용, 비우면 도메인 제한 없음
if ($ok_scheme && $allow !== '') {
    $ok_domain = false;
    $host = strtolower($p['host']);
    foreach (explode(',', $allow) as $d) {
        $d = strtolower(trim($d));
        if ($d !== '' && ($host === $d || substr($host, -strlen('.' . $d)) === '.' . $d)) { $ok_domain = true; break; }
    }
}
if (!$ok_scheme || !$ok_domain) {
    rv_log('pdf_fail', $apply_no, $agno);
    rv_pdf_fail('강연자료를 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.');
}

rv_log('pdf', $apply_no, $agno);
header('Location: ' . $url);
exit;
