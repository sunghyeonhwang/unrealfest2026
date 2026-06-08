import React from "react";
import { Youtube, ArrowUpRight } from "lucide-react";

export const Footer = () => {
  return (
    <footer id="footer" className="bg-black pt-20 pb-10 border-t border-white/10 text-sm">
      <div className="max-w-7xl mx-auto px-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
          <div className="col-span-1 md:col-span-2">
            <div className="flex items-center gap-2 mb-8 group">
              <img src="./white_logo.svg" alt="Unreal Fest 2026" className="h-8" />
            </div>
            <div className="flex gap-4">
              <a href="#" className="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:border-[#00C1D5] transition-colors">
                <Youtube className="w-4 h-4" />
              </a>
              <a href="#" className="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:border-[#00C1D5] transition-colors">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16.273 12.845L7.376 0H0v24h7.727V11.155L16.624 24H24V0h-7.727v12.845z"/></svg>
              </a>
              <a href="#" className="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:border-[#00C1D5] transition-colors">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.477 3 2 6.463 2 10.691c0 2.818 1.867 5.29 4.682 6.678-.177.63-.64 2.284-.733 2.64-.117.443.162.436.34.317.14-.093 2.23-1.516 3.132-2.132.52.078 1.053.12 1.579.12 5.523 0 10-3.463 10-7.623C22 6.463 17.523 3 12 3z"/></svg>
              </a>
            </div>
          </div>

          <div>
            <h4 className="text-white font-bold mb-6 tracking-widest text-xs uppercase">Unreal Fest Seoul</h4>
            <ul className="space-y-4 text-slate-400">
              <li><button onClick={() => document.getElementById("overview")?.scrollIntoView({ behavior: "smooth" })} className="hover:text-[#00C1D5] transition-colors">소개</button></li>
              <li><button onClick={() => document.getElementById("agenda")?.scrollIntoView({ behavior: "smooth" })} className="hover:text-[#00C1D5] transition-colors">아젠다</button></li>
              <li><button onClick={() => document.getElementById("register")?.scrollIntoView({ behavior: "smooth" })} className="hover:text-[#00C1D5] transition-colors">티켓</button></li>
              <li><button onClick={() => document.getElementById("venue")?.scrollIntoView({ behavior: "smooth" })} className="hover:text-[#00C1D5] transition-colors">행사장 안내</button></li>
              <li><button onClick={() => document.getElementById("event-benefits")?.scrollIntoView({ behavior: "smooth" })} className="hover:text-[#00C1D5] transition-colors">이벤트</button></li>
              <li><button onClick={() => document.getElementById("faq")?.scrollIntoView({ behavior: "smooth" })} className="hover:text-[#00C1D5] transition-colors">FAQ</button></li>
            </ul>
          </div>

          <div>
            <h4 className="text-white font-bold mb-6 tracking-widest text-xs uppercase">Epic Lounge</h4>
            <ul className="space-y-4 text-slate-400">
              <li>
                <a href="#" className="hover:text-white transition-colors flex items-center gap-1 group">
                  새소식
                  <ArrowUpRight className="w-3 h-3 opacity-50 group-hover:opacity-100" />
                </a>
              </li>
              <li>
                <a href="#" className="hover:text-white transition-colors flex items-center gap-1 group">
                  이벤트
                  <ArrowUpRight className="w-3 h-3 opacity-50 group-hover:opacity-100" />
                </a>
              </li>
              <li>
                <a href="#" className="hover:text-white transition-colors flex items-center gap-1 group">
                  리소스
                  <ArrowUpRight className="w-3 h-3 opacity-50 group-hover:opacity-100" />
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div className="pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-4 text-slate-500">
          <p>© 2026 Epic Games, Inc. 모든 권리 보유. Unreal, Unreal Engine은 에픽게임즈의 상표입니다.</p>
          <div className="flex gap-6">
            <a href="#" className="hover:text-white transition-colors">이용약관</a>
            <a href="#" className="hover:text-white transition-colors">개인정보처리방침</a>
            <a href="#" className="hover:text-white transition-colors">쿠키 정책</a>
          </div>
        </div>
      </div>
    </footer>
  );
};
