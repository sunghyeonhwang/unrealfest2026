import React from "react";
import { Stamp, Share2, MessageSquareHeart, Gift } from "lucide-react";

const events = [
  {
    icon: <Stamp className="w-7 h-7 text-[#dd0ab2] dark:text-[#dd9cdf]" />,
    title: "스탬프 투어",
    desc: "스폰서 및 파트너 부스를 둘러보고 스탬프를 모아보세요. 일정 개수 이상 모은 참가자 전원에게 한정판 굿즈를 증정합니다.",
    image:
      "https://images.unsplash.com/photo-1560439514-4e9645039924?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxldmVudCUyMGJvb3RoJTIwY29uZmVyZW5jZSUyMG5ldHdvcmtpbmd8ZW58MXx8fHwxNzc4MTE5NDY1fDA&ixlib=rb-4.1.0&q=80&w=1080",
  },
  {
    icon: <Share2 className="w-7 h-7 text-[#fa4616] dark:text-[#ff8674]" />,
    title: "SNS 인증 이벤트",
    desc: "현장 포토존에서 사진을 찍고 #UnrealFestSeoul2026 해시태그와 함께 공개 게시물을 업로드하면 추첨을 통해 상품을 드립니다.",
    image:
      "https://images.unsplash.com/photo-1761195689615-9469b65dac01?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwyfHxldmVudCUyMGJvb3RoJTIwY29uZmVyZW5jZSUyMG5ldHdvcmtpbmd8ZW58MXx8fHwxNzc4MTE5NDY1fDA&ixlib=rb-4.1.0&q=80&w=1080",
  },
  {
    icon: <MessageSquareHeart className="w-7 h-7 text-[#ff8f1c] dark:text-[#fecb8b]" />,
    title: "강연 후기 이벤트",
    desc: "행사 종료 후 공식 설문지를 통해 세션 후기를 남겨주세요. 정성스러운 후기를 작성해 주신 분들 중 추첨을 통해 경품을 증정합니다.",
    image:
      "https://images.unsplash.com/photo-1768982418146-c1e7f5087d9d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHw1fHxldmVudCUyMGJvb3RoJTIwY29uZmVyZW5jZSUyMG5ldHdvcmtpbmd8ZW58MXx8fHwxNzc4MTE5NDY1fDA&ixlib=rb-4.1.0&q=80&w=1080",
  },
  {
    icon: <Gift className="w-7 h-7 text-[#307fe2] dark:text-[#92c1e9]" />,
    title: "럭키 드로우",
    desc: "행사 2일차 클로징 세션 현장에서 진행되는 즉석 추첨 이벤트입니다. 현장에 끝까지 함께해 주신 참가자분들께 푸짐한 경품을 드립니다.",
    image:
      "https://images.unsplash.com/photo-1774729200208-53562848bcbd?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHw2fHxldmVudCUyMGJvb3RoJTIwY29uZmVyZW5jZSUyMG5ldHdvcmtpbmd8ZW58MXx8fHwxNzc4MTE5NDY1fDA&ixlib=rb-4.1.0&q=80&w=1080",
  },
];

export const EventPrograms = () => {
  return (
    <section
      id="event-programs"
      className="py-24 bg-slate-50 dark:bg-[#0B0C10] relative border-t border-slate-200 dark:border-white/5 transition-colors duration-300"
    >
      <div className="max-w-7xl mx-auto px-6">
        <div className="mb-16">
          <h2 className="text-3xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">
            이벤트
          </h2>
          <p className="text-lg text-slate-600 dark:text-slate-400 max-w-2xl">
            언리얼 페스트 서울 2026을 더욱 특별하게 만들어 줄, 참가자분들을 위한 다양한 참여 이벤트를 준비했습니다.
          </p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {events.map((ev) => (
            <div
              key={ev.title}
              className="group relative overflow-hidden rounded-2xl bg-white dark:bg-black border border-slate-200 dark:border-white/5 hover:border-cyan-500/50 dark:hover:border-cyan-500/50 transition-all duration-500 shadow-sm hover:shadow-md dark:shadow-none"
            >
              <div className="absolute inset-0 z-0">
                <div className="absolute inset-0 bg-gradient-to-t from-white via-white/80 to-transparent dark:from-[#0B0C10] dark:via-[#0B0C10]/80 dark:to-transparent z-10 transition-colors duration-300"></div>
                <img
                  src={ev.image}
                  alt={ev.title}
                  className="w-full h-full object-cover opacity-10 dark:opacity-50 group-hover:scale-105 transition-transform duration-700"
                />
              </div>
              <div className="relative z-20 p-8 h-full flex flex-col justify-end min-h-[320px]">
                <div className="mb-4 bg-white/80 dark:bg-black/50 w-16 h-16 rounded-2xl border border-slate-200 dark:border-white/10 flex items-center justify-center backdrop-blur-md shadow-sm dark:shadow-none">
                  {ev.icon}
                </div>
                <h3 className="text-2xl font-bold text-slate-900 dark:text-white mb-3 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">
                  {ev.title}
                </h3>
                <p className="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                  {ev.desc}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};
