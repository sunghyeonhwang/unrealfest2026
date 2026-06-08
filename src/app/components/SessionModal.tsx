import React from "react";
import { X, CalendarPlus, ArrowRight, Clock, MapPin, BarChart, User } from "lucide-react";
import { cn } from "../lib/utils";

export interface SessionData {
  id: string;
  title: string;
  time: string;
  date: string;
  track: string;
  level: string;
  speaker: {
    name: string;
    role: string;
    company: string;
  };
  desc: string;
  contents: string[];
  target: string;
  location: string;
}

interface SessionModalProps {
  isOpen: boolean;
  onClose: () => void;
  session: SessionData | null;
}

export const SessionModal: React.FC<SessionModalProps> = ({ isOpen, onClose, session }) => {
  if (!isOpen || !session) return null;

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
      {/* Backdrop */}
      <div 
        className="absolute inset-0 bg-white/80 dark:bg-[#09090b]/80 backdrop-blur-sm transition-opacity"
        onClick={onClose}
      />
      
      {/* Modal content */}
      <div className="relative bg-white dark:bg-[#111115] shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto z-10 flex flex-col transition-colors duration-300" style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 28px), calc(100% - 28px) 100%, 0 100%)" }}>
        {/* Header */}
        <div className="sticky top-0 bg-white/90 dark:bg-[#111115]/90 backdrop-blur-md border-b border-slate-200 dark:border-white/5 px-6 py-4 flex items-center justify-between z-20">
          <div className="flex items-center gap-3">
            <span className={cn(
              "px-3 py-1 rounded-full text-xs font-bold border",
              session.track === "게임 - 프로그래밍" ? "bg-track-cyan-light/20 text-track-cyan-dark border-track-cyan/30 dark:bg-track-cyan-dark/40 dark:text-track-cyan dark:border-track-cyan/30" :
              session.track === "게임 - 아트" ? "bg-track-magenta-light/20 text-track-magenta-dark border-track-magenta/30 dark:bg-track-magenta-dark/40 dark:text-track-magenta-light dark:border-track-magenta/30" :
              session.track === "미디어 & 엔터테인먼트" ? "bg-track-blue-light/20 text-track-blue-dark border-track-blue/30 dark:bg-track-blue-dark/40 dark:text-track-blue-light dark:border-track-blue/30" :
              "bg-track-orange-light/20 text-track-orange-dark border-track-orange/30 dark:bg-track-orange-dark/40 dark:text-track-orange-light dark:border-track-orange/30"
            )}>
              {session.track}
            </span>
            <span className="px-3 py-1 rounded-full bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-bold text-slate-600 dark:text-slate-300">
              {session.level}
            </span>
          </div>
          <button 
            onClick={onClose}
            className="p-2 hover:bg-slate-100 dark:hover:bg-white/5 rounded-full text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors"
          >
            <X className="w-6 h-6" />
          </button>
        </div>

        {/* Body */}
        <div className="p-6 sm:p-10">
          <h2 className="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-6 leading-tight tracking-tight">
            {session.title}
          </h2>

          <div className="flex flex-wrap gap-6 mb-10 text-sm sm:text-base text-slate-600 dark:text-slate-300">
            <div className="flex items-center gap-2">
              <Clock className="w-5 h-5 text-slate-400 dark:text-slate-500" />
              <span>{session.date} • {session.time}</span>
            </div>
            <div className="flex items-center gap-2">
              <MapPin className="w-5 h-5 text-slate-400 dark:text-slate-500" />
              <span>{session.location}</span>
            </div>
            <div className="flex items-center gap-2">
              <BarChart className="w-5 h-5 text-slate-400 dark:text-slate-500" />
              <span>{session.level}</span>
            </div>
          </div>

          <div className="grid md:grid-cols-[1fr_300px] gap-10">
            {/* Main Content */}
            <div className="space-y-8">
              <section>
                <h3 className="text-xl font-bold text-slate-900 dark:text-white mb-4">세션 소개</h3>
                <p className="text-slate-600 dark:text-slate-400 leading-relaxed">
                  {session.desc}
                </p>
              </section>

              <section>
                <h3 className="text-xl font-bold text-slate-900 dark:text-white mb-4">세션 목차</h3>
                <ul className="space-y-3">
                  {session.contents.map((item, idx) => (
                    <li key={idx} className="flex gap-3 text-slate-600 dark:text-slate-400">
                      <span className={cn(
                        "font-bold",
                        session.track === "게임 - 프로그래밍" ? "text-track-cyan-dark dark:text-track-cyan" :
                        session.track === "게임 - 아트" ? "text-track-magenta-dark dark:text-track-magenta-light" :
                        session.track === "미디어 & 엔터테인먼트" ? "text-track-blue-dark dark:text-track-blue-light" :
                        "text-track-orange-dark dark:text-track-orange-light"
                      )}>{idx + 1}.</span>
                      <span>{item}</span>
                    </li>
                  ))}
                </ul>
              </section>

              <section>
                <h3 className="text-xl font-bold text-slate-900 dark:text-white mb-4">권장 대상</h3>
                <div className="bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-none p-5 text-slate-700 dark:text-slate-300">
                  {session.target}
                </div>
              </section>
            </div>

            {/* Sidebar info */}
            <div className="space-y-6">
              <div className="bg-slate-50 dark:bg-black border border-slate-200 dark:border-white/10 rounded-none p-6 shadow-sm dark:shadow-none">
                <h3 className="text-sm font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-4">발표자</h3>
                <div className="flex items-start gap-4">
                  <div className="w-12 h-12 rounded-full bg-white dark:bg-[#1A1B23] border border-slate-200 dark:border-white/10 flex items-center justify-center flex-shrink-0 shadow-sm dark:shadow-none">
                    <User className="w-6 h-6 text-slate-400" />
                  </div>
                  <div>
                    <div className="text-lg font-bold text-slate-900 dark:text-white mb-1">{session.speaker.name}</div>
                    <div className={cn(
                      "text-sm mb-1 font-medium",
                      session.track === "게임 - 프로그래밍" ? "text-track-cyan-dark dark:text-track-cyan" :
                      session.track === "게임 - 아트" ? "text-track-magenta-dark dark:text-track-magenta-light" :
                      session.track === "미디어 & 엔터테인먼트" ? "text-track-blue-dark dark:text-track-blue-light" :
                      "text-track-orange-dark dark:text-track-orange-light"
                    )}>{session.speaker.role}</div>
                    <div className="text-xs text-slate-500 dark:text-slate-500">{session.speaker.company}</div>
                  </div>
                </div>
              </div>

              <div className="flex flex-col gap-3">
                <button className="w-full py-3 px-4 rounded-none bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-white font-medium flex items-center justify-center gap-2 transition-colors">
                  <CalendarPlus className="w-5 h-5" />
                  캘린더 추가
                </button>
                <button
                  onClick={() => { onClose(); setTimeout(() => document.getElementById("register")?.scrollIntoView({ behavior: "smooth" }), 100); }}
                  className={cn(
                    "w-full py-3 px-4 rounded-none text-white dark:text-black font-bold flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow-md",
                    session.track === "게임 - 프로그래밍" ? "bg-track-cyan-dark hover:bg-track-cyan-dark/90 dark:bg-track-cyan dark:hover:bg-track-cyan/90 dark:hover:shadow-[0_0_15px_rgba(0,193,213,0.3)]" :
                    session.track === "게임 - 아트" ? "bg-track-magenta-dark hover:bg-track-magenta-dark/90 dark:bg-track-magenta dark:hover:bg-track-magenta/90 dark:hover:shadow-[0_0_15px_rgba(221,10,178,0.3)]" :
                    session.track === "미디어 & 엔터테인먼트" ? "bg-track-blue-dark hover:bg-track-blue-dark/90 dark:bg-track-blue dark:hover:bg-track-blue/90 dark:hover:shadow-[0_0_15px_rgba(48,127,226,0.3)]" :
                    "bg-track-orange-dark hover:bg-track-orange-dark/90 dark:bg-track-orange dark:hover:bg-track-orange/90 dark:hover:shadow-[0_0_15px_rgba(255,143,28,0.3)]"
                  )}
                >
                  지금 등록하기
                  <ArrowRight className="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
