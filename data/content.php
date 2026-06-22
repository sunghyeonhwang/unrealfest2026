<?php
// Unreal Fest Seoul 2026 — 정적 콘텐츠 데이터 (canonical = React 라이브 기준)
// 출처: Overview.tsx / Venue.tsx / EventBenefits.tsx. lib.php 접근자에서 require.

// ── Overview (소개) ──
$overview = [
  'image' => 'https://unrealsummit16.cafe24.com/2026/ufs26/overview_text.svg',
  'image_alt' => 'Unreal Ideas Start Here',
  'paragraphs' => [
    '언리얼 페스트 서울 2026에서 언리얼 엔진과 에픽 에코시스템 전반의 혁신적인 기술과 인사이트를 경험해 보세요.',
    '게임, 영화 및 TV, 애니메이션, 자동차, 시뮬레이션 등 다양한 산업 분야에서 활용되는 최신 제작 기술과 실제 프로젝트를 통해 얻은 제작 경험과 노하우를 확인할 수 있습니다.',
    '리얼타임 렌더링, 애니메이션, 버추얼 프로덕션, 메타휴먼, 디지털 트윈, {br}AI 기반 제작 워크플로 등 다양한 리얼타임 기술과 현업 전문가들의 실전 인사이트를 통해 인터랙티브 3D 경험 제작의 새로운 가능성을 발견해 보세요.',
  ],
  'features' => [
    ['icon'=>'layout-grid', 'title'=>'최신 기술과 워크플로', 'desc'=>'언리얼 엔진의 최신 기능과 차세대 리얼타임 제작 환경을 직접 확인해 보세요.'],
    ['icon'=>'users', 'title'=>'전문가 인사이트', 'desc'=>'다양한 산업 분야의 전문가들이 공유하는 {br}실제 프로젝트 사례와 제작 인사이트를 만나보세요.'],
    ['icon'=>'zap', 'title'=>'전시 및 체험', 'desc'=>'언리얼 엔진과 에픽 에코시스템을 경험하고 인디 게임과 파트너사 전시를 만나보세요.'],
    ['icon'=>'video', 'title'=>'일부 세션 온라인 생중계', 'desc'=>'현장에 오지 못해도 온라인 생중계로 {br}언리얼 페스트 서울 2026을 만나보세요.'],
  ],
];

// ── Venue (행사장 안내) ──
$venue = [
  'name' => '웨스틴 서울 파르나스',
  'address' => '서울특별시 강남구 봉은사로 524',
  'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3165.5!2d127.0574461!3d37.5129292!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x357ca46beba84f0d%3A0xa07ae6d7a2a200e7!2z7Juo7Iqk7YuEIOyEnOyauCDtjIzrpbTrgpjsiqQ!5e0!3m2!1sko!2skr!4v1700000000000!5m2!1sko!2skr&maptype=roadmap',
  'map_link' => 'https://www.google.com/maps/place/%EC%9B%A8%EC%8A%A4%ED%8B%B4+%EC%84%9C%EC%9A%B8+%ED%8C%8C%EB%A5%B4%EB%82%98%EC%8A%A4/data=!4m13!1m2!2m1!1z7Juo7Iqk7Yu07KGw7ISg7ISc7Jq47YyM66W064KY7Iqk!3m9!1s0x357ca46beba84f0d:0xa07ae6d7a2a200e7!5m2!4m1!1i2!8m2!3d37.5129292!4d127.0574461!15sCiHsm6jsiqTti7TsobDshKDshJzsmrjtjIzrpbTrgpjsiqWSAQVob3RlbOABAA!16s%2Fg%2F11yhvkgm6_?entry=ttu',
  'cards' => [
    ['icon'=>'map-pin', 'title'=>'행사장 체크인', 'body'=>'지하 1층 하모니 볼룸 앞 데스크에서 QR 코드 확인 후 명찰을 수령할 수 있습니다. 체크인은 첫날 오전 9시, 둘째 날 오전 9시 30분부터 시작됩니다.'],
    ['icon'=>'train', 'title'=>'대중교통', 'subway'=>'2호선 삼성역 5번 출구, 9호선 봉은사역 7번 출구', 'bus'=>'봉은사·코엑스 북문 정류장 하차'],
    ['icon'=>'car', 'title'=>'주차 안내', 'body'=>'행사 참가자에게는 무료 주차권이 제공됩니다만 주차 공간이 제한될 수 있으니 가급적 대중교통 이용을 권장 드립니다. 명찰 수령 후 데스크에서 차량 번호를 등록해 주세요.'],
  ],
];

// ── Event Benefits (이벤트) ──
$events = [
  ['type'=>'오프라인', 'title'=>'오프라인 등록 이벤트', 'desc'=>'현장 참석자 전원에게 한정판 굿즈 증정!', 'date'=>'8.20 (목) ~ 8.21 (금)', 'note'=>''],
  ['type'=>'오프라인', 'title'=>'경품 추첨 이벤트', 'desc'=>'오프라인 참석자 대상, 세션 종료 후 경품 추첨! (1일 1회)', 'date'=>'8.20 (목) ~ 8.21 (금)', 'note'=>''],
  ['type'=>'오프라인', 'title'=>'얼리버드 체크인 이벤트', 'desc'=>'현장 체크인 선착순 300명에게 한정판 굿즈 추가 증정!', 'date'=>'8.20 (목) 한정', 'note'=>''],
  ['type'=>'온라인', 'title'=>'출석 체크 이벤트', 'desc'=>'양일간 시청한 분들 중 추첨을 통해 200명에게 굿즈 증정!', 'date'=>'8.20 (목) ~ 8.21 (금)', 'note'=>'*온라인 체크인 시 자동 응모'],
];
