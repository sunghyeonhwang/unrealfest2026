import React from "react";
import { ArrowRight, ExternalLink } from "lucide-react";
import { Link } from "react-router";

const goldSponsors = [
  {
    name: "Xsolla",
    logo: "./logo1.svg",
    desc: "리얼타임 AI 렌더링의 선두주자. 개발자들의 차세대 그래픽라이브파이프라인 구축을 전폭 지원합니다. 현장 부스에서 RTX50 시리즈 기반의 실시간 8K 레스 트레이싱 데모와 최신 DLSS4.0 적용 사례를 직접 확인하세요.",
    link: "#",
  },
  {
    name: "HP",
    logo: "./logo2.svg",
    desc: "확장 가능한 클라우드 인프라를 통해 전 세계 수백만 명의 플레이어에게 즉시 접근 가능한 멀티플레이어 경험을 제공합니다. AWS GameLift와 연계한 엔진의 백엔드 및 효율적인 백엔드 아키텍처 사례를 소개합니다.",
    link: "#",
  },
];

const silverSponsors = [
  { name: "Silver Sponsor 1", logo: "./logo3.svg", desc: "리얼타임 렌더링과 가상화 환경에서의 혁신을 선도합니다. 차세대 GPU 아키텍처를 통해 크리에이터와 개발자의 워크플로를 최적화합니다.", link: "#" },
  { name: "Perforce", logo: "./logo4.svg", desc: "언리얼 이니시에 대규모 합동작업 관리, PC, 콘솔/모바일 프리커 환경에 특화된 크로스 플랫폼 개발 지원을 지원합니다.", link: "#" },
  { name: "Samsung", logo: "./logo7.svg", desc: "클라우디오엔진을 기반으로 몰입형 공간 오디오 및 3D 랜더링 엔진을 이해혈 음 유로 수행합니다.", link: "#" },
  { name: "Audiokinetic", logo: "./logo8.svg", desc: "실시간 3D 환경에서 완벽한 캐릭셜 성능을 위한 전문 솔루션을 제공합니다.", link: "#" },
];

export default function SponsorsDetail() {
  return (
    <div className="bg-[#09090b] min-h-screen text-white">
      {/* PAGE HEADING */}
      <section className="relative pt-24 pb-16 overflow-hidden border-b border-[#27272a]" style={{ backgroundColor: "#0e0f14" }}>
        <div className="absolute right-0 top-0 bottom-0 w-[70%] z-0">
          <img
            src="./session_hero.jpg"
            alt=""
            className="w-full h-full object-cover opacity-30"
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
            스폰서
          </h1>
          <p className="text-[#90a1b9] max-w-2xl text-base leading-relaxed">
            Unreal Fest Seoul 2026은 차세대 3D 생태계를 이끌어가는 최고의 파트너들과 함께합니다.
            행사장에 마련된 파트너 부스에서 최신 기술 데모를 직접 경험해 보세요.
          </p>
        </div>
      </section>

      {/* 골드 스폰서 */}
      <section className="max-w-7xl mx-auto px-6 py-16">
        <h2 className="text-2xl font-bold text-[#D4A843] mb-8 tracking-tight">골드 스폰서</h2>
        <div className="grid md:grid-cols-2 gap-6">
          {goldSponsors.map((s, i) => (
            <div key={i} className="bg-[#0e0f14] border border-[rgba(212,168,67,0.3)] p-8 flex flex-col">
              <div className="h-24 flex items-center justify-center mb-6">
                <img src={s.logo} alt={s.name} className="object-contain invert" style={{ width: "300px" }} />
              </div>
              <p className="text-sm text-[#a1a1aa] leading-relaxed mb-6 flex-grow">{s.desc}</p>
              <a href={s.link} className="inline-flex items-center gap-1.5 text-sm text-[#D4A843] font-medium hover:underline">
                <ExternalLink className="w-3.5 h-3.5" />
                웹사이트 방문하기
              </a>
            </div>
          ))}
        </div>
      </section>

      {/* 실버 스폰서 */}
      <section className="max-w-7xl mx-auto px-6 pb-16">
        <h2 className="text-2xl font-bold text-[#a1a1aa] mb-8 tracking-tight">실버 스폰서</h2>
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {silverSponsors.map((s, i) => (
            <div key={i} className="bg-[#0e0f14] border border-[#27272a] p-6 flex flex-col">
              <div className="h-16 flex items-center justify-center mb-4">
                <img src={s.logo} alt={s.name} className="h-9 object-contain invert" />
              </div>
              <p className="text-xs text-[#a1a1aa] leading-relaxed mb-4 flex-grow">{s.desc}</p>
              <a href={s.link} className="inline-flex items-center gap-1 text-xs text-[#71717a] hover:text-white transition-colors">
                자세히 알아보기
                <ArrowRight className="w-3 h-3" />
              </a>
            </div>
          ))}
        </div>
      </section>

      {/* 스폰서십 문의 */}
      <section className="max-w-7xl mx-auto px-6 pb-24">
        <div className="text-center py-16 border-t border-[#27272a]">
          <h2 className="text-2xl md:text-3xl font-bold text-white mb-4 tracking-tight">스폰서십 문의</h2>
          <p className="text-[#a1a1aa] max-w-2xl mx-auto mb-8 leading-relaxed">
            리얼타임 3D 생태계를 이끌어가는 진보가 및 의사결정자들을 직접 만날 수 있는 특별한 기회를 잡으세요.
            다양한 스폰서십 패키지가 준비되어 있습니다.
          </p>
          <a
            href="mailto:sponsor@unrealfest.kr"
            className="inline-flex items-center gap-2 px-8 py-3.5 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-medium transition-colors"
            style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 12px), calc(100% - 12px) 100%, 0 100%)" }}
          >
            스폰서십 브로셔 요청하기
          </a>
        </div>
      </section>
    </div>
  );
}
