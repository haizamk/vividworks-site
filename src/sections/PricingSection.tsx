import { pricingDisclaimer } from "../data/pricing.ts";
import { Container } from "../components/layout/Container.tsx";
import { SectionHeading } from "../components/common/SectionHeading.tsx";
import { PricingGrid } from "../components/pricing/PricingCard.tsx";
import { Button } from "../components/ui/Button.tsx";

export function PricingSection() {
  return (
    <section id="pakej" aria-labelledby="pakej-tajuk">
      <Container className="py-16 sm:py-20">
        <SectionHeading
          eyebrow="Pakej"
          title="Mulakan dengan skop yang jelas"
          description="Starter ialah spesifikasi RM0 yang dirancang. Grow, Pro dan Elite belum mempunyai harga atau had."
          titleId="pakej-tajuk"
        />
        <div className="mt-12">
          <PricingGrid />
        </div>
        <p className="mx-auto mt-8 max-w-3xl text-center text-sm leading-6 text-muted">{pricingDisclaimer}</p>
        <div className="mt-6 text-center">
          <Button href="/pricing" variant="secondary">
            Lihat perbandingan penuh
          </Button>
        </div>
      </Container>
    </section>
  );
}
