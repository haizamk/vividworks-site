import { Check } from "lucide-react";
import { Link } from "react-router-dom";
import { Button } from "../components/ui/Button.tsx";
import { Container } from "../components/layout/Container.tsx";
import { futureTarget } from "../lib/actions.ts";
import { cx } from "../lib/cx.ts";
import { frontPlans } from "./content.ts";

export function PricingSection() {
  const start = futureTarget("bina-website");

  return (
    <section id="harga" aria-labelledby="harga-tajuk" className="scroll-mt-24">
      <Container className="py-16 sm:py-20">
        <div className="fp-reveal">
          <div className="mx-auto max-w-2xl text-center">
            <p className="text-sm font-semibold text-fp-indigo">Harga</p>
            <h2 id="harga-tajuk" className="mt-2 text-3xl font-bold tracking-tight text-fp-ink sm:text-4xl">
              Mulakan dengan skop yang dinyatakan
            </h2>
            <p className="mt-3 text-sm leading-6 text-fp-muted sm:text-base">
              Hanya RM0 untuk pakej FREE telah dinyatakan. Harga Premium belum diumumkan. Laman ini tidak memproses bayaran.
            </p>
          </div>
          <div className="mx-auto mt-10 grid max-w-4xl gap-5 lg:grid-cols-2">
            {frontPlans.map((plan) => (
              <article
                key={plan.id}
                className={cx(
                  "flex h-full flex-col rounded-3xl border bg-white p-6 shadow-[0_20px_50px_-36px_rgba(15,23,42,0.55)] sm:p-8",
                  plan.featured ? "border-fp-indigo ring-1 ring-fp-indigo" : "border-fp-line",
                )}
              >
                <div className="flex items-center justify-between gap-3">
                  <h3 className="text-lg font-semibold text-fp-ink">{plan.name}</h3>
                  {plan.featured ? (
                    <p className="rounded-full bg-fp-indigo-soft px-3 py-1 text-xs font-semibold text-fp-indigo">Skop semasa</p>
                  ) : (
                    <p className="rounded-full bg-[#f8fafc] px-3 py-1 text-xs font-semibold text-fp-muted">Cadangan</p>
                  )}
                </div>
                <p className="mt-4 text-3xl font-extrabold tracking-tight text-fp-ink">{plan.price}</p>
                <p className="mt-2 text-sm leading-6 text-fp-muted">{plan.priceNote}</p>
                <p className="mt-4 text-sm leading-6 text-fp-ink">{plan.summary}</p>
                <ul className="mt-6 flex-1 space-y-3">
                  {plan.features.map((feature) => (
                    <li key={feature} className="flex items-start gap-2 text-sm text-fp-ink">
                      <Check size={16} className="mt-0.5 shrink-0 text-fp-indigo" aria-hidden="true" />
                      {feature}
                    </li>
                  ))}
                </ul>
                <div className="mt-8">
                  {plan.intent === "contact" ? (
                    <Button href="/contact" variant="indigoSecondary" shape="full" className="w-full">
                      {plan.cta}
                    </Button>
                  ) : (
                    <Button href={start.href} external={start.external} variant="indigo" shape="full" className="w-full">
                      {plan.cta}
                    </Button>
                  )}
                </div>
              </article>
            ))}
          </div>
          <p className="mt-6 text-center text-sm text-fp-muted">
            Perbandingan penuh, termasuk label yang belum disahkan, ada di{" "}
            <Link to="/pricing" className="font-semibold text-fp-indigo underline-offset-4 hover:underline">
              halaman harga
            </Link>
            .
          </p>
        </div>
      </Container>
    </section>
  );
}
