import { RouterProvider } from "react-router";
import { ThemeProvider } from "next-themes";
import { router } from "./routes";

// Force reload
export default function App() {
  return (
    <ThemeProvider attribute="class" defaultTheme="dark">
      <RouterProvider router={router} />
    </ThemeProvider>
  );
}
