import { useState } from "react";
import type { KeyboardEvent } from "react";
import { ChevronDown } from "lucide-react";
import { Link } from "react-router-dom";
import { Container } from "../components/layout/Container.tsx";
import { faqs } from "../data/faqs.ts";
import { cx } from "../lib/cx.ts";

export function FAQAccordion() {
  const [openId, setOpenId] = useState<string | null>(faqs[0]?.id ?? null);

  function moveFocus(index: number) {
    const item = faqs[index];
    if (!item) return;
    document.getElementById(`fp-soalan-${item.id}`)?.focus();
  }

  function onKeyDown(event: KeyboardEvent<HTMLButtonElement>, index: number) {
    if (event.key === "ArrowDown") {
      event.preventDefault();
      moveFocus((index + 1) % faqs.length);
    } else if (event.key === "ArrowUp") {
      event.preventDefault();
      moveFocus((index - 1 + faqs.length) % faqs.length);
    } else if (event.key === "Home") {
      event.preventDefault();
      moveFocus(0);
    } else if (event.key === "End") {
      event.preventDefault();
      moveFocus(faqs.length - 1);
    }
  }

  return (
    <section id="faq" aria-labelledby="faq-tajuk" className="scroll-mt-24 bg-fp-indigo-soft/70">
      <Container className="py-16 sm:py-20">
        <div className="fp-reveal">
          <div className="mx-auto max-w-2xl text-center">
            <p className="text-sm font-semibold text-fp-indigo">FAQ</p>
            <h2 id="faq-tajuk" className="mt-2 text-3xl font-bold tracking-tight text-fp-ink sm:text-4xl">
              Jawapan berdasarkan status sebenar
            </h2>
            <p className="mt-3 text-sm leading-6 text-fp-muted sm:text-base">
              Jika sesuatu belum dibuka atau belum disahkan, jawapan menyatakan demikian.
            </p>
          </div>
          <div className="mx-auto mt-10 max-w-3xl divide-y divide-fp-line overflow-hidden rounded-2xl border border-fp-line bg-white">
            {faqs.map((item, index) => {
              const open = openId === item.id;
              const panelId = `fp-jawapan-${item.id}`;
              const buttonId = `fp-soalan-${item.id}`;
              return (
                <div key={item.id}>
                  <h3 className="m-0 text-base font-semibold">
                    <button
                      id={buttonId}
                      type="button"
                      className="flex w-full items-center justify-between gap-4 px-4 py-4 text-left text-fp-ink hover:bg-fp-indigo-soft/60 sm:px-5"
                      aria-expanded={open}
                      aria-controls={panelId}
                      onClick={() => setOpenId(open ? null : item.id)}
                      onKeyDown={(event) => onKeyDown(event, index)}
                    >
                      <span>{item.question}</span>
                      <ChevronDown
                        size={18}
                        className={cx("shrink-0 text-fp-indigo transition-transform duration-200", open && "rotate-180")}
                      />
                    </button>
                  </h3>
                  <div
                    id={panelId}
                    role="region"
                    aria-labelledby={buttonId}
                    className={cx("grid transition-[grid-template-rows] duration-200", open ? "grid-rows-[1fr]" : "grid-rows-[0fr]")}
                  >
                    <div className="overflow-hidden" hidden={open ? undefined : true}>
                      <p className="px-4 pb-5 text-sm leading-7 text-fp-muted sm:px-5">{item.answer}</p>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
          <p className="mt-6 text-center text-sm text-fp-muted">
            <Link to="/faq" className="font-semibold text-fp-indigo underline-offset-4 hover:underline">
              Halaman FAQ penuh
            </Link>
          </p>
        </div>
      </Container>
    </section>
  );
}
