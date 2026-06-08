import { createHashRouter } from "react-router";
import AppLayout from "./AppLayout";
import Home from "./pages/Home";
import TicketPurchase from "./pages/TicketPurchase";
import Sessions from "./pages/Sessions";
import SessionDetail from "./pages/SessionDetail";
import SponsorsDetail from "./pages/SponsorsDetail";
import TicketCancel from "./pages/TicketCancel";
import MyTicket from "./pages/MyTicket";
import Schedule from "./pages/Schedule";
import TicketOnline from "./pages/TicketOnline";

export const router = createHashRouter([
  {
    path: "/",
    Component: AppLayout,
    children: [
      { index: true, Component: Home },
      { path: "ticket", Component: TicketPurchase },
      { path: "ticket/cancel", Component: TicketCancel },
      { path: "myticket", Component: MyTicket },
      { path: "schedule", Component: Schedule },
      { path: "ticket/online", Component: TicketOnline },
      { path: "sessions", Component: Sessions },
      { path: "session/:id", Component: SessionDetail },
      { path: "sponsors", Component: SponsorsDetail },
    ],
  },
]);
