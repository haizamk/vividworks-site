import { Container } from "../components/layout/Container.tsx";
import { industries } from "./content.ts";

export function IndustryShowcase() {
  return (
    <section id="industri" aria-labelledby="industri-tajuk" className="scroll-mt-24 bg-fp-indigo-soft/70">
      <Container className="py-16 sm:py-20">
        <div className="fp-reveal">
          <div className="mx-auto max-w-2xl text-center">
            <p className="text-sm font-semibold text-fp-indigo">Industri</p>
            <h2 id="industri-tajuk" className="mt-2 text-3xl font-bold tracking-tight text-fp-ink sm:text-4xl">
              Sesuai untuk Pelbagai Industri Perniagaan
            </h2>
            <p className="mt-3 text-sm leading-6 text-fp-muted sm:text-base">
              Contoh kategori perniagaan yang biasa memerlukan laman web. Ia bukan senarai pelanggan sedia ada dan tiada testimoni dipaparkan.
            </p>
          </div>
          <ul className="mt-10 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
            {industries.map((item) => (
              <li key={item.title}>
                <article className="h-full rounded-2xl border border-white bg-white p-4 shadow-[0_16px_40px_-32px_rgba(15,23,42,0.45)] transition-transform duration-200 hover:-translate-y-1 sm:p-5">
                  <span className="grid size-11 place-items-center rounded-xl bg-fp-indigo-soft text-fp-indigo">
                    <item.icon size={20} aria-hidden="true" />
                  </span>
                  <h3 className="mt-4 text-base font-semibold text-fp-ink">{item.title}</h3>
                  <p className="mt-1 text-sm leading-6 text-fp-muted">{item.text}</p>
                </article>
              </li>
            ))}
          </ul>
        </div>
      </Container>
    </section>
  );
}
