<?php
/* Unreal Fest Seoul 2026 — 티켓 페이지 공통 초기화 (_ticket_init.php)
 * ticket-all.php / ticket-day.php 최상단에서 require.
 * common.php 로드, e()/asset_v(), 트랙 정원 잔여 조회, 본인인증 세션값, 트랙 정의/렌더 헬퍼.
 * PHP 7.0 호환.
 */
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
require __DIR__ . '/_assets.php';
include_once "../common.php";

// 트랙 잔여 (오프라인 정원 — 온라인은 무제한)
$trackRemain = array();
$_tk = sql_query("SELECT name,date1 FROM 2026_event_ticket");
if ($_tk) { while ($x = $_tk->fetch_assoc()) {
    $reg = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE apply_temp_yn='N' AND apply_pay_status<>0 AND apply_track LIKE '%".sql_real_escape_string($x['name'])."%'");
    $trackRemain[$x['name']] = (int)$x['date1'] - ($reg ? (int)$reg['c'] : 0);
}}

// 본인인증 결과(세션) — ../common.php 연동 시 채워짐. 미연동 환경 폴백.
$sess_ci   = isset($_SESSION['CI']) ? $_SESSION['CI'] : '';
$sess_di   = isset($_SESSION['DI']) ? $_SESSION['DI'] : '';
$sess_name = isset($_SESSION['RSLT_NAME']) ? $_SESSION['RSLT_NAME'] : '';
$sess_tel  = isset($_SESSION['TEL_NO']) ? $_SESSION['TEL_NO'] : '';

// 트랙 정의 (Day 1 / Day 2 동일 4트랙)
$UFS_TRACKS = array(
  1 => array('DAY1_TR1'=>'게임: 프로그래밍','DAY1_TR2'=>'게임: 아트','DAY1_TR3'=>'미디어 & 엔터테인먼트','DAY1_TR4'=>'산업 & 시뮬레이션'),
  2 => array('DAY2_TR1'=>'게임: 프로그래밍','DAY2_TR2'=>'게임: 아트','DAY2_TR3'=>'미디어 & 엔터테인먼트','DAY2_TR4'=>'산업 & 시뮬레이션'),
);

// 트랙 선택 박스 렌더 (day=1|2). $trackRemain 으로 마감 처리.
if (!function_exists('ufs_track_box')) {
function ufs_track_box($day, $tracks, $trackRemain) {
    $dlabel = ($day === 1) ? 'Day 1. 8월 20일(목)' : 'Day 2. 8월 21일(금)';
    $field  = ($day === 1) ? 'day1track' : 'day2track';
    $boxid  = ($day === 1) ? 'day1box' : 'day2box';
    echo '<div id="'.$boxid.'" class="trackbox mb-6">';
    echo '<h3 class="text-sm font-bold text-white mb-3">'.e($dlabel).' 트랙 선택 <span class="text-[#00C1D5]">*</span></h3>';
    echo '<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">';
    foreach ($tracks as $v=>$l) {
        $full = isset($trackRemain[$v]) && $trackRemain[$v] <= 0;
        echo '<label class="trk '.($full?'trk-full opacity-40 cursor-not-allowed':'cursor-pointer hover:border-white/20').' p-3 border text-center text-sm font-medium transition-all border-[#27272a] text-[#71717a]">';
        echo '<input type="radio" name="'.$field.'" value="'.e($v).'" class="sr-only" '.($full?'disabled':'').'>'.e($l);
        if ($full) { echo ' <span class="text-[#ff8674] text-xs">(마감)</span>'; }
        echo '</label>';
    }
    echo '</div></div>';
}
}
