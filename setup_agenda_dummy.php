<?php
/* Unreal Fest Seoul 2026 — 아젠다 컬럼 추가 + 더미 상세 데이터 (1회용, 실행 후 삭제)
 * 1) ag_sp_photo(헤드샷 URL) 컬럼 추가
 * 2) 세션/키노트 행의 비어있는 소개/목차/권장대상에 더미 입력
 * 실행: /v3/unrealfest2026/setup_agenda_dummy.php?key=ufs2026dummy
 * PHP 7.0 호환.
 */
include_once "../common.php";

$key = isset($_GET['key']) ? $_GET['key'] : '';
if ($key !== 'ufs2026dummy') { http_response_code(403); exit('forbidden'); }

header('Content-Type: text/plain; charset=utf-8');

$chk = sql_query("SHOW TABLES LIKE 'cb_unreal_2026_agenda'");
if (!$chk || $chk->num_rows === 0) { exit("테이블이 없습니다. 먼저 setup_agenda_db.php 를 실행하세요.\n"); }

// 1) ag_sp_photo 컬럼 추가 (없으면)
$col = sql_query("SHOW COLUMNS FROM cb_unreal_2026_agenda LIKE 'ag_sp_photo'");
if (!$col || $col->num_rows === 0) {
    sql_query("ALTER TABLE cb_unreal_2026_agenda ADD COLUMN ag_sp_photo VARCHAR(255) NOT NULL DEFAULT '' AFTER ag_sp_company");
    echo "ag_sp_photo 컬럼 추가됨\n";
} else {
    echo "ag_sp_photo 컬럼 이미 존재\n";
}

// 2) 더미 상세 (비어있는 세션/키노트만 — 기존 입력은 보존)
$contents = "세션 개요와 배경\n핵심 기술 및 기능 소개\n실전 적용 사례와 라이브 데모\n정리 및 Q&A";
$target   = "언리얼 엔진 사용자, 게임·영상·산업 분야 개발자, 관련 업계 종사자";

$res = sql_query("SELECT ag_no, ag_title FROM cb_unreal_2026_agenda WHERE ag_slot_type IN ('session','keynote') AND (ag_desc IS NULL OR ag_desc='')");
$n = 0;
if ($res) {
    $cont_e = sql_real_escape_string($contents);
    $tgt_e  = sql_real_escape_string($target);
    while ($r = $res->fetch_assoc()) {
        $no = (int)$r['ag_no'];
        $title = $r['ag_title'] !== '' ? $r['ag_title'] : '이 세션';
        $desc = $title . "에 대한 심도 있는 발표입니다. 실무에서 마주하는 문제와 해결 과정을 구체적인 사례와 함께 공유하며, 참가자들이 현장에서 바로 적용할 수 있는 인사이트와 노하우를 제공합니다.";
        $desc_e = sql_real_escape_string($desc);
        sql_query("UPDATE cb_unreal_2026_agenda SET ag_desc='$desc_e', ag_contents='$cont_e', ag_target='$tgt_e' WHERE ag_no=$no");
        $n++;
    }
}
echo $n . "개 세션/키노트에 더미 상세 신규 입력됨\n";

$tot = sql_fetch("SELECT COUNT(*) c FROM cb_unreal_2026_agenda WHERE ag_slot_type IN ('session','keynote')");
$filled = sql_fetch("SELECT COUNT(*) c FROM cb_unreal_2026_agenda WHERE ag_slot_type IN ('session','keynote') AND ag_desc<>''");
echo "세션/키노트 총 " . ($tot ? (int)$tot['c'] : 0) . "개 중 상세입력 " . ($filled ? (int)$filled['c'] : 0) . "개\n";
echo "\n이 파일은 실행 후 삭제하세요.\n";
