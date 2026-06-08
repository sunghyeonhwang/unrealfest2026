<?php
/* Unreal Fest Seoul 2026 — 트랙 정원 저장 proc (adm/2026_event2_remain_proc.php) */
include_once('./_common.php');
if (!function_exists('is_admin') || !is_admin($member['mb_id'])) {
    alert('관리자 로그인이 필요합니다.', G5_ADMIN_URL);
}
$cap = isset($_POST['cap']) && is_array($_POST['cap']) ? $_POST['cap'] : array();
foreach ($cap as $name => $val) {
    $n = sql_real_escape_string($name);
    $v = (int)$val; if ($v < 0) $v = 0;
    sql_query("UPDATE 2026_event_ticket SET date1='$v', date2='$v' WHERE name='$n'");
}
header('Location: 2026_event2_remain.php?saved=1');
exit;
