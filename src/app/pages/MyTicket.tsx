import React, { useState } from "react";
import { ArrowLeft, Search, Edit3, XCircle, Check, AlertTriangle, Calendar, MapPin, User, Ticket } from "lucide-react";
import { Link } from "react-router";
import { cn } from "../lib/utils";

const inputCls = "w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] transition-all outline-none focus:border-[#00C1D5] text-sm";
const selectCls = inputCls + " appearance-none";
const sectionCls = "bg-[#0e0f14] border border-[#27272a] p-6 md:p-8";
const labelCls = "text-sm font-medium text-[#a1a1aa]";

// 데모 데이터
const demoOrder = {
  orderNo: "UF2026-00001234",
  ticket: "오프라인 양일권",
  price: 60000,
  paymentDate: "2026-05-26",
  paymentMethod: "신용카드",
  name: "홍길동",
  email: "user@example.com",
  phone: "010-1234-5678",
  company: "에픽게임즈",
  department: "개발팀",
  job: "프로그래밍",
  industry: "게임",
  tshirt: "L",
  day1Track: "게임: 프로그래밍",
  day2Track: "게임: 아트",
};

type View = "search" | "info" | "edit" | "cancel-confirm" | "cancel-done" | "edit-done";

export default function MyTicket() {
  const [view, setView] = useState<View>("search");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");

  // 수정용 상태
  const [editData, setEditData] = useState(demoOrder);

  return (
    <div className="pt-32 pb-24 min-h-screen bg-[#09090b]">
      <div className="max-w-3xl mx-auto px-6">
        <Link to="/" className="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm">
          <ArrowLeft className="w-4 h-4" />
          메인으로
        </Link>

        {/* ─── 1. 조회 ─── */}
        {view === "search" && (
          <>
            <h1 className="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 확인</h1>
            <p className="text-[#a1a1aa] mb-10">등록 시 사용한 이메일과 전화번호로 등록 정보를 조회할 수 있습니다.</p>

            <div className={sectionCls}>
              <h2 className="text-lg font-bold text-white mb-5 flex items-center gap-2">
                <Search className="w-5 h-5 text-[#00C1D5]" />
                등록 조회
              </h2>
              <div className="space-y-4">
                <div className="space-y-2">
                  <label className={labelCls}>이메일 *</label>
                  <input type="email" className={inputCls} placeholder="등록 시 사용한 이메일" value={email} onChange={(e) => setEmail(e.target.value)} />
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>전화번호 *</label>
                  <input type="tel" className={inputCls} placeholder="010-1234-5678" value={phone} onChange={(e) => setPhone(e.target.value)} />
                </div>
              </div>
              <button className="mt-6 w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold transition-all" onClick={() => setView("info")}>
                조회하기
              </button>
            </div>
          </>
        )}

        {/* ─── 2. 등록 정보 확인 ─── */}
        {view === "info" && (
          <>
            <h1 className="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 정보</h1>
            <p className="text-[#a1a1aa] mb-10">등록하신 정보를 확인하고 수정하거나 취소할 수 있습니다.</p>

            {/* QR코드 + 티켓 정보 */}
            <div className={sectionCls + " mb-4"}>
              <div className="flex flex-col md:flex-row gap-8 mb-6">
                {/* QR 코드 */}
                <div className="flex flex-col items-center gap-3 flex-shrink-0">
                  <div className="w-48 h-48 bg-white p-3 flex items-center justify-center">
                    <svg viewBox="0 0 200 200" className="w-full h-full">
                      <rect width="200" height="200" fill="white"/>
                      <g fill="black">
                        {/* QR 코드 패턴 (데모) */}
                        <rect x="20" y="20" width="60" height="60"/>
                        <rect x="28" y="28" width="44" height="44" fill="white"/>
                        <rect x="36" y="36" width="28" height="28"/>
                        <rect x="120" y="20" width="60" height="60"/>
                        <rect x="128" y="28" width="44" height="44" fill="white"/>
                        <rect x="136" y="36" width="28" height="28"/>
                        <rect x="20" y="120" width="60" height="60"/>
                        <rect x="28" y="128" width="44" height="44" fill="white"/>
                        <rect x="36" y="136" width="28" height="28"/>
                        <rect x="90" y="20" width="10" height="10"/>
                        <rect x="90" y="40" width="10" height="10"/>
                        <rect x="90" y="60" width="10" height="10"/>
                        <rect x="90" y="90" width="10" height="10"/>
                        <rect x="100" y="90" width="10" height="10"/>
                        <rect x="120" y="90" width="10" height="10"/>
                        <rect x="140" y="90" width="10" height="10"/>
                        <rect x="160" y="90" width="10" height="10"/>
                        <rect x="90" y="100" width="10" height="10"/>
                        <rect x="120" y="100" width="10" height="10"/>
                        <rect x="150" y="100" width="10" height="10"/>
                        <rect x="90" y="120" width="10" height="10"/>
                        <rect x="90" y="140" width="10" height="10"/>
                        <rect x="120" y="120" width="10" height="10"/>
                        <rect x="140" y="120" width="10" height="10"/>
                        <rect x="120" y="140" width="10" height="10"/>
                        <rect x="160" y="140" width="10" height="10"/>
                        <rect x="120" y="160" width="10" height="10"/>
                        <rect x="140" y="160" width="10" height="10"/>
                        <rect x="160" y="160" width="10" height="10"/>
                      </g>
                    </svg>
                  </div>
                  <p className="text-xs text-[#71717a] text-center">현장 체크인 시 QR코드를 제시해주세요</p>
                </div>

                {/* 티켓 요약 */}
                <div className="flex-grow">
                  <div className="flex items-center gap-3 mb-4">
                    <div className="w-10 h-10 bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center">
                      <Ticket className="w-5 h-5 text-[#00C1D5]" />
                    </div>
                    <div>
                      <div className="text-sm text-[#71717a]">주문번호</div>
                      <div className="text-white font-bold">{demoOrder.orderNo}</div>
                    </div>
                  </div>
                  <div className="bg-[rgba(0,193,213,0.08)] border border-[rgba(0,193,213,0.2)] p-4 mb-4">
                    <div className="text-[#00C1D5] font-bold text-lg">{demoOrder.ticket}</div>
                    <div className="text-sm text-[#a1a1aa] mt-1">2026. 8. 20 (목) - 8. 21 (금) · 웨스틴 서울 파르나스</div>
                  </div>
                </div>
              </div>
              <div className="grid sm:grid-cols-2 gap-4 text-sm">
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">티켓 종류</span>
                  <span className="text-[#00C1D5] font-bold">{demoOrder.ticket}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">결제 금액</span>
                  <span className="text-white font-bold">₩{demoOrder.price.toLocaleString()}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">결제일</span>
                  <span className="text-white">{demoOrder.paymentDate}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">결제 수단</span>
                  <span className="text-white">{demoOrder.paymentMethod}</span>
                </div>
              </div>
            </div>

            {/* 참가자 정보 */}
            <div className={sectionCls + " mb-4"}>
              <h2 className="text-lg font-bold text-white mb-5">참가자 정보</h2>
              <div className="grid sm:grid-cols-2 gap-4 text-sm">
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">이름</span>
                  <span className="text-white">{demoOrder.name}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">이메일</span>
                  <span className="text-white">{demoOrder.email}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">전화번호</span>
                  <span className="text-white">{demoOrder.phone}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">회사/소속</span>
                  <span className="text-white">{demoOrder.company}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">부서</span>
                  <span className="text-white">{demoOrder.department}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">직무</span>
                  <span className="text-white">{demoOrder.job}</span>
                </div>
              </div>
            </div>

            {/* 행사 선택 정보 */}
            <div className={sectionCls + " mb-4"}>
              <h2 className="text-lg font-bold text-white mb-5">행사 선택 정보</h2>
              <div className="grid sm:grid-cols-2 gap-4 text-sm">
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">티셔츠 사이즈</span>
                  <span className="text-white">{demoOrder.tshirt}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">산업/관심 분야</span>
                  <span className="text-white">{demoOrder.industry}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">Day 1 트랙</span>
                  <span className="text-white">{demoOrder.day1Track}</span>
                </div>
                <div className="flex justify-between py-2 border-b border-[#27272a]">
                  <span className="text-[#71717a]">Day 2 트랙</span>
                  <span className="text-white">{demoOrder.day2Track}</span>
                </div>
              </div>
            </div>

            {/* 액션 버튼 */}
            <div className="flex gap-4 mt-8">
              <button className="flex-1 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold flex items-center justify-center gap-2 transition-all" onClick={() => setView("edit")}>
                <Edit3 className="w-4 h-4" />
                정보 수정
              </button>
              <button className="flex-1 border border-[#27272a] text-[#a1a1aa] py-3 font-bold flex items-center justify-center gap-2 hover:text-[#ff8674] hover:border-[rgba(250,70,22,0.3)] transition-all" onClick={() => setView("cancel-confirm")}>
                <XCircle className="w-4 h-4" />
                등록 취소
              </button>
            </div>
          </>
        )}

        {/* ─── 3. 정보 수정 ─── */}
        {view === "edit" && (
          <>
            <h1 className="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">정보 수정</h1>
            <p className="text-[#a1a1aa] mb-10">변경할 정보를 수정한 후 저장해주세요. 이름과 이메일은 변경할 수 없습니다.</p>

            <div className={sectionCls + " mb-4"}>
              <h2 className="text-lg font-bold text-white mb-5">기본 정보</h2>
              <div className="grid md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <label className={labelCls}>이름</label>
                  <input type="text" className={inputCls + " opacity-50 cursor-not-allowed"} value={editData.name} disabled />
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>이메일</label>
                  <input type="email" className={inputCls + " opacity-50 cursor-not-allowed"} value={editData.email} disabled />
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>전화번호 *</label>
                  <input type="tel" className={inputCls} value={editData.phone} onChange={(e) => setEditData({ ...editData, phone: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>회사명/소속 *</label>
                  <input type="text" className={inputCls} value={editData.company} onChange={(e) => setEditData({ ...editData, company: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>부서</label>
                  <input type="text" className={inputCls} value={editData.department} onChange={(e) => setEditData({ ...editData, department: e.target.value })} />
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>직무 *</label>
                  <select className={selectCls} value={editData.job} onChange={(e) => setEditData({ ...editData, job: e.target.value })}>
                    <option>프로그래밍</option>
                    <option>비주얼 아트</option>
                    <option>프로덕션</option>
                    <option>엔지니어링</option>
                    <option>기획</option>
                    <option>비즈니스/마케팅</option>
                    <option>기타</option>
                  </select>
                </div>
              </div>
            </div>

            <div className={sectionCls + " mb-4"}>
              <h2 className="text-lg font-bold text-white mb-5">행사 선택 정보</h2>
              <div className="grid md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <label className={labelCls}>티셔츠 사이즈 *</label>
                  <div className="flex gap-3">
                    {["M", "L", "XL", "3XL"].map(size => (
                      <button key={size} onClick={() => setEditData({ ...editData, tshirt: size })} className={cn("w-14 h-14 border flex items-center justify-center text-sm font-bold transition-all", editData.tshirt === size ? "border-[#00C1D5] bg-[rgba(0,79,89,0.2)] text-[#00C1D5]" : "border-[#27272a] text-[#71717a] hover:border-white/20")}>
                        {size}
                      </button>
                    ))}
                  </div>
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>산업/관심 분야 *</label>
                  <select className={selectCls} value={editData.industry} onChange={(e) => setEditData({ ...editData, industry: e.target.value })}>
                    <option>게임</option>
                    <option>건축</option>
                    <option>자동차 & 운송</option>
                    <option>영화 & TV</option>
                    <option>애니메이션</option>
                    <option>VR/AR</option>
                    <option>교육</option>
                    <option>기타</option>
                  </select>
                </div>
              </div>

              <div className="mt-6 space-y-4">
                <div className="space-y-2">
                  <label className={labelCls}>Day 1 (8.20 목) 트랙 *</label>
                  <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    {["게임: 프로그래밍", "게임: 아트", "미디어 & 엔터테인먼트", "산업 & 시뮬레이션"].map(tr => (
                      <button key={tr} onClick={() => setEditData({ ...editData, day1Track: tr })} className={cn("p-3 border text-center text-sm font-medium transition-all", editData.day1Track === tr ? "border-[#00C1D5] bg-[rgba(0,79,89,0.2)] text-[#9adbe8]" : "border-[#27272a] text-[#71717a] hover:border-white/20")}>
                        {tr}
                      </button>
                    ))}
                  </div>
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>Day 2 (8.21 금) 트랙 *</label>
                  <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    {["게임: 프로그래밍", "게임: 아트", "미디어 & 엔터테인먼트", "산업 & 시뮬레이션"].map(tr => (
                      <button key={tr} onClick={() => setEditData({ ...editData, day2Track: tr })} className={cn("p-3 border text-center text-sm font-medium transition-all", editData.day2Track === tr ? "border-[#00C1D5] bg-[rgba(0,79,89,0.2)] text-[#9adbe8]" : "border-[#27272a] text-[#71717a] hover:border-white/20")}>
                        {tr}
                      </button>
                    ))}
                  </div>
                </div>
              </div>
            </div>

            <div className="flex gap-4 mt-8">
              <button className="flex-1 border border-[#27272a] text-[#a1a1aa] py-3 font-bold hover:text-white hover:border-white/20 transition-colors" onClick={() => setView("info")}>
                취소
              </button>
              <button className="flex-1 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold flex items-center justify-center gap-2 transition-all" onClick={() => setView("edit-done")}>
                <Check className="w-4 h-4" />
                저장하기
              </button>
            </div>
          </>
        )}

        {/* ─── 4. 수정 완료 ─── */}
        {view === "edit-done" && (
          <div className={sectionCls + " text-center py-16"}>
            <div className="w-16 h-16 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center mx-auto mb-6">
              <Check className="w-8 h-8 text-[#00C1D5]" />
            </div>
            <h2 className="text-2xl font-bold text-white mb-3">정보가 수정되었습니다</h2>
            <p className="text-[#a1a1aa] mb-2">주문번호: <span className="text-white font-medium">{demoOrder.orderNo}</span></p>
            <p className="text-sm text-[#71717a] mb-8">변경된 정보는 즉시 반영됩니다.</p>
            <div className="flex gap-4 justify-center">
              <button className="px-8 py-3 border border-[#27272a] text-[#a1a1aa] font-bold hover:text-white hover:border-white/20 transition-colors" onClick={() => setView("info")}>
                등록 정보 보기
              </button>
              <Link to="/" className="px-8 py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-bold transition-colors">
                메인으로
              </Link>
            </div>
          </div>
        )}

        {/* ─── 5. 취소 확인 ─── */}
        {view === "cancel-confirm" && (
          <>
            <h1 className="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 취소 / 환불 신청</h1>
            <p className="text-[#a1a1aa] mb-10">아래 내용을 확인하신 후 취소를 진행해주세요.</p>

            {/* 환불 규정 */}
            <div className="bg-[rgba(250,70,22,0.08)] border border-[rgba(250,70,22,0.25)] p-6 mb-6">
              <div className="flex items-start gap-3">
                <AlertTriangle className="w-5 h-5 text-[#ff8674] flex-shrink-0 mt-0.5" />
                <div>
                  <h3 className="text-base font-bold text-white mb-2">환불 규정 안내</h3>
                  <ul className="text-sm text-[#a1a1aa] space-y-1.5">
                    <li>• 행사 시작 7일 전 (8월 13일 23:59)까지: <strong className="text-white">100% 환불</strong></li>
                    <li>• 8월 14일 ~ 8월 17일: <strong className="text-white">50% 환불</strong> (수수료 차감)</li>
                    <li>• 8월 18일 이후: <strong className="text-[#ff8674]">환불 불가</strong></li>
                  </ul>
                </div>
              </div>
            </div>

            {/* 주문 요약 */}
            <div className={sectionCls + " mb-6"}>
              <div className="space-y-3 pb-5 border-b border-[#27272a]">
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">주문번호</span>
                  <span className="text-white font-medium">{demoOrder.orderNo}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">티켓</span>
                  <span className="text-[#00C1D5] font-bold">{demoOrder.ticket}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-[#71717a]">결제 금액</span>
                  <span className="text-white font-bold">₩{demoOrder.price.toLocaleString()}</span>
                </div>
              </div>
              <div className="pt-5">
                <div className="flex justify-between items-center">
                  <span className="text-[#71717a]">환불 예정 금액</span>
                  <span className="text-xl font-black text-white">₩{demoOrder.price.toLocaleString()}</span>
                </div>
                <p className="text-xs text-[#71717a] mt-2">환불은 결제 수단으로 3~5영업일 이내 처리됩니다.</p>
              </div>
            </div>

            {/* 취소 사유 */}
            <div className={sectionCls + " mb-6"}>
              <h2 className="text-lg font-bold text-white mb-5">취소 사유 (선택)</h2>
              <select className={selectCls}>
                <option value="">선택해주세요</option>
                <option>일정 변경</option>
                <option>개인 사정</option>
                <option>다른 행사 참석</option>
                <option>티켓 변경 (양일권 → 1일권 등)</option>
                <option>기타</option>
              </select>
            </div>

            {/* 동의 + 버튼 */}
            <label className="flex items-start gap-2 cursor-pointer mb-6">
              <input type="checkbox" className="mt-0.5 text-[#00C1D5] focus:ring-[#00C1D5] rounded bg-transparent border-[#27272a]" />
              <span className="text-sm text-[#a1a1aa]">
                환불 규정을 확인했으며, 취소 및 환불에 동의합니다. <span className="text-[#00C1D5]">(필수)</span>
              </span>
            </label>
            <div className="flex gap-4">
              <button className="flex-1 border border-[#27272a] text-[#a1a1aa] py-3 font-bold hover:text-white hover:border-white/20 transition-colors" onClick={() => setView("info")}>
                이전으로
              </button>
              <button className="flex-1 bg-[#FA4616] hover:bg-[#e03e12] text-white py-3 font-bold transition-all" onClick={() => setView("cancel-done")}>
                취소 및 환불 신청
              </button>
            </div>
          </>
        )}

        {/* ─── 6. 취소 완료 ─── */}
        {view === "cancel-done" && (
          <div className={sectionCls + " text-center py-16"}>
            <div className="w-16 h-16 rounded-full bg-[rgba(0,193,213,0.1)] border border-[rgba(0,193,213,0.2)] flex items-center justify-center mx-auto mb-6">
              <Check className="w-8 h-8 text-[#00C1D5]" />
            </div>
            <h2 className="text-2xl font-bold text-white mb-3">취소 신청이 완료되었습니다</h2>
            <p className="text-[#a1a1aa] mb-2">주문번호: <span className="text-white font-medium">{demoOrder.orderNo}</span></p>
            <p className="text-sm text-[#71717a] mb-8">환불은 결제 수단으로 3~5영업일 이내 처리되며, 처리 완료 시 이메일로 안내드립니다.</p>
            <Link to="/" className="inline-flex items-center gap-2 px-8 py-3 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] font-bold transition-colors">
              메인으로 돌아가기
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
