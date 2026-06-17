<?php
// Unreal Fest Seoul 2026 — 스폰서 데이터 (canonical = React 라이브 기준)
// 출처: 홈 섹션 src/app/components/Sponsors.tsx (플레이스홀더 로고) +
//       상세 페이지 src/app/pages/SponsorsDetail.tsx (이름+설명+링크).
// lib.php ufs_sponsors_home()/ufs_sponsors_detail() 가 require하여 사용.
// ⚠️ 설명문 일부는 라이브에 깨진 더미카피("레스 트레이싱","캐릭셜","이해혈 음 유로")가 그대로 노출 중 —
//    1:1 재현 원칙에 따라 동일 반영. 운영팀 실데이터 확정 시 교체 필요.
// 로고는 사이트 루트의 ./logoN.svg (public/ → 서버 루트 업로드본).

// 홈(index) 스폰서 섹션: 로고만 (이름은 플레이스홀더)
$sponsors_home = [
  'gold' => [
    ['name'=>'Gold Sponsor 1', 'src'=>'./logo1.svg'],
    ['name'=>'Gold Sponsor 2', 'src'=>'./logo2.svg'],
  ],
  'silver' => [
    ['name'=>'Perforce', 'src'=>'https://unrealsummit16.cafe24.com/2026/ufs26/logo_perforce.svg'],
    ['name'=>'Giant Step', 'src'=>'https://unrealsummit16.cafe24.com/2026/ufs26/logo_giantstep.svg'],
    ['name'=>'Hospi', 'src'=>'https://unrealsummit16.cafe24.com/2026/ufs26/logo_hospi.svg'],
    ['name'=>'Audiokinetic', 'src'=>'https://unrealsummit16.cafe24.com/2026/ufs26/logo_audiokinetic.svg'],
  ],
];

// 상세(sponsors.php) 페이지: 이름 + 설명 + 링크
$sponsors_detail = [
  'gold' => [
    ['name'=>'Xsolla', 'logo'=>'./logo1.svg', 'link'=>'#',
      'desc'=>"리얼타임 AI 렌더링의 선두주자. 개발자들의 차세대 그래픽라이브파이프라인 구축을 전폭 지원합니다. 현장 부스에서 RTX50 시리즈 기반의 실시간 8K 레스 트레이싱 데모와 최신 DLSS4.0 적용 사례를 직접 확인하세요."],
    ['name'=>'HP', 'logo'=>'./logo2.svg', 'link'=>'#',
      'desc'=>"확장 가능한 클라우드 인프라를 통해 전 세계 수백만 명의 플레이어에게 즉시 접근 가능한 멀티플레이어 경험을 제공합니다. AWS GameLift와 연계한 엔진의 백엔드 및 효율적인 백엔드 아키텍처 사례를 소개합니다."],
  ],
  'silver' => [
    ['name'=>'Silver Sponsor 1', 'logo'=>'./logo3.svg', 'link'=>'#',
      'desc'=>"리얼타임 렌더링과 가상화 환경에서의 혁신을 선도합니다. 차세대 GPU 아키텍처를 통해 크리에이터와 개발자의 워크플로를 최적화합니다."],
    ['name'=>'Perforce', 'logo'=>'./logo4.svg', 'link'=>'#',
      'desc'=>"언리얼 이니시에 대규모 합동작업 관리, PC, 콘솔/모바일 프리커 환경에 특화된 크로스 플랫폼 개발 지원을 지원합니다."],
    ['name'=>'Samsung', 'logo'=>'./logo7.svg', 'link'=>'#',
      'desc'=>"클라우디오엔진을 기반으로 몰입형 공간 오디오 및 3D 랜더링 엔진을 이해혈 음 유로 수행합니다."],
    ['name'=>'Audiokinetic', 'logo'=>'./logo8.svg', 'link'=>'#',
      'desc'=>"실시간 3D 환경에서 완벽한 캐릭셜 성능을 위한 전문 솔루션을 제공합니다."],
  ],
];
