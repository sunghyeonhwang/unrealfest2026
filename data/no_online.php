<?php
/* 온라인 중계 제외(현장 전용) 세션 표시 — 배지/리본 단일 소스.
 * 공용: data/agenda_grid.php(랜딩 프리뷰) + schedule_preview.php. 각 함수 function_exists 가드(중복정의 방지).
 * 배지/리본 색은 #00FFC8 통일(변형 스위치 $GLOBALS['ufs_no_online_variant']는 향후 재분기용, 현재 두 분기 동일). */
// 하드코딩 폴백(초기 15세션) — DB 컬럼(ag_no_online) 조회 실패 시에만 사용. 2026-07-21 시트 보라색 셀 매핑.
if (!function_exists('ufs_no_online_fallback')) {
function ufs_no_online_fallback() {
    return array('d1-t1-s2'=>1,'d1-t1-s3'=>1,'d1-t2-s1'=>1,'d1-t2-s5'=>1,'d1-t3-s1'=>1,'d1-t3-s5'=>1,'d1-t4-s2'=>1,'d2-t1-s4'=>1,'d2-t1-s5'=>1,'d2-t2-s4'=>1,'d2-t3-s3'=>1,'d2-t3-s4'=>1,'d2-t4-s2a'=>1,'d2-t4-s2b'=>1,'d2-t4-s5'=>1);
}
}
if (!function_exists('ufs_no_online')) {
// 온라인 중계 제외 세션 — 단일 소스 = DB cb_unreal_2026_agenda.ag_no_online(관리자 토글).
// 요청당 1회 조회 후 정적 캐시. DB 접근 불가/컬럼 없음이면 하드코딩 폴백으로 무중단.
function ufs_no_online($id) {
    static $L = null;
    if ($L === null) {
        $L = array();
        $ok = false;  // 쿼리 성공 여부(컬럼 존재). 성공이면 빈 결과=진짜 0건으로 신뢰(폴백 금지).
        if (function_exists('sql_query')) {
            $res = @sql_query("SELECT ag_sid FROM cb_unreal_2026_agenda WHERE ag_no_online=1");
            if ($res) { $ok = true; while ($r = $res->fetch_assoc()) { if ($r['ag_sid'] !== '') $L[$r['ag_sid']] = 1; } }
        }
        if (!$ok) { $L = ufs_no_online_fallback(); }  // 컬럼 미존재/쿼리 실패 시에만 폴백
    }
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
