<?php
/* Unreal Fest Seoul 2026 — 개인 등록 쿠폰 헬퍼 (_coupon.php)
 * cb_unreal_2026_coupon(단체와 동일 테이블) 재사용. 검증 로직 = group-coupon-check.php와 동일 규칙.
 * ⚠️ 토글 기본 OFF — 관리자 페이지(adm/2026_coupon.php)의 ON/OFF 버튼으로 제어.
 *   저장 위치: cb_unreal_2026_config[indiv_coupon] = 'on' | 'off'(기본). 얼리버드 eb_extend와 동일 패턴.
 *   OFF면 apply_pay/티켓페이지의 쿠폰 분기가 전혀 실행되지 않아 라이브 결제에 무영향.
 * 우선순위: 프리뷰키(브라우저별) > 상수 하드오버라이드 > DB 설정(indiv_coupon).
 * 정책: 100% 쿠폰=무료 완료 허용 / 환불 시 cp_used-1 복원 / 쿠폰은 정상가 기준(얼리버드 종료 후).
 */

if (!defined('UFS_INDIVIDUAL_COUPON')) define('UFS_INDIVIDUAL_COUPON', false); // 비상 하드 오버라이드(보통 false, DB 설정으로 제어)

if (!function_exists('ufs_coupon_enabled')) {
function ufs_coupon_enabled() {
    // 1) 프리뷰: ?ufs_coupon_preview=KEY 접속 시 이 브라우저만 활성(쿠키 유지). 공개/다른 방문자엔 영향 없음.
    $key = 'ufscpn2026x9f3a';
    if (isset($_GET['ufs_coupon_preview'])) {
        if ($_GET['ufs_coupon_preview'] === $key) { @setcookie('ufs_cpn_pv', $key, 0, '/'); $_COOKIE['ufs_cpn_pv'] = $key; return true; }
        @setcookie('ufs_cpn_pv', '', time() - 3600, '/'); return false;   // 잘못된 키=해제
    }
    if (isset($_COOKIE['ufs_cpn_pv']) && $_COOKIE['ufs_cpn_pv'] === $key) return true;
    // 2) 상수 하드 오버라이드
    if (defined('UFS_INDIVIDUAL_COUPON') && UFS_INDIVIDUAL_COUPON) return true;
    // 3) 관리자 설정(cb_unreal_2026_config[indiv_coupon]='on') — 기본 off
    static $cfg = null;
    if ($cfg === null) {
        $cfg = 'off';
        if (function_exists('sql_fetch')) {
            $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='indiv_coupon'");
            if ($r && isset($r['cfg_val']) && trim((string)$r['cfg_val']) !== '') $cfg = trim((string)$r['cfg_val']);
        }
    }
    return ($cfg === 'on');
}
}

/* 쿠폰 코드 검증. 반환 array('ok'=>bool,'percent'=>int(0~100),'row'=>?,'code'=>str,'msg'=>str).
 * 사용횟수(cp_used) 증가는 여기서 하지 않음 — 결제완료 시점에만 증가(그룹과 동일 원칙). */
if (!function_exists('ufs_coupon_check')) {
function ufs_coupon_check($code) {
    $code = strtoupper(trim((string)$code));
    if ($code === '') return array('ok'=>false,'percent'=>0,'code'=>'','msg'=>'쿠폰 코드를 입력해 주세요.');
    $r = @sql_fetch("SELECT * FROM cb_unreal_2026_coupon WHERE cp_code='".sql_real_escape_string($code)."' LIMIT 1");
    if (!$r)                       return array('ok'=>false,'percent'=>0,'code'=>$code,'msg'=>'유효하지 않은 쿠폰입니다.');
    if ($r['cp_active'] !== 'Y')   return array('ok'=>false,'percent'=>0,'code'=>$code,'msg'=>'사용할 수 없는 쿠폰입니다.');
    if (!empty($r['cp_expire']) && $r['cp_expire'] !== '0000-00-00' && $r['cp_expire'] < date('Y-m-d'))
                                   return array('ok'=>false,'percent'=>0,'code'=>$code,'msg'=>'만료된 쿠폰입니다.');
    if ((int)$r['cp_max'] > 0 && (int)$r['cp_used'] >= (int)$r['cp_max'])
                                   return array('ok'=>false,'percent'=>0,'code'=>$code,'msg'=>'사용 한도가 초과된 쿠폰입니다.');
    $pct = (int)$r['cp_percent']; if ($pct < 0) $pct = 0; if ($pct > 100) $pct = 100;
    return array('ok'=>true,'percent'=>$pct,'row'=>$r,'code'=>$code,'msg'=>$pct.'% 할인 쿠폰이 적용되었습니다.');
}
}

/* 쿠폰 적용가(원). 100원 단위 반올림. 100%면 0(무료). */
if (!function_exists('ufs_coupon_apply_price')) {
function ufs_coupon_apply_price($base, $percent) {
    $base = (int)$base; $percent = (int)$percent;
    if ($percent <= 0)   return $base;
    if ($percent >= 100) return 0;
    return (int)(round($base * (100 - $percent) / 100 / 100) * 100);
}
}

/* 결제완료 시 사용횟수 +1 (중복 방지는 호출측에서 '최초 확정 시 1회'만 호출하도록 보장). */
if (!function_exists('ufs_coupon_use')) {
function ufs_coupon_use($code) {
    $code = strtoupper(trim((string)$code));
    if ($code === '') return;
    @sql_query("UPDATE cb_unreal_2026_coupon SET cp_used = cp_used + 1 WHERE cp_code='".sql_real_escape_string($code)."'");
}
}

/* 환불/취소 시 사용횟수 -1 복원 (0 미만 방지). */
if (!function_exists('ufs_coupon_unuse')) {
function ufs_coupon_unuse($code) {
    $code = strtoupper(trim((string)$code));
    if ($code === '') return;
    @sql_query("UPDATE cb_unreal_2026_coupon SET cp_used = GREATEST(cp_used - 1, 0) WHERE cp_code='".sql_real_escape_string($code)."'");
}
}
