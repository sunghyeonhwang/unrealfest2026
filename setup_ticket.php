<?php
/* 2026_event_ticket 정원 테이블 생성 + 8트랙 시드 (1회용, 실행 후 삭제)
 * 실행: /v3/unrealfest2026/setup_ticket.php?key=ufs2026setup
 */
include_once "../common.php";
$key = isset($_GET['key']) ? $_GET['key'] : '';
if ($key !== 'ufs2026setup') { http_response_code(403); exit('forbidden'); }
header('Content-Type: text/plain; charset=utf-8');

sql_query("CREATE TABLE IF NOT EXISTS 2026_event_ticket (
  name VARCHAR(40) NOT NULL,
  label VARCHAR(120) NOT NULL DEFAULT '',
  date1 INT NOT NULL DEFAULT 0,
  date2 INT NOT NULL DEFAULT 0,
  PRIMARY KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$tracks = array(
  'DAY1_TR1'=>'Day 1 · 게임: 프로그래밍',  'DAY1_TR2'=>'Day 1 · 게임: 아트',
  'DAY1_TR3'=>'Day 1 · 미디어 & 엔터테인먼트', 'DAY1_TR4'=>'Day 1 · 산업 & 시뮬레이션',
  'DAY2_TR1'=>'Day 2 · 게임: 프로그래밍',  'DAY2_TR2'=>'Day 2 · 게임: 아트',
  'DAY2_TR3'=>'Day 2 · 미디어 & 엔터테인먼트', 'DAY2_TR4'=>'Day 2 · 산업 & 시뮬레이션',
);
foreach ($tracks as $name=>$label) {
    $n = sql_real_escape_string($name); $l = sql_real_escape_string($label);
    sql_query("INSERT IGNORE INTO 2026_event_ticket (name,label,date1,date2) VALUES ('$n','$l',500,500)");
}
$r = sql_query("SELECT count(*) c FROM 2026_event_ticket");
$row = $r ? $r->fetch_assoc() : null;
echo "OK: 2026_event_ticket 트랙 ".($row?$row['c']:'?')."개\n실행 후 이 파일을 삭제하세요.\n";
