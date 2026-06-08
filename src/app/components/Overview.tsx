import React from "react";
import { LayoutGrid, Users, Zap, Video } from "lucide-react";

export const Overview = () => {
  const features = [
    {
      icon: <LayoutGrid className="w-6 h-6 text-[#00C1D5]" />,
      title: "최신 기술과 워크플로",
      desc: "언리얼 엔진의 최신 기능과 차세대 리얼타임 제작 환경을 직접 확인해 보세요.",
    },
    {
      icon: <Users className="w-6 h-6 text-[#00C1D5]" />,
      title: "전문가 인사이트",
      desc: "다양한 산업 분야의 전문가들이 공유하는 실제 프로젝트 사례와 제작 인사이트를 만나보세요.",
    },
    {
      icon: <Zap className="w-6 h-6 text-[#00C1D5]" />,
      title: "에픽 에코시스템 경험",
      desc: "언리얼 엔진과 메타휴먼, UEFN, 팹 등 에픽 에코시스템을 현장에서 경험해 보세요.",
    },
    {
      icon: <Video className="w-6 h-6 text-[#00C1D5]" />,
      title: "일부 세션 온라인 생중계",
      desc: "키노트와 일부 세션을 온라인 생중계로 함께 만나보세요.",
    },
  ];

  return (
    <section id="overview" className="py-24 bg-[#09090b] relative border-t border-white/5">
      <div className="max-w-7xl mx-auto px-6 relative z-10">
        <div className="grid lg:grid-cols-[1fr_1.2fr] gap-12 items-start">
          <div>
            <img
              src="https://unrealsummit16.cafe24.com/2026/ufs26/overview_text.svg"
              alt="Unreal Ideas Start Here"
              className="mb-6" style={{ width: "420px", maxWidth: "100%" }}
            />
            <div className="space-y-4 text-[#a1a1aa] leading-relaxed text-[18px]" style={{ fontFamily: "'Daeojamjil', sans-serif", fontWeight: 400 }}>
              <p>
                언리얼 페스트 서울 2026에서 언리얼 엔진과 에픽 에코시스템이 만들어가는 리얼타임 3D의 미래를 경험해 보세요.
              </p>
              <p>
                게임, 영화 및 TV, 애니메이션, 자동차, 시뮬레이션 등 산업 전반의 최신 제작 기술과 혁신 사례를 한자리에서 만나볼 수 있습니다.
              </p>
              <p>
                AI 기반 제작 워크플로, 차세대 그래픽, 버추얼 프로덕션, 애니메이션, 디지털 트윈까지. 빠르게 변화하는 리얼타임 기술의 흐름과 현업 전문가들의 실전 프로젝트 사례 및 인사이트를 통해 새로운 가능성을 직접 확인해 보세요.
              </p>
            </div>
          </div>

          <div className="grid sm:grid-cols-2 gap-6">
            {features.map((feature, idx) => (
              <div
                key={idx}
                className="bg-[#0e0f14] p-6 text-center flex flex-col items-center"
              >
                <div className="w-12 h-12 bg-[#111115] border border-[#27272a] flex items-center justify-center mb-4">
                  {feature.icon}
                </div>
                <h3 className="text-lg font-bold text-white mb-2">{feature.title}</h3>
                <p className="text-sm text-[#a1a1aa] leading-relaxed" style={{ fontFamily: "'Daeojamjil', sans-serif", fontWeight: 400 }}>{feature.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};
