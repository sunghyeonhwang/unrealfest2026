import React from "react";
import { ArrowRight } from "lucide-react";
import { Link } from "react-router";

const jamsil = { fontFamily: "'Daeojamjil', sans-serif" };

export const Register = () => {
  return (
    <section id="register" className="py-24 bg-[#0e0f14] relative border-t border-[#27272a]">
      <div className="max-w-7xl mx-auto px-6">
        <div className="mb-16">
          <h2 className="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight">티켓</h2>
          <p className="text-base text-[#90a1b9]">
            오프라인과 온라인으로 Unreal Fest Seoul 2026을 경험해 보세요.
          </p>
        </div>

        <div className="grid md:grid-cols-3 gap-[26px] pt-[35px]">
          {/* 오프라인 양일권 */}
          <div className="relative bg-[#0e0f14] border border-[#27272a] p-9 flex flex-col items-center text-center">
            <div className="absolute -top-[13px] left-0 bg-[#00C1D5] text-[#090a0f] text-[14px] font-bold px-[18px] py-[7px]">
              얼리버드 50% 할인
            </div>
            <h3 className="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px]" style={{ ...jamsil, fontWeight: 500 }}>오프라인 양일권</h3>
            <div className="mb-1">
              <span className="text-[18px] text-[#71717a] line-through tracking-tight">₩ 120,000</span>
            </div>
            <div className="mb-2">
              <span className="text-[40px] font-bold text-white tracking-tight">₩ 60,000</span>
            </div>
            <p className="text-[13px] text-[#9adbe8] mb-auto">얼리버드 할인 (~7/13 마감)</p>
            <Link
              to="/ticket?type=all"
              className="mt-[35px] w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 transition-colors"
              style={jamsil}
            >
              양일권 등록하기
              <ArrowRight className="w-[18px] h-[18px]" />
            </Link>
          </div>

          {/* 오프라인 1일권 */}
          <div className="relative bg-[#0e0f14] border border-[rgba(0,193,213,0.5)] p-9 flex flex-col items-center text-center shadow-[0_0_11px_rgba(0,193,213,0.1)]">
            <div className="absolute -top-[13px] left-0 bg-[#00C1D5] text-[#090a0f] text-[14px] font-bold px-[18px] py-[7px]">
              얼리버드 50% 할인
            </div>
            <h3 className="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px]" style={{ ...jamsil, fontWeight: 500 }}>오프라인 1일권</h3>
            <div className="mb-1">
              <span className="text-[18px] text-[#71717a] line-through tracking-tight">₩ 60,000</span>
            </div>
            <div className="mb-2">
              <span className="text-[40px] font-bold text-white tracking-tight">₩ 30,000</span>
            </div>
            <p className="text-[13px] text-[#9adbe8] mb-auto">얼리버드 할인 (~7/13 마감)</p>
            <Link
              to="/ticket?type=day1"
              className="mt-[35px] w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 transition-colors"
              style={jamsil}
            >
              1일권 등록하기
              <ArrowRight className="w-[18px] h-[18px]" />
            </Link>
          </div>

          {/* 온라인 */}
          <div className="bg-[#0e0f14] border border-[#27272a] p-9 flex flex-col items-center text-center">
            <h3 className="text-[38px] text-white mt-[18px] mb-[26px] leading-[46px]" style={{ ...jamsil, fontWeight: 500 }}>온라인</h3>
            <div className="mb-2">
              <span className="text-[26px] font-bold text-[#a1a1aa]">무료</span>
            </div>
            <p className="text-[15px] text-[#71717a] mb-auto">(일부 세션 생중계)</p>
            <Link
              to="/ticket/online"
              className="mt-[35px] w-full border border-[#27272a] text-[#a1a1aa] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 hover:border-white/30 hover:text-white transition-colors"
              style={jamsil}
            >
              무료 등록하기
              <ArrowRight className="w-[18px] h-[18px]" />
            </Link>
          </div>
        </div>

        {/* 안내 문구 */}
        <p className="text-sm text-[#00C1D5] font-bold mt-8 text-right tracking-tight">
          · *오프라인 티켓은 한정 수량으로 조기 마감될 수 있습니다.
        </p>

        {/* 단체 등록 및 기업 결제 */}
        <div className="mt-12 border border-[#27272a] p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h3 className="text-xl font-bold text-[#fafafa] mb-2">단체 등록 및 기업 결제</h3>
            <p className="text-sm text-[#a1a1aa]">
              5인 이상 단체 등록 시 세금계산서 발행 및 무통장 입금을 지원합니다. 관련 문의는 운영 사무국으로 연락해 주세요.
            </p>
          </div>
          <a
            href="mailto:contact@epicgames.com"
            className="flex-shrink-0 inline-flex items-center gap-2 px-6 py-2.5 bg-white text-black text-sm font-bold hover:bg-white/90 transition-colors whitespace-nowrap"
            style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%)" }}
          >
            문의하기
            <ArrowRight className="w-3 h-3" />
          </a>
        </div>
      </div>
    </section>
  );
};
