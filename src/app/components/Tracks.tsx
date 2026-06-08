import React from "react";

export const Tracks = () => {
  const tracks = [
    {
      id: "game-programming",
      title: "게임 - 프로그래밍",
      desc: "모바일, PC, 콘솔을 아우르는 차세대 게임 개발 파이프라인과 최적화 기법.",
      video: "./AAAGames-Fall2025-WebBanner_1080p30-H265-5Mbps.mp4",
      hoverCls: "group-hover:text-track-cyan dark:group-hover:text-track-cyan-light",
    },
    {
      id: "game-art",
      title: "게임 - 아트",
      desc: "최신 에셋 제작 파이프라인, 비주얼 이펙트 및 차세대 렌더링 기술.",
      video: "./unreal-engine-animation-reel.mp4",
      hoverCls: "group-hover:text-track-magenta dark:group-hover:text-track-magenta-light",
    },
    {
      id: "media",
      title: "미디어 & 엔터테인먼트",
      desc: "버추얼 프로덕션, 애니메이션, 브로드캐스트 등 영상 콘텐츠 제작의 혁신.",
      video: "./film-and-tv-hero.mp4",
      hoverCls: "group-hover:text-track-blue dark:group-hover:text-track-blue-light",
    },
    {
      id: "industry",
      title: "산업 & 시뮬레이션",
      desc: "건축, 자동차, 디지털 트윈 등 비게임 분야의 리얼타임 기술 도입 사례.",
      video: "./automotive-and-transport-hero.mp4",
      hoverCls: "group-hover:text-track-orange dark:group-hover:text-track-orange-light",
    },
  ];

  return (
    <section id="tracks" className="py-24 bg-neutral-50 dark:bg-[#0B0C10] relative transition-colors duration-300">
      <div className="max-w-7xl mx-auto px-6">
        <div className="mb-16">
          <h2 className="text-3xl md:text-5xl font-bold text-black dark:text-white mb-4 tracking-tight">주요 트랙</h2>
          <p className="text-lg text-black/60 dark:text-slate-400 max-w-2xl">
            참가자의 관심사와 산업 분야에 맞춘 4개의 주요 트랙을 운영합니다.
            각 트랙별로 가장 깊이 있는 기술 세션과 성공 사례를 만나보세요.
          </p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {tracks.map((track) => (
            <div
              key={track.id}
              className="group relative overflow-hidden bg-white dark:bg-black border-0 transition-all duration-500 shadow-sm hover:shadow-md dark:shadow-none"
              style={{ clipPath: "polygon(0 0, calc(100% - 24px) 0, 100% 24px, 100% 100%, 0 100%)" }}
            >
              <div className="absolute inset-0 z-0">
                <div className="absolute inset-0 bg-gradient-to-t from-white via-white/80 to-transparent dark:from-[#0B0C10] dark:via-[#0B0C10]/80 dark:to-transparent z-10 transition-colors duration-300"></div>
                <video
                  autoPlay
                  loop
                  muted
                  playsInline
                  className="w-full h-full object-cover opacity-20 dark:opacity-50 group-hover:scale-105 transition-transform duration-700"
                >
                  <source src={track.video} type="video/mp4" />
                </video>
              </div>
              <div className="relative z-20 p-8 h-full flex flex-col justify-end min-h-[320px]">
                <h3 className={`text-2xl font-bold text-black dark:text-white mb-3 transition-colors ${track.hoverCls}`}>
                  {track.title}
                </h3>
                <p className="text-black/60 dark:text-slate-400 text-sm leading-relaxed line-clamp-2">
                  {track.desc}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
