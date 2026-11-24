export type FaqItem = {
  id: string;
  question: string;
  answer: string;
};

export const faqs: FaqItem[] = [
  {
    id: "coding",
    question: "Adakah saya perlu tahu coding?",
    answer:
      "Tidak. vividworks.my dirancang supaya anda menerangkan perniagaan, menyemak cadangan reka bentuk dan mengubah kandungan melalui editor visual, tanpa menulis kod. Aliran ini masih dalam pembangunan dan belum dibuka sebagai perkhidmatan langsung di laman ini.",
  },
  {
    id: "pakej-free",
    question: "Apakah yang termasuk dalam pakej Starter?",
    answer:
      "Spesifikasi yang dirancang ialah RM0 (Free Forever dalam dokumen produk), 1 hingga 3 halaman statik, 1 kali AI Generate, 2 kali AI Edit, editor GrapesJS selepas kredit AI habis, dan fail HTML, CSS serta JavaScript untuk website yang diterbitkan. Tiada pangkalan data khusus untuk website pelanggan. Pakej ini belum dibuka, jadi tiada kredit atau editor boleh digunakan sekarang.",
  },
  {
    id: "edit-selepas-terbit",
    question: "Bolehkah saya mengedit website selepas diterbitkan?",
    answer:
      "Penyuntingan visual ialah sebahagian aliran kerja yang dirancang. Sama ada suntingan masih boleh dibuat selepas website diterbitkan belum disahkan. Tiada janji dikunci pada masa ini.",
  },
  {
    id: "mesra-telefon",
    question: "Adakah website mesra telefon bimbit?",
    answer:
      "Pratonton template pada laman ini disediakan untuk desktop dan telefon. Sama ada setiap website pelanggan dijamin mesra telefon bimbit belum disahkan di luar spesifikasi Starter yang masih dirancang.",
  },
  {
    id: "domain",
    question: "Bolehkah saya menggunakan domain sendiri?",
    answer:
      "Sokongan domain sendiri belum disahkan. Sehingga spesifikasi pakej rasmi diterbitkan, laman ini tidak menjanjikan pendaftaran domain, pemindahan domain atau sambungan DNS.",
  },
  {
    id: "naik-taraf",
    question: "Bagaimana proses naik taraf pakej?",
    answer:
      "Naik taraf masih dalam perancangan. Grow, Pro dan Elite belum mempunyai harga atau had penggunaan. Laman ini tidak memproses bayaran. Untuk pertanyaan, gunakan halaman Hubungi Kami.",
  },
  {
    id: "bila-boleh-diguna",
    question: "Bilakah AI Website Builder boleh digunakan?",
    answer:
      "Pembina website AI belum dibuka untuk kegunaan umum. Laman ini ialah laman pemasaran. Butang seperti “Bina Website Percuma” membawa ke halaman Akan Datang sehingga platform disepadukan. Tarikh pelancaran belum diumumkan.",
  },
  {
    id: "preview",
    question: "Apakah perbezaan antara vividworks.my dan preview.vividworks.my?",
    answer:
      "Kedua-duanya ialah laman pemasaran yang sama dari segi produk: tiada pembina, editor atau penerbitan langsung. preview.vividworks.my ialah salinan pratonton untuk semakan dalaman atau demonstrasi sebelum perubahan diterbitkan ke domain utama. Ia bukan persekitaran pelanggan dan bukan sandbox editor.",
  },
  {
    id: "borang-hubungi",
    question: "Apa yang berlaku selepas saya menghantar borang Hubungi Kami?",
    answer:
      "Mesej disimpan pada pelayan laman ini (fail atau pangkalan data, bergantung pada konfigurasi pelayan). Tiada e-mel automatik dihantar kepada anda atau kepada pasukan vividworks. Anda boleh menyalin ringkasan mesej selepas ia disimpan. Saluran e-mel atau telefon rasmi belum disahkan pada halaman hubungi.",
  },
  {
    id: "nama-pakej",
    question: "Mengapa ada Starter, Grow, Pro dan Elite?",
    answer:
      "Starter ialah nama pakej untuk spesifikasi RM0 yang dirancang. Grow, Pro dan Elite ialah label pakej berbayar yang dinamakan pada laman ini; harga, had penggunaan dan ciri tambahan belum diumumkan. Identiti storan borang masih menggunakan id teknikal (free, starter, professional, business) tanpa mengubah backend.",
  },
];
