const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export const planIds = ["free", "starter", "professional", "business"];

const planLabels = {
  free: "Starter",
  starter: "Grow",
  professional: "Pro",
  business: "Elite",
};

export function validateContact(draft) {
  const errors = {};
  const name = String(draft?.name ?? "").trim();
  const email = String(draft?.email ?? "").trim();
  const business = String(draft?.business ?? "").trim();
  const plan = String(draft?.plan ?? "").trim();
  const message = String(draft?.message ?? "").trim();

  if (name.length < 2) errors.name = "Masukkan nama (sekurang-kurangnya 2 aksara).";
  else if (name.length > 80) errors.name = "Nama terlalu panjang.";

  if (!emailPattern.test(email) || email.length > 120) {
    errors.email = "Masukkan alamat e-mel yang sah.";
  }

  if (business.length > 120) errors.business = "Nama perniagaan terlalu panjang.";

  if (plan && !planIds.includes(plan)) errors.plan = "Pakej tidak dikenali.";

  if (message.length < 20) {
    errors.message = "Mesej perlu sekurang-kurangnya 20 aksara supaya ia jelas.";
  } else if (message.length > 2000) {
    errors.message = "Mesej terlalu panjang. Pendekkan kepada 2,000 aksara.";
  }

  return errors;
}

export function normalizeContact(draft) {
  return {
    name: String(draft?.name ?? "").trim(),
    email: String(draft?.email ?? "").trim(),
    business: String(draft?.business ?? "").trim(),
    plan: String(draft?.plan ?? "").trim(),
    message: String(draft?.message ?? "").trim(),
  };
}

export function contactSummary(draft) {
  const contact = normalizeContact(draft);
  return [
    `Nama: ${contact.name}`,
    `E-mel: ${contact.email}`,
    `Perniagaan: ${contact.business || "—"}`,
    `Pakej: ${planLabels[contact.plan] || contact.plan || "Belum pasti"}`,
    "",
    contact.message,
  ].join("\n");
}
