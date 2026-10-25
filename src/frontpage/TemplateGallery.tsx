import { useRef } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { Link } from "react-router-dom";
import { PreviewImage } from "../components/templates/PreviewImage.tsx";
import { Container } from "../components/layout/Container.tsx";
import { templates } from "../data/templates.ts";
import { showcaseTemplates } from "./content.ts";

export function TemplateGallery() {
  const scroller = useRef<HTMLUListElement>(null);

  function scrollByCard(direction: -1 | 1) {
    const node = scroller.current;
    if (!node) return;
    const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const card = node.querySelector("li");
    const distance = card ? card.getBoundingClientRect().width + 16 : node.clientWidth * 0.8;
    node.scrollBy({ left: direction * distance, behavior: reduce ? "auto" : "smooth" });
  }

  const cards = showcaseTemplates.map((item) => {
    const template = templates.find((entry) => entry.id === item.id);
    return { ...item, template };
  });

  return (
    <section id="templat" aria-labelledby="templat-tajuk" className="scroll-mt-24 bg-fp-indigo-soft/70">
      <Container className="py-16 sm:py-20">
        <div className="fp-reveal">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div className="max-w-2xl">
              <p className="text-sm font-semibold text-fp-indigo">Templat</p>
              <h2 id="templat-tajuk" className="mt-2 text-3xl font-bold tracking-tight text-fp-ink sm:text-4xl">
                Contoh reka bentuk
              </h2>
              <p className="mt-3 text-sm leading-6 text-fp-muted sm:text-base">
                Enam arah visual untuk restoran, kedai, servis, fesyen, korporat dan portfolio. Contoh ini belum boleh digunakan untuk membina website.
              </p>
            </div>
            <div className="flex gap-2">
              <button
                type="button"
                className="grid size-11 place-items-center rounded-full border border-fp-line bg-white text-fp-ink hover:bg-fp-indigo-soft"
                onClick={() => scrollByCard(-1)}
              >
                <ChevronLeft size={18} />
                <span className="sr-only">Templat sebelumnya</span>
              </button>
              <button
                type="button"
                className="grid size-11 place-items-center rounded-full border border-fp-line bg-white text-fp-ink hover:bg-fp-indigo-soft"
                onClick={() => scrollByCard(1)}
              >
                <ChevronRight size={18} />
                <span className="sr-only">Templat seterusnya</span>
              </button>
            </div>
          </div>

          <ul
            ref={scroller}
            className="mt-8 flex w-full min-w-0 snap-x snap-mandatory gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
          >
            {cards.map((card) => (
              <li key={card.id} className="w-[min(100%,18.5rem)] shrink-0 snap-start">
                <article className="flex h-full flex-col overflow-hidden rounded-2xl border border-white bg-white shadow-[0_18px_40px_-32px_rgba(15,23,42,0.5)] transition-transform duration-200 hover:-translate-y-1">
                  {card.template ? (
                    <PreviewImage
                      src={card.template.image}
                      alt={card.template.alt}
                      width={960}
                      height={600}
                      className="aspect-[16/10] bg-fp-indigo-soft"
                    />
                  ) : null}
                  <div className="flex flex-1 flex-col p-4">
                    <h3 className="text-base font-semibold text-fp-ink">{card.label}</h3>
                    <p className="mt-1 text-sm text-fp-muted">{card.note}</p>
                    <p className="mt-3 text-xs font-semibold tracking-wide text-fp-indigo uppercase">Pratonton sahaja</p>
                  </div>
                </article>
              </li>
            ))}
          </ul>

          <p className="mt-6 text-sm text-fp-muted">
            <Link to="/templates" className="font-semibold text-fp-indigo underline-offset-4 hover:underline">
              Buka galeri pratonton penuh
            </Link>
            . Memilih template di sana membawa ke halaman Akan Datang dan tidak menyimpan pilihan.
          </p>
        </div>
      </Container>
    </section>
  );
}
