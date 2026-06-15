<?php
/* Unreal Fest Seoul 2026 — ag_speakers(복수 연사 JSON) 컬럼 추가 (1회용, 실행 후 삭제)
 * 실행: /v3/unrealfest2026/setup_agenda_speakers.php?key=ufs2026spk
 * PHP 7.0 호환.
 */
include_once "../common.php";

$key = isset($_GET['key']) ? $_GET['key'] : '';
if ($key !== 'ufs2026spk') { http_response_code(403); exit('forbidden'); }

header('Content-Type: text/plain; charset=utf-8');

$chk = sql_query("SHOW TABLES LIKE 'cb_unreal_2026_agenda'");
if (!$chk || $chk->num_rows === 0) { exit("테이블이 없습니다.\n"); }

$col = sql_query("SHOW COLUMNS FROM cb_unreal_2026_agenda LIKE 'ag_speakers'");
if (!$col || $col->num_rows === 0) {
    sql_query("ALTER TABLE cb_unreal_2026_agenda ADD COLUMN ag_speakers TEXT NULL AFTER ag_sp_photo");
    echo "ag_speakers 컬럼 추가됨\n";
} else {
    echo "ag_speakers 컬럼 이미 존재\n";
}
echo "\n이 파일은 실행 후 삭제하세요.\n";
