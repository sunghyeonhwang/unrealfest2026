import React, { useState, useMemo } from "react";
import { LayoutGrid, List, SlidersHorizontal, X, User } from "lucide-react";
import { Link } from "react-router";
import { cn } from "../lib/utils";
import { mockSessions } from "../components/Agenda";
import type { SessionData } from "../components/SessionModal";

const trackColors: Record<string, { bg: string; text: string; border: string; dot: string }> = {
  "키노트": { bg: "bg-[rgba(0,193,213,0.1)]", text: "text-[#00C1D5]", border: "border-[rgba(0,193,213,0.3)]", dot: "bg-[#00C1D5]" },
  "게임 - 프로그래밍": { bg: "bg-[rgba(48,127,226,0.1)]", text: "text-[#5a9be6]", border: "border-[rgba(48,127,226,0.25)]", dot: "bg-[#5a9be6]" },
  "게임 - 아트": { bg: "bg-[rgba(255,143,28,0.1)]", text: "text-[#fecb8b]", border: "border-[rgba(255,143,28,0.25)]", dot: "bg-[#fecb8b]" },
  "미디어 & 엔터테인먼트": { bg: "bg-[rgba(250,70,22,0.1)]", text: "text-[#ff8674]", border: "border-[rgba(250,70,22,0.25)]", dot: "bg-[#ff8674]" },
  "산업 & 시뮬레이션": { bg: "bg-[rgba(221,10,178,0.1)]", text: "text-[#dd9cdf]", border: "border-[rgba(221,10,178,0.25)]", dot: "bg-[#dd9cdf]" },
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
  "초보자용": "초급",
  "중급자용": "중급",
  "전문가용": "고급",
};

const topics = [
  "AI", "Unreal Engine", "Game", "Art", "Entertainment",
  "Security", "Infra", "XR / AR", "Digital Twin", "Automotive", "MetaHuman",
];

function getSessionTopics(session: SessionData): string[] {
  const tags: string[] = [];
  const text = `${session.title} ${session.desc} ${session.contents.join(" ")}`.toLowerCase();
  if (text.includes("언리얼") || text.includes("unreal")) tags.push("Unreal Engine");
  if (text.includes("ai") || text.includes("인공지능") || text.includes("머신러닝")) tags.push("AI");
  if (text.includes("게임") || text.includes("game")) tags.push("Game");
  if (text.includes("아트") || text.includes("라이팅") || text.includes("캐릭터") || text.includes("머티리얼") || text.includes("이펙트")) tags.push("Art");
  if (text.includes("애니메이션") || text.includes("시네마틱") || text.includes("영상") || text.includes("콘서트") || text.includes("모션")) tags.push("Entertainment");
  if (text.includes("xr") || text.includes("vr") || text.includes("ar ")) tags.push("XR / AR");
  if (text.includes("디지털 트윈") || text.includes("digital twin")) tags.push("Digital Twin");
  if (text.includes("자율주행") || text.includes("automotive") || text.includes("자동차")) tags.push("Automotive");
  if (text.includes("메타휴먼") || text.includes("metahuman")) tags.push("MetaHuman");
  if (text.includes("보안") || text.includes("security")) tags.push("Security");
  if (text.includes("인프라") || text.includes("infra") || text.includes("서버") || text.includes("네트워크")) tags.push("Infra");
  if (tags.length === 0) tags.push("Unreal Engine");
  return tags;
}

const tracks = ["키노트", "게임 - 프로그래밍", "게임 - 아트", "미디어 & 엔터테인먼트", "산업 & 시뮬레이션"];
const trackFilters = [
  { key: "all", label: "전체" },
  ...tracks.map(t => ({ key: t, label: trackLabel[t] })),
];

// 시간 슬롯 추출
function getTimeSlots(sessions: SessionData[]) {
  const slots = [...new Set(sessions.map(s => s.time))].sort();
  return slots;
}

// 트랙뷰 세션 카드
const TrackSessionCard = ({ session }: { session: SessionData }) => {
  const c = trackColors[session.track] || trackColors["키노트"];
  return (
    <Link
      to={`/session/${session.id}`}
      className={cn("block p-5 border transition-all hover:bg-[#111115]", c.border, "bg-[#0e0f14]")}
    >
      <div className="flex items-center gap-2 mb-2">
        <span className={cn("px-2 py-0.5 text-[11px] font-bold", c.bg, c.text)}>
          {trackLabel[session.track]}
        </span>
        <span className="px-2 py-0.5 text-[11px] font-semibold bg-[#27272a] text-[#f4f4f5]">
          {levelLabel[session.level]}
        </span>
      </div>
      <h3 className="text-base font-bold text-[#fafafa] mb-2 leading-snug tracking-tight">
        {session.title}
      </h3>
      <p className="text-xs text-[#a1a1aa] line-clamp-2 mb-3">{session.desc}</p>
      <div className="flex items-center gap-2">
        <div className={cn("w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0", c.dot)}>
          <User className="w-3 h-3 text-black/60" />
        </div>
        <span className="text-xs text-[#a1a1aa]">{session.speaker.name} · {session.speaker.company}</span>
      </div>
    </Link>
  );
};

// 그리드뷰 세션 셀
const GridCell = ({ session }: { session: SessionData }) => {
  const c = trackColors[session.track] || trackColors["키노트"];
  return (
    <Link
      to={`/session/${session.id}`}
      className="block bg-[#0e0f14] p-5 hover:bg-[#111115] transition-colors h-full min-h-[240px] flex flex-col gap-2"
    >
      <div className="flex items-center gap-2 flex-wrap">
        <span className={cn("px-2.5 py-0.5 text-[11px] font-bold", c.bg, c.text)}>
          {trackLabel[session.track]}
        </span>
        <span className="px-2 py-0.5 text-[11px] font-semibold bg-[#27272a] text-[#f4f4f5]">
          {levelLabel[session.level]}
        </span>
      </div>
      <h4 className="text-[15px] font-bold text-[#fafafa] leading-snug tracking-tight line-clamp-3 flex-grow">
        {session.title}
      </h4>
      <div className="flex items-center gap-2.5 mt-auto">
        <div className={cn("w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0", c.dot)}>
          <User className="w-4 h-4 text-black/60" />
        </div>
        <div className="min-w-0">
          <div className="text-sm font-medium text-[#fafafa] truncate">{session.speaker.name}</div>
          <div className="text-xs text-[#71717a] truncate">{session.speaker.company}</div>
        </div>
      </div>
    </Link>
  );
};

export default function Schedule() {
  const [activeDay, setActiveDay] = useState<"day1" | "day2">("day1");
  const [viewMode, setViewMode] = useState<"track" | "grid">("track");
  const [activeTrack, setActiveTrack] = useState("all");
  const [activeLevel, setActiveLevel] = useState("all");
  const [activeTopics, setActiveTopics] = useState<Set<string>>(new Set());
  const [filterOpen, setFilterOpen] = useState(false);

  const hasFilter = activeTrack !== "all" || activeLevel !== "all" || activeTopics.size > 0;
  const resetFilters = () => { setActiveTrack("all"); setActiveLevel("all"); setActiveTopics(new Set()); };

  const toggleTopic = (t: string) => {
    setActiveTopics(prev => {
      const next = new Set(prev);
      if (next.has(t)) next.delete(t); else next.add(t);
      return next;
    });
  };

  const isDay1 = activeDay === "day1";
  const daySessions = useMemo(() =>
    mockSessions.filter(s => isDay1 ? s.date.includes("Day 1") : s.date.includes("Day 2")),
    [isDay1]
  );

  const filteredSessions = useMemo(() =>
    daySessions.filter(s => {
      if (activeTrack !== "all" && s.track !== activeTrack) return false;
      if (activeLevel !== "all" && s.level !== activeLevel) return false;
      if (activeTopics.size > 0) {
        const st = getSessionTopics(s);
        for (const t of activeTopics) {
          if (!st.includes(t)) return false;
        }
      }
      return true;
    }),
    [daySessions, activeTrack, activeLevel, activeTopics]
  );

  const timeSlots = getTimeSlots(daySessions);

  // 그리드뷰용 데이터
  const gridTracks = tracks.filter(t => t !== "키노트");
  const gridTimeSlots = getTimeSlots(daySessions.filter(s => s.track !== "키노트"));
  const keynotes = daySessions.filter(s => s.track === "키노트");

  return (
    <div className="bg-[#09090b] min-h-screen text-white">
      {/* 헤딩 */}
      <section className="relative pt-24 pb-12 border-b border-[#27272a]" style={{ backgroundColor: "#0e0f14" }}>
        <div className="max-w-7xl mx-auto px-6 pt-12">
          <h1 className="text-5xl md:text-6xl mb-4 tracking-tight" style={{ fontFamily: "'Daeojamjil', sans-serif", fontWeight: 500 }}>
            아젠다
          </h1>
          <p className="text-[#90a1b9] max-w-2xl text-base leading-relaxed">
            최신 기술과 새로운 아이디어, 다양한 산업 분야의 세션을 만나보세요.
          </p>
        </div>
      </section>

      {/* 컨트롤 바 */}
      <div className="sticky top-[73px] z-40 bg-[#111115] border-b border-[#27272a]">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between gap-4 flex-wrap">
          {/* 날짜 선택 */}
          <div className="flex items-center gap-4 flex-wrap">
            <div className="flex">
              <button
                onClick={() => setActiveDay("day1")}
                className={cn("px-5 py-2.5 text-sm font-bold transition-all", activeDay === "day1" ? "bg-[#00C1D5] text-black" : "text-[#71717a] hover:text-white")}
              >
                Day 1 · 8.20 (목)
              </button>
              <button
                onClick={() => setActiveDay("day2")}
                className={cn("px-5 py-2.5 text-sm font-bold transition-all", activeDay === "day2" ? "bg-[#00C1D5] text-black" : "text-[#71717a] hover:text-white")}
              >
                Day 2 · 8.21 (금)
              </button>
            </div>
          </div>

          <div className="flex items-center gap-3">
            {/* 뷰 전환 */}
            <div className="flex items-center gap-1 border border-[#27272a]">
              <button
                onClick={() => setViewMode("track")}
                className={cn("px-3 py-2 transition-colors", viewMode === "track" ? "bg-white text-black" : "text-[#71717a] hover:text-white")}
                title="트랙뷰"
              >
                <List className="w-5 h-5" />
              </button>
              <button
                onClick={() => setViewMode("grid")}
                className={cn("px-3 py-2 transition-colors", viewMode === "grid" ? "bg-white text-black" : "text-[#71717a] hover:text-white")}
                title="그리드뷰"
              >
                <LayoutGrid className="w-5 h-5" />
              </button>
            </div>
            {/* 필터 버튼 */}
            <div className="relative">
              <button
                onClick={() => setFilterOpen(!filterOpen)}
                className={cn("px-4 py-2 text-sm font-medium flex items-center gap-1.5 border transition-colors", hasFilter ? "border-[#00C1D5] text-[#00C1D5]" : "border-[#27272a] text-[#a1a1aa] hover:text-white hover:border-white/20")}
              >
                <SlidersHorizontal className="w-4 h-4" />
                Filter
                {hasFilter && <span className="w-1.5 h-1.5 rounded-full bg-[#00C1D5]" />}
              </button>

              {/* 드롭다운 패널 */}
              {filterOpen && (
                <div className="absolute right-0 top-full mt-2 w-[420px] bg-[#111115]/80 backdrop-blur-xl border border-white/10 shadow-2xl z-50">
                  <div className="p-6">
                    <div className="flex items-center justify-between mb-6">
                      <h2 className="text-base font-bold text-white">Filter</h2>
                      <div className="flex items-center gap-3">
                        <button onClick={resetFilters} className="text-xs text-[#a1a1aa] underline hover:text-white">Reset</button>
                        <button onClick={() => setFilterOpen(false)} className="text-[#a1a1aa] hover:text-white">
                          <X className="w-5 h-5" />
                        </button>
                      </div>
                    </div>

                    <div className="mb-6">
                      <h3 className="text-sm font-bold text-white mb-3">트랙</h3>
                      <div className="grid grid-cols-2 gap-2">
                        {trackFilters.map(t => (
                          <label key={t.key} className="flex items-center gap-2.5 cursor-pointer py-1">
                            <input type="checkbox" checked={activeTrack === t.key} onChange={() => setActiveTrack(activeTrack === t.key ? "all" : t.key)} className="w-4 h-4 rounded text-[#00C1D5] focus:ring-[#00C1D5] bg-transparent border-[#27272a]" />
                            <span className={cn("text-sm", activeTrack === t.key ? "text-white" : "text-[#a1a1aa]")}>{t.label}</span>
                          </label>
                        ))}
                      </div>
                    </div>

                    <div className="mb-6">
                      <h3 className="text-sm font-bold text-white mb-3">난이도</h3>
                      <div className="grid grid-cols-2 gap-2">
                        {[
                          { key: "all", label: "전체" },
                          { key: "초보자용", label: "초보자용" },
                          { key: "중급자용", label: "중급자용" },
                          { key: "전문가용", label: "전문가용" },
                        ].map(l => (
                          <label key={l.key} className="flex items-center gap-2.5 cursor-pointer py-1">
                            <input type="checkbox" checked={activeLevel === l.key} onChange={() => setActiveLevel(activeLevel === l.key ? "all" : l.key)} className="w-4 h-4 rounded text-[#00C1D5] focus:ring-[#00C1D5] bg-transparent border-[#27272a]" />
                            <span className={cn("text-sm", activeLevel === l.key ? "text-white" : "text-[#a1a1aa]")}>{l.label}</span>
                          </label>
                        ))}
                      </div>
                    </div>

                    <div className="mb-6">
                      <h3 className="text-sm font-bold text-white mb-3">토픽</h3>
                      <div className="grid grid-cols-2 gap-2">
                        {topics.map(t => (
                          <label key={t} className="flex items-center gap-2.5 cursor-pointer py-1">
                            <input type="checkbox" checked={activeTopics.has(t)} onChange={() => toggleTopic(t)} className="w-4 h-4 rounded text-[#00C1D5] focus:ring-[#00C1D5] bg-transparent border-[#27272a]" />
                            <span className={cn("text-sm", activeTopics.has(t) ? "text-white" : "text-[#a1a1aa]")}>{t}</span>
                          </label>
                        ))}
                      </div>
                    </div>

                    <button onClick={() => setFilterOpen(false)} className="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-2.5 font-bold text-sm transition-all">
                      적용하기
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* 콘텐츠 */}
      <div className="max-w-7xl mx-auto px-6 py-8 pb-24">

        {/* ── 트랙뷰 ── */}
        {viewMode === "track" && (
          <div>
            {timeSlots.map(time => {
              const sessionsInSlot = filteredSessions.filter(s => s.time === time);
              if (sessionsInSlot.length === 0) return null;

              return (
                <div key={time} className="flex border-b border-[#27272a]">
                  {/* 좌측 시간 */}
                  <div className="w-[120px] md:w-[160px] flex-shrink-0 py-6 pr-6">
                    <div className="text-lg font-bold text-white tracking-tight sticky top-[140px]">{time}</div>
                  </div>

                  {/* 우측 세션 리스트 */}
                  <div className="flex-grow border-l border-[#27272a] divide-y divide-[#27272a]">
                    {sessionsInSlot.map(s => {
                      const c = trackColors[s.track] || trackColors["키노트"];
                      const isKeynote = s.track === "키노트";
                      return (
                        <Link
                          key={s.id}
                          to={`/session/${s.id}`}
                          className={cn("block p-6 hover:bg-[#0e0f14] transition-colors", isKeynote && "bg-[rgba(0,193,213,0.03)]")}
                        >
                          <div className="flex items-center gap-2 mb-2">
                            <span className={cn("px-2 py-0.5 text-[11px] font-bold", c.bg, c.text)}>
                              {trackLabel[s.track]}
                            </span>
                            <span className="px-2 py-0.5 text-[11px] font-semibold bg-[#27272a] text-[#f4f4f5]">
                              {levelLabel[s.level]}
                            </span>
                          </div>
                          <h3 className={cn("font-bold text-[#fafafa] mb-2 tracking-tight leading-snug", isKeynote ? "text-xl" : "text-base")}>
                            {s.title}
                          </h3>
                          <p className="text-sm text-[#a1a1aa] mb-3 line-clamp-2">{s.desc}</p>
                          <div className="flex items-center gap-2">
                            <div className={cn("w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0", c.dot)}>
                              <User className="w-3.5 h-3.5 text-black/60" />
                            </div>
                            <span className="text-sm text-[#a1a1aa]">{s.speaker.name}</span>
                            <span className="text-xs text-[#71717a]">{s.speaker.company}</span>
                          </div>
                        </Link>
                      );
                    })}
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {/* ── 그리드뷰 (타임테이블) ── */}
        {viewMode === "grid" && (
          <div className="overflow-x-auto">
            <table className="w-full min-w-[900px] border-collapse">
              <thead>
                <tr>
                  <th className="w-[100px] p-3 text-left text-xs font-bold text-[#71717a] uppercase border-b border-[#27272a] sticky left-0 bg-[#09090b] z-10">
                    시간
                  </th>
                  {gridTracks.map(track => (
                    <th key={track} className="p-3 text-center text-xs font-bold border-b border-[#27272a]">
                      <span className={cn(trackColors[track].text)}>
                        {trackLabel[track]}
                      </span>
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {/* 키노트 행 */}
                {keynotes.map(k => (
                  <tr key={k.id} className="border-b border-[#27272a]">
                    <td className="p-3 text-sm font-bold text-white align-top sticky left-0 bg-[#09090b] z-10">
                      {k.time}
                    </td>
                    <td colSpan={4} className="p-2">
                      <Link
                        to={`/session/${k.id}`}
                        className="block bg-[#00C1D5] hover:bg-[#00b0c2] p-6 rounded-[6px] transition-all relative overflow-hidden"
                      >
                        <div className="relative z-10 max-w-[70%]">
                          <div className="flex items-center gap-2 mb-2">
                            <span className="px-2 py-0.5 text-[11px] font-bold bg-black/20 text-white">키노트</span>
                            <span className="px-2 py-0.5 text-[11px] font-semibold bg-black/20 text-white">{levelLabel[k.level]}</span>
                          </div>
                          <h3 className="text-lg font-bold text-black mb-3 tracking-tight leading-snug">{k.title}</h3>
                          <div>
                            <div className="text-sm font-bold text-black">{k.speaker.name}</div>
                            <div className="text-xs text-black/60">{k.speaker.role} · {k.speaker.company}</div>
                          </div>
                        </div>
                        <div className="absolute right-4 bottom-0 w-[25%] hidden md:flex items-end justify-center">
                          <img
                            src={k.id === "keynote-1" ? "https://unrealsummit16.cafe24.com/2026/images/Tim_Sweeney%201.png" : k.id === "keynote-2" ? "https://unrealsummit16.cafe24.com/2026/images/i19891652231.png" : ""}
                            alt={k.speaker.name}
                            className="h-32 object-cover object-top"
                            onError={(e) => { (e.target as HTMLImageElement).style.display = "none"; }}
                          />
                        </div>
                      </Link>
                    </td>
                  </tr>
                ))}
                {/* 일반 세션 행 */}
                {gridTimeSlots.map(time => {
                  const sessionsInSlot = daySessions.filter(s => s.time === time && s.track !== "키노트");
                  if (sessionsInSlot.length === 0) return null;
                  return (
                    <tr key={time} className="border-b border-[#27272a]">
                      <td className="p-3 text-sm font-bold text-white align-top sticky left-0 bg-[#09090b] z-10">
                        {time}
                      </td>
                      {gridTracks.map(track => {
                        const session = sessionsInSlot.find(s => s.track === track);
                        return (
                          <td key={track} className="p-2 align-top">
                            {session ? <GridCell session={session} /> : <div className="h-full" />}
                          </td>
                        );
                      })}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* 트랙 범례 (그리드뷰) */}
        {viewMode === "grid" && (
          <div className="flex flex-wrap gap-4 mt-8 pt-6 border-t border-[#27272a]">
            {tracks.map(t => (
              <div key={t} className="flex items-center gap-1.5 text-xs text-[#a1a1aa]">
                <span className={cn("w-2.5 h-2.5 rounded-full", trackColors[t].dot)} />
                {trackLabel[t]}
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
