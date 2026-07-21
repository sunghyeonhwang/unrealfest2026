<?php
/* 광고 전환추적 — 어크로스 ADN 3.0 (매체: AND). Unreal Fest 2026 전용.
 * 배치: 각 공개 페이지 head 에서 _wcs.php 와 나란히 include.
 * 공통 로더 + 방문자 태그는 요청당 1회 출력. 등록/결제 완료 페이지에서
 *   $ufs_conv = ['value'=>결제금액(int), 'id'=>주문번호]
 * 를 설정해 두면 같은 호출에서 전환(Purchase)을 함께 전송(금액>0 일 때만).
 * 광고주 ID(ui)=110302. ut(방문자 유형) 기본 'Home'; 페이지에서 $adn_ut 로 override 가능.
 * 성능: 로더는 async — 방문자/전환 init 는 window load + fnc_adn3_health_ok_check 후 실행되므로
 *        async 여도 안전(load 이벤트는 async 스크립트 로드 완료 후 발생). ad-block 대비 typeof 가드.
 * 미도입: 온사이트 팝업 스크립트(보류). 개인정보처리방침 행태정보 고지는 별도 예정.
 * TODO(AND 확인): 방문자 ut 페이지별 분류 필요 여부. 현재 전 페이지 'Home' 고정. */
if (defined('G5_IS_ADMIN')) return;                 // 관리자 미집계(marketing_head 픽셀과 동일 정책)

$adn_ui = '110302';                                  // 광고주 ID (ui) — 4개 블록 공통
$adn_e  = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

/* ── 공통 로더 + 방문자 태그 (요청당 1회) ── */
if (!defined('UFS_ADN_EMITTED')):
  define('UFS_ADN_EMITTED', 1);
  $adn_ut = isset($adn_ut) ? $adn_ut : 'Home';
?>
<!-- ADN3.0 Tracker[공통] start -->
<script async src="//fin.rainbownine.net/js/across_adn_3.0.1.js" type="text/javascript"></script>
<!-- ADN3.0 Tracker[공통] end -->
<!-- ADN3.0 Tracker[방문자] start -->
<script type="text/javascript">
window.addEventListener('load', function () {
  if (typeof fnc_adn3_health_ok_check !== 'function') return;
  fnc_adn3_health_ok_check(function () {
    var c = new fn_across_adn3_contain();
    c.init({ "ut": "<?= $adn_e($adn_ut) ?>", "ui": "<?= $adn_ui ?>" });
  });
});
</script>
<!-- ADN3.0 Tracker[방문자] end -->
<?php endif; ?>
<?php
/* ── 전환 태그 ($ufs_conv 설정 + 금액>0 일 때 1회) ── */
if (!empty($ufs_conv) && (int)$ufs_conv['value'] > 0 && !defined('UFS_ADN_CONV_EMITTED')):
  define('UFS_ADN_CONV_EMITTED', 1);
  $adn_uo = preg_replace('/[^0-9A-Za-z_\-]/', '', (string)$ufs_conv['id']);   // 주문번호
  $adn_up = (int)$ufs_conv['value'];                                          // 결제금액(원, 정수)
?>
<!-- ADN3.0 Tracker[전환] start -->
<script type="text/javascript">
window.addEventListener('load', function () {
  if (typeof fnc_adn3_health_ok_check !== 'function') return;
  fnc_adn3_health_ok_check(function () {
    var o = new fn_across_adn3_contain();
    o.init({ "ut": "Purchase", "ui": "<?= $adn_ui ?>", "uo": "<?= $adn_e($adn_uo) ?>", "up": "<?= $adn_up ?>" });
  });
});
</script>
<!-- ADN3.0 Tracker[전환] end -->
<?php endif; ?>
