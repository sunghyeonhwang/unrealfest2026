<?php
/* 광고 전환추적 공통 스크립트 — 전 공개 페이지 head에서 include. (그룹 플로우 제외)
 * 공통(PV/유입/방문)은 항상 출력. 등록 완료 페이지에서
 *   $ufs_conv = ['value'=>금액, 'id'=>주문번호]
 * 를 설정해 두면 같은 호출에서 전환을 함께 전송한다.
 *  - 네이버(CTS): 구매(purchase) 전환, wcs.trans (신/구 혼용 금지). PV(wcs_do)는 1회만.
 *  - 카카오(Pixel): 서비스신청 signUp 전환. 완료 페이지 기준(슬라이드 6 지침).
 * ───────────────────────────────────────────────
 * ▶ 카카오 Track ID는 받는 즉시 아래 $ufs_kakao_id 한 줄만 채우면 전 페이지 활성화됨.
 *   (빈 값이면 카카오 스크립트는 출력하지 않음 = 깨진 픽셀 방지) */

$ufs_naver_wa = 's_305d6577e8a8';   // 네이버 CTS AccountId
$ufs_kakao_id = '';                  // TODO: 카카오 픽셀 Track ID (받으면 입력)

$wcs_e = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<!-- Naver Wcslog (CTS) -->
<script type="text/javascript" src="//wcs.naver.net/wcslog.js"></script>
<script type="text/javascript">
if (!wcs_add) var wcs_add = {};
wcs_add["wa"] = "<?= $wcs_e($ufs_naver_wa) ?>";
if (!_nasa) var _nasa = {};
if (window.wcs) {
  wcs.inflow();
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
<?php if ($ufs_kakao_id !== ''): ?>
<!-- Kakao Pixel -->
<script type="text/javascript" charset="UTF-8" src="//t1.daumcdn.net/kas/static/kp.js"></script>
<script type="text/javascript">
kakaoPixel('<?= $wcs_e($ufs_kakao_id) ?>').pageView();
<?php if (!empty($ufs_conv)): ?>kakaoPixel('<?= $wcs_e($ufs_kakao_id) ?>').signUp();
<?php endif; ?>
</script>
<?php endif; ?>
