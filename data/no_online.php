<?php
/* 온라인 중계 제외(현장 전용) 세션 표시 — 배지/리본 단일 소스.
 * 공용: data/agenda_grid.php(랜딩 프리뷰) + schedule_preview.php. 각 함수 function_exists 가드(중복정의 방지).
 * 배지/리본 색은 #00FFC8 통일(변형 스위치 $GLOBALS['ufs_no_online_variant']는 향후 재분기용, 현재 두 분기 동일). */
if (!function_exists('ufs_no_online')) {
// 온라인 중계 제외 15세션 — 시트 보라색 셀 매핑(2026-07-21)
function ufs_no_online($id) {
    static $L = array('d1-t1-s2'=>1,'d1-t1-s3'=>1,'d1-t2-s1'=>1,'d1-t2-s5'=>1,'d1-t3-s1'=>1,'d1-t3-s5'=>1,'d1-t4-s2'=>1,'d2-t1-s4'=>1,'d2-t1-s5'=>1,'d2-t2-s4'=>1,'d2-t3-s3'=>1,'d2-t3-s4'=>1,'d2-t4-s2a'=>1,'d2-t4-s2b'=>1,'d2-t4-s5'=>1);
    return isset($L[$id]);
}
}
if (!function_exists('ufs_no_online_colors')) {
function ufs_no_online_colors() {
    $v = isset($GLOBALS['ufs_no_online_variant']) ? $GLOBALS['ufs_no_online_variant'] : 'white';
    if ($v === 'yellow') return array('fill'=>'#00FFC8','text'=>'#0a0a0a','border'=>'#0a0a0a','icon'=>'#1a1a1a');
    return array('fill'=>'#00FFC8','text'=>'#0a0a0a','border'=>'#0a0a0a','icon'=>'#1a1a1a');
}
}
if (!function_exists('ufs_no_online_ribbon')) {
// 우측 상단 코너 삼각형 리본 + ⓘ 아이콘(호버 툴팁). opacity .5. 컨테이너는 position:relative 필요.
function ufs_no_online_ribbon($id) {
    if (!ufs_no_online($id)) return '';
    $c = ufs_no_online_colors();
    return '<span title="온라인 중계 제외" style="position:absolute;top:0;right:0;width:46px;height:46px;overflow:hidden;z-index:2;opacity:.5">'
      .'<span style="position:absolute;top:0;right:0;width:0;height:0;border-style:solid;border-width:0 46px 46px 0;border-color:transparent '.$c['fill'].' transparent transparent"></span>'
      .'<svg style="position:absolute;top:6px;right:6px;color:'.$c['icon'].'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>'
      .'</span>';
}
}
if (!function_exists('ufs_no_online_badge')) {
// 인라인 배지 — 채움 + 블랙 테두리(박스) + 블랙 텍스트.
function ufs_no_online_badge($id) {
    if (!ufs_no_online($id)) return '';
    $c = ufs_no_online_colors();
    return '<span style="display:inline-flex;align-items:center;padding:2px 7px;font-size:10px;font-weight:700;color:'.$c['text'].';background:'.$c['fill'].';border:1px solid '.$c['border'].';white-space:nowrap">온라인 중계 제외</span>';
}
}
