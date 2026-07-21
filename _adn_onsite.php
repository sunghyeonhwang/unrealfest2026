<?php
/* 어크로스 ADN 온사이트 마케팅(팝업) — 랜딩 페이지 body 끝에서만 include.
 * 적용: index.php + index_landing_preview.php + index_landing_preview_nohigh.php
 *       (등록/결제 퍼널 페이지엔 미적용 → 등록 플로우 무영향).
 * 팝업 실제 노출/디자인/타겟/빈도는 AND(어크로스) 대시보드 캠페인 설정으로 결정.
 *  → 캠페인 미설정 시 아무것도 안 뜸(스크립트는 "무장"만).
 * ui=110302. 관리자 미노출. 중복 include 가드. 벤더 원본 변수/파일명(onsight 철자) 유지 필수.
 * ⚠️ 선행조건(운영): 에픽/그리프 승인 · AND 팝업 사양 확정(이메일수집 없이) · 개인정보처리방침 행태정보 고지. */
if (defined('G5_IS_ADMIN')) return;                    // 관리자 미노출
if (defined('UFS_ADN_ONSITE_EMITTED')) return;         // 중복 include 방지
define('UFS_ADN_ONSITE_EMITTED', 1);
?>
<!-- ADN 온사이트 설치 start -->
<script type="text/javascript">
  var adn_onsight_param = adn_onsight_param || [];
  adn_onsight_param.push([{ ui: "110302" }]);
</script>
<script type="text/javascript" src="//fin.rainbownine.net/js/across_adn_onsight_1.0.0.js"></script>
<!-- ADN 온사이트 설치 end -->
