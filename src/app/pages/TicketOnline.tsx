import React, { useState } from "react";
import { ArrowLeft, ShieldCheck, Check } from "lucide-react";
import { Link } from "react-router";
import { cn } from "../lib/utils";

const inputCls = "w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] transition-all outline-none focus:border-[#00C1D5] text-sm";
const selectCls = inputCls + " appearance-none";
const labelCls = "text-sm font-medium text-[#a1a1aa]";
const sectionCls = "bg-[#0e0f14] border border-[#27272a] p-6 md:p-8";

export default function TicketOnline() {
  return (
    <div className="pt-32 pb-24 min-h-screen bg-[#09090b]">
      <div className="max-w-3xl mx-auto px-6">
        <Link to="/#register" className="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm">
          <ArrowLeft className="w-4 h-4" />
          돌아가기
        </Link>

        <h1 className="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">언리얼 페스트 2026 서울 온라인 등록</h1>
        <p className="text-[#a1a1aa] mb-10">온라인으로 Unreal Fest Seoul 2026의 주요 세션을 시청할 수 있습니다.</p>

        {/* 안내 */}
        <div className="bg-[rgba(0,193,213,0.05)] border border-[rgba(0,193,213,0.2)] p-6 mb-6">
          <h3 className="text-base font-bold text-white mb-3">온라인 시청 안내</h3>
          <ul className="text-sm text-[#a1a1aa] space-y-1.5">
            <li>• 키노트 및 주요 세션 실시간 스트리밍</li>
            <li>• 행사 종료 후 1달 내 다시보기 제공</li>
            <li>• 발표자 동의에 따라 일부 세션만 중계될 수 있습니다</li>
            <li className="text-[#71717a]">• Q&A 참여 및 현장 프로그램은 제공되지 않습니다</li>
          </ul>
        </div>

        <div className="space-y-4">
          {/* 약관 동의 */}
          <div className={sectionCls}>
            <h2 className="text-lg font-bold text-white mb-5 flex items-center gap-2">
              <ShieldCheck className="w-5 h-5 text-[#00C1D5]" />
              약관 동의
            </h2>
            <div className="space-y-3">
              <label className="flex items-center gap-3 p-3 bg-[rgba(0,79,89,0.3)] border border-[rgba(0,193,213,0.3)] cursor-pointer">
                <input type="checkbox" className="text-[#00C1D5] focus:ring-[#00C1D5] rounded bg-transparent border-[#27272a]" />
                <span className="text-sm font-bold text-white">전체 동의</span>
              </label>
              <div className="h-px bg-[#27272a]" />
              {[
                { text: "에픽 라운지 이용약관 동의 및 개인정보보호정책 확인", req: true },
                { text: "광고 수신 동의", req: false },
              ].map((item, i) => (
                <label key={i} className="flex items-start gap-3 px-3 py-2 cursor-pointer">
                  <input type="checkbox" className="mt-0.5 text-[#00C1D5] focus:ring-[#00C1D5] rounded bg-transparent border-[#27272a]" />
                  <span className="text-sm text-[#a1a1aa]">
                    {item.text}
                    <span className={cn("ml-1 text-xs", item.req ? "text-[#00C1D5]" : "text-[#71717a]")}>{item.req ? "(필수)" : "(선택)"}</span>
                  </span>
                </label>
              ))}
            </div>
          </div>

          {/* 기본 정보 */}
          <div className={sectionCls}>
            <h2 className="text-lg font-bold text-white mb-5">기본 정보</h2>
            <div className="grid md:grid-cols-3 gap-6">
              <div className="space-y-2">
                <label className={labelCls}>이름 *</label>
                <input type="text" className={inputCls} placeholder="홍길동" />
              </div>
              <div className="space-y-2">
                <label className={labelCls}>이메일 *</label>
                <input type="email" className={inputCls} placeholder="email@example.com" />
              </div>
              <div className="space-y-2">
                <label className={labelCls}>연락처 *</label>
                <input type="tel" className={inputCls} placeholder="010-1234-5678" />
              </div>
            </div>
          </div>

          {/* 소속 및 관심 분야 */}
          <div className={sectionCls}>
            <h2 className="text-lg font-bold text-white mb-5">소속 및 관심 분야</h2>
            <div className="space-y-6">
              <div className="grid md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <label className={labelCls}>직업 *</label>
                  <select className={selectCls}>
                    <option value="">선택해주세요</option>
                    <option>직장인</option>
                    <option>학생</option>
                    <option>교육자/교육기관</option>
                    <option>인디 개발자</option>
                    <option>프리랜서</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>회사명/소속</label>
                  <input type="text" className={inputCls} placeholder="에픽게임즈" />
                </div>
              </div>
              <div className="grid md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <label className={labelCls}>직무</label>
                  <select className={selectCls}>
                    <option value="">선택해주세요</option>
                    <option>비주얼 아트</option>
                    <option>프로그래밍</option>
                    <option>프로덕션</option>
                    <option>엔지니어링</option>
                    <option>기획</option>
                    <option>비즈니스/마케팅</option>
                    <option>기타</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>산업/관심 분야 *</label>
                  <select className={selectCls}>
                    <option value="">선택해주세요</option>
                    <option>게임</option>
                    <option>영화 & TV</option>
                    <option>방송 & 라이브 이벤트</option>
                    <option>애니메이션</option>
                    <option>건축</option>
                    <option>자동차</option>
                    <option>제조/시뮬레이션</option>
                    <option>소프트웨어 & 툴 개발</option>
                    <option>VR·AR</option>
                    <option>교육</option>
                    <option>기타</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          {/* 등록 버튼 */}
          <button
            className="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all"
            onClick={() => alert("온라인 등록이 완료되었습니다. (데모)")}
          >
            무료 등록하기
            <Check className="w-5 h-5" />
          </button>

          <Link to="/#register" className="block w-full text-center text-sm text-[#71717a] hover:text-white py-3 transition-colors">
            취소
          </Link>
        </div>
      </div>
    </div>
  );
}
