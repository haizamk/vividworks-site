import type { LucideIcon } from "lucide-react";
import { Building2, Languages, Layers, MonitorSmartphone, Palette, PenLine, Sparkles, Workflow } from "lucide-react";

export type IconItem = {
  title: string;
  description: string;
  icon: LucideIcon;
};

export const benefits: IconItem[] = [
  {
    title: "Jana Website dengan AI",
    description: "Terangkan perniagaan anda dan dapatkan cadangan reka bentuk.",
    icon: Sparkles,
  },
  {
    title: "Editor Visual Mudah",
    description: "Sunting teks, gambar, warna dan susun atur dengan GrapesJS.",
    icon: PenLine,
  },
  {
    title: "Website Pantas dan Responsif",
    description: "Reka bentuk sesuai untuk desktop, tablet dan telefon.",
    icon: MonitorSmartphone,
  },
  {
    title: "Pilihan Pakej Fleksibel",
    description: "Mulakan secara percuma dan naik taraf apabila perniagaan berkembang.",
    icon: Layers,
  },
];

export const steps = [
  {
    id: "cerita",
    step: "01",
    title: "Ceritakan Perniagaan Anda",
    description: "Pengguna menerangkan jenis perniagaan dan keperluan website.",
  },
  {
    id: "jana",
    step: "02",
    title: "Jana Reka Bentuk dengan AI",
    description: "AI menyediakan cadangan reka bentuk dan kandungan.",
  },
  {
    id: "sesuaikan",
    step: "03",
    title: "Sesuaikan Website",
    description: "Pengguna mengubah teks, gambar, warna dan susun atur menggunakan editor visual.",
  },
  {
    id: "terbit",
    step: "04",
    title: "Pratonton dan Terbitkan",
    description: "Pengguna menyemak website sebelum proses penerbitan.",
  },
] as const;

export const workflowNote =
  "Empat langkah ini ialah aliran kerja yang dirancang. Penjanaan AI, editor visual dan penerbitan belum boleh digunakan melalui laman pemasaran ini.";

export const reasons: IconItem[] = [
  {
    title: "Direka untuk perniagaan Malaysia",
    description: "Bahasa, contoh dan aliran kerja disusun dengan konteks perniagaan tempatan.",
    icon: Building2,
  },
  {
    title: "Mudah digunakan tanpa kemahiran coding",
    description: "Anda menerangkan keperluan, kemudian menyesuaikan hasil melalui editor visual.",
    icon: PenLine,
  },
  {
    title: "Reka bentuk moden dan profesional",
    description: "Arah template mengekalkan rupa yang kemas untuk PKS dan jenama yang sedang berkembang.",
    icon: Palette,
  },
  {
    title: "Sokongan Bahasa Melayu",
    description: "Laman pemasaran dan panduan ini ditulis dalam Bahasa Melayu Malaysia.",
    icon: Languages,
  },
  {
    title: "Fleksibel untuk perniagaan yang berkembang",
    description: "Mulakan dengan spesifikasi Starter yang dirancang, kemudian semak naik taraf apabila butiran rasmi disahkan.",
    icon: Layers,
  },
  {
    title: "Teknologi AI dan editor visual",
    description: "Cadangan reka bentuk AI digandingkan dengan penyuntingan visual. Integrasi penuh masih dalam perancangan.",
    icon: Workflow,
  },
];

export const heroPoints = [
  "Untuk perniagaan Malaysia",
  "Tanpa kemahiran coding",
  "Editor visual dirancang",
] as const;

export const conceptCaption =
  "Visual konsep produk. Ia menggambarkan arah platform dan bukan tangkapan skrin sistem yang telah dilancarkan.";

export const comingSoonCopy = {
  "log-masuk": {
    title: "Log masuk belum dibuka",
    body: "Akaun pelanggan akan disediakan apabila platform disepadukan. Tiada log masuk diproses di laman pemasaran ini.",
  },
  "bina-website": {
    title: "Pembina website belum dibuka",
    body: "Butang ini disediakan untuk pendaftaran pada masa hadapan. Tiada akaun didaftarkan dan tiada website dijana.",
  },
  "mula-percuma": {
    title: "Pakej percuma belum boleh dimulakan",
    body: "Permintaan untuk memulakan pakej Starter tidak diproses di laman ini. Tiada akaun, bil atau website dicipta.",
  },
  "pilih-template": {
    title: "Pemilihan template belum disambungkan",
    body: "Template ini ialah pratonton reka bentuk. Memilihnya tidak membuka editor dan tidak menyimpan pilihan anda.",
  },
} as const;

export type ComingSoonIntent = keyof typeof comingSoonCopy;

export const legalDraft = {
  label: "Draf untuk semakan",
  notice:
    "Halaman ini bukan dokumen undang-undang muktamad. Ia memerlukan kelulusan sebelum digunakan sebagai dasar atau terma rasmi. Tiada kewajipan, tempoh atau hak dicipta di sini.",
  draftedOn: "25 November 2026",
} as const;

export const privacySections = [
  {
    id: "pengenalan",
    heading: "Pengenalan",
    body: "Bahagian ini akan memperkenalkan pihak yang mengendalikan laman dan skop dasar. Teks muktamad belum diluluskan.",
  },
  {
    id: "data",
    heading: "Data yang mungkin dikumpul",
    body: "Jenis data, borang dan rekod teknikal akan disenaraikan selepas kelulusan. Tiada kategori data diisytiharkan sebagai muktamad.",
  },
  {
    id: "tujuan",
    heading: "Tujuan penggunaan",
    body: "Tujuan pemprosesan akan ditulis apabila operasi platform disahkan. Laman ini tidak menetapkan tujuan tersebut sekarang.",
  },
  {
    id: "perkongsian",
    heading: "Perkongsian data",
    body: "Penerima data dan pembekal perkhidmatan belum disenaraikan. Tiada perkongsian dijanjikan atau dinafikan secara muktamad.",
  },
  {
    id: "tempoh",
    heading: "Tempoh penyimpanan",
    body: "Tempoh simpanan belum diluluskan. Jangan anggap sebarang tempoh sebagai dasar semasa.",
  },
  {
    id: "hak",
    heading: "Hak anda",
    body: "Hak akses, pembetulan atau pemadaman akan diterangkan selepas semakan undang-undang. Prosedur permohonan belum dibuka.",
  },
  {
    id: "hubungan",
    heading: "Hubungi",
    body: "Saluran rasmi untuk pertanyaan privasi belum disahkan. Hantar pertanyaan melalui borang Hubungi Kami; mesej disimpan pada pelayan tetapi tiada e-mel automatik dihantar sehingga alamat rasmi diberikan.",
  },
] as const;

export const termsSections = [
  {
    id: "penerimaan",
    heading: "Penerimaan terma",
    body: "Cara terma diterima akan ditulis selepas kelulusan. Penggunaan laman pemasaran ini tidak dianggap sebagai penerimaan kontrak.",
  },
  {
    id: "perkhidmatan",
    heading: "Perkhidmatan",
    body: "Skop perkhidmatan pembina website belum ditawarkan melalui laman ini. Penerangan produk di laman pemasaran bukan perjanjian perkhidmatan.",
  },
  {
    id: "akaun",
    heading: "Akaun",
    body: "Pendaftaran akaun belum dibuka. Tiada syarat akaun, kata laluan atau penggantungan ditetapkan di sini.",
  },
  {
    id: "bayaran",
    heading: "Pakej dan bayaran",
    body: "Selain spesifikasi Starter yang dinyatakan sebagai maklumat pemasaran, harga dan terma bayaran bagi Grow, Pro dan Elite belum diluluskan. Laman ini tidak memproses pembayaran.",
  },
  {
    id: "harta",
    heading: "Harta intelek",
    body: "Pemilik kandungan, template dan output AI akan dinyatakan selepas kelulusan. Tiada lesen diberikan atau ditarik balik oleh draf ini.",
  },
  {
    id: "liabiliti",
    heading: "Had liabiliti",
    body: "Had liabiliti belum diluluskan dan sengaja tidak ditulis supaya tiada syarat undang-undang dicipta.",
  },
  {
    id: "perubahan",
    heading: "Perubahan",
    body: "Cara terma dikemas kini akan diterangkan dalam dokumen muktamad. Tarikh kuat kuasa belum ditetapkan.",
  },
  {
    id: "undang-undang",
    heading: "Undang-undang yang terpakai",
    body: "Bidang kuasa dan tempat penyelesaian pertikaian akan dinyatakan selepas kelulusan. Draf ini tidak memilih undang-undang.",
  },
] as const;
