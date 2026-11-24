const siteUrl = (import.meta.env.VITE_SITE_URL?.trim() || "https://vividworks.my").replace(
  /\/$/,
  "",
);

export const site = {
  name: "vividworks.my",
  url: siteUrl,
  locale: "ms-MY",
  ogLocale: "ms_MY",
  year: 2026,
  description:
    "Bina website profesional dengan AI untuk perniagaan Malaysia — tanpa kod. Laman ini memaparkan arah produk vividworks.my; pembina, editor dan penerbitan belum dibuka.",
  footerDescription:
    "Platform AI Website Builder untuk individu dan perniagaan kecil di Malaysia. Laman ini ialah laman pemasaran; tiada akaun, bayaran atau website pelanggan diproses di sini.",
  marketingNotice:
    "Pembina website AI, editor visual dan penerbitan belum dibuka. Butang daftar atau bina website membawa ke halaman Akan Datang sehingga platform disepadukan.",
  contact: {
    email: import.meta.env.VITE_CONTACT_EMAIL?.trim() || "",
    phone: "",
    address: "",
  },
  future: {
    loginUrl: import.meta.env.VITE_SAAS_LOGIN_URL?.trim() || "",
    registerUrl: import.meta.env.VITE_SAAS_REGISTER_URL?.trim() || "",
    editorUrl: import.meta.env.VITE_SAAS_EDITOR_URL?.trim() || "",
  },
} as const;

export const navigation = [
  { label: "Utama", href: "/" },
  { label: "Pakej", href: "/pricing" },
  { label: "Template", href: "/templates" },
  { label: "Cara Kerja", href: "/how-it-works" },
  { label: "Soalan Lazim", href: "/faq" },
  { label: "Hubungi Kami", href: "/contact" },
] as const;

export const legalLinks = [
  { label: "Dasar Privasi", href: "/privacy" },
  { label: "Terma Perkhidmatan", href: "/terms" },
] as const;

export function absoluteUrl(path = "/"): string {
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  return `${site.url}${path.startsWith("/") ? path : `/${path}`}`;
}
