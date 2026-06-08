import React, { useState, useMemo } from "react";
import { Search, RotateCcw, User, LayoutGrid, Columns2, ChevronDown } from "lucide-react";
import { Link } from "react-router";
import { cn } from "../lib/utils";
import { mockSessions } from "../components/Agenda";
import type { SessionData } from "../components/SessionModal";

const trackBadgeStyle: Record<string, string> = {
  "게임 - 프로그래밍": "bg-[rgba(48,127,226,0.1)] text-[#5a9be6] border border-[rgba(48,127,226,0.25)]",
  "게임 - 아트": "bg-[rgba(255,143,28,0.1)] text-[#fecb8b] border border-[rgba(255,143,28,0.25)]",
  "미디어 & 엔터테인먼트": "bg-[rgba(250,70,22,0.1)] text-[#ff8674] border border-[rgba(250,70,22,0.25)]",
  "산업 & 시뮬레이션": "bg-[rgba(221,10,178,0.1)] text-[#dd9cdf] border border-[rgba(221,10,178,0.25)]",
};

const trackLabel: Record<string, string> = {
  "게임 - 프로그래밍": "게임-프로그래밍",
  "게임 - 아트": "게임-아트",
  "미디어 & 엔터테인먼트": "미디어&엔터테인먼트",
  "산업 & 시뮬레이션": "산업&시뮬레이션",
};

const levelLabel: Record<string, string> = {
  "전체 참가자": "All Levels",
  "초보자용": "초급",
  "중급자용": "중급",
  "전문가용": "고급",
};

function getSessionCategories(session: SessionData): string[] {
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
  return tags.slice(0, 3);
}

const allCategories = [
  "전체", "AI", "Unreal Engine", "Game", "Art", "Entertainment",
  "Security", "Infra", "XR / AR", "Digital Twin", "Automotive", "MetaHuman",
];

const trackFilters = [
  { key: "all", label: "전체 트랙" },
  { key: "게임 - 프로그래밍", label: "게임-프로그래밍" },
  { key: "게임 - 아트", label: "게임-아트" },
  { key: "미디어 & 엔터테인먼트", label: "미디어&엔터테인먼트" },
  { key: "산업 & 시뮬레이션", label: "산업&시뮬레이션" },
];

const difficultyFilters = [
  { key: "all", label: "전체" },
  { key: "초보자용", label: "초보자용" },
  { key: "중급자용", label: "중급자용" },
  { key: "전문가용", label: "전문가용" },
];

const SidebarSection = ({ title, children, defaultOpen = true }: { title: string; children: React.ReactNode; defaultOpen?: boolean }) => {
  const [open, setOpen] = useState(defaultOpen);
  return (
    <div>
      <button
        onClick={() => setOpen(!open)}
        className="w-full flex items-center justify-between text-xs font-semibold text-[#71717a] uppercase tracking-[0.96px] mb-3 hover:text-white transition-colors"
      >
        {title}
        <ChevronDown className={cn("w-3.5 h-3.5 transition-transform", open ? "rotate-0" : "-rotate-90")} />
      </button>
      {open && <div className="flex flex-col gap-0">{children}</div>}
    </div>
  );
};

const FilterBtn = ({ active, onClick, children }: { active: boolean; onClick: () => void; children: React.ReactNode }) => (
  <button
    onClick={onClick}
    className={cn(
      "w-full text-left px-3 py-2.5 text-sm transition-colors",
      active
        ? "bg-[rgba(0,79,89,0.5)] text-[#9adbe8] font-semibold"
        : "bg-transparent text-[#a1a1aa] hover:text-white"
    )}
  >
    {children}
  </button>
);

export default function Sessions() {
  const [activeDay, setActiveDay] = useState<"all" | "day1" | "day2">("all");
  const [activeTrack, setActiveTrack] = useState("all");
  const [activeLevel, setActiveLevel] = useState("all");
  const [activeCategories, setActiveCategories] = useState<Set<string>>(new Set());
  const [searchQuery, setSearchQuery] = useState("");
  const [cols, setCols] = useState<2 | 3>(2);

  const toggleCategory = (cat: string) => {
    setActiveCategories((prev) => {
      const next = new Set(prev);
      if (cat === "전체") return new Set();
      if (next.has(cat)) next.delete(cat);
      else next.add(cat);
      return next;
    });
  };

  const filteredSessions = useMemo(() => {
    return mockSessions.filter((s) => {
      if (s.track === "키노트") return false;
      if (activeDay !== "all") {
        const dayMatch = activeDay === "day1" ? s.date.includes("Day 1") : s.date.includes("Day 2");
        if (!dayMatch) return false;
      }
      if (activeTrack !== "all" && s.track !== activeTrack) return false;
      if (activeLevel !== "all" && s.level !== activeLevel) return false;
      if (activeCategories.size > 0) {
        const cats = getSessionCategories(s);
        for (const selected of activeCategories) {
          if (!cats.includes(selected)) return false;
        }
      }
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase();
        const haystack = `${s.title} ${s.desc} ${s.speaker.name} ${s.speaker.company}`.toLowerCase();
        if (!haystack.includes(q)) return false;
      }
      return true;
    }).sort((a, b) => a.time.localeCompare(b.time));
  }, [activeDay, activeTrack, activeLevel, activeCategories, searchQuery]);

  const resetFilters = () => {
    setActiveTrack("all");
    setActiveLevel("all");
    setActiveCategories(new Set());
    setSearchQuery("");
    setActiveDay("all");
  };

  return (
    <div className="bg-[#09090b] min-h-screen text-white">
      {/* PAGE HEADING */}
      <section className="relative pt-24 pb-16 overflow-hidden border-b border-[#27272a]" style={{ backgroundColor: "#0e0f14" }}>
        <div className="absolute right-0 top-0 bottom-0 w-[70%] z-0">
          <img
            src="./session_hero.jpg"
            alt=""
            className="w-full h-full object-cover opacity-40"
            onError={(e) => { (e.target as HTMLImageElement).style.display = "none"; }}
          />
          <div className="absolute inset-0 bg-gradient-to-r from-[#0e0f14] via-[#0e0f14]/70 to-transparent" />
          <div className="absolute inset-0 bg-gradient-to-b from-[#0e0f14]/40 to-[#0e0f14]" />
        </div>
        <div className="relative z-10 max-w-7xl mx-auto px-6 pt-12">
          <h1
            className="text-5xl md:text-6xl mb-4 tracking-tight"
            style={{ fontFamily: "'Daeojamjil', sans-serif", fontWeight: 500 }}
          >
            아젠다
          </h1>
          <p className="text-[#90a1b9] max-w-2xl text-base leading-relaxed">
            원하는 트랙과 난이도의 세션을 찾아보고 일정을 계획하세요.
            세션 카드를 클릭하면 상세 정보를 확인할 수 있습니다.
          </p>
        </div>
      </section>

      {/* TOP BAR — 날짜 선택만 */}
      <div className="sticky top-[73px] z-40 bg-[#111115] border-b border-[#27272a]">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
          <div className="flex items-center gap-0">
            {([
              { key: "all" as const, label: "전체" },
              { key: "day1" as const, label: "Day 1 (8.20 목)" },
              { key: "day2" as const, label: "Day 2 (8.21 금)" },
            ]).map((d) => (
              <button
                key={d.key}
                onClick={() => setActiveDay(d.key)}
                className={cn(
                  "px-6 py-2.5 text-sm font-bold transition-all",
                  activeDay === d.key
                    ? "bg-[#00C1D5] text-black"
                    : "bg-transparent text-[#71717a] hover:text-white"
                )}
              >
                {d.label}
              </button>
            ))}
          </div>
          {/* 2단/3단 뷰 토글 */}
          <div className="flex items-center gap-1">
            <button
              onClick={() => setCols(2)}
              className={cn(
                "p-2 transition-colors",
                cols === 2 ? "text-white" : "text-[#71717a] hover:text-white"
              )}
              title="2단 뷰"
            >
              <Columns2 className="w-5 h-5" />
            </button>
            <button
              onClick={() => setCols(3)}
              className={cn(
                "p-2 transition-colors",
                cols === 3 ? "text-white" : "text-[#71717a] hover:text-white"
              )}
              title="3단 뷰"
            >
              <LayoutGrid className="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>

      {/* BODY: Sidebar + Cards */}
      <div className="max-w-7xl mx-auto px-6 py-8 pb-24">
        <div className="flex flex-col lg:flex-row gap-8">
          {/* LEFT SIDEBAR */}
          <aside className="lg:w-[280px] flex-shrink-0">
            <div className="lg:sticky lg:top-[140px] bg-[#0e0f14] border border-[#27272a] p-6 space-y-6">
              {/* Search */}
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#71717a]" />
                <input
                  type="text"
                  placeholder="세션 검색..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full bg-[#0e0f14] border border-[#27272a] pl-10 pr-4 py-3 text-sm text-white placeholder:text-[#71717a] focus:outline-none focus:border-[#00C1D5] transition-colors"
                />
              </div>

              {/* 트랙 */}
              <SidebarSection title="트랙" defaultOpen={false}>
                {trackFilters.map((t) => (
                  <FilterBtn key={t.key} active={activeTrack === t.key} onClick={() => setActiveTrack(t.key)}>
                    {t.label}
                  </FilterBtn>
                ))}
              </SidebarSection>

              {/* 난이도 */}
              <SidebarSection title="난이도" defaultOpen={false}>
                {difficultyFilters.map((d) => (
                  <FilterBtn key={d.key} active={activeLevel === d.key} onClick={() => setActiveLevel(d.key)}>
                    {d.label}
                  </FilterBtn>
                ))}
              </SidebarSection>

              {/* 기술 분야 */}
              <SidebarSection title="기술 분야">
                {allCategories.map((cat) => {
                  const isAll = cat === "전체";
                  const isActive = isAll ? activeCategories.size === 0 : activeCategories.has(cat);
                  return (
                    <FilterBtn key={cat} active={isActive} onClick={() => toggleCategory(cat)}>
                      {cat}
                    </FilterBtn>
                  );
                })}
              </SidebarSection>

              {/* Reset */}
              <button
                onClick={resetFilters}
                className="w-full py-2.5 text-sm text-[#71717a] border border-[#27272a] text-center hover:text-white hover:border-white/20 transition-colors"
              >
                필터 초기화
              </button>
            </div>
          </aside>

          {/* RIGHT CONTENT */}
          <div className="flex-grow min-w-0">
            <p className="text-sm text-[#a1a1aa] mb-6">
              총 <span className="text-[#00C1D5] font-bold">{filteredSessions.length}</span>개의 세션
            </p>

            {filteredSessions.length > 0 ? (
              <div className={cn("grid gap-5", cols === 2 ? "md:grid-cols-2" : "md:grid-cols-2 lg:grid-cols-3")}>
                {filteredSessions.map((session) => {
                  const categories = getSessionCategories(session);
                  return (
                    <Link
                      key={session.id}
                      to={`/session/${session.id}`}
                      className="group bg-[#0e0f14] p-6 cursor-pointer hover:bg-[#111115] transition-all flex flex-col"
                    >
                      <div className="flex items-center justify-between mb-3">
                        <span className={cn("px-3 py-0.5 text-xs font-medium", trackBadgeStyle[session.track])}>
                          {trackLabel[session.track]}
                        </span>
                        <span className="text-xs text-[#71717a]">
                          {levelLabel[session.level] || session.level}
                        </span>
                      </div>

                      <div className="flex flex-wrap gap-1.5 mb-3">
                        {categories.map((cat) => (
                          <span key={cat} className="px-2.5 py-0.5 text-[10px] text-[#71717a] border border-[#27272a] bg-[#0e0f14] rounded-full tracking-wide">
                            {cat}
                          </span>
                        ))}
                      </div>

                      <h3 className="text-lg font-bold text-[#fafafa] mb-2 group-hover:text-[#00C1D5] transition-colors leading-snug flex-grow tracking-tight">
                        {session.title}
                      </h3>

                      <p className="text-sm text-[#a1a1aa] mb-4 line-clamp-2 leading-relaxed">
                        {session.desc}
                      </p>

                      <div className="flex items-center gap-3 mb-3">
                        <div className="w-8 h-8 rounded-full bg-[#0e0f14] border border-[#27272a] flex items-center justify-center flex-shrink-0">
                          <User className="w-4 h-4 text-[#71717a]" />
                        </div>
                        <div className="min-w-0">
                          <div className="text-sm font-semibold text-[#fafafa] truncate">{session.speaker.name}</div>
                          <div className="text-xs text-[#71717a] truncate">{session.speaker.role} · {session.speaker.company}</div>
                        </div>
                      </div>

                      <div className="flex items-center gap-3 text-xs text-[#71717a] pt-3 border-t border-white/5">
                        <span>{session.date.includes("Day 1") ? "8월 20일" : "8월 21일"}</span>
                        <span className="w-px h-3 bg-[#27272a]" />
                        <span>{session.time}</span>
                        <span className="w-px h-3 bg-[#27272a]" />
                        <span>{session.location}</span>
                      </div>
                    </Link>
                  );
                })}
              </div>
            ) : (
              <div className="py-20 text-center border border-dashed border-[#27272a]">
                <p className="text-[#71717a] mb-2">선택한 조건에 맞는 세션이 없습니다.</p>
                <button
                  onClick={resetFilters}
                  className="text-[#00C1D5] text-sm font-medium hover:underline"
                >
                  필터 초기화하기
                </button>
              </div>
            )}
          </div>
        </div>
      </div>

    </div>
  );
}
