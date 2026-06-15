<?php
/* Unreal Fest Seoul 2026 — 아젠다/스케줄 DB 셋업 (1회용, 실행 후 삭제)
 * cb_unreal_2026_agenda 생성. 공개 index_s.php/schedule_s.php/session_s.php 와
 * 관리자 2026_agenda_*.php 가 공유한다.
 * 실행: /v3/unrealfest2026/setup_agenda_db.php?key=ufs2026agenda
 * PHP 7.0 호환. charset=utf8 (실서버 제약).
 */
include_once "../common.php"; // sql_query (Gnuboard DB)

$key = isset($_GET['key']) ? $_GET['key'] : '';
if ($key !== 'ufs2026agenda') { http_response_code(403); exit('forbidden'); }

header('Content-Type: text/plain; charset=utf-8');

$sql = "CREATE TABLE IF NOT EXISTS cb_unreal_2026_agenda (
  ag_no INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ag_sid VARCHAR(40) NOT NULL DEFAULT '',
  ag_day TINYINT NOT NULL DEFAULT 1,
  ag_slot_type VARCHAR(20) NOT NULL DEFAULT 'session',
  ag_track VARCHAR(60) NOT NULL DEFAULT '',
  ag_time VARCHAR(40) NOT NULL DEFAULT '',
  ag_time_start VARCHAR(8) NOT NULL DEFAULT '',
  ag_time_end VARCHAR(8) NOT NULL DEFAULT '',
  ag_title VARCHAR(300) NOT NULL DEFAULT '',
  ag_sp_name VARCHAR(200) NOT NULL DEFAULT '',
  ag_sp_role VARCHAR(150) NOT NULL DEFAULT '',
  ag_sp_company VARCHAR(200) NOT NULL DEFAULT '',
  ag_level VARCHAR(40) NOT NULL DEFAULT '전체 참가자',
  ag_location VARCHAR(120) NOT NULL DEFAULT '',
  ag_desc TEXT,
  ag_contents TEXT,
  ag_target VARCHAR(255) NOT NULL DEFAULT '',
  ag_sort INT NOT NULL DEFAULT 0,
  ag_is_active CHAR(1) NOT NULL DEFAULT 'Y',
  ag_reg_dt DATETIME NULL DEFAULT NULL,
  ag_upd_dt DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (ag_no),
  UNIQUE KEY uniq_sid (ag_sid),
  KEY idx_day_sort (ag_day, ag_sort),
  KEY idx_slot (ag_slot_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

sql_query($sql);

$chk = sql_query("SHOW TABLES LIKE 'cb_unreal_2026_agenda'");
$exists = ($chk && $chk->num_rows > 0);
echo $exists ? "OK: cb_unreal_2026_agenda 생성됨\n" : "FAIL\n";

if ($exists) {
    $cols = sql_query("SHOW COLUMNS FROM cb_unreal_2026_agenda");
    if ($cols) {
        $n = 0;
        while ($r = $cols->fetch_assoc()) { $n++; }
        echo "컬럼 수: $n\n";
    }
    $cnt = sql_fetch("SELECT COUNT(*) c FROM cb_unreal_2026_agenda");
    echo "현재 행 수: " . ($cnt ? (int)$cnt['c'] : 0) . "\n";
}
echo "\n다음 단계: 관리자 > 스케줄 관리(CSV)에서 평면 CSV를 업로드하세요.\n";
echo "이 파일은 실행 후 삭제하세요.\n";
