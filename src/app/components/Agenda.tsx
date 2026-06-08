import React, { useRef, useState, useCallback } from "react";
import { ArrowRight, ChevronLeft, ChevronRight, User } from "lucide-react";
import { Link, useNavigate } from "react-router";
import { cn } from "../lib/utils";
import type { SessionData } from "./SessionModal";

export const mockSessions: SessionData[] = [
  { id: "keynote-1", title: "키노트 1: 리얼타임 3D가 만드는 다음 10년", time: "10:00 - 11:00", date: "2026. 8. 20 (목) - Day 1", track: "키노트", level: "전체 참가자", speaker: { name: "팀 스위니 (Tim Sweeney)", role: "Founder & CEO", company: "에픽게임즈" }, desc: "에픽게임즈의 창립자 팀 스위니가 직접 전하는 언리얼 엔진과 리얼타임 3D의 미래 비전.", contents: ["언리얼 엔진 6.0 비전", "리얼타임 3D의 산업적 확장", "오픈 메타버스와 표준", "Q&A"], target: "전체 참가자", location: "Main Stage A" },
  { id: "keynote-2", title: "키노트 2: 크리에이터 이코노미와 언리얼 에디터의 진화", time: "11:00 - 12:00", date: "2026. 8. 20 (목) - Day 1", track: "키노트", level: "전체 참가자", speaker: { name: "킴 리브레 (Kim Libreri)", role: "CTO", company: "에픽게임즈" }, desc: "에픽게임즈 CTO 킴 리브레가 소개하는 차세대 크리에이터 도구와 워크플로우.", contents: ["UEFN 로드맵", "페이블(Fab) 마켓플레이스", "메타휴먼의 미래", "오픈 라이브 데모"], target: "전체 참가자", location: "Main Stage A" },
  { id: "s1-3", title: "PC 및 콘솔 최적화 노하우 A to Z", time: "14:00 - 15:00", date: "2026. 8. 20 (목) - Day 1", track: "게임 - 프로그래밍", level: "전문가용", speaker: { name: "박최적", role: "성능 엔지니어", company: "NCSOFT" }, desc: "콘솔 환경에서 프레임드랍을 최소화하고 60프레임을 고정하기 위한 다양한 병목 현상 분석 및 해결 기법을 공유합니다.", contents: ["프로파일링 방법론", "CPU/GPU 바운드", "메모리 최적화", "Q&A"], target: "게임 프로그래머", location: "Track Room 1" },
  { id: "s2-3", title: "루멘을 활용한 역동적인 시네마틱 라이팅 연출", time: "14:00 - 15:00", date: "2026. 8. 20 (목) - Day 1", track: "게임 - 아트", level: "중급자용", speaker: { name: "한빛", role: "시네마틱 라이팅 아티스트", company: "스마일게이트" }, desc: "동적 글로벌 일루미네이션인 루멘의 세팅법부터 컷신 연출 시 몰입감을 극대화하는 조명 배치 노하우를 공개합니다.", contents: ["루멘 기초 구조", "실내외 조명 세팅", "시퀀서 연동", "시연"], target: "라이팅 아티스트", location: "Track Room 2" },
  { id: "s3-3", title: "리얼타임 애니메이션 파이프라인 혁신 사례", time: "14:00 - 15:00", date: "2026. 8. 20 (목) - Day 1", track: "미디어 & 엔터테인먼트", level: "전문가용", speaker: { name: "정애니", role: "애니메이션 리드", company: "픽셀 스튜디오" }, desc: "기존 오프라인 렌더링 파이프라인을 언리얼 엔진 기반의 리얼타임으로 전환하며 얻은 효율성과 품질 향상 사례를 소개합니다.", contents: ["파이프라인 전환 과정", "리얼타임 렌더링 팁", "제작 시간 단축 성과", "향후 전망"], target: "애니메이터", location: "Track Room 3" },
  { id: "s4-3", title: "디지털 트윈: 대규모 도시 스마트 시티 구축기", time: "14:00 - 15:00", date: "2026. 8. 20 (목) - Day 1", track: "산업 & 시뮬레이션", level: "전문가용", speaker: { name: "김시티", role: "스마트시티 기획자", company: "도시공사 테크센터" }, desc: "언리얼 엔진을 활용하여 도시 전체의 데이터를 시각화하고 시뮬레이션하는 디지털 트윈 프로젝트의 노하우를 공유합니다.", contents: ["디지털 트윈 정의", "GIS 데이터 처리", "최적화", "Q&A"], target: "공공기관, 도시 계획가", location: "Track Room 4" },
  { id: "d1-s2-1", title: "언리얼 엔진 멀티플레이어 네트워크 프로그래밍 심화", time: "15:30 - 16:30", date: "2026. 8. 20 (목) - Day 1", track: "게임 - 프로그래밍", level: "전문가용", speaker: { name: "이넷코", role: "네트워크 프로그래머", company: "넥슨" }, desc: "대규모 멀티플레이어 환경에서 레플리케이션, 프리딕션, 서버 오소리타티브 모델을 효율적으로 구현하는 방법을 다룹니다.", contents: ["레플리케이션 심화", "프리딕션과 보정", "대역폭 최적화", "실전 사례"], target: "네트워크 프로그래머", location: "Track Room 1" },
  { id: "d1-s2-2", title: "나나이트 메시와 버추얼 섀도우 맵 실전 활용", time: "15:30 - 16:30", date: "2026. 8. 20 (목) - Day 1", track: "게임 - 아트", level: "전문가용", speaker: { name: "오폴리", role: "테크니컬 아티스트", company: "크래프톤" }, desc: "나나이트 메시의 LOD 자동화와 버추얼 섀도우 맵을 활용한 고품질 비주얼 파이프라인 구축 노하우를 소개합니다.", contents: ["나나이트 파이프라인", "VSM 설정 가이드", "퍼포먼스 트레이드오프", "Q&A"], target: "테크니컬 아티스트", location: "Track Room 2" },
  { id: "d1-s2-3", title: "버추얼 프로덕션: LED 볼륨 촬영 현장 가이드", time: "15:30 - 16:30", date: "2026. 8. 20 (목) - Day 1", track: "미디어 & 엔터테인먼트", level: "중급자용", speaker: { name: "강촬영", role: "VP 슈퍼바이저", company: "덱스터 스튜디오" }, desc: "In-Camera VFX 환경에서 언리얼 엔진과 nDisplay를 활용한 LED 월 촬영 세팅부터 색보정까지의 전 과정을 공유합니다.", contents: ["nDisplay 구성", "카메라 트래킹 연동", "색보정 파이프라인", "현장 트러블슈팅"], target: "촬영 감독, VP 엔지니어", location: "Track Room 3" },
  { id: "d1-s2-4", title: "자율주행 시뮬레이션 환경 구축과 센서 모델링", time: "15:30 - 16:30", date: "2026. 8. 20 (목) - Day 1", track: "산업 & 시뮬레이션", level: "중급자용", speaker: { name: "차자율", role: "시뮬레이션 엔지니어", company: "현대오토에버" }, desc: "언리얼 엔진 기반으로 라이다, 카메라, 레이더 센서를 모델링하고 자율주행 알고리즘을 검증하는 시뮬레이션 환경을 소개합니다.", contents: ["센서 모델링 기법", "날씨/조명 시뮬레이션", "시나리오 자동 생성", "검증 파이프라인"], target: "자율주행 엔지니어", location: "Track Room 4" },
  { id: "d1-s3-1", title: "GAS(Gameplay Ability System) 완전 정복", time: "17:00 - 18:00", date: "2026. 8. 20 (목) - Day 1", track: "게임 - 프로그래밍", level: "중급자용", speaker: { name: "한능력", role: "게임플레이 프로그래머", company: "펄어비스" }, desc: "언리얼 엔진의 Gameplay Ability System을 활용하여 확장성 높은 스킬/버프 시스템을 설계하고 구현하는 실전 가이드입니다.", contents: ["GAS 아키텍처 이해", "어빌리티와 이펙트 설계", "어트리뷰트 시스템", "실전 적용 패턴"], target: "게임플레이 프로그래머", location: "Track Room 1" },
  { id: "d1-s3-2", title: "메타휴먼으로 만드는 하이퀄리티 캐릭터 워크플로", time: "17:00 - 18:00", date: "2026. 8. 20 (목) - Day 1", track: "게임 - 아트", level: "초보자용", speaker: { name: "민캐릭", role: "캐릭터 아티스트", company: "에픽게임즈 코리아" }, desc: "메타휴먼 크리에이터로 포토리얼 캐릭터를 제작하고 커스터마이징하는 워크플로를 초보자 눈높이에서 설명합니다.", contents: ["메타휴먼 기초", "페이셜 커스터마이징", "의상/헤어 설정", "애니메이션 연동"], target: "캐릭터 아티스트", location: "Track Room 2" },
  { id: "d1-s3-3", title: "언리얼 엔진으로 만드는 인터랙티브 건축 시각화", time: "17:00 - 18:00", date: "2026. 8. 20 (목) - Day 1", track: "미디어 & 엔터테인먼트", level: "초보자용", speaker: { name: "배건축", role: "건축 시각화 디렉터", company: "아키비즈 스튜디오" }, desc: "건축 설계 데이터를 언리얼 엔진에서 인터랙티브 워크스루로 전환하는 과정과 클라이언트 프레젠테이션 활용 사례를 소개합니다.", contents: ["데이터 임포트", "머티리얼 세팅", "인터랙션 블루프린트", "VR 프레젠테이션"], target: "건축 디자이너", location: "Track Room 3" },
  { id: "d1-s3-4", title: "국방 훈련 시뮬레이터: 언리얼 엔진 적용 사례", time: "17:00 - 18:00", date: "2026. 8. 20 (목) - Day 1", track: "산업 & 시뮬레이션", level: "전문가용", speaker: { name: "정국방", role: "시뮬레이션 PM", company: "LIG넥스원" }, desc: "군사 훈련용 시뮬레이터에 언리얼 엔진을 적용하여 몰입감 높은 가상 훈련 환경을 구축한 실전 프로젝트 사례를 공유합니다.", contents: ["요구사항 분석", "지형/환경 생성", "멀티유저 훈련 시나리오", "성과 평가"], target: "국방/시뮬레이션 개발자", location: "Track Room 4" },
  { id: "s5-1", title: "모바일 MMORPG 서버 아키텍처와 언리얼 엔진 최적화", time: "10:00 - 11:30", date: "2026. 8. 21 (금) - Day 2", track: "게임 - 프로그래밍", level: "전문가용", speaker: { name: "최서버", role: "백엔드 리드", company: "메가게임즈" }, desc: "수만 명이 동시 접속하는 모바일 환경에서 언리얼 엔진 클라이언트와 서버 간의 데이터 동기화 및 렌더링 최적화 기법을 공유합니다.", contents: ["서버 아키텍처 구조", "네트워크 패킷 최적화", "모바일 디바이스 프로파일링", "라이브 서비스 이슈"], target: "서버 프로그래머", location: "Track Room 1" },
  { id: "s5-2", title: "언리얼 6.0 프로시저럴 환경 생성 심화", time: "10:00 - 11:30", date: "2026. 8. 21 (금) - Day 2", track: "게임 - 아트", level: "중급자용", speaker: { name: "권절차", role: "환경 아티스트", company: "에픽게임즈" }, desc: "PCG 프레임워크를 심도있게 파고들어 복잡한 룰 기반의 월드 생성 자동화 팁을 공유합니다.", contents: ["PCG 노드 설계", "바이옴 기반 생성", "월드 파티션 연동", "시연"], target: "레벨 디자이너", location: "Track Room 2" },
  { id: "s5-3", title: "XR 라이브 콘서트: 인터랙티브 콘텐츠 기획", time: "10:00 - 11:30", date: "2026. 8. 21 (금) - Day 2", track: "미디어 & 엔터테인먼트", level: "초보자용", speaker: { name: "임가상", role: "크리에이티브 디렉터", company: "메타스테이지" }, desc: "언리얼 기반 XR 콘서트 무대 구축 및 관객 참여형 인터랙션 구현 기법을 소개합니다.", contents: ["XR 기획 개요", "네트워크 멀티플레이 이벤트", "조명/무대 제어", "성공 사례"], target: "콘서트 기획자", location: "Track Room 3" },
  { id: "s5-4", title: "공장 설비 및 공정 시뮬레이션의 최적화", time: "10:00 - 11:30", date: "2026. 8. 21 (금) - Day 2", track: "산업 & 시뮬레이션", level: "전문가용", speaker: { name: "윤설비", role: "시뮬레이션 연구원", company: "한국제조기술" }, desc: "실제 기계 공장의 공정을 물리 엔진과 연동하여 시뮬레이션하고 병목 구간을 진단하는 실증 사례.", contents: ["카오스 피직스 응용", "데이터 동기화", "공정 시각화", "결과 분석"], target: "스마트팩토리 기획자", location: "Track Room 4" },
  { id: "d2-s2-1", title: "ECS와 매스 엔티티로 대규모 시뮬레이션 구현하기", time: "13:00 - 14:30", date: "2026. 8. 21 (금) - Day 2", track: "게임 - 프로그래밍", level: "전문가용", speaker: { name: "송매스", role: "엔진 프로그래머", company: "넷마블" }, desc: "언리얼의 매스 엔티티 시스템을 활용하여 수천 개의 AI 에이전트를 동시에 시뮬레이션하는 기법과 최적화 전략을 공유합니다.", contents: ["Mass Entity 아키텍처", "프래그먼트 설계", "프로세서 최적화", "실전 벤치마크"], target: "엔진 프로그래머", location: "Track Room 1" },
  { id: "d2-s2-2", title: "서브스턴스와 언리얼의 프로시저럴 머티리얼 워크플로", time: "13:00 - 14:30", date: "2026. 8. 21 (금) - Day 2", track: "게임 - 아트", level: "중급자용", speaker: { name: "김머티", role: "머티리얼 아티스트", company: "시프트업" }, desc: "서브스턴스 디자이너에서 제작한 프로시저럴 머티리얼을 언리얼 엔진에 최적화하여 적용하는 전체 워크플로를 소개합니다.", contents: ["프로시저럴 머티리얼 설계", "파라미터 노출 전략", "언리얼 머티리얼 통합", "라이브 데모"], target: "머티리얼 아티스트", location: "Track Room 2" },
  { id: "d2-s2-3", title: "실시간 모션 캡처와 라이브 퍼포먼스 기술", time: "13:00 - 14:30", date: "2026. 8. 21 (금) - Day 2", track: "미디어 & 엔터테인먼트", level: "중급자용", speaker: { name: "홍모캡", role: "모션캡처 디렉터", company: "버추얼 크루" }, desc: "실시간 모션 캡처 장비와 언리얼 엔진 라이브 링크를 연동하여 라이브 퍼포먼스를 실현하는 파이프라인을 공개합니다.", contents: ["모캡 장비 세팅", "Live Link 연동", "리타게팅 최적화", "라이브 시연"], target: "애니메이터", location: "Track Room 3" },
  { id: "d2-s2-4", title: "의료 시뮬레이션: 수술 훈련 플랫폼 개발기", time: "13:00 - 14:30", date: "2026. 8. 21 (금) - Day 2", track: "산업 & 시뮬레이션", level: "중급자용", speaker: { name: "이메디", role: "의료 시뮬레이션 리드", company: "메디컬 테크놀로지" }, desc: "언리얼 엔진 기반 수술 시뮬레이터 개발 과정에서의 햅틱 피드백 연동, 해부학 모델링, 물리 인터랙션 구현 경험을 공유합니다.", contents: ["해부학 모델 구축", "햅틱 디바이스 연동", "물리 시뮬레이션", "임상 검증 사례"], target: "의료 IT 개발자", location: "Track Room 4" },
  { id: "d2-s3-1", title: "언리얼 엔진 AI와 비헤이비어 트리 마스터 클래스", time: "15:00 - 16:30", date: "2026. 8. 21 (금) - Day 2", track: "게임 - 프로그래밍", level: "중급자용", speaker: { name: "류에이", role: "AI 프로그래머", company: "카카오게임즈" }, desc: "비헤이비어 트리와 EQS를 결합하여 전략적으로 행동하는 게임 AI를 설계하고 디버깅하는 실전 테크닉을 다룹니다.", contents: ["비헤이비어 트리 설계 패턴", "EQS 활용법", "AI 퍼셉션 시스템", "디버깅 도구 활용"], target: "AI 프로그래머", location: "Track Room 1" },
  { id: "d2-s3-2", title: "언리얼 엔진 이펙트: 나이아가라 파티클 심화", time: "15:00 - 16:30", date: "2026. 8. 21 (금) - Day 2", track: "게임 - 아트", level: "전문가용", speaker: { name: "장이펙", role: "VFX 아티스트", company: "데브시스터즈" }, desc: "나이아가라 시스템의 고급 모듈을 활용하여 대규모 파티클 이펙트를 효율적으로 제작하고 최적화하는 기법을 공유합니다.", contents: ["시뮬레이션 스테이지 심화", "데이터 인터페이스 활용", "GPU 파티클 최적화", "시연"], target: "VFX 아티스트", location: "Track Room 2" },
  { id: "d2-s3-3", title: "AI 기반 자동 시퀀서 편집과 카메라 연출", time: "15:00 - 16:30", date: "2026. 8. 21 (금) - Day 2", track: "미디어 & 엔터테인먼트", level: "전문가용", speaker: { name: "윤시퀀", role: "시네마틱 디렉터", company: "에픽게임즈" }, desc: "머신러닝 기반 카메라 자동 연출과 시퀀서 편집 자동화 도구의 프로토타입을 소개하고 향후 로드맵을 공유합니다.", contents: ["AI 카메라 연출 원리", "시퀀서 자동화 API", "프로토타입 시연", "향후 로드맵"], target: "시네마틱 디렉터", location: "Track Room 3" },
  { id: "d2-s3-4", title: "에너지 인프라 모니터링을 위한 3D 대시보드 구축", time: "15:00 - 16:30", date: "2026. 8. 21 (금) - Day 2", track: "산업 & 시뮬레이션", level: "초보자용", speaker: { name: "박에너", role: "데이터 시각화 리드", company: "한국전력 디지털" }, desc: "발전소와 송전망의 실시간 데이터를 언리얼 엔진 기반 3D 대시보드로 시각화하여 운영 효율을 높인 사례를 소개합니다.", contents: ["IoT 데이터 연동", "3D 대시보드 설계", "알림 시스템 구현", "운영 성과"], target: "에너지/인프라 관리자", location: "Track Room 4" },
];

const trackBadgeStyle: Record<string, string> = {
  "키노트": "bg-[#00C1D5] text-black",
  "게임 - 프로그래밍": "bg-[#307fe2] text-black",
  "게임 - 아트": "bg-[#FF8F1C] text-black",
  "미디어 & 엔터테인먼트": "bg-[#FA4616] text-black",
  "산업 & 시뮬레이션": "bg-[#DD0AB2] text-black",
};

const trackLabel: Record<string, string> = {
  "키노트": "키노트",
  "게임 - 프로그래밍": "프로그래밍",
  "게임 - 아트": "아트",
  "미디어 & 엔터테인먼트": "미디어&엔터테인먼트",
  "산업 & 시뮬레이션": "산업&시뮬레이션",
};

const avatarColor: Record<string, string> = {
  "게임 - 프로그래밍": "bg-[#5a9be6]",
  "게임 - 아트": "bg-[#fecb8b]",
  "미디어 & 엔터테인먼트": "bg-[#ff8674]",
  "산업 & 시뮬레이션": "bg-[#dd9cdf]",
};

const levelLabel: Record<string, string> = {
  "전체 참가자": "전체",
  "초보자용": "초급",
  "중급자용": "중급",
  "전문가용": "고급",
};

const SessionCard = ({ session, onClick }: { session: SessionData; onClick?: () => void }) => (
  <div
    className="flex-shrink-0 w-[320px] min-h-[240px] bg-[#131418] rounded-[6px] px-5 py-[22px] flex flex-col gap-2 cursor-pointer hover:bg-[#1a1b20] transition-colors"
    onClick={onClick}
  >
    {/* 배지 + 시간 */}
    <div className="flex items-center gap-2 flex-wrap">
      <span className={cn("px-1.5 py-1 text-[12px] font-extrabold rounded-[4px]", trackBadgeStyle[session.track])}>
        {trackLabel[session.track]}
      </span>
    </div>

    {/* 제목 */}
    <h4 className="text-[18px] font-bold text-white leading-[28px] tracking-tight line-clamp-3 flex-grow" style={{ fontFamily: "'Inter Tight', sans-serif" }}>
      {session.title}
    </h4>

    {/* 스피커 */}
    <div className="flex items-center gap-2.5 mt-auto">
      <div className={cn("w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0", avatarColor[session.track] || "bg-[#00C1D5]")}>
        <User className="w-5 h-5 text-black/60" />
      </div>
      <div className="min-w-0">
        <div className="text-[13px] font-bold text-white truncate">{session.speaker.name}</div>
        <div className="text-[13px] text-white/80 truncate">{session.speaker.company}</div>
      </div>
    </div>
  </div>
);

const DayCarousel = ({ title, sessions, onCardClick }: { title: string; sessions: SessionData[]; onCardClick: () => void }) => {
  const scrollRef = useRef<HTMLDivElement>(null);
  const isDragging = useRef(false);
  const startX = useRef(0);
  const scrollLeft = useRef(0);
  const hasMoved = useRef(false);

  const scroll = (dir: "left" | "right") => {
    if (!scrollRef.current) return;
    scrollRef.current.scrollBy({ left: dir === "left" ? -340 : 340, behavior: "smooth" });
  };

  const onMouseDown = useCallback((e: React.MouseEvent) => {
    if (!scrollRef.current) return;
    isDragging.current = true;
    hasMoved.current = false;
    startX.current = e.pageX - scrollRef.current.offsetLeft;
    scrollLeft.current = scrollRef.current.scrollLeft;
    scrollRef.current.style.cursor = "grabbing";
  }, []);

  const onMouseMove = useCallback((e: React.MouseEvent) => {
    if (!isDragging.current || !scrollRef.current) return;
    e.preventDefault();
    const x = e.pageX - scrollRef.current.offsetLeft;
    const walk = x - startX.current;
    if (Math.abs(walk) > 5) hasMoved.current = true;
    scrollRef.current.scrollLeft = scrollLeft.current - walk;
  }, []);

  const onMouseUp = useCallback(() => {
    isDragging.current = false;
    if (scrollRef.current) scrollRef.current.style.cursor = "grab";
  }, []);

  return (
    <div className="mb-10">
      <div className="max-w-7xl mx-auto px-6 flex items-center justify-between mb-6">
        <h3 className="text-xl font-bold text-white tracking-tight">{title}</h3>
        <div className="flex items-center gap-2">
          <button onClick={() => scroll("left")} className="w-9 h-9 border border-[#27272a] flex items-center justify-center text-[#71717a] hover:text-white hover:border-white/30 transition-colors">
            <ChevronLeft className="w-5 h-5" />
          </button>
          <button onClick={() => scroll("right")} className="w-9 h-9 border border-[#27272a] flex items-center justify-center text-[#71717a] hover:text-white hover:border-white/30 transition-colors">
            <ChevronRight className="w-5 h-5" />
          </button>
        </div>
      </div>
      <div className="max-w-7xl mx-auto px-6">
        <div className="relative">
          {/* 좌우 그라데이션 오버레이 */}
          {/* <div className="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-[#09090b] to-transparent z-10 pointer-events-none" />
          <div className="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-[#09090b] to-transparent z-10 pointer-events-none" /> */}
          <div ref={scrollRef} className="overflow-x-auto pb-2 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden cursor-grab select-none" onMouseDown={onMouseDown} onMouseMove={onMouseMove} onMouseUp={onMouseUp} onMouseLeave={onMouseUp}>
            <div className="flex gap-4">
              {[...sessions, ...sessions, ...sessions].map((s, i) => (
                <SessionCard key={`${s.id}-${i}`} session={s} onClick={onCardClick} />
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export const Agenda = () => {
  const navigate = useNavigate();
  const keynote = mockSessions.find(s => s.id === "keynote-1")!;
  const day1Sessions = mockSessions.filter(s => s.track !== "키노트" && s.date.includes("Day 1"));
  const day2Sessions = mockSessions.filter(s => s.track !== "키노트" && s.date.includes("Day 2"));
  const goToSchedule = () => navigate("/schedule");

  return (
    <section id="agenda" className="py-24 bg-[#09090b] relative border-t border-white/5">
      <div className="max-w-7xl mx-auto px-6 mb-12">
        <h2 className="text-3xl md:text-5xl text-white mb-4 tracking-tight">아젠다</h2>
        <p className="text-[#90a1b9]">
          최신 기술과 새로운 아이디어, 다양한 산업 분야의 세션을 만나보세요.
        </p>
      </div>

      {/* 키노트 피처드 카드 */}
      <div className="max-w-7xl mx-auto px-6 mb-12">
        <div className="grid md:grid-cols-2 gap-6">
          {mockSessions.filter(s => s.track === "키노트").map((k) => (
            <Link key={k.id} to={`/session/${k.id}`} className="block bg-[#00C1D5] p-6 hover:bg-[#00b0c2] transition-colors relative overflow-hidden min-h-[240px] rounded-[6px]">
              <span className="absolute top-6 right-6 text-sm font-bold text-black/70 z-10">{k.time}</span>
              <div className="relative z-10 max-w-[65%]">
                <div className="flex items-center gap-2 mb-4">
                  <span className="px-2.5 py-0.5 text-[11px] font-bold bg-black/20 text-white">키노트</span>
                  <span className="px-2 py-0.5 text-[11px] font-semibold bg-black/20 text-white">{levelLabel[k.level]}</span>
                </div>
                <h3 className="text-xl font-bold text-black mb-6 tracking-tight leading-snug">
                  {k.title}
                </h3>
                <div>
                  <div className="text-sm font-bold text-black">{k.speaker.name}</div>
                  <div className="text-xs text-black/60">{k.speaker.role} · {k.speaker.company}</div>
                </div>
              </div>
              <div className="absolute right-4 bottom-0 w-[35%] hidden md:flex items-end justify-center">
                <img
                  src={k.id === "keynote-1" ? "./Tim_Sweeney_1.png" : k.id === "keynote-2" ? "./keynote2.png" : ""}
                  alt={k.speaker.name}
                  className="h-full object-cover object-top"
                  onError={(e) => { (e.target as HTMLImageElement).style.display = "none"; }}
                />
              </div>
            </Link>
          ))}
        </div>
      </div>

      {/* Day 1 캐러셀 */}
      <DayCarousel
        title="Day. 1 | 8월 20일 (목)"
        sessions={day1Sessions}
        onCardClick={goToSchedule}
      />

      {/* Day 2 캐러셀 */}
      <DayCarousel
        title="Day. 2 | 8월 21일 (금)"
        sessions={day2Sessions}
        onCardClick={goToSchedule}
      />

      {/* CTA */}
      <div className="text-center mt-4">
        <Link
          to="/sessions"
          className="inline-flex items-center gap-2 px-10 py-3.5 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-medium transition-colors"
          style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 12px), calc(100% - 12px) 100%, 0 100%)" }}
        >
          전체 세션 보기
          <ArrowRight className="w-4 h-4" />
        </Link>
      </div>
    </section>
  );
};
