import { useEffect, useRef, useState } from "react";
import { Menu, X } from "lucide-react";
import { NavLink, useLocation } from "react-router-dom";
import { Button } from "../components/ui/Button.tsx";
import { useBodyScrollLock } from "../hooks/useBodyScrollLock.ts";
import { futureTarget } from "../lib/actions.ts";
import { cx } from "../lib/cx.ts";
import { frontNav } from "./content.ts";
import { MobileMenu } from "./MobileMenu.tsx";

const focusableSelector = "a[href], button:not([disabled])";

function currentSection(hash: string): string {
  const id = decodeURIComponent(hash.replace("#", ""));
  if (frontNav.some((item) => item.id === id)) return id;
  return "utama";
}

function Mark() {
  return (
    <span className="inline-flex items-center gap-2.5">
      <svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true">
        <rect width="32" height="32" rx="10" fill="#4F46E5" />
        <path d="M8.8 8.4h3.7L16 18.6l3.5-10.2h3.7L17.2 23.6h-2.4L8.8 8.4z" fill="#FFFFFF" />
      </svg>
      <span className="text-[1.05rem] font-bold tracking-tight text-fp-ink">VividWorks</span>
    </span>
  );
}

export function Navbar() {
  const location = useLocation();
  const locationKey = `${location.pathname}${location.hash}`;
  const [open, setOpen] = useState(false);
  const [menuLocation, setMenuLocation] = useState(locationKey);
  const [scrolled, setScrolled] = useState(false);
  const headerRef = useRef<HTMLDivElement>(null);
  const menuButtonRef = useRef<HTMLButtonElement>(null);
  const login = futureTarget("log-masuk");
  const start = futureTarget("bina-website");
  const currentId = currentSection(location.hash);

  if (menuLocation !== locationKey) {
    setMenuLocation(locationKey);
    setOpen(false);
  }

  useBodyScrollLock(open);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 4);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    if (!open) return;
    const header = headerRef.current;
    const visible = () =>
      header
        ? [...header.querySelectorAll<HTMLElement>(focusableSelector)].filter(
            (element) => element.getClientRects().length > 0,
          )
        : [];
    visible()[0]?.focus();

    function onKeyDown(event: KeyboardEvent) {
      if (event.key === "Escape") {
        setOpen(false);
        menuButtonRef.current?.focus();
        return;
      }
      if (event.key !== "Tab") return;
      const items = visible();
      const first = items[0];
      const last = items[items.length - 1];
      if (!first || !last) return;
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }

    document.addEventListener("keydown", onKeyDown);
    return () => document.removeEventListener("keydown", onKeyDown);
  }, [open]);

  return (
    <div ref={headerRef}>
    <header
      className={cx(
        "sticky top-0 z-50 border-b bg-white/90 backdrop-blur-md",
        scrolled || open ? "border-fp-line" : "border-transparent",
      )}
    >
      <div className="mx-auto flex h-[4.25rem] w-full max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <NavLink to="/#utama" aria-label="VividWorks, halaman utama" className="shrink-0 rounded-lg">
          <Mark />
        </NavLink>

        <nav aria-label="Utama" className="hidden items-center gap-1 xl:flex">
          {frontNav.map((item) => {
            const current = item.id === currentId;
            return (
              <NavLink
                key={item.id}
                to={item.href}
                aria-current={current ? "location" : undefined}
                className={cx(
                  "rounded-lg px-3 py-2 text-sm font-medium text-fp-muted hover:bg-fp-indigo-soft hover:text-fp-ink",
                  current && "text-fp-indigo",
                )}
              >
                {item.label}
              </NavLink>
            );
          })}
        </nav>

        <div className="hidden items-center gap-2 xl:flex">
          <Button href={login.href} external={login.external} variant="indigoGhost" shape="full">
            Log Masuk
          </Button>
          <Button href={start.href} external={start.external} variant="indigo" shape="full">
            Bina Website Percuma
          </Button>
        </div>

        <button
          ref={menuButtonRef}
          type="button"
          className="grid size-11 place-items-center rounded-xl text-fp-ink hover:bg-fp-indigo-soft xl:hidden"
          aria-expanded={open}
          aria-controls="menu-frontpage"
          onClick={() => setOpen((value) => !value)}
        >
          {open ? <X size={20} /> : <Menu size={20} />}
          <span className="sr-only">{open ? "Tutup menu" : "Buka menu"}</span>
        </button>
      </div>

    </header>
    {open ? (
      <MobileMenu id="menu-frontpage" items={frontNav} currentId={currentId} login={login} start={start} />
    ) : null}
    </div>
  );
}
