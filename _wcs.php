<?php
/* 광고 전환추적 공통 스크립트 — 전 공개 페이지 head에서 include. (그룹 플로우 제외)
 * 공통(PV/유입/방문)은 항상 출력. 등록 완료 페이지에서
 *   $ufs_conv = ['value'=>금액, 'id'=>주문번호]
 * 를 설정해 두면 같은 호출에서 전환을 함께 전송한다.
 *  - 네이버(CTS): 구매(purchase) 전환, wcs.trans (신/구 혼용 금지). PV(wcs_do)는 1회만.
 *  - 카카오 픽셀은 라운지 전역 config(v3_seo_config.seo_kakao_pixel_id)로 일원화 관리 → 여기선 다루지 않음. */

$ufs_naver_wa = 's_305d6577e8a8';   // 네이버 CTS AccountId
$wcs_e = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<!-- Naver Wcslog (CTS) -->
<script type="text/javascript" src="//wcs.naver.net/wcslog.js"></script>
<script type="text/javascript">
if (window.wcs) {
  if (!wcs_add) var wcs_add = {};
  wcs_add["wa"] = "<?= $wcs_e($ufs_naver_wa) ?>";
  wcs.inflow("epiclounge.co.kr");   // 전환추적 cookie domain (공식 wcs.trans 가이드 필수 인자)
  wcs_do();
<?php if (!empty($ufs_conv) && isset($ufs_conv['value'])): ?>
  var _conv = {};
  _conv.type = "purchase";
<?php if (!empty($ufs_conv['id'])): ?>  _conv.id = "<?= $wcs_e($ufs_conv['id']) ?>";
<?php endif; ?>  _conv.value = "<?= (int)$ufs_conv['value'] ?>";
  wcs.trans(_conv);
<?php endif; ?>
}
</script>
