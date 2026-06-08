import React from "react";

const goldSponsors = [
  { name: "Gold Sponsor 1", src: "./logo1.svg" },
  { name: "Gold Sponsor 2", src: "./logo2.svg" },
];

const silverSponsors = [
  { name: "Silver Sponsor 1", src: "./logo3.svg" },
  { name: "Silver Sponsor 2", src: "./logo4.svg" },
  { name: "Silver Sponsor 3", src: "./logo7.svg" },
  { name: "Silver Sponsor 4", src: "./logo8.svg" },
];

export const Sponsors = () => {
  return (
    <section id="sponsors" className="py-24 bg-neutral-50 dark:bg-[#0B0C10] relative transition-colors duration-300">
      <div className="max-w-7xl mx-auto px-6">
        <div className="mb-16">
          <h2 className="text-3xl md:text-5xl font-bold text-black dark:text-white mb-4 tracking-tight">스폰서</h2>
          <p className="text-lg text-black/60 dark:text-slate-400">
            언리얼 페스트 서울 2026을 함께 만들어가는 파트너사입니다.
          </p>
        </div>

        <div className="space-y-16">
          {/* Gold Sponsor */}
          <div>
            <h3 className="text-center text-amber-600 dark:text-amber-500 font-bold tracking-[0.2em] mb-8 text-sm">GOLD SPONSORS</h3>
            <div className="grid md:grid-cols-2 gap-6 lg:gap-8">
              {goldSponsors.map((s, i) => (
                <div key={i} className="h-40 md:h-48 bg-white dark:bg-gradient-to-br dark:from-[#1c1c21] dark:to-[#111115] border border-amber-200 dark:border-amber-500/20 hover:border-amber-400 dark:hover:border-amber-500/50 rounded-none flex items-center justify-center transition-all group shadow-sm dark:shadow-none">
                  <img src={s.src} alt={s.name} className="w-64 h-20 object-contain dark:invert transition-opacity" />
                </div>
              ))}
            </div>
          </div>

          {/* Silver Sponsor */}
          <div>
            <h3 className="text-center text-black/60 dark:text-slate-400 font-bold tracking-[0.2em] mb-8 text-sm">SILVER SPONSORS</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
              {silverSponsors.map((s, i) => (
                <div key={i} className="h-28 md:h-32 bg-white dark:bg-gradient-to-br dark:from-[#16161c] dark:to-[#0d0d11] border border-black/10 dark:border-white/10 hover:border-black/15 dark:hover:border-slate-400/50 rounded-none flex items-center justify-center transition-all group shadow-sm dark:shadow-none">
                  <img src={s.src} alt={s.name} className="w-32 h-12 object-contain dark:invert transition-opacity" />
                </div>
              ))}
            </div>
          </div>
        </div>
        
        <div className="mt-20 text-center">
          <a
            href="#/sponsors"
            className="inline-flex items-center px-8 py-3.5 bg-[#27272a] hover:bg-[#3f3f46] text-white font-semibold transition-all duration-200"
          >
            자세히보기
          </a>
        </div>
      </div>
    </section>
  );
};
