import { Suspense } from "react";
import { Outlet, useLocation } from "react-router-dom";
import { Footer as FrontFooter } from "../../frontpage/Footer.tsx";
import { Navbar } from "../../frontpage/Navbar.tsx";
import { useRouteScroll } from "../../hooks/useRouteScroll.ts";
import { cx } from "../../lib/cx.ts";
import { Footer } from "./Footer.tsx";
import { Header } from "./Header.tsx";
import { RouteFallback } from "./RouteFallback.tsx";

export function RootLayout() {
  useRouteScroll();
  const { pathname } = useLocation();
  const frontpage = pathname === "/";

  return (
    <div className={cx("flex min-h-screen flex-col bg-white", frontpage && "fp-shell")}>
      <a className="skip-link" href="#kandungan">
        Langkau ke kandungan
      </a>
      {frontpage ? <Navbar /> : <Header />}
      <main id="kandungan" tabIndex={-1} className="flex-1">
        <Suspense fallback={<RouteFallback />}>
          <Outlet />
        </Suspense>
      </main>
      {frontpage ? <FrontFooter /> : <Footer />}
    </div>
  );
}
