<?php
// Unreal Fest Seoul 2026 — 스폰서 데이터 (더미: dist 프리뷰 기준, 실데이터는 운영팀 확정 예정)
// tier별 그룹: gold(grid-cols-2, 소개 포함) / silver(grid-cols-2 md:grid-cols-4, 로고만)
// 로고는 public/ 의 임시 SVG 사용. invert_dark=true → 다크 고정 페이지에서 dark:invert.

$sponsors = array(
  'gold' => array(
    array('name'=>'NVIDIA', 'logo'=>'public/logo-nvidia.svg', 'invert_dark'=>true,
      'url'=>'https://www.nvidia.com', 'desc'=>'리얼타임 렌더링과 GPU 가속, AI 그래픽 기술로 차세대 비주얼 경험을 함께 만들어갑니다.'),
    array('name'=>'AWS', 'logo'=>'public/logo-aws.svg', 'invert_dark'=>true,
      'url'=>'https://aws.amazon.com', 'desc'=>'확장 가능한 클라우드 인프라로 대규모 멀티플레이어와 실시간 송출을 지원합니다.'),
  ),
  'silver' => array(
    array('name'=>'Intel', 'logo'=>'public/logo-intel.svg', 'invert_dark'=>true, 'url'=>'https://www.intel.com'),
    array('name'=>'Autodesk', 'logo'=>'public/logo-autodesk.svg', 'invert_dark'=>true, 'url'=>'https://www.autodesk.com'),
    array('name'=>'Adobe', 'logo'=>'public/logo-adobe.svg', 'invert_dark'=>true, 'url'=>'https://www.adobe.com'),
    array('name'=>'Unity', 'logo'=>'public/logo-unity.svg', 'invert_dark'=>true, 'url'=>'https://unity.com'),
    array('name'=>'Samsung', 'logo'=>'public/logo-samsung.svg', 'invert_dark'=>true, 'url'=>'https://www.samsung.com'),
    array('name'=>'Partner 1', 'logo'=>'public/logo1.svg', 'invert_dark'=>true, 'url'=>null),
    array('name'=>'Partner 2', 'logo'=>'public/logo3.svg', 'invert_dark'=>true, 'url'=>null),
    array('name'=>'Partner 3', 'logo'=>'public/logo4.svg', 'invert_dark'=>true, 'url'=>null),
  ),
);
