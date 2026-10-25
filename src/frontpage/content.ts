import type { LucideIcon } from "lucide-react";
import { Building2, Palette, Shirt, Store, UtensilsCrossed, Wrench } from "lucide-react";

export const frontNav = [
  { id: "utama", label: "Utama", href: "/#utama" },
  { id: "templat", label: "Templat", href: "/#templat" },
  { id: "harga", label: "Harga", href: "/#harga" },
  { id: "cara", label: "Cara Berfungsi", href: "/#cara" },
  { id: "faq", label: "FAQ", href: "/#faq" },
] as const;

export const industries: { title: string; text: string; icon: LucideIcon }[] = [
  { title: "Restoran", text: "Menu, lokasi dan waktu operasi.", icon: UtensilsCrossed },
  { title: "Kedai online", text: "Produk dan seruan tindakan.", icon: Store },
  { title: "Servis", text: "Perkhidmatan dan borang pertanyaan.", icon: Wrench },
  { title: "Fesyen", text: "Koleksi dan identiti jenama.", icon: Shirt },
  { title: "Korporat", text: "Profil firma dan halaman rasmi.", icon: Building2 },
  { title: "Portfolio", text: "Hasil kerja dan profil peribadi.", icon: Palette },
];

export const steps = [
  {
    id: "pilih",
    title: "Pilih Templat",
    text: "Pilih arah reka bentuk daripada contoh yang dipaparkan. Memilih contoh belum membuka editor.",
  },
  {
    id: "sesuai",
    title: "Sesuaikan Website",
    text: "Editor untuk teks, gambar dan warna ialah sebahagian produk yang sedang dibangunkan.",
  },
  {
    id: "terbit",
    title: "Terbitkan Website",
    text: "Penerbitan belum dibuka. Laman ini tidak menjana dan tidak menerbitkan website.",
  },
] as const;

export const showcaseTemplates = [
  { id: "food-restaurant", label: "Restoran", note: "Menu dan suasana" },
  { id: "modern-commerce", label: "Kedai Online", note: "Produk dan tawaran" },
  { id: "minimal-clean", label: "Servis", note: "Perkhidmatan profesional" },
  { id: "premium-luxury", label: "Fesyen", note: "Rupa jenama yang lebih luas" },
  { id: "corporate-pro", label: "Korporat", note: "Struktur halaman rasmi" },
  { id: "creative-tech", label: "Portfolio", note: "Hasil kerja dan profil" },
] as const;

