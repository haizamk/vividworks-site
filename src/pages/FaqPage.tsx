import { faqs } from "../data/faqs.ts";
import { breadcrumbNode, faqNodes, graph, organizationNodes } from "../lib/schema.ts";
import { Accordion } from "../components/ui/Accordion.tsx";
import { Button } from "../components/ui/Button.tsx";
import { Container } from "../components/layout/Container.tsx";
import { PageHeader } from "../components/layout/PageHeader.tsx";
import { Seo } from "../components/seo/Seo.tsx";

const description =
  "Jawapan tentang coding, pakej Starter, suntingan, telefon bimbit, domain, naik taraf dan bila pembina website AI boleh digunakan.";

export default function FaqPage() {
  return (
    <>
      <Seo
        title="Soalan Lazim"
        description={description}
        path="/faq"
        jsonLd={graph([
          ...organizationNodes("/faq"),
          ...faqNodes("/faq"),
          breadcrumbNode([
            { name: "Utama", path: "/" },
            { name: "Soalan Lazim", path: "/faq" },
          ]),
        ])}
      />
      <PageHeader
        eyebrow="Soalan lazim"
        title="Soalan Lazim"
        description={description}
      />
      <Container className="py-14 sm:py-16">
        <div className="mx-auto max-w-3xl">
          <Accordion items={faqs} />
          <div className="mt-8 rounded-2xl border border-line bg-surface p-5">
            <h2 className="text-lg font-semibold text-navy">Masih perlukan penjelasan?</h2>
            <p className="mt-2 text-sm leading-6 text-muted">
              Saluran e-mel, telefon dan alamat rasmi belum disahkan. Borang hubungi menyimpan mesej pada pelayan, tetapi e-mel automatik belum diaktifkan.
            </p>
            <div className="mt-4">
              <Button href="/contact">Hubungi Kami</Button>
            </div>
          </div>
        </div>
      </Container>
    </>
  );
}
