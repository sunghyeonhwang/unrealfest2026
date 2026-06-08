import React, { useEffect } from "react";
import { Outlet, useLocation } from "react-router";
import { Header } from "./components/Header";
import { Footer } from "./components/Footer";

export default function AppLayout() {
  const { pathname } = useLocation();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

  return (
    <div className="bg-white dark:bg-black min-h-screen text-black dark:text-white font-sans selection:bg-cyan-500/30 flex flex-col transition-colors duration-300">
      <Header />
      <main className="flex-grow">
        <Outlet />
      </main>
      <Footer />
    </div>
  );
}
