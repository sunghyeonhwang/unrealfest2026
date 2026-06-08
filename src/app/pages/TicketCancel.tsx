import React, { useState } from "react";
import { ArrowLeft, AlertTriangle, Search } from "lucide-react";
import { Link } from "react-router";

const inputCls = "w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] transition-all outline-none focus:border-[#00C1D5] text-sm";
const sectionCls = "bg-[#0e0f14] border border-[#27272a] p-6 md:p-8";

export default function TicketCancel() {
  const [step, setStep] = useState<"search" | "confirm" | "done">("search");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");

  return (
    <div className="pt-32 pb-24 min-h-screen bg-[#09090b]">
      <div className="max-w-3xl mx-auto px-6">
        <Link to="/#register" className="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm">
          <ArrowLeft className="w-4 h-4" />
          돌아가기
        </Link>

        <h1 className="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 취소 / 환불 신청</h1>
        <p className="text-[#a1a1aa] mb-10">등록하신 티켓의 취소 및 환불을 신청할 수 있습니다.</p>

        {step === "search" && (
          <div className="space-y-6">
            {/* 환불 규정 안내 */}
            <div className="bg-[rgba(250,70,22,0.08)] border border-[rgba(250,70,22,0.25)] p-6">
              <div className="flex items-start gap-3">
                <AlertTriangle className="w-5 h-5 text-[#ff8674] flex-shrink-0 mt-0.5" />
                <div>
                  <h3 className="text-base font-bold text-white mb-2">환불 규정 안내</h3>
                  <ul className="text-sm text-[#a1a1aa] space-y-1.5">
                    <li>• 행사 시작 7일 전 (8월 13일 23:59)까지: <strong className="text-white">100% 환불</strong></li>
                    <li>• 8월 14일 ~ 8월 17일: <strong className="text-white">50% 환불</strong> (수수료 차감)</li>
                    <li>• 8월 18일 이후: <strong className="text-[#ff8674]">환불 불가</strong></li>
                    <li>• 타인 양도는 행사 시작 3일 전까지 가능합니다.</li>
                  </ul>
                </div>
              </div>
            </div>

            {/* 주문 조회 */}
            <div className={sectionCls}>
              <h2 className="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <Search className="w-5 h-5 text-[#00C1D5]" />
                주문 조회
              </h2>
              <p className="text-sm text-[#a1a1aa] mb-6">등록 시 사용한 이메일과 전화번호를 입력해주세요.</p>
              <div className="space-y-4">
                <div className="space-y-2">
                  <label className="text-sm font-medium text-[#a1a1aa]">이메일 *</label>
                  <input
                    type="email"
                    className={inputCls}
                    placeholder="등록 시 사용한 이메일"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                  />
                </div>
                <div className="space-y-2">
                  <label className="text-sm font-medium text-[#a1a1aa]">전화번호 *</label>
                  <input
                    type="tel"
                    className={inputCls}
                    placeholder="010-1234-5678"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                  />
                </div>
              </div>
              <button
                className="mt-6 w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold flex items-center justify-center gap-2 transition-all"
                onClick={() => setStep("confirm")}
              >
                주문 조회하기
              </button>
            </div>
          </div>
        )}

        {step === "confirm" && (
          <div className="space-y-6">
            {/* 주문 정보 */}
            <div className={sectionCls}>
              <h2 className="text-lg font-bold text-white mb-5">주문 정보</h2>
              <div className="space-y-3 pb-5 border-b border-[#27272a]">
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">주문번호</span>
                  <span className="text-white font-medium">UF2026-00001234</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">이메일</span>
                  <span className="text-white">{email || "user@example.com"}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">티켓</span>
                  <span className="text-[#00C1D5] font-bold">오프라인 양일권</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">결제 금액</span>
                  <span className="text-white font-bold">₩60,000</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">결제일</span>
                  <span className="text-white">2026-05-26</span>
                </div>
              </div>
              <div className="pt-5 space-y-3">
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">환불 예정 금액</span>
                  <span className="text-xl font-black text-white">₩60,000</span>
                </div>
                <p className="text-xs text-[#71717a]">환불은 결제 수단으로 3~5영업일 이내 처리됩니다.</p>
              </div>
            </div>

            {/* 취소 사유 */}
            <div className={sectionCls}>
              <h2 className="text-lg font-bold text-white mb-5">취소 사유 (선택)</h2>
              <select className={inputCls + " appearance-none"}>
                <option value="">선택해주세요</option>
                <option>일정 변경</option>
                <option>개인 사정</option>
                <option>다른 행사 참석</option>
                <option>티켓 변경 (양일권 → 1일권 등)</option>
                <option>기타</option>
              </select>
            </div>

            {/* 동의 + 버튼 */}
            <div className={sectionCls}>
              <label className="flex items-start gap-2 cursor-pointer mb-6">
                <input type="checkbox" className="mt-0.5 text-[#00C1D5] focus:ring-[#00C1D5] rounded bg-transparent border-[#27272a]" />
                <span className="text-sm text-[#a1a1aa]">
                  환불 규정을 확인했으며, 취소 및 환불에 동의합니다. <span className="text-[#00C1D5]">(필수)</span>
                </span>
              </label>
              <div className="flex gap-4">
                <button
                  className="flex-1 border border-[#27272a] text-[#a1a1aa] py-3 font-bold hover:text-white hover:border-white/20 transition-colors"
                  onClick={() => setStep("search")}
                >
                  이전으로
                </button>
                <button
                  className="flex-1 bg-[#FA4616] hover:bg-[#e03e12] text-white py-3 font-bold transition-all"
                  onClick={() => setStep("done")}
                >
                  취소 및 환불 신청
                </button>
              </div>
            </div>
          </div>
        )}

        {step === "done" && (
          <div className={sectionCls + " text-center py-16"}>
            <div className="w-16 h-16 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center mx-auto mb-6">
              <svg className="w-8 h-8 text-[#00C1D5]" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h2 className="text-2xl font-bold text-white mb-3">취소 신청이 완료되었습니다</h2>
            <p className="text-[#a1a1aa] mb-2">주문번호: <span className="text-white font-medium">UF2026-00001234</span></p>
            <p className="text-sm text-[#71717a] mb-8">환불은 결제 수단으로 3~5영업일 이내 처리되며, 처리 완료 시 이메일로 안내드립니다.</p>
            <Link
              to="/"
              className="inline-flex items-center gap-2 px-8 py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-bold transition-colors"
            >
              메인으로 돌아가기
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
