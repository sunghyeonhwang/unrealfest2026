<?php
// Unreal Fest Seoul 2026 — 세션 데이터 (출처: sessions.md, 더미)
// 총 26건 (키노트 2 + 트랙 24). require 시 $sessions 배열을 채움.
// 후속 DB 전환 시 이 파일만 DB 조회로 교체 (data/lib.php 인터페이스 유지).

$sessions = array(
  // ===== 키노트 =====
  array('id'=>'keynote-1','track'=>'키노트','difficulty'=>'전체 참가자',
    'speaker'=>'팀 스위니 (Tim Sweeney)','speaker_title'=>'Founder & CEO','affiliation'=>'에픽게임즈',
    'title'=>'리얼타임 3D가 만드는 다음 10년',
    'description'=>'에픽게임즈의 창립자 팀 스위니가 직접 전하는 언리얼 엔진과 리얼타임 3D의 미래 비전. 게임을 넘어 엔터테인먼트, 산업 전반으로 확장되는 메타버스 시대의 기술적 방향성을 공유합니다.',
    'toc'=>array('언리얼 엔진 6.0 비전','리얼타임 3D의 산업적 확장','오픈 메타버스와 표준','Q&A'),
    'target'=>'전체 참가자','room'=>'그랜드 볼룸 (전체)','day'=>1,'time'=>'10:00 - 11:00','is_keynote'=>true),

  array('id'=>'keynote-2','track'=>'키노트','difficulty'=>'전체 참가자',
    'speaker'=>'킴 리브레 (Kim Libreri)','speaker_title'=>'CTO','affiliation'=>'에픽게임즈',
    'title'=>'크리에이터 이코노미와 언리얼 에디터의 진화',
    'description'=>'에픽게임즈 CTO 킴 리브레가 소개하는 차세대 크리에이터 도구와 워크플로우. 포트나이트의 UEFN, 메타휴먼, 페이블 등 크리에이터 이코노미를 가속화하는 핵심 기술들을 공개합니다.',
    'toc'=>array('UEFN 로드맵','페이블(Fab) 마켓플레이스','메타휴먼의 미래','오픈 라이브 데모'),
    'target'=>'전체 참가자','room'=>'그랜드 볼룸 (전체)','day'=>1,'time'=>'11:00 - 12:00','is_keynote'=>true),

  // ===== Day 1 — 14:00 ~ 15:00 =====
  array('id'=>'s1-3','track'=>'게임 - 프로그래밍','difficulty'=>'전문가용',
    'speaker'=>'박최적','speaker_title'=>'성능 엔지니어','affiliation'=>'NCSOFT',
    'title'=>'PC 및 콘솔 최적화 노하우 A to Z',
    'description'=>'콘솔 환경에서 프레임드랍을 최소화하고 60프레임을 고정하기 위한 다양한 병목 현상 분석 및 해결 기법을 공유합니다.',
    'toc'=>array('프로파일링 방법론','CPU·GPU 바운드','메모리 최적화','Q&A'),
    'target'=>'게임 프로그래머, 최적화 엔지니어','room'=>'그랜드 볼룸 A','day'=>1,'time'=>'14:00 - 15:00','is_keynote'=>false),

  array('id'=>'s2-3','track'=>'게임 - 아트','difficulty'=>'중급자용',
    'speaker'=>'한빛','speaker_title'=>'시네마틱 라이팅 아티스트','affiliation'=>'스마일게이트',
    'title'=>'루멘을 활용한 역동적인 시네마틱 라이팅 연출',
    'description'=>'동적 글로벌 일루미네이션인 루멘의 세팅법부터 컷신 연출 시 몰입감을 극대화하는 조명 배치 노하우를 공개합니다.',
    'toc'=>array('루멘 기초 구조','실내외 조명 세팅','시퀀서 연동','시연'),
    'target'=>'라이팅 아티스트, 컷신 디자이너','room'=>'그랜드 볼룸 C','day'=>1,'time'=>'14:00 - 15:00','is_keynote'=>false),

  array('id'=>'s3-3','track'=>'미디어 & 엔터테인먼트','difficulty'=>'전문가용',
    'speaker'=>'정애니','speaker_title'=>'애니메이션 리드','affiliation'=>'픽셀 스튜디오',
    'title'=>'리얼타임 애니메이션 파이프라인 혁신 사례',
    'description'=>'기존 오프라인 렌더링 파이프라인을 언리얼 엔진 기반의 리얼타임으로 전환하며 얻은 효율성과 품질 향상 사례를 소개합니다.',
    'toc'=>array('파이프라인 전환 과정','리얼타임 렌더링 팁','제작 시간 단축 성과','향후 전망'),
    'target'=>'애니메이터, 파이프라인 TD, 프로듀서','room'=>'그랜드 볼룸 B','day'=>1,'time'=>'14:00 - 15:00','is_keynote'=>false),

  array('id'=>'s4-3','track'=>'산업 & 시뮬레이션','difficulty'=>'전문가용',
    'speaker'=>'김시티','speaker_title'=>'스마트시티 기획자','affiliation'=>'도시공사 테크센터',
    'title'=>'디지털 트윈: 대규모 도시 스마트 시티 구축기',
    'description'=>'언리얼 엔진을 활용하여 도시 전체의 데이터를 시각화하고 시뮬레이션하는 디지털 트윈 프로젝트의 노하우를 공유합니다.',
    'toc'=>array('디지털 트윈 정의','방대한 GIS 데이터 처리','최적화 및 퍼포먼스 관리','Q&A'),
    'target'=>'공공기관, 도시 계획가, GIS 전문가','room'=>'컨퍼런스 룸 1','day'=>1,'time'=>'14:00 - 15:00','is_keynote'=>false),

  // ===== Day 1 — 15:30 ~ 16:30 =====
  array('id'=>'d1-s2-1','track'=>'게임 - 프로그래밍','difficulty'=>'전문가용',
    'speaker'=>'이넷코','speaker_title'=>'네트워크 프로그래머','affiliation'=>'넥슨',
    'title'=>'언리얼 엔진 멀티플레이어 네트워크 프로그래밍 심화',
    'description'=>'대규모 멀티플레이어 환경에서 레플리케이션, 프리딕션, 서버 오소리타티브 모델을 효율적으로 구현하는 방법을 다룹니다.',
    'toc'=>array('레플리케이션 심화','프리딕션과 보정','대역폭 최적화','실전 사례'),
    'target'=>'네트워크 프로그래머, 서버 엔지니어','room'=>'그랜드 볼룸 A','day'=>1,'time'=>'15:30 - 16:30','is_keynote'=>false),

  array('id'=>'d1-s2-2','track'=>'게임 - 아트','difficulty'=>'전문가용',
    'speaker'=>'오폴리','speaker_title'=>'테크니컬 아티스트','affiliation'=>'크래프톤',
    'title'=>'나나이트 메시와 버추얼 섀도우 맵 실전 활용',
    'description'=>'나나이트 메시의 LOD 자동화와 버추얼 섀도우 맵을 활용한 고품질 비주얼 파이프라인 구축 노하우를 소개합니다.',
    'toc'=>array('나나이트 파이프라인','VSM 설정 가이드','퍼포먼스 트레이드오프','Q&A'),
    'target'=>'테크니컬 아티스트, 3D 모델러','room'=>'그랜드 볼룸 C','day'=>1,'time'=>'15:30 - 16:30','is_keynote'=>false),

  array('id'=>'d1-s2-3','track'=>'미디어 & 엔터테인먼트','difficulty'=>'중급자용',
    'speaker'=>'강촬영','speaker_title'=>'VP 슈퍼바이저','affiliation'=>'덱스터 스튜디오',
    'title'=>'버추얼 프로덕션: LED 볼륨 촬영 현장 가이드',
    'description'=>'In-Camera VFX 환경에서 언리얼 엔진과 nDisplay를 활용한 LED 월 촬영 세팅부터 색보정까지의 전 과정을 공유합니다.',
    'toc'=>array('nDisplay 구성','카메라 트래킹 연동','색보정 파이프라인','현장 트러블슈팅'),
    'target'=>'촬영 감독, VP 엔지니어, VFX 아티스트','room'=>'그랜드 볼룸 B','day'=>1,'time'=>'15:30 - 16:30','is_keynote'=>false),

  array('id'=>'d1-s2-4','track'=>'산업 & 시뮬레이션','difficulty'=>'중급자용',
    'speaker'=>'차자율','speaker_title'=>'시뮬레이션 엔지니어','affiliation'=>'현대오토에버',
    'title'=>'자율주행 시뮬레이션 환경 구축과 센서 모델링',
    'description'=>'언리얼 엔진 기반으로 라이다, 카메라, 레이더 센서를 모델링하고 자율주행 알고리즘을 검증하는 시뮬레이션 환경을 소개합니다.',
    'toc'=>array('센서 모델링 기법','날씨·조명 시뮬레이션','시나리오 자동 생성','검증 파이프라인'),
    'target'=>'자율주행 엔지니어, 시뮬레이션 개발자','room'=>'컨퍼런스 룸 1','day'=>1,'time'=>'15:30 - 16:30','is_keynote'=>false),

  // ===== Day 1 — 17:00 ~ 18:00 =====
  array('id'=>'d1-s3-1','track'=>'게임 - 프로그래밍','difficulty'=>'중급자용',
    'speaker'=>'한능력','speaker_title'=>'게임플레이 프로그래머','affiliation'=>'펄어비스',
    'title'=>'GAS(Gameplay Ability System) 완전 정복',
    'description'=>'언리얼 엔진의 Gameplay Ability System을 활용하여 확장성 높은 스킬/버프 시스템을 설계하고 구현하는 실전 가이드입니다.',
    'toc'=>array('GAS 아키텍처 이해','어빌리티와 이펙트 설계','어트리뷰트 시스템','실전 적용 패턴'),
    'target'=>'게임플레이 프로그래머','room'=>'그랜드 볼룸 A','day'=>1,'time'=>'17:00 - 18:00','is_keynote'=>false),

  array('id'=>'d1-s3-2','track'=>'게임 - 아트','difficulty'=>'초보자용',
    'speaker'=>'민캐릭','speaker_title'=>'캐릭터 아티스트','affiliation'=>'에픽게임즈 코리아',
    'title'=>'메타휴먼으로 만드는 하이퀄리티 캐릭터 워크플로',
    'description'=>'메타휴먼 크리에이터로 포토리얼 캐릭터를 제작하고 커스터마이징하는 워크플로를 초보자 눈높이에서 설명합니다.',
    'toc'=>array('메타휴먼 기초','페이셜 커스터마이징','의상·헤어 설정','애니메이션 연동'),
    'target'=>'캐릭터 아티스트, 입문 개발자','room'=>'그랜드 볼룸 C','day'=>1,'time'=>'17:00 - 18:00','is_keynote'=>false),

  array('id'=>'d1-s3-3','track'=>'미디어 & 엔터테인먼트','difficulty'=>'초보자용',
    'speaker'=>'배건축','speaker_title'=>'건축 시각화 디렉터','affiliation'=>'아키비즈 스튜디오',
    'title'=>'언리얼 엔진으로 만드는 인터랙티브 건축 시각화',
    'description'=>'건축 설계 데이터를 언리얼 엔진에서 인터랙티브 워크스루로 전환하는 과정과 클라이언트 프레젠테이션 활용 사례를 소개합니다.',
    'toc'=>array('데이터 임포트 파이프라인','머티리얼 세팅','인터랙션 블루프린트','VR 프레젠테이션'),
    'target'=>'건축 디자이너, 시각화 아티스트','room'=>'그랜드 볼룸 B','day'=>1,'time'=>'17:00 - 18:00','is_keynote'=>false),

  array('id'=>'d1-s3-4','track'=>'산업 & 시뮬레이션','difficulty'=>'전문가용',
    'speaker'=>'정국방','speaker_title'=>'시뮬레이션 PM','affiliation'=>'LIG넥스원',
    'title'=>'국방 훈련 시뮬레이터: 언리얼 엔진 적용 사례',
    'description'=>'군사 훈련용 시뮬레이터에 언리얼 엔진을 적용하여 몰입감 높은 가상 훈련 환경을 구축한 실전 프로젝트 사례를 공유합니다.',
    'toc'=>array('요구사항 분석','지형·환경 생성','멀티유저 훈련 시나리오','성과 평가'),
    'target'=>'국방/시뮬레이션 개발자, PM','room'=>'컨퍼런스 룸 1','day'=>1,'time'=>'17:00 - 18:00','is_keynote'=>false),

  // ===== Day 2 — 10:00 ~ 11:30 =====
  array('id'=>'s5-1','track'=>'게임 - 프로그래밍','difficulty'=>'전문가용',
    'speaker'=>'최서버','speaker_title'=>'백엔드 리드','affiliation'=>'메가게임즈',
    'title'=>'모바일 MMORPG 서버 아키텍처와 언리얼 엔진 최적화',
    'description'=>'수만 명이 동시 접속하는 모바일 환경에서 언리얼 엔진 클라이언트와 서버 간의 데이터 동기화 및 렌더링 최적화 기법을 공유합니다.',
    'toc'=>array('서버 아키텍처 구조','네트워크 패킷 최적화','모바일 디바이스 프로파일링','라이브 서비스 이슈'),
    'target'=>'서버 프로그래머, 클라이언트 개발자','room'=>'그랜드 볼룸 A','day'=>2,'time'=>'10:00 - 11:30','is_keynote'=>false),

  array('id'=>'s5-2','track'=>'게임 - 아트','difficulty'=>'중급자용',
    'speaker'=>'권절차','speaker_title'=>'환경 아티스트','affiliation'=>'에픽게임즈',
    'title'=>'언리얼 6.0 프로시저럴 환경 생성 심화',
    'description'=>'PCG 프레임워크를 심도있게 파고들어 복잡한 룰 기반의 월드 생성 자동화 팁을 공유합니다.',
    'toc'=>array('PCG 노드 설계','바이옴 기반 생성','월드 파티션 연동','시연'),
    'target'=>'레벨 디자이너, 환경 아티스트','room'=>'그랜드 볼룸 C','day'=>2,'time'=>'10:00 - 11:30','is_keynote'=>false),

  array('id'=>'s5-3','track'=>'미디어 & 엔터테인먼트','difficulty'=>'초보자용',
    'speaker'=>'임가상','speaker_title'=>'크리에이티브 디렉터','affiliation'=>'메타스테이지',
    'title'=>'XR 라이브 콘서트: 인터랙티브 콘텐츠 기획',
    'description'=>'언리얼 기반 XR 콘서트 무대 구축 및 관객 참여형 인터랙션 구현 기법을 소개합니다.',
    'toc'=>array('XR 기획 개요','네트워크 멀티플레이 이벤트','조명·무대 제어','성공 사례'),
    'target'=>'콘서트 기획자, XR 디자이너','room'=>'그랜드 볼룸 B','day'=>2,'time'=>'10:00 - 11:30','is_keynote'=>false),

  array('id'=>'s5-4','track'=>'산업 & 시뮬레이션','difficulty'=>'전문가용',
    'speaker'=>'윤설비','speaker_title'=>'시뮬레이션 연구원','affiliation'=>'한국제조기술',
    'title'=>'공장 설비 및 공정 시뮬레이션의 최적화',
    'description'=>'실제 기계 공장의 공정을 물리 엔진과 연동하여 시뮬레이션하고 병목 구간을 진단하는 실증 사례.',
    'toc'=>array('카오스 피직스 응용','데이터 동기화','공정 시각화','결과 분석'),
    'target'=>'스마트팩토리 기획자, 시뮬레이션 개발자','room'=>'컨퍼런스 룸 1','day'=>2,'time'=>'10:00 - 11:30','is_keynote'=>false),

  // ===== Day 2 — 13:00 ~ 14:30 =====
  array('id'=>'d2-s2-1','track'=>'게임 - 프로그래밍','difficulty'=>'전문가용',
    'speaker'=>'송매스','speaker_title'=>'엔진 프로그래머','affiliation'=>'넷마블',
    'title'=>'ECS와 매스 엔티티로 대규모 시뮬레이션 구현하기',
    'description'=>'언리얼의 매스 엔티티 시스템을 활용하여 수천 개의 AI 에이전트를 동시에 시뮬레이션하는 기법과 최적화 전략을 공유합니다.',
    'toc'=>array('Mass Entity 아키텍처','프래그먼트 설계','프로세서 최적화','실전 벤치마크'),
    'target'=>'엔진 프로그래머, AI 프로그래머','room'=>'그랜드 볼룸 A','day'=>2,'time'=>'13:00 - 14:30','is_keynote'=>false),

  array('id'=>'d2-s2-2','track'=>'게임 - 아트','difficulty'=>'중급자용',
    'speaker'=>'김머티','speaker_title'=>'머티리얼 아티스트','affiliation'=>'시프트업',
    'title'=>'서브스턴스와 언리얼의 프로시저럴 머티리얼 워크플로',
    'description'=>'서브스턴스 디자이너에서 제작한 프로시저럴 머티리얼을 언리얼 엔진에 최적화하여 적용하는 전체 워크플로를 소개합니다.',
    'toc'=>array('프로시저럴 머티리얼 설계','파라미터 노출 전략','언리얼 머티리얼 통합','라이브 데모'),
    'target'=>'머티리얼/텍스처 아티스트','room'=>'그랜드 볼룸 C','day'=>2,'time'=>'13:00 - 14:30','is_keynote'=>false),

  array('id'=>'d2-s2-3','track'=>'미디어 & 엔터테인먼트','difficulty'=>'중급자용',
    'speaker'=>'홍모캡','speaker_title'=>'모션캡처 디렉터','affiliation'=>'버추얼 크루',
    'title'=>'실시간 모션 캡처와 라이브 퍼포먼스 기술',
    'description'=>'실시간 모션 캡처 장비와 언리얼 엔진 라이브 링크를 연동하여 라이브 퍼포먼스를 실현하는 파이프라인을 공개합니다.',
    'toc'=>array('모캡 장비 세팅','Live Link 연동','리타게팅 최적화','라이브 시연'),
    'target'=>'애니메이터, 모션캡처 테크니션','room'=>'그랜드 볼룸 B','day'=>2,'time'=>'13:00 - 14:30','is_keynote'=>false),

  array('id'=>'d2-s2-4','track'=>'산업 & 시뮬레이션','difficulty'=>'중급자용',
    'speaker'=>'이메디','speaker_title'=>'의료 시뮬레이션 리드','affiliation'=>'메디컬 테크놀로지',
    'title'=>'의료 시뮬레이션: 수술 훈련 플랫폼 개발기',
    'description'=>'언리얼 엔진 기반 수술 시뮬레이터 개발 과정에서의 햅틱 피드백 연동, 해부학 모델링, 물리 인터랙션 구현 경험을 공유합니다.',
    'toc'=>array('해부학 모델 구축','햅틱 디바이스 연동','물리 시뮬레이션','임상 검증 사례'),
    'target'=>'의료 IT 개발자, 시뮬레이션 엔지니어','room'=>'컨퍼런스 룸 1','day'=>2,'time'=>'13:00 - 14:30','is_keynote'=>false),

  // ===== Day 2 — 15:00 ~ 16:30 =====
  array('id'=>'d2-s3-1','track'=>'게임 - 프로그래밍','difficulty'=>'중급자용',
    'speaker'=>'류에이','speaker_title'=>'AI 프로그래머','affiliation'=>'카카오게임즈',
    'title'=>'언리얼 엔진 AI와 비헤이비어 트리 마스터 클래스',
    'description'=>'비헤이비어 트리와 EQS를 결합하여 전략적으로 행동하는 게임 AI를 설계하고 디버깅하는 실전 테크닉을 다룹니다.',
    'toc'=>array('비헤이비어 트리 설계 패턴','EQS 활용법','AI 퍼셉션 시스템','디버깅 도구 활용'),
    'target'=>'AI 프로그래머, 게임 디자이너','room'=>'그랜드 볼룸 A','day'=>2,'time'=>'15:00 - 16:30','is_keynote'=>false),

  array('id'=>'d2-s3-2','track'=>'게임 - 아트','difficulty'=>'전문가용',
    'speaker'=>'장이펙','speaker_title'=>'VFX 아티스트','affiliation'=>'데브시스터즈',
    'title'=>'언리얼 엔진 이펙트: 나이아가라 파티클 심화',
    'description'=>'나이아가라 시스템의 고급 모듈을 활용하여 대규모 파티클 이펙트를 효율적으로 제작하고 최적화하는 기법을 공유합니다.',
    'toc'=>array('시뮬레이션 스테이지 심화','데이터 인터페이스 활용','GPU 파티클 최적화','시연'),
    'target'=>'VFX 아티스트, 테크니컬 아티스트','room'=>'그랜드 볼룸 C','day'=>2,'time'=>'15:00 - 16:30','is_keynote'=>false),

  array('id'=>'d2-s3-3','track'=>'미디어 & 엔터테인먼트','difficulty'=>'전문가용',
    'speaker'=>'윤시퀀','speaker_title'=>'시네마틱 디렉터','affiliation'=>'에픽게임즈',
    'title'=>'AI 기반 자동 시퀀서 편집과 카메라 연출',
    'description'=>'머신러닝 기반 카메라 자동 연출과 시퀀서 편집 자동화 도구의 프로토타입을 소개하고 향후 로드맵을 공유합니다.',
    'toc'=>array('AI 카메라 연출 원리','시퀀서 자동화 API','프로토타입 시연','향후 로드맵'),
    'target'=>'시네마틱 디렉터, 영상 프로듀서','room'=>'그랜드 볼룸 B','day'=>2,'time'=>'15:00 - 16:30','is_keynote'=>false),

  array('id'=>'d2-s3-4','track'=>'산업 & 시뮬레이션','difficulty'=>'초보자용',
    'speaker'=>'박에너','speaker_title'=>'데이터 시각화 리드','affiliation'=>'한국전력 디지털',
    'title'=>'에너지 인프라 모니터링을 위한 3D 대시보드 구축',
    'description'=>'발전소와 송전망의 실시간 데이터를 언리얼 엔진 기반 3D 대시보드로 시각화하여 운영 효율을 높인 사례를 소개합니다.',
    'toc'=>array('IoT 데이터 연동','3D 대시보드 설계','알림 시스템 구현','운영 성과'),
    'target'=>'에너지/인프라 관리자, 데이터 엔지니어','room'=>'컨퍼런스 룸 1','day'=>2,'time'=>'15:00 - 16:30','is_keynote'=>false),
);
