import { Container } from "../components/layout/Container.tsx";
import { steps } from "./content.ts";

export function HowItWorks() {
  return (
    <section id="cara" aria-labelledby="cara-tajuk" className="scroll-mt-24">
      <Container className="py-16 sm:py-20">
        <div className="fp-reveal">
          <div className="mx-auto max-w-2xl text-center">
            <p className="text-sm font-semibold text-fp-indigo">Cara berfungsi</p>
            <h2 id="cara-tajuk" className="mt-2 text-3xl font-bold tracking-tight text-fp-ink sm:text-4xl">
              Tiga langkah yang dirancang
            </h2>
            <p className="mt-3 text-sm leading-6 text-fp-muted sm:text-base">
              Editor dan penerbitan masih dalam pembangunan. Langkah ini menerangkan arah produk, bukan aliran yang sudah hidup.
            </p>
          </div>
          <ol className="mt-12 grid gap-8 lg:grid-cols-3 lg:gap-6">
            {steps.map((step, index) => (
              <li key={step.id} className="relative text-center">
                {index < steps.length - 1 ? (
                  <span aria-hidden="true" className="absolute top-6 left-[calc(50%+2.5rem)] hidden h-px w-[calc(100%-5rem)] bg-fp-line lg:block" />
                ) : null}
                <span className="relative mx-auto grid size-12 place-items-center rounded-full bg-fp-indigo text-lg font-bold text-white">
                  {index + 1}
                </span>
                <h3 className="mt-4 text-lg font-semibold text-fp-ink">{step.title}</h3>
                <p className="mx-auto mt-2 max-w-xs text-sm leading-6 text-fp-muted">{step.text}</p>
              </li>
            ))}
          </ol>
        </div>
      </Container>
    </section>
  );
}
