import React from "react";
import { CalendarDays } from "lucide-react";

interface EventCardProps {
  type: "오프라인" | "온라인";
  title: string;
  desc: string;
  date: string;
  note?: string;
}

const EventCard = ({ type, title, desc, date, note }: EventCardProps) => {
  const isOffline = type === "오프라인";
  return (
    <div className="bg-[#131418] rounded-[6px] p-6 md:p-8 flex flex-col gap-3 min-h-[192px]">
      <span
        className="inline-block self-start text-[12px] font-semibold px-3 py-1"
        style={{
          fontFamily: "'Inter Tight', sans-serif",
          backgroundColor: isOffline ? "#00C1D5" : "#FF8F1C",
          color: "#0b0c10",
        }}
      >
        {type} 전용
      </span>
      <h3
        className="text-[24px] font-extrabold text-white leading-[32px]"
        style={{ fontFamily: "'Inter Tight', sans-serif" }}
      >
        {title}
      </h3>
      <p
        className="text-[14px] text-[#90a1b9] flex-grow"
        style={{ fontFamily: "'Inter Tight', sans-serif", letterSpacing: "-0.42px" }}
      >
        {desc}
      </p>
      {note && <p className="text-[12px] text-[#71717a]" style={{ fontFamily: "'Inter Tight', sans-serif" }}>{note}</p>}
      <div className="flex items-center gap-1.5 text-[12px] font-medium text-white" style={{ fontFamily: "'Inter Tight', sans-serif" }}>
        <CalendarDays className="w-3.5 h-3.5" />
        {date}
      </div>
    </div>
  );
};

export const EventBenefits = () => {
  return (
    <section id="event-benefits" className="py-24 bg-[#09090b] relative border-t border-white/5">
      <div className="max-w-7xl mx-auto px-6">
        <div className="mb-16">
          <h2 className="text-3xl md:text-5xl font-bold text-white mb-4 tracking-tight">이벤트</h2>
          <p className="text-lg text-[#90a1b9]">
            현장과 온라인에서 진행되는 다양한 이벤트를 만나보세요.
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-6">
          <EventCard
            type="오프라인"
            title="오프라인 등록 이벤트"
            desc="현장 참석자 전원에게 한정판 굿즈 증정!"
            date="8.20 (목) ~ 8.21 (금)"
          />
          <EventCard
            type="오프라인"
            title="경품 추첨 이벤트"
            desc="오프라인 참석자 대상, 세션 종료 후 경품 추첨! (1일 1회)"
            date="8.20 (목) ~ 8.21 (금)"
          />
          <EventCard
            type="오프라인"
            title="얼리버드 체크인 이벤트"
            desc="현장 체크인 선착순 300명 한정판 굿즈 추가 증정!"
            date="8.20 (목) 한정"
          />
          <EventCard
            type="온라인"
            title="출석 체크 이벤트"
            desc="양일간 시청한 분 중 추첨을 통해 300명께 굿즈 증정!"
            note="*온라인 체크인 시 자동 응모"
            date="8.20 (목) ~ 8.21 (금)"
          />
        </div>

        <p className="text-xs text-[#71717a] mt-8 text-right">
          · 경품은 사정에 따라 변경되거나 이미지와 다를 수 있습니다.
        </p>
      </div>
    </section>
  );
};
