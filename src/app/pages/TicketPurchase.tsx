import React, { useState } from "react";
import { ArrowLeft, CheckCircle2, CreditCard, ShieldCheck, Check } from "lucide-react";
import { Link } from "react-router";
import { cn } from "../lib/utils";

const inputCls = "w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] transition-all outline-none focus:border-[#00C1D5] text-sm";
const selectCls = inputCls + " appearance-none";
const labelCls = "text-sm font-medium text-[#a1a1aa]";
const sectionCls = "bg-[#0e0f14] border border-[#27272a] p-6 md:p-8";

const tickets = [
  { code: "ALL", name: "양일권 (8.20~21)", price: 150000, desc: "Day 1 + Day 2 전체 참석", sub: "양일권" },
  { code: "DAY1", name: "Day 1 단일권 (8.20)", price: 80000, desc: "Day 1만 참석", sub: "Day 1 단일권" },
  { code: "DAY2", name: "Day 2 단일권 (8.21)", price: 80000, desc: "Day 2 참석", sub: "Day 2 단일권" },
];

export default function TicketPurchase() {
  const [jobType, setJobType] = useState("");
  const [selectedTicket, setSelectedTicket] = useState("ALL");
  const [day1Track, setDay1Track] = useState("");
  const [day2Track, setDay2Track] = useState("");
  const [isForeigner, setIsForeigner] = useState(false);

  const ticket = tickets.find(t => t.code === selectedTicket)!;
  const showDay1 = selectedTicket === "ALL" || selectedTicket === "DAY1";
  const showDay2 = selectedTicket === "ALL" || selectedTicket === "DAY2";

  return (
    <div className="pt-32 pb-24 min-h-screen bg-[#09090b]">
      <div className="max-w-7xl mx-auto px-6">
        <Link to="/#register" className="inline-flex items-center gap-2 text-[#71717a] hover:text-white transition-colors mb-8 text-sm">
          <ArrowLeft className="w-4 h-4" />
          돌아가기
        </Link>

        <h1 className="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">언리얼 페스트 2026 서울 오프라인 등록</h1>
        <p className="text-[#a1a1aa] mb-10">티켓을 선택하고 정보를 입력해 주세요.</p>

        <div className="grid lg:grid-cols-12 gap-8 items-start">
          {/* 좌측: 폼 영역 */}
          <div className="lg:col-span-7 xl:col-span-8 space-y-4">

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

          {/* 티켓 선택 */}
          <div className={sectionCls}>
            <h2 className="text-lg font-bold text-white mb-5">티켓 선택</h2>
            <div className="grid md:grid-cols-3 gap-4 mb-8">
              {tickets.map(t => (
                <label
                  key={t.code}
                  className={cn(
                    "relative p-5 border cursor-pointer transition-all",
                    selectedTicket === t.code
                      ? "border-[#00C1D5] bg-[rgba(0,79,89,0.2)]"
                      : "border-[#27272a] hover:border-white/20"
                  )}
                >
                  <input type="radio" name="ticket" value={t.code} checked={selectedTicket === t.code} onChange={() => setSelectedTicket(t.code)} className="peer sr-only" />
                  <div className="text-xl font-black text-white mb-1">₩{t.price.toLocaleString()}</div>
                  <div className="text-sm font-bold text-[#a1a1aa] mb-2">{t.name}</div>
                  <div className="text-xs text-[#71717a]">{t.desc}</div>
                  {selectedTicket === t.code && <div className="absolute top-3 right-3"><CheckCircle2 className="w-5 h-5 text-[#00C1D5]" /></div>}
                </label>
              ))}
            </div>

            {/* 혜택 */}
            <div className="bg-[#111115] p-5 border border-[#27272a] mb-8">
              <h4 className="text-sm font-bold text-[#a1a1aa] mb-3">혜택</h4>
              <div className="grid sm:grid-cols-2 gap-2 text-sm text-[#a1a1aa]">
                {(selectedTicket === "ALL" ? [
                  "20일/21일 전체 세션 참여",
                  "한정판 굿즈 제공",
                  "Q&A 참여",
                  "전시 및 체험존 이용",
                  "이벤트 및 경품 참여",
                ] : selectedTicket === "DAY1" ? [
                  "20일 전체 세션 참여",
                  "한정판 굿즈 제공",
                  "Q&A 참여",
                  "전시 및 체험존 이용",
                  "이벤트 및 경품 참여",
                ] : [
                  "21일 전체 세션 참여",
                  "한정판 굿즈 제공",
                  "Q&A 참여",
                  "전시 및 체험존 이용",
                  "이벤트 및 경품 참여",
                ]).map((item, i) => (
                  <div key={i} className="flex items-center gap-2"><span className="w-1.5 h-1.5 rounded-full bg-[#00C1D5]" />{item}</div>
                ))}
              </div>
            </div>
          </div>

          {/* 본인 인증 */}
          <div className={sectionCls}>
            <h2 className="text-lg font-bold text-white mb-5 flex items-center gap-2">
              <ShieldCheck className="w-5 h-5 text-[#00C1D5]" />
              본인 인증
            </h2>
            <p className="text-sm text-[#a1a1aa] mb-5">본인 확인을 위해 아래 인증 방법 중 하나를 선택해주세요.</p>
            <div className="flex flex-wrap gap-4">
              <button className="px-6 py-3 bg-[#00C1D5] text-black font-bold hover:bg-[#00a8ba] transition-all">
                휴대폰 본인 인증
              </button>
              <button className="px-6 py-3 bg-transparent text-[#a1a1aa] font-bold border border-[#27272a] hover:border-white/20 hover:text-white transition-all">
                아이핀 본인 인증
              </button>
            </div>
          </div>

          {/* 기본 정보 */}
          <div className={sectionCls}>
            <h2 className="text-lg font-bold text-white mb-5">기본 정보</h2>
            <div className="grid md:grid-cols-3 gap-6">
              <div className="space-y-2">
                <label className={labelCls}>이름 <span className="text-[#00C1D5]">*</span></label>
                <input type="text" className={inputCls} placeholder="홍길동" />
              </div>
              <div className="space-y-2">
                <label className={labelCls}>이메일 <span className="text-[#00C1D5]">*</span></label>
                <input type="email" className={inputCls} placeholder="email@example.com" />
              </div>
              <div className="space-y-2">
                <label className={labelCls}>연락처 <span className="text-[#00C1D5]">*</span></label>
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
                  <label className={labelCls}>직업 <span className="text-[#00C1D5]">*</span></label>
                  <select value={jobType} onChange={e => setJobType(e.target.value)} className={selectCls}>
                    <option value="">선택해주세요</option>
                    <option value="office">직장인</option>
                    <option value="student">학생</option>
                    <option value="educator">교육자/교육기관</option>
                    <option value="indie">인디 개발자</option>
                    <option value="freelance">프리랜서</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>회사명/소속 <span className="text-[#00C1D5]">*</span></label>
                  <input type="text" className={inputCls} placeholder="에픽게임즈" />
                </div>
              </div>
              <div className="grid md:grid-cols-3 gap-6">
                <div className="space-y-2">
                  <label className={labelCls}>부서</label>
                  <input type="text" className={inputCls} placeholder="개발팀" />
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>직무 <span className="text-[#00C1D5]">*</span></label>
                  <select className={selectCls}>
                    <option value="">선택해주세요</option>
                    <option>비주얼 아트</option>
                    <option>프로그래밍</option>
                    <option>프로덕션</option>
                    <option>엔지니어링</option>
                    <option>설계</option>
                    <option>기획</option>
                    <option>R&D</option>
                    <option>IT</option>
                    <option>감독/PD</option>
                    <option>비즈니스/마케팅</option>
                    <option>C-level</option>
                    <option>기타</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <label className={labelCls}>산업/관심 분야 <span className="text-[#00C1D5]">*</span></label>
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

          {/* 티셔츠 + 트랙 선택 */}
          <div className={sectionCls}>
            <div className="mb-8">
              <h2 className="text-lg font-bold text-white mb-2">티셔츠 사이즈 선택 <span className="text-[#00C1D5]">*</span></h2>
              <p className="text-xs text-[#71717a] mb-4">오프라인 참가자에게 지급되며 사이즈 교환은 불가합니다.</p>
              <div className="flex flex-wrap gap-3">
                {["M", "L", "XL", "XXL"].map(size => (
                  <label key={size} className="relative cursor-pointer">
                    <input type="radio" name="tshirt" value={size} className="peer sr-only" />
                    <div className="w-14 h-14 border border-[#27272a] bg-[#0e0f14] flex items-center justify-center text-sm font-bold text-[#71717a] peer-checked:border-[#00C1D5] peer-checked:bg-[rgba(0,79,89,0.2)] peer-checked:text-[#00C1D5] transition-all hover:border-white/20">
                      {size}
                    </div>
                  </label>
                ))}
              </div>
            </div>

            {showDay1 && (
              <div className="mb-6">
                <h3 className="text-sm font-bold text-white mb-3">Day 1. 8월 20일(목) 트랙 선택 <span className="text-[#00C1D5]">*</span></h3>
                <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                  {[
                    { value: "DAY1_TR1", label: "게임: 프로그래밍" },
                    { value: "DAY1_TR2", label: "게임: 아트" },
                    { value: "DAY1_TR3", label: "미디어 & 엔터테인먼트" },
                    { value: "DAY1_TR4", label: "산업 & 시뮬레이션" },
                  ].map(tr => (
                    <label key={tr.value} className={cn("p-3 border text-center cursor-pointer text-sm font-medium transition-all", day1Track === tr.value ? "border-[#00C1D5] bg-[rgba(0,79,89,0.2)] text-[#9adbe8]" : "border-[#27272a] text-[#71717a] hover:border-white/20")}>
                      <input type="radio" name="day1track" value={tr.value} checked={day1Track === tr.value} onChange={() => setDay1Track(tr.value)} className="sr-only" />
                      {tr.label}
                    </label>
                  ))}
                </div>
                <p className="text-xs text-[#71717a] mt-2">※ 트랙 선택 순서로 입장이 우선 결정됩니다.</p>
              </div>
            )}
            {showDay2 && (
              <div>
                <h3 className="text-sm font-bold text-white mb-3">Day 2. 8월 21일(금) 트랙 선택 <span className="text-[#00C1D5]">*</span></h3>
                <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                  {[
                    { value: "DAY2_TR1", label: "게임: 프로그래밍" },
                    { value: "DAY2_TR2", label: "게임: 아트" },
                    { value: "DAY2_TR3", label: "미디어 & 엔터테인먼트" },
                    { value: "DAY2_TR4", label: "산업 & 시뮬레이션" },
                  ].map(tr => (
                    <label key={tr.value} className={cn("p-3 border text-center cursor-pointer text-sm font-medium transition-all", day2Track === tr.value ? "border-[#00C1D5] bg-[rgba(0,79,89,0.2)] text-[#9adbe8]" : "border-[#27272a] text-[#71717a] hover:border-white/20")}>
                      <input type="radio" name="day2track" value={tr.value} checked={day2Track === tr.value} onChange={() => setDay2Track(tr.value)} className="sr-only" />
                      {tr.label}
                    </label>
                  ))}
                </div>
              </div>
            )}
          </div>

          </div>

          {/* 우측: 결제 sticky 사이드바 */}
          <div className="lg:col-span-5 xl:col-span-4 self-start sticky top-28">
            <div className="bg-[#0e0f14] border border-[#27272a] p-6 lg:p-8 space-y-6">
              <h3 className="text-lg font-bold text-white">주문 요약</h3>

              <div className="pb-5 border-b border-[#27272a]">
                <div className="text-[#00C1D5] font-bold text-sm mb-1">{ticket.sub}</div>
                <div className="flex justify-between items-center">
                  <span className="text-sm text-[#a1a1aa]">티켓 금액</span>
                  <span className="text-sm text-[#a1a1aa]">₩{ticket.price.toLocaleString()}</span>
                </div>
                <div className="flex justify-between items-center mt-1">
                  <span className="text-sm text-[#a1a1aa]">부가세 (VAT)</span>
                  <span className="text-sm text-[#a1a1aa]">포함</span>
                </div>
              </div>

              <div className="flex justify-between items-end">
                <span className="text-[#a1a1aa] font-medium">총 결제 금액</span>
                <span className="text-3xl font-black text-white">₩{ticket.price.toLocaleString()}</span>
              </div>

              {/* 결제 수단 */}
              <div className="space-y-2">
                <label className="flex items-center gap-3 p-3 border border-[#00C1D5] bg-[rgba(0,79,89,0.2)] cursor-pointer">
                  <input type="radio" name="payment" defaultChecked className="text-[#00C1D5] focus:ring-[#00C1D5] w-4 h-4 bg-transparent border-[#27272a]" />
                  <CreditCard className="w-4 h-4 text-[#00C1D5]" />
                  <span className="text-white font-medium text-sm">신용/체크카드</span>
                </label>
                <label className="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20 transition-colors">
                  <input type="radio" name="payment" className="text-[#00C1D5] focus:ring-[#00C1D5] w-4 h-4 bg-transparent border-[#27272a]" />
                  <div className="w-4 h-4 rounded bg-[#FEE500] text-black flex items-center justify-center font-black text-[8px]">P</div>
                  <span className="text-white font-medium text-sm">카카오페이</span>
                </label>
                <label className="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20 transition-colors">
                  <input type="radio" name="payment" className="text-[#00C1D5] focus:ring-[#00C1D5] w-4 h-4 bg-transparent border-[#27272a]" />
                  <div className="w-4 h-4 rounded bg-[#03C75A] text-white flex items-center justify-center font-bold text-[10px]">N</div>
                  <span className="text-white font-medium text-sm">네이버페이</span>
                </label>
                <label className="flex items-center gap-3 p-3 border border-[#27272a] bg-[#111115] cursor-pointer hover:border-white/20 transition-colors">
                  <input type="radio" name="payment" className="text-[#00C1D5] focus:ring-[#00C1D5] w-4 h-4 bg-transparent border-[#27272a]" />
                  <div className="w-4 h-4 rounded bg-[#0064FF] text-white flex items-center justify-center font-bold text-[8px]">T</div>
                  <span className="text-white font-medium text-sm">토스페이</span>
                </label>
              </div>

              {/* 환불 규정 */}
              <div className="text-xs text-[#71717a] space-y-1">
                <p>• 8월 13일 23:59까지 환불 가능</p>
                <p>• 이후 취소/노쇼: 환불 불가</p>
              </div>
              <label className="flex items-start gap-2 cursor-pointer">
                <input type="checkbox" className="mt-0.5 text-[#00C1D5] focus:ring-[#00C1D5] rounded bg-transparent border-[#27272a]" />
                <span className="text-xs text-[#a1a1aa]">취소/환불 규정에 동의합니다. (필수)</span>
              </label>

              <button
                className="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all"
                onClick={() => alert("결제가 완료되었습니다. (데모)")}
              >
                ₩{ticket.price.toLocaleString()} 결제하기
                <Check className="w-5 h-5" />
              </button>

              <Link to="/myticket" className="block w-full text-center text-sm text-[#71717a] hover:text-white py-3 transition-colors">
                등록 확인 / 취소
              </Link>
            </div>
          </div>

        </div>
      </div>
    </div>
  );
}
