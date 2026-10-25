/**
 * Pricing source of truth for the marketing site.
 *
 * Starter is the planned RM0 specification. Grow, Pro and Elite are named,
 * but their prices and limits are not invented here.
 *
 * Stored plan ids stay inside the Laravel allow-list
 * (free, starter, professional, business) so the contact form does not need
 * a backend change. The visible names are Starter, Grow, Pro and Elite.
 */

export type FeatureId = "pages" | "stack" | "database" | "aiGenerate" | "aiEdit" | "editor";

export type PlanFeature = {
  id: FeatureId;
  value: string;
  confirmed: boolean;
};

export type PlanCtaIntent = "start-free" | "view-info" | "contact";

export type PricingPlan = {
  id: "free" | "starter" | "professional" | "business";
  name: string;
  nameStatus: "confirmed" | "proposed";
  badge: string;
  price: {
    display: string;
    confirmed: boolean;
    note: string;
  };
  summary: string;
  features: PlanFeature[];
  cta: {
    label: string;
    intent: PlanCtaIntent;
  };
};

export const comparisonRows: { id: FeatureId | "nameStatus" | "price"; label: string }[] = [
  { id: "nameStatus", label: "Status nama" },
  { id: "price", label: "Harga" },
  { id: "pages", label: "Halaman" },
  { id: "stack", label: "Format website diterbitkan" },
  { id: "aiGenerate", label: "AI Generate" },
  { id: "aiEdit", label: "AI Edit" },
  { id: "editor", label: "Editor selepas kredit AI" },
  { id: "database", label: "Pangkalan data website pelanggan" },
];

const pendingFeatureNote = "Akan disahkan";

export const pricingPlans: PricingPlan[] = [
  {
    id: "free",
    name: "Starter",
    nameStatus: "confirmed",
    badge: "Dirancang",
    price: {
      display: "RM0",
      confirmed: true,
      note: "Free Forever. Pakej ini belum dibuka.",
    },
    summary: "Spesifikasi yang dirancang. Fungsi di bawah belum tersedia pada laman ini.",
    features: [
      { id: "pages", value: "1–3 halaman statik", confirmed: true },
      { id: "aiGenerate", value: "1 kali AI Generate", confirmed: true },
      { id: "aiEdit", value: "2 kali AI Edit", confirmed: true },
      { id: "editor", value: "Editor GrapesJS selepas kredit AI habis", confirmed: true },
      { id: "stack", value: "HTML, CSS dan JavaScript untuk website diterbitkan", confirmed: true },
      {
        id: "database",
        value: "Tiada pangkalan data khusus untuk website pelanggan",
        confirmed: true,
      },
    ],
    cta: { label: "Bina Website Percuma", intent: "start-free" },
  },
  {
    id: "starter",
    name: "Grow",
    nameStatus: "proposed",
    badge: "Harga belum diumumkan",
    price: { display: "Harga Akan Diumumkan", confirmed: false, note: "Tiada harga atau had penggunaan." },
    summary: "Nama pakej telah ditetapkan. Butiran lain belum diumumkan.",
    features: [],
    cta: { label: "Tanya butiran", intent: "contact" },
  },
  {
    id: "professional",
    name: "Pro",
    nameStatus: "proposed",
    badge: "Harga belum diumumkan",
    price: { display: "Harga Akan Diumumkan", confirmed: false, note: "Tiada harga atau had penggunaan." },
    summary: "Nama pakej telah ditetapkan. Butiran lain belum diumumkan.",
    features: [],
    cta: { label: "Tanya butiran", intent: "contact" },
  },
  {
    id: "business",
    name: "Elite",
    nameStatus: "proposed",
    badge: "Harga belum diumumkan",
    price: { display: "Harga Akan Diumumkan", confirmed: false, note: "Tiada harga atau had penggunaan." },
    summary: "Nama pakej telah ditetapkan. Butiran lain belum diumumkan.",
    features: [],
    cta: { label: "Tanya butiran", intent: "contact" },
  },
];

export const pricingDisclaimer =
  "Starter ialah spesifikasi RM0 yang dirancang dan belum dilancarkan. Grow, Pro dan Elite belum mempunyai harga atau had penggunaan. Laman ini tidak memproses bayaran.";

export function planById(id: string | null | undefined): PricingPlan | undefined {
  return pricingPlans.find((plan) => plan.id === id);
}

export function comparisonValue(
  plan: PricingPlan,
  rowId: (typeof comparisonRows)[number]["id"],
): { value: string; confirmed: boolean } {
  if (rowId === "nameStatus") {
    return plan.nameStatus === "confirmed"
      ? { value: "Digunakan pada laman ini", confirmed: true }
      : { value: "Label pemasaran cadangan", confirmed: true };
  }
  if (rowId === "price") {
    return { value: plan.price.display, confirmed: plan.price.confirmed };
  }
  const feature = plan.features.find((item) => item.id === rowId);
  if (!feature) return { value: pendingFeatureNote, confirmed: false };
  return { value: feature.value, confirmed: feature.confirmed };
}
