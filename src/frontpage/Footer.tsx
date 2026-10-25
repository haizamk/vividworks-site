import { Link } from "react-router-dom";
import { Container } from "../components/layout/Container.tsx";
import { site } from "../data/site.ts";
import { frontNav } from "./content.ts";

const more = [
  { label: "Hubungi", href: "/contact" },
  { label: "Privasi", href: "/privacy" },
  { label: "Terma", href: "/terms" },
  { label: "Akan Datang", href: "/akan-datang" },
];

export function Footer() {
  return (
    <footer className="border-t border-fp-line bg-white">
      <Container className="grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <Link to="/#utama" className="inline-flex items-center gap-2.5 rounded-lg" aria-label="VividWorks, halaman utama">
            <svg width="28" height="28" viewBox="0 0 32 32" aria-hidden="true">
              <rect width="32" height="32" rx="10" fill="#4F46E5" />
              <path d="M8.8 8.4h3.7L16 18.6l3.5-10.2h3.7L17.2 23.6h-2.4L8.8 8.4z" fill="#FFFFFF" />
            </svg>
            <span className="text-base font-bold tracking-tight text-fp-ink">VividWorks</span>
          </Link>
          <p className="mt-4 max-w-xs text-sm leading-6 text-fp-muted">
            Platform AI Website Builder untuk individu dan perniagaan kecil di Malaysia. Laman ini ialah laman pemasaran.
          </p>
        </div>
        <div>
          <h2 className="text-sm font-semibold text-fp-ink">Pada halaman ini</h2>
          <ul className="mt-4 space-y-2">
            {frontNav.map((item) => (
              <li key={item.id}>
                <Link to={item.href} className="text-sm text-fp-muted hover:text-fp-indigo">
                  {item.label}
                </Link>
              </li>
            ))}
          </ul>
        </div>
        <div>
          <h2 className="text-sm font-semibold text-fp-ink">Halaman lain</h2>
          <ul className="mt-4 space-y-2">
            {more.map((item) => (
              <li key={item.href}>
                <Link to={item.href} className="text-sm text-fp-muted hover:text-fp-indigo">
                  {item.label}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </Container>
      <div className="border-t border-fp-line">
        <Container className="py-5 text-sm text-fp-muted">
          <p>© {site.year} vividworks.my. Hak cipta terpelihara.</p>
        </Container>
      </div>
    </footer>
  );
}
