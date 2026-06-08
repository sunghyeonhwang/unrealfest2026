import React, { useEffect } from "react";
import { useLocation } from "react-router";
import { Hero } from "../components/Hero";
import { Overview } from "../components/Overview";
import { Agenda } from "../components/Agenda";
import { Register } from "../components/Register";
import { Venue } from "../components/Venue";
import { Sponsors } from "../components/Sponsors";
import { FAQ } from "../components/FAQ";
import { EventBenefits } from "../components/EventBenefits";

export default function Home() {
  const { hash } = useLocation();

  useEffect(() => {
    if (hash) {
      setTimeout(() => {
        const id = hash.replace("#", "");
        const element = document.getElementById(id);
        if (element) {
          element.scrollIntoView({ behavior: "smooth" });
        }
      }, 100);
    } else {
      window.scrollTo(0, 0);
    }
  }, [hash]);

  return (
    <>
      <Hero />
      <Overview />
      <Agenda />
      <Register />
      <Venue />
      <Sponsors />
      <EventBenefits />
      <FAQ />
    </>
  );
}
