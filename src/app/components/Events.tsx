import React from "react";
import { Users, PartyPopper, Ticket, Trophy } from "lucide-react";

export const Events = () => {
  return (
    <section id="events" className="py-24 bg-white dark:bg-[#0B0C10] relative border-t border-slate-200 dark:border-white/5 transition-colors duration-300">
      <div className="max-w-7xl mx-auto px-6">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">부대 이벤트</h2>
          <p className="text-lg text-slate-600 dark:text-slate-400">
            강연 외에도 현장에서 즐길 수 있는 다양한 커뮤니티 프로그램이 준비되어 있습니다.
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-8">
          <div className="bg-slate-50 dark:bg-[#111115] border border-slate-200 dark:border-white/10 rounded-2xl p-8 hover:border-track-cyan/50 dark:hover:border-track-cyan/30 transition-all group shadow-sm dark:shadow-none">
            <div className="w-14 h-14 bg-track-cyan-light/20 dark:bg-track-cyan/10 rounded-xl border border-track-cyan/30 dark:border-track-cyan/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
              <Users className="w-7 h-7 text-track-cyan-dark dark:text-track-cyan" />
            </div>
            <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">커뮤니티 라운지</h3>
            <p className="text-slate-600 dark:text-slate-400 leading-relaxed">
              언리얼 엔진 개발자, 크리에이터들과 자유롭게 네트워킹할 수 있는 공간입니다.
              에픽게임즈 기술 지원팀과의 1:1 상담도 가능합니다.
            </p>
          </div>

          <div className="bg-slate-50 dark:bg-[#111115] border border-slate-200 dark:border-white/10 rounded-2xl p-8 hover:border-track-blue/50 dark:hover:border-track-blue/30 transition-all group shadow-sm dark:shadow-none">
            <div className="w-14 h-14 bg-track-blue-light/20 dark:bg-track-blue/10 rounded-xl border border-track-blue/30 dark:border-track-blue/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
              <Ticket className="w-7 h-7 text-track-blue-dark dark:text-track-blue-light" />
            </div>
            <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">체험 부스 존</h3>
            <p className="text-slate-600 dark:text-slate-400 leading-relaxed">
              최신 기술이 적용된 스폰서 및 파트너사의 데모 부스를 체험해 보세요.
              미출시 게임, 시뮬레이션, VR 콘텐츠를 직접 플레이할 수 있습니다.
            </p>
          </div>

          <div className="bg-slate-50 dark:bg-[#111115] border border-slate-200 dark:border-white/10 rounded-2xl p-8 hover:border-track-magenta/50 dark:hover:border-track-magenta/30 transition-all group shadow-sm dark:shadow-none">
            <div className="w-14 h-14 bg-track-magenta-light/20 dark:bg-track-magenta/10 rounded-xl border border-track-magenta/30 dark:border-track-magenta/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
              <PartyPopper className="w-7 h-7 text-track-magenta-dark dark:text-track-magenta-light" />
            </div>
            <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">에픽 네트워킹 파티</h3>
            <p className="text-slate-600 dark:text-slate-400 leading-relaxed">
              1일차 행사 종료 후, 현장에서 진행되는 프라이빗 파티입니다.
              시원한 음료, 다과와 함께 새로운 파트너를 만나보세요.
            </p>
          </div>

          <div className="bg-slate-50 dark:bg-[#111115] border border-slate-200 dark:border-white/10 rounded-2xl p-8 hover:border-emerald-500/50 dark:hover:border-emerald-500/30 transition-all group shadow-sm dark:shadow-none">
            <div className="w-14 h-14 bg-emerald-100/50 dark:bg-emerald-500/10 rounded-xl border border-emerald-300/30 dark:border-emerald-500/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
              <Trophy className="w-7 h-7 text-emerald-600 dark:text-emerald-400" />
            </div>
            <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-4">언리얼 챌린지</h3>
            <p className="text-slate-600 dark:text-slate-400 leading-relaxed">
              현장에서 주어진 미션을 해결하고 경품을 획득하세요.
              초보자부터 숙련 개발자까지 누구나 참여 가능한 실시간 챌린지입니다.
            </p>
          </div>
        </div>
      </div>
    </section>
  );
};
