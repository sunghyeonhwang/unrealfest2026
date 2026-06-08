import React from "react";
import { useParams, Link } from "react-router";
import { ArrowLeft, Calendar, Clock, MapPin, Plus, Share2, User } from "lucide-react";
import { cn } from "../lib/utils";
import { mockSessions } from "../components/Agenda";

const trackBadgeStyle: Record<string, string> = {
  "키노트": "bg-[#00C1D5] text-white",
  "게임 - 프로그래밍": "bg-[rgba(48,127,226,0.1)] text-[#5a9be6] border border-[rgba(48,127,226,0.25)]",
  "게임 - 아트": "bg-[rgba(255,143,28,0.1)] text-[#fecb8b] border border-[rgba(255,143,28,0.25)]",
  "미디어 & 엔터테인먼트": "bg-[rgba(250,70,22,0.1)] text-[#ff8674] border border-[rgba(250,70,22,0.25)]",
  "산업 & 시뮬레이션": "bg-[rgba(221,10,178,0.1)] text-[#dd9cdf] border border-[rgba(221,10,178,0.25)]",
};

const trackLabel: Record<string, string> = {
  "키노트": "키노트",
  "게임 - 프로그래밍": "게임-프로그래밍",
  "게임 - 아트": "게임-아트",
  "미디어 & 엔터테인먼트": "미디어&엔터테인먼트",
  "산업 & 시뮬레이션": "산업&시뮬레이션",
};

const levelLabel: Record<string, string> = {
  "전체 참가자": "전체",
  "초보자용": "초보자",
  "중급자용": "중급",
  "전문가용": "전문가",
};

function getSessionCategories(session: typeof mockSessions[0]): string[] {
  const tags: string[] = [];
  const text = `${session.title} ${session.desc} ${session.contents.join(" ")}`.toLowerCase();
  if (text.includes("언리얼") || text.includes("unreal")) tags.push("Unreal Engine");
  if (text.includes("ai") || text.includes("인공지능")) tags.push("AI");
  if (text.includes("아트") || text.includes("라이팅") || text.includes("캐릭터") || text.includes("머티리얼") || text.includes("이펙트")) tags.push("Art");
  if (text.includes("xr") || text.includes("vr") || text.includes("ar ")) tags.push("XR / AR");
  if (text.includes("디지털 트윈")) tags.push("Digital Twin");
  if (text.includes("자율주행") || text.includes("자동차")) tags.push("Automotive");
  if (text.includes("메타휴먼")) tags.push("MetaHuman");
  if (text.includes("게임") || text.includes("game")) tags.push("Game");
  if (tags.length === 0) tags.push("Unreal Engine");
  return tags.slice(0, 3);
}

export default function SessionDetail() {
  const { id } = useParams<{ id: string }>();
  const session = mockSessions.find((s) => s.id === id);

  if (!session) {
    return (
      <div className="bg-[#09090b] min-h-screen text-white pt-32 text-center">
        <p className="text-[#71717a]">세션을 찾을 수 없습니다.</p>
        <Link to="/sessions" className="text-[#00C1D5] mt-4 inline-block hover:underline">아젠다로 돌아가기</Link>
      </div>
    );
  }

  const categories = getSessionCategories(session);
  const dateStr = session.date.includes("Day 1") ? "2026-08-20" : "2026-08-21";

  // 같은 트랙의 다른 세션 2개를 관련 세션으로
  const relatedSessions = mockSessions
    .filter((s) => s.id !== session.id && (s.track === session.track || s.track === "키노트"))
    .slice(0, 2);

  return (
    <div className="bg-[#09090b] min-h-screen text-white">
      {/* 상단 헤딩 영역 */}
      <section className="bg-[#0e0f14] border-b border-[#27272a] pt-24 pb-10">
        <div className="max-w-7xl mx-auto px-6">
          <Link to="/sessions" className="inline-flex items-center gap-2 text-sm text-[#71717a] hover:text-white transition-colors mb-6">
            <ArrowLeft className="w-4 h-4" />
            아젠다로 돌아가기
          </Link>

          {/* 제목 */}
          <h1 className="text-3xl md:text-4xl font-bold text-[#fafafa] mb-6 tracking-tight leading-tight">
            {session.title}
          </h1>

          {/* 메타 정보 */}
          <div className="flex flex-wrap items-center gap-6 text-sm text-[#a1a1aa] mb-6">
            <span className="flex items-center gap-1.5">
              <Calendar className="w-4 h-4" />
              {dateStr}
            </span>
            <span className="flex items-center gap-1.5">
              <Clock className="w-4 h-4" />
              {session.time}
            </span>
            <span className="flex items-center gap-1.5">
              <MapPin className="w-4 h-4" />
              {session.location}
            </span>
          </div>

        </div>
      </section>

      {/* 본문 */}
      <section className="max-w-7xl mx-auto px-6 py-12">
        <div className="grid lg:grid-cols-[1fr_360px] gap-12">
          {/* 좌측 콘텐츠 */}
          <div className="space-y-10">
            {/* 세션 소개 */}
            <div>
              <h2 className="text-xl font-bold text-white mb-4">세션 소개</h2>
              <p className="text-[#a1a1aa] leading-relaxed">{session.desc}</p>
            </div>

            {/* 목차 */}
            <div>
              <h2 className="text-xl font-bold text-white mb-4">세션 목차</h2>
              <ul className="space-y-2">
                {session.contents.map((item, idx) => (
                  <li key={idx} className="flex items-start gap-2 text-[#a1a1aa]">
                    <span className="text-[#00C1D5] mt-1">•</span>
                    {item}
                  </li>
                ))}
              </ul>
            </div>

            {/* 권장 대상 */}
            <div>
              <h2 className="text-xl font-bold text-white mb-4">권장 대상</h2>
              <ul className="space-y-2">
                {session.target.split(",").map((item, idx) => (
                  <li key={idx} className="flex items-start gap-2 text-[#a1a1aa]">
                    <span className="text-[#00C1D5] mt-1">•</span>
                    {item.trim()}
                  </li>
                ))}
              </ul>
            </div>
          </div>

          {/* 우측 사이드바 */}
          <div className="space-y-6">
            {/* 연사 소개 */}
            <div className="bg-[#0e0f14] p-6">
              <div className="flex items-start justify-between mb-4">
                <div>
                  <div className="text-xl font-bold text-[#fafafa]">{session.speaker.name}</div>
                  <div className="text-sm text-[#a1a1aa]">{session.speaker.role}</div>
                  <div className="text-xs text-[#71717a]">{session.speaker.company}</div>
                </div>
                <div className="w-16 h-16 rounded-full bg-[#1a1a1f] border border-[#27272a] flex items-center justify-center flex-shrink-0 overflow-hidden">
                  {session.id === "keynote-1" ? (
                    <img src="https://unrealsummit16.cafe24.com/2026/images/Tim_Sweeney%201.png" alt={session.speaker.name} className="w-full h-full object-cover" />
                  ) : session.id === "keynote-2" ? (
                    <img src="https://unrealsummit16.cafe24.com/2026/images/i19891652231.png" alt={session.speaker.name} className="w-full h-full object-cover" />
                  ) : (
                    <User className="w-8 h-8 text-[#71717a]" />
                  )}
                </div>
              </div>
              <p className="text-sm text-[#a1a1aa] leading-relaxed">
                에픽게임즈 스토어의 포트폴리오 전략을 총괄하며, 에픽게임즈 퍼블리싱, 무료 게임 프로그램, 그리고 스토어 콘텐츠 등 전반에 걸쳐 에픽게임즈가 지원하는 다양한 프로그램의 방향을 결정합니다. 내부 주요 팀들과 협업해 우수한 개발자를 발굴 및 지원하고, 지속 가능한 비즈니스 성장을 돕고 있습니다.
              </p>
            </div>

            {/* 액션 버튼 */}
            <div className="space-y-3">
              <button className="w-full flex items-center justify-center gap-2 py-3 border border-[#27272a] text-[#a1a1aa] text-sm font-medium hover:text-white hover:border-white/20 transition-colors">
                <Plus className="w-4 h-4" />
                일정에 추가하기
              </button>
              <button className="w-full flex items-center justify-center gap-2 py-3 border border-[#27272a] text-[#a1a1aa] text-sm font-medium hover:text-white hover:border-white/20 transition-colors">
                <Share2 className="w-4 h-4" />
                세션 공유하기
              </button>
            </div>

            {/* 등록 CTA */}
            <div className="p-6 text-center">
              <p className="text-sm text-[#a1a1aa] mb-4">언리얼 페스트 등록 전이신가요?</p>
              <Link
                to="/#register"
                className="inline-flex items-center justify-center w-full py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-bold transition-colors"
                style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%)" }}
              >
                지금 등록하기
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* 키워드 */}
      <section className="max-w-7xl mx-auto px-6 pb-12">
        <h2 className="text-xl font-bold text-white mb-4">키워드</h2>
        <div className="flex flex-wrap gap-2">
          <span className={cn("px-3 py-1.5 text-sm font-medium", trackBadgeStyle[session.track])}>
            {trackLabel[session.track]}
          </span>
          <span className="px-3 py-1.5 text-sm font-semibold bg-[#27272a] text-[#f4f4f5]">
            {levelLabel[session.level] || session.level}
          </span>
          {categories.map((cat) => (
            <span key={cat} className="px-3 py-1.5 text-sm text-[#a1a1aa] border border-[#27272a]">
              {cat}
            </span>
          ))}
        </div>
      </section>

      {/* 관련 세션 */}
      {relatedSessions.length > 0 && (
        <section className="max-w-7xl mx-auto px-6 pb-24">
          <h2 className="text-xl font-bold text-white mb-6">관련 세션</h2>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            {relatedSessions.map((s) => (
              <Link
                key={s.id}
                to={`/session/${s.id}`}
                className="bg-[#0e0f14] p-5 hover:bg-[#111115] transition-colors flex flex-col gap-2"
              >
                <span className={cn("self-start px-2.5 py-0.5 text-xs font-medium", trackBadgeStyle[s.track])}>
                  {trackLabel[s.track]}
                </span>
                <h3 className="text-base font-bold text-[#fafafa] tracking-tight">{s.title}</h3>
                <span className="text-xs text-[#71717a]">{s.time} · {s.location}</span>
              </Link>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
