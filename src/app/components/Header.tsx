import React, { useState, useEffect } from "react";
import { Menu, X, ArrowRight } from "lucide-react";
import { Link } from "react-router";
import { cn } from "../lib/utils";


const scrollTo = (id: string) => {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: "smooth" });
};

export const Header = () => {
  const [isScrolled, setIsScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 50);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const navLinks = [
    { name: "소개", id: "overview" },
    { name: "아젠다", id: "agenda" },
    { name: "티켓", id: "register" },
    { name: "행사장 안내", id: "venue" },
    { name: "이벤트", id: "event-benefits" },
    { name: "FAQ", id: "faq" },
  ];

  return (
    <header
      className={cn(
        "fixed top-0 left-0 right-0 z-50 transition-all duration-300 border-b",
        isScrolled
          ? "bg-[#09090b]/90 backdrop-blur-md border-white/10 py-4 shadow-sm"
          : "bg-[#09090b]/70 backdrop-blur-sm border-transparent py-6"
      )}
    >
      <div className="max-w-7xl mx-auto px-6 flex items-center justify-between">
        <button onClick={() => scrollTo("hero")} className="flex items-center group">
          <img src="./white_logo.svg" alt="Unreal Fest 2026" className="h-8" />
        </button>

        {/* Desktop Nav */}
        <nav className="hidden lg:flex items-center gap-8">
          <ul className="flex items-center gap-6">
            {navLinks.map((link) => (
              <li key={link.name}>
                <button
                  onClick={() => scrollTo(link.id)}
                  className="text-sm text-slate-300 hover:text-[#00C1D5] transition-colors font-bold"
                >
                  {link.name}
                </button>
              </li>
            ))}
          </ul>

          <div className="flex items-center gap-4 ml-4 pl-8 border-l border-white/10">
            <Link to="/myticket" className="text-sm text-slate-400 hover:text-white transition-colors">
              등록 확인
            </Link>
            <button
              onClick={() => scrollTo("register")}
              className="bg-[#00C1D5] hover:bg-[#004F59] text-white px-5 py-2.5 text-sm font-bold flex items-center gap-2 transition-all"
              style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%)" }}
            >
              지금 등록하기
              <ArrowRight className="w-4 h-4" />
            </button>
          </div>
        </nav>

        {/* Mobile Menu Toggle */}
        <div className="lg:hidden flex items-center gap-4">
          <button
            className="text-white p-2"
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          >
            {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>
      </div>

      {/* Mobile Nav */}
      {mobileMenuOpen && (
        <div className="lg:hidden absolute top-full left-0 right-0 bg-[#09090b] border-b border-white/10 p-6 flex flex-col gap-6 shadow-2xl">
          <ul className="flex flex-col gap-4">
            {navLinks.map((link) => (
              <li key={link.name}>
                <button
                  className="text-lg text-slate-300 hover:text-[#00C1D5] transition-colors block font-medium"
                  onClick={() => { scrollTo(link.id); setMobileMenuOpen(false); }}
                >
                  {link.name}
                </button>
              </li>
            ))}
          </ul>
          <div className="flex flex-col gap-4 pt-4 border-t border-white/10">
            <button
              className="text-center bg-[#00C1D5] hover:bg-[#004F59] text-white px-5 py-3 font-bold"
              style={{ clipPath: "polygon(0 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%)" }}
              onClick={() => { scrollTo("register"); setMobileMenuOpen(false); }}
            >
              지금 등록하기
            </button>
            <button
              className="text-center text-slate-400 py-2"
              onClick={() => { scrollTo("register"); setMobileMenuOpen(false); }}
            >
              등록 확인
            </button>
          </div>
        </div>
      )}
    </header>
  );
};
