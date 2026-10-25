import { Check } from "lucide-react";
import { Link } from "react-router-dom";
import { Button } from "../components/ui/Button.tsx";
import { Container } from "../components/layout/Container.tsx";
import { pricingPlans } from "../data/pricing.ts";
import { futureTarget } from "../lib/actions.ts";
import { cx } from "../lib/cx.ts";

export function PricingSection() {
  const start = futureTarget("bina-website");

  return (
    <section id="harga" aria-labelledby="harga-tajuk" className="scroll-mt-24">
      <Container className="py-16 sm:py-20">
        <div className="fp-reveal">
          <div className="mx-auto max-w-2xl text-center">
            <p className="text-sm font-semibold text-fp-indigo">Harga</p>
            <h2 id="harga-tajuk" className="mt-2 text-3xl font-bold tracking-tight text-fp-ink sm:text-4xl">
              Empat pakej, satu harga yang dinyatakan
            </h2>
            <p className="mt-3 text-sm leading-6 text-fp-muted sm:text-base">
              Starter ialah RM0, Free Forever, dan masih dirancang. Grow, Pro dan Elite belum mempunyai harga atau had. Laman ini tidak memproses bayaran.
            </p>
          </div>
          <div className="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {pricingPlans.map((plan) => {
              const featured = plan.id === "free";
              return (
                <article
                  key={plan.id}
                  className={cx(
                    "flex h-full flex-col rounded-3xl border bg-white p-5 shadow-[0_20px_50px_-36px_rgba(15,23,42,0.55)]",
                    featured ? "border-fp-indigo ring-1 ring-fp-indigo" : "border-fp-line",
                  )}
                >
                  <div className="flex items-start justify-between gap-3">
                    <h3 className="text-lg font-semibold text-fp-ink">{plan.name}</h3>
                    <p
                      className={cx(
                        "rounded-full px-2.5 py-1 text-[11px] font-semibold",
                        featured ? "bg-fp-indigo-soft text-fp-indigo" : "bg-[#f8fafc] text-fp-muted",
                      )}
                    >
                      {plan.badge}
                    </p>
                  </div>
                  <p className="mt-4 text-2xl font-extrabold tracking-tight text-fp-ink">{plan.price.display}</p>
                  {plan.price.note ? <p className="mt-2 text-sm leading-6 text-fp-muted">{plan.price.note}</p> : null}
                  <p className="mt-4 text-sm leading-6 text-fp-ink">{plan.summary}</p>
                  {plan.features.length > 0 ? (
                    <ul className="mt-5 flex-1 space-y-3">
                      {plan.features.map((feature) => (
                        <li key={feature.id} className="flex items-start gap-2 text-sm text-fp-ink">
                          <Check size={16} className="mt-0.5 shrink-0 text-fp-indigo" aria-hidden="true" />
                          {feature.value}
                        </li>
                      ))}
                    </ul>
                  ) : (
                    <p className="mt-5 flex-1 rounded-2xl border border-dashed border-fp-line px-4 py-4 text-sm leading-6 text-fp-muted">
                      Tiada harga, had atau ciri tambahan diumumkan.
                    </p>
                  )}
                  <div className="mt-6">
                    {plan.cta.intent === "contact" ? (
                      <Button href={`/contact?pakej=${plan.id}`} variant="indigoSecondary" shape="full" className="w-full">
                        {plan.cta.label}
                      </Button>
                    ) : (
                      <Button href={start.href} external={start.external} variant="indigo" shape="full" className="w-full">
                        {plan.cta.label}
                      </Button>
                    )}
                  </div>
                </article>
              );
            })}
          </div>
          <p className="mt-6 text-center text-sm text-fp-muted">
            Perbandingan penuh ada di{" "}
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
