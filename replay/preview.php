<?php
/* Unreal Fest Seoul 2026 — 세션 미리보기 (replay/preview.php)
 *
 * 체크인 없이 특정 세션 영상을 확인하는 내부 공유용 단독 페이지.
 *  - 접근 = ?s=<세션ID>&k=<세션별 키>  (키 = substr(md5(SECRET . sid), 0, 16))
 *  - 허용 세션은 $ALLOW 화이트리스트로 제한 (필요 시 sid 추가)
 *  - 영상은 기존 Vimeo Embed only + epiclounge 도메인 제한을 그대로 사용
 * PHP 7.0 호환. noindex + no-store.
 */
include_once "../../common.php";
require_once __DIR__ . '/_common_replay.php';

header('Cache-Control: private, no-store');
header('X-Robots-Tag: noindex, nofollow');

$SECRET = '691878dfc21a1dcfa7540fe9';
$ALLOW  = array('d2-t1-s5', 'd2-t2-s3');   // 공유 허용 세션 (이환, 모범 사례 오해와 진실)

/* 자막 자동 표시 — replay/index.php 의 $RV_TEXTTRACK 와 동일 규칙 (현재 미사용) */
$TEXTTRACK = array();

$sid = isset($_GET['s']) ? preg_replace('/[^a-z0-9\-]/', '', $_GET['s']) : '';
$key = isset($_GET['k']) ? $_GET['k'] : '';
$ok  = ($sid !== '' && in_array($sid, $ALLOW, true)
        && hash_equals(substr(md5($SECRET . $sid), 0, 16), (string)$key));

$title = ''; $embed = '';
if ($ok) {
    $r = sql_fetch("SELECT a.ag_title, v.rv_vimeo, v.rv_vimeo_hash, v.rv_vimeo_qa, v.rv_vimeo_qa_hash, v.rv_video_public
        FROM cb_unreal_2026_replay_vod v
        JOIN cb_unreal_2026_agenda a ON a.ag_no = v.rv_agno
        WHERE a.ag_sid = '" . sql_real_escape_string($sid) . "'");
    // ?v=qa 면 오프라인용(QA 포함) 영상, 기본은 온라인용(QA 제외)
    $use_qa = (isset($_GET['v']) && $_GET['v'] === 'qa');
    $vid  = $use_qa ? $r['rv_vimeo_qa'] : $r['rv_vimeo'];
    $vh   = $use_qa ? $r['rv_vimeo_qa_hash'] : $r['rv_vimeo_hash'];
    if ($r && $r['rv_video_public'] === 'Y' && $vid !== '') {
        $title = $r['ag_title'] . ($use_qa ? ' (Q&A 포함)' : '');
        $embed = rv_vimeo_embed($vid, $vh);
        if (isset($TEXTTRACK[$vid])) $embed .= '&texttrack=' . $TEXTTRACK[$vid];
    }
}
if ($embed === '') { http_response_code(404); die('Not Found'); }
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= rv_e($title) ?> — Unreal Fest Seoul 2026</title>
<style>
body{margin:0;background:#08080a;color:#eaeaef;font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;word-break:keep-all}
.wrap{max-width:1080px;margin:0 auto;padding:32px 20px 60px}
h1{font-size:20px;font-weight:800;line-height:1.5;margin:0 0 18px}
.frame{position:relative;width:100%;padding-top:56.25%;background:#000;border:1px solid #1e1e25}
.frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.note{font-size:12px;color:#6b6b76;margin-top:16px;line-height:1.7}
</style></head>
<body>
<div class="wrap">
  <h1><?= rv_e($title) ?></h1>
  <div class="frame"><iframe src="<?= rv_e($embed) ?>" title="미리보기" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen referrerpolicy="strict-origin"></iframe></div>
  <p class="note">Unreal Fest Seoul 2026 세션 미리보기 — 내부 확인용 링크입니다. 외부 공유를 삼가 주세요.</p>
</div>
</body></html>
