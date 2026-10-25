import { pricingDisclaimer } from "../data/pricing.ts";
import { breadcrumbNode, graph, organizationNodes } from "../lib/schema.ts";
import { ComparisonTable } from "../components/pricing/ComparisonTable.tsx";
import { PricingGrid } from "../components/pricing/PricingCard.tsx";
import { Container } from "../components/layout/Container.tsx";
import { PageHeader } from "../components/layout/PageHeader.tsx";
import { Seo } from "../components/seo/Seo.tsx";
import { CtaSection } from "../sections/CtaSection.tsx";

const description =
  "Bandingkan Starter RM0 yang dirancang dengan Grow, Pro dan Elite. Harga yang belum diumumkan dipaparkan dengan jelas.";

export default function PricingPage() {
  return (
    <>
      <Seo
        title="Pakej"
        description={description}
        path="/pricing"
        jsonLd={graph([
          ...organizationNodes("/pricing"),
          breadcrumbNode([
            { name: "Utama", path: "/" },
            { name: "Pakej", path: "/pricing" },
          ]),
        ])}
      />
      <PageHeader eyebrow="Pakej" title="Pakej" description={description} />
      <Container className="py-14 sm:py-16">
        <PricingGrid />
        <p className="mx-auto mt-8 max-w-3xl text-sm leading-6 text-muted">{pricingDisclaimer}</p>
        <h2 id="perbandingan" className="mt-14 text-2xl font-semibold tracking-tight text-navy">
          Perbandingan
        </h2>
        <p className="mt-2 max-w-2xl text-sm leading-6 text-muted">
          Hanya maklumat yang telah dinyatakan dipaparkan sebagai disahkan. Sel lain menunggu spesifikasi rasmi.
        </p>
        <div className="mt-6">
          <ComparisonTable />
        </div>
      </Container>
      <CtaSection />
    </>
  );
}
