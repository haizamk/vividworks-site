import { site } from "../data/site.ts";
import { faqNodes, graph, organizationNodes } from "../lib/schema.ts";
import { Seo } from "../components/seo/Seo.tsx";
import { FAQAccordion } from "../frontpage/FAQAccordion.tsx";
import { FinalCTA } from "../frontpage/FinalCTA.tsx";
import { HeroSection } from "../frontpage/HeroSection.tsx";
import { HowItWorks } from "../frontpage/HowItWorks.tsx";
import { IndustryShowcase } from "../frontpage/IndustryShowcase.tsx";
import { PricingSection } from "../frontpage/PricingSection.tsx";
import { TemplateGallery } from "../frontpage/TemplateGallery.tsx";

const description = site.description;

export function HomePage() {
  return (
    <>
      <Seo
        title="Bina Website Profesional dengan AI"
        description={description}
        path="/"
        jsonLd={graph([...organizationNodes("/"), ...faqNodes("/")])}
      />
      <HeroSection />
      <IndustryShowcase />
      <HowItWorks />
      <TemplateGallery />
      <PricingSection />
      <FAQAccordion />
      <FinalCTA />
    </>
  );
}
