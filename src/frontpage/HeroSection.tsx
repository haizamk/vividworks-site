import { ArrowRight, CircleDollarSign, Hammer, MousePointerClick } from "lucide-react";
import { Button } from "../components/ui/Button.tsx";
import { Container } from "../components/layout/Container.tsx";
import { futureTarget } from "../lib/actions.ts";
import { EditorPreview } from "./EditorPreview.tsx";

const points = [
  { icon: MousePointerClick, label: "Tanpa perlu tahu kod" },
  { icon: CircleDollarSign, label: "Skop FREE RM0" },
  { icon: Hammer, label: "Editor masih dibangunkan" },
];

export function HeroSection() {
  const start = futureTarget("bina-website");

  return (
    <section id="utama" aria-labelledby="utama-tajuk" className="scroll-mt-24 overflow-hidden">
      <Container className="grid items-center gap-12 py-14 sm:py-16 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:py-20">
        <div>
          <p className="inline-flex rounded-full bg-fp-indigo-soft px-3 py-1 text-xs font-semibold text-fp-indigo">
            AI Website Builder untuk pasaran Malaysia
          </p>
          <h1 id="utama-tajuk" className="mt-4 max-w-xl text-[2rem] leading-[1.15] font-extrabold tracking-tight text-fp-ink min-[390px]:text-4xl sm:text-5xl lg:text-[3.25rem] lg:leading-[1.08]">
            Bina Website Profesional <span className="text-fp-indigo">dengan AI</span>
          </h1>
          <p className="mt-5 max-w-xl text-base leading-7 text-fp-muted sm:text-lg">
            VividWorks membantu individu dan perniagaan kecil membina website profesional tanpa perlu tahu kod.
          </p>
          <div className="mt-7 flex flex-col gap-3 sm:flex-row">
            <Button href={start.href} external={start.external} variant="indigo" shape="full" size="lg">
              Bina Website Percuma
              <ArrowRight size={18} />
            </Button>
            <Button href="/#templat" variant="indigoSecondary" shape="full" size="lg">
              Lihat Templat
            </Button>
          </div>
          <ul className="mt-7 grid gap-3 sm:grid-cols-3">
            {points.map((point) => (
              <li key={point.label} className="flex items-center gap-2 text-sm font-medium text-fp-ink">
                <point.icon size={16} className="shrink-0 text-fp-indigo" aria-hidden="true" />
                {point.label}
              </li>
            ))}
          </ul>
        </div>
        <EditorPreview />
      </Container>
    </section>
  );
}
