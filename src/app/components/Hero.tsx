import React, { useState, useEffect } from "react";
import { ArrowRight } from "lucide-react";
import { motion } from "motion/react";

const EARLYBIRD_END = new Date("2026-07-13T23:59:59+09:00").getTime();

const Countdown = () => {
  const [now, setNow] = useState(Date.now());

  useEffect(() => {
    const timer = setInterval(() => setNow(Date.now()), 1000);
    return () => clearInterval(timer);
  }, []);

  const diff = Math.max(0, EARLYBIRD_END - now);
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
  const minutes = Math.floor((diff / (1000 * 60)) % 60);
  const seconds = Math.floor((diff / 1000) % 60);
  const pad = (n: number) => String(n).padStart(2, "0");

  return (
    <div className="relative bg-[#050508] px-8 py-4 cursor-pointer" onClick={() => document.getElementById("register")?.scrollIntoView({ behavior: "smooth" })}>
      {/* 얼리버드 종료까지 뱃지 */}
      <div className="absolute -top-[30px] left-0 bg-[#00C1D5] px-5 py-1">
        <span className="text-[#090a0f] text-[14px] font-bold tracking-tight">얼리버드 종료까지</span>
      </div>
      <div className="flex items-center gap-0 mt-1">
        <div className="flex flex-col items-center w-[40px]">
          <span className="text-xl font-bold text-[#9adbe8] tabular-nums font-mono">{pad(days)}</span>
          <span className="text-[10px] text-[#71717a] mt-1 tracking-wider">일</span>
        </div>
        <span className="text-lg text-[#3f3f46] mx-1.5 font-light">:</span>
        <div className="flex flex-col items-center w-[40px]">
          <span className="text-xl font-bold text-[#9adbe8] tabular-nums font-mono">{pad(hours)}</span>
          <span className="text-[10px] text-[#71717a] mt-1 tracking-wider">시간</span>
        </div>
        <span className="text-lg text-[#3f3f46] mx-1.5 font-light">:</span>
        <div className="flex flex-col items-center w-[40px]">
          <span className="text-xl font-bold text-[#9adbe8] tabular-nums font-mono">{pad(minutes)}</span>
          <span className="text-[10px] text-[#71717a] mt-1 tracking-wider">분</span>
        </div>
        <span className="text-lg text-[#3f3f46] mx-1.5 font-light">:</span>
        <div className="flex flex-col items-center w-[40px]">
          <span className="text-xl font-bold text-[#9adbe8] tabular-nums font-mono">{pad(seconds)}</span>
          <span className="text-[10px] text-[#71717a] mt-1 tracking-wider">초</span>
        </div>
      </div>
    </div>
  );
};

export const Hero = () => {
  return (
    <section id="hero" className="relative h-screen overflow-hidden">
      {/* 배경 영상 */}
      <video
        autoPlay
        loop
        muted
        playsInline
        className="absolute inset-0 w-full h-full object-cover object-bottom"
        style={{ objectPosition: "calc(50% + 200px) bottom" }}
      >
        <source src="https://epiclounge.co.kr/v3/preview_2026/WEBSITE_USE_ONLY_Fest_ambient_loop_1920x1080_v05.mp4" type="video/mp4" />
      </video>
      {/* 그라데이션 오버레이 */}
      <div className="absolute inset-0 bg-gradient-to-b from-black via-black/60 to-transparent"></div>

      {/* 텍스트 콘텐츠 — 왼쪽 정렬 */}
      <div className="relative z-10 max-w-7xl mx-auto px-6 w-full flex flex-col items-start pt-52 md:pt-64 pb-[45vh]">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.1, ease: "easeOut" }}
          className="mb-10"
        >
          <img
            src="https://unrealsummit16.cafe24.com/2026/ufs26/hero_new_main_logo.svg"
            alt="Unreal Fest Seoul 2026"
            style={{ width: "700px", maxWidth: "100%" }}
          />
        </motion.div>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.3, ease: "easeOut" }}
          className="flex flex-col sm:flex-row items-start gap-4 mb-10"
        >
          <button
            onClick={() => document.getElementById("register")?.scrollIntoView({ behavior: "smooth" })}
            className="bg-[#00C1D5] hover:bg-[#004F59] text-white px-8 py-4 font-bold text-lg flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow-lg"
            style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 12px), calc(100% - 12px) 100%, 0 100%)" }}
          >
            지금 등록하기
            <ArrowRight className="w-5 h-5" />
          </button>
          <button
            onClick={() => document.getElementById("agenda")?.scrollIntoView({ behavior: "smooth" })}
            className="bg-white/10 backdrop-blur-md border border-white/20 hover:bg-white/20 text-white px-8 py-4 font-bold text-lg flex items-center justify-center transition-all"
          >
            아젠다 보기
          </button>
        </motion.div>

      </div>

      {/* 카운트다운 — 우측 하단 */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.5, ease: "easeOut" }}
        className="absolute bottom-12 right-12 z-10"
      >
        <Countdown />
      </motion.div>
    </section>
  );
};
