# Audit Projek vividworks-site (untuk sambungan ChatGPT / Cursor)

**Disediakan:** 25 November 2026 (Asia/Kuala_Lumpur)  
**Pemilik teknikal:** Khairul Haizam  
**Repo GitHub kanonikal:** https://github.com/haizamk/vividworks-site  
**Repo Cursor/Origin:** khairul-haizam/vividworks-site (sumber kerja harian Cursor Cloud Agent)  
**Head `main` semasa audit:** kemas kini ikut commit terakhir (lihat §11).

**Rujukan workflow produk:** [`docs/WORKFLOW-FREE-PLAN-TO-WEBSITE.md`](./WORKFLOW-FREE-PLAN-TO-WEBSITE.md)

Dokumen ini merangkum apa yang berlaku dari sesi deploy GitHub (24–25 Nov 2026) hingga berhenti kerja, apa yang sudah wujud di repo dan di pelayan, serta langkah menuju **production** (`vividworks.my`). Ia **bukan** dokumen undang-undang atau spesifikasi produk muktamad.

---

## 1. Ringkasan eksekutif

| Bidang | Status |
|--------|--------|
| Laman pemasaran (React + Express API) | **Siap** untuk preview |
| Domain preview | **Live** — https://preview.vividworks.my |
| Domain produksi `vividworks.my` | **Belum** diikat dalam repo ini sebagai target deploy utama |
| CI/CD | Push `main` → GitHub Actions → SSH deploy ke Coolify VPS |
| Northflank / Render | **Tidak digunakan** (Northflank dibuang dari repo pada 24 Nov) |
| Produk SaaS (editor, AI, akaun, bayaran) | **Tidak** dalam skop laman ini — butang → `/akan-datang` |
| Pangkalan data inquiries (preview) | **File** (`storage: file` pada `/api/health`) |

---

## 2. Kronologi sesi (24–25 Nov 2026, UTC+8)

Peristiwa mengikut aliran chat Cursor Cloud Agent dan git `main`, bukan sejarah penuh repo sejak permulaan projek.

| Masa (anggaran) | Peristiwa | Hasil |
|-----------------|-----------|--------|
| 24 Nov, malam | Permintaan **push GitHub** | `main` disync ke `haizamk/vividworks-site`; workflow **Deploy preview** run #1 **success** |
| 24 Nov | Soalan **next step** | Dikenal pasti aliran: GitHub → Actions → Coolify; Northflank masih tertinggal dalam README |
| 24 Nov | **Northflank tidak digunakan** — doc belum dikemas | PR #2: buang `northflank.json`, MCP Northflank, kemas README → **Deployment (GitHub + Coolify)** |
| 24 Nov | Merge doc cleanup | Fast-forward `main` ke `c7461ea`; deploy preview run #2 |
| 25 Nov | **Redesign frontpage** dahulu, content kemudian | Rebase branch `cursor/vividworks-frontpage-redesign-3339`; PR #3 |
| 25 Nov | Ujian tempatan | Navbar **sticky** rosak (wrapper div); commit fix `6be61b4` |
| 25 Nov | Merge redesign | `main` → `6be61b4`; deploy preview run #3 **success** |
| 25 Nov | **Content pass** | FAQ baharu, meta site, nota harga; `main` → `b11209a`; deploy run #4 **success** |
| 25 Nov, ~00:14 | **Berhenti kerja** | Minta fail audit ini |

**Nota operasi:** Agen tidak mempunyai scope `origin pr merge`; merge dilakukan dengan fast-forward `main` secara git push. PR Origin #2 dan #3 mungkin masih *Open* di UI walaupun kod sudah di `main` — tutup/merge manual untuk kemas rekod.

---

## 3. Apa yang berjaya dibina (deliverables)

### 3.1 Aplikasi

- **Frontend:** React (Vite), Bahasa Melayu, halaman: `/`, `/pricing`, `/templates`, `/how-it-works`, `/faq`, `/contact`, `/privacy`, `/terms`, `/akan-datang`.
- **Homepage baharu (`src/frontpage/`):** Navbar anchor, hero + mock editor, industri, 3 langkah, karusel templat, 4 kad harga, FAQ accordion, CTA, footer khusus `/`.
- **Backend:** Express pada port 8080 (Docker) / 8787 (dev); `POST /api/contact`, `GET /api/health`, `GET /api/inquiries` (perlukan `ADMIN_TOKEN`).
- **Dockerfile:** Build production Node 22, `npm run build`, `npm start`.
- **Laravel (`laravel/`):** API fasa B1 **berasingan** — tidak menggantikan Express pada preview; jangan migrate DB preview tanpa kelulusan.

### 3.2 Deploy & tooling

| Komponen | Lokasi |
|----------|--------|
| Push mirror ke GitHub | `.github/scripts/push-to-github.sh` (`GITHUB_TOKEN`) |
| Deploy preview on push `main` | `.github/workflows/preview-deploy.yml` |
| Script deploy di VPS | `deploy/preview/vividpreview-deploy.sh` |
| Bootstrap host | `deploy/preview/bootstrap-server.sh` |
| Secret CI | GitHub: `VIVIDPREVIEW_SSH_KEY` |
| Host | `ubuntu@51.79.159.241`, container `vividpreview-site`, Traefik → `preview.vividworks.my` |

### 3.3 Data & messaging (kebenaran)

- **Pakej:** Starter (RM0, dirancang, belum dibuka); Grow / Pro / Elite — **nama sahaja**, tiada harga reka.
- **Contact:** e-mel/telefon/alamat **kosong** sengaja sehingga Khairul sahkan.
- **Legal:** draf dalam `src/data/content.ts` — **bukan** muktamad.
- **FAQ:** 10 item termasuk preview vs prod, borang hubungi, nama pakej (diseragamkan homepage + `/faq`).

### 3.4 Perubahan doc berkaitan hosting

- Northflank: **dipadam** (`northflank.json`, entri MCP dalam `.cursor/mcp.json`).
- README: seksyen **Deployment (GitHub + Coolify)**.

---

## 4. Apa yang **tidak** dibina (elak salah faham ChatGPT)

- Akaun pelanggan, log masuk, bil, payment gateway.
- AI generate/edit, GrapesJS editor hidup, penerbitan website pelanggan.
- Email automatik selepas borang hubungi.
- Deploy automatik ke **vividworks.my** (hanya `preview.vividworks.my` didokumentasikan).
- PostgreSQL pada preview (boleh ditambah kemudian melalui env container).
- CI `test/lint/build` pada setiap PR (hanya workflow deploy preview wujud).

---

## 5. Keadaan git & branch

```
main @ b11209a  →  origin/main, github/main (selari)
```

Branch feature masih wujud di remote (mungkin sudah merge atau usang):

- `cursor/vividworks-frontpage-redesign-3339` — **sudah** di `main`
- `cursor/remove-northflank-docs-03cd` — **sudah** di `main`
- `origin/cursor/fix-preview-ssh-deploy-b926` — kerja SSH deploy lebih awal
- `origin/cursor/vividworks-stitch-c2-3339` — belum disemak/digabung dalam sesi ini

Commit penting pada `main` (24–25 Nov):

```
b11209a  Content pass (FAQ, site meta, pricing notes)
6be61b4  Navbar sticky fix
70df572  Package naming (Starter/Grow/Pro/Elite)
…        Frontpage redesign (src/frontpage/*)
c7461ea  Remove Northflank; document GitHub + Coolify
0dd8b3c  push-to-github.sh refresh remote URL
```

---

## 6. Senarai semak content — **menunggu Khairul**

| # | Input diperlukan | Fail / env |
|---|------------------|------------|
| 1 | E-mel rasmi | `VITE_CONTACT_EMAIL`, `src/data/site.ts` |
| 2 | Telefon & alamat | `src/data/site.ts` |
| 3 | Harga & ciri Grow / Pro / Elite | `src/data/pricing.ts` |
| 4 | Pengesahan copy Starter / Free Forever / tarikh buka | `pricing.ts`, `faqs.ts` |
| 5 | Privasi & Terma diluluskan | `src/data/content.ts` |
| 6 | URL SaaS apabila platform hidup | `VITE_SAAS_LOGIN_URL`, `VITE_SAAS_REGISTER_URL`, `VITE_SAAS_EDITOR_URL` |

Tanpa input di atas, laman kekal jujur sebagai **pemasaran + borang simpanan** sahaja.

---

## 7. Roadmap menuju **production** (`vividworks.my`)

Langkah dicadangkan mengikut kebergantungan. Tiada anggaran hari/minggu — fokus teknikal.

### Fasa A — Preview stabil (sebahagian **done**)

- [x] GitHub kanonikal + auto-deploy preview
- [x] Homepage redesign + content pass asas
- [ ] Tutup PR Origin terbuka (#2, #3) untuk rekod
- [ ] Workflow CI: `npm test`, `lint`, `typecheck`, `build` pada push/PR
- [ ] Semakan manual penuh (mobile, borang contact, halaman dalaman)

### Fasa B — Production hosting

1. **Tentukan prod:** sama VPS Coolify atau hos lain (Hestia, dll. — ada artifak nginx/php-fpm dalam `deploy/` untuk konteks Laravel/legacy, bukan laluan utama marketing site sekarang).
2. **DNS:** `vividworks.my` (dan `www` jika perlu) → reverse proxy yang betul.
3. **Pipeline:** duplikasi atau perluaskan `preview-deploy.yml` (contoh: workflow `production-deploy.yml` pada tag atau push `main` dengan kelulusan manual).
4. **Env production:** `VITE_SITE_URL=https://vividworks.my`, secrets di host (bukan git).
5. **TLS:** Let's Encrypt via Traefik/Coolify (sudah digunakan pada preview).

### Fasa C — Data & operasi

1. **PostgreSQL** untuk inquiries (optional tetapi disyorkan untuk prod) — set `DATABASE_URL` pada container/service.
2. **`ADMIN_TOKEN`** pada pelayan — akses `GET /api/inquiries`.
3. **Backup** inquiries + prosedur pemulihan.
4. **Notifikasi** — e-mel/Slack apabila inquiry masuk (perlu feature baru; API kini tidak hantar e-mel).

### Fasa D — Go-live pemasaran

1. Lengkapkan senarai semak content (§6).
2. Ganti draf legal dengan teks diluluskan.
3. SEO: `VITE_SITE_URL`, semak meta halaman.
4. Pemantauan: uptime `/api/health`, log container.

### Fasa E — Produk (luar repo marketing ini)

Integrasi URL SaaS, editor, AI, billing — projek berasingan; laman pemasaran hanya pautan apabila URL env diset.

---

## 8. Rujukan pantas untuk agen / ChatGPT

**Clone & dev**

```bash
npm ci
npm run dev    # http://127.0.0.1:4321, API 8787
npm test && npm run lint && npm run typecheck && npm run build
```

**Push ke GitHub + trigger preview deploy**

```bash
git checkout main
# ... commit ...
git push origin main
bash .github/scripts/push-to-github.sh   # perlukan GITHUB_TOKEN
```

**Sahkan preview**

```bash
curl -s https://preview.vividworks.my/api/health
```

**Sumber kebenaran copy:** `src/data/*.ts`, bukan hardcode rawak dalam komponen.

**Prinsip:** Jangan reka e-mel, telefon, harga berbayar, atau janji produk yang belum dilancarkan.

---

## 9. Isu dikenali / hutang teknikal

| Isu | Nota |
|-----|------|
| PR Origin tidak auto-merge oleh agen | Token scope; merge manual atau git ff `main` |
| `origin pr merge` ditolak | Gunakan UI atau push `main` langsung |
| Scroll reveal (`.fp-reveal`) | Kandungan mungkin `opacity: 0` pada screenshot full-page; OK dalam viewport sebenar |
| Dua sistem langkah “cara kerja” | Homepage: 3 langkah (`src/frontpage/content.ts`); `/how-it-works`: 4 langkah (`src/data/content.ts`) — sengaja berbeza, boleh diselaraskan kemudian |
| Branch `vividworks-stitch-c2-3339` | Belum digabung; semak sebelum delete |

---

## 10. Sambungan kerja esok (cadangan pertama)

1. Baca fail ini + [`WORKFLOW-FREE-PLAN-TO-WEBSITE.md`](./WORKFLOW-FREE-PLAN-TO-WEBSITE.md) + `docs/f1-dan-ujian-staging-2026-11-22.md`.
2. Khairul beri input content (e-mel, harga Grow/Pro/Elite) **atau** arahkan Fasa auth/SaaS (§13).
3. Production marketing: DNS `vividworks.my` + pipeline (§7); production platform: Laravel + Supabase prod + terbitkan W2 dengan berhati-hati.

---

## 11. Changelog fail audit

| Tarikh | Versi | Perubahan |
|--------|-------|-----------|
| 2026-11-25 | 1.0 | Audit awal sesi 24–25 Nov; `main` @ `b11209a` |
| 2026-11-25 | 1.1 | §12–§15: Laravel, n8n, Supabase; pautan workflow free plan |

Apabila `main` bergerak, kemas kini **§1 head SHA**, **§5 commit list**, dan **§11** — atau jana `PROJECT-AUDIT-YYYY-MM-DD.md` baharu untuk sesi seterusnya.

---

## 12. Laravel (Fasa B1 / F1) — adakah berjaya?

**Dalam repo:** **Ya, sebagai API berasingan** — bukan sebahagian laman preview pemasaran.

| Aspek | Status |
|-------|--------|
| Laluan API | `GET/POST /api/health`, `contact`, `inquiries`, **`POST /api/ai/design-generations`**, **`POST /api/ai/design-callback`** |
| PostgreSQL / Supabase | Migrasi `generations`, `generation_attempts`, `callback_inbox`, `generation_outbox`, dll. |
| PHPUnit | Liputan luas F1 mock, callback, SiteSpec v1.1 (`tests/Feature/F1MockLifecycleTest.php`, `DesignCallbackTest.php`, …) |
| Staging API | Dirujuk n8n: `https://api-staging.vividworks.my/api/ai/design-callback` |
| Preview marketing | React masih guna **Express** (`server/`), bukan Laravel |
| Deploy Laravel pada preview.vividworks.my | **Tidak** (artifak nginx/php-fpm dalam `deploy/` untuk konteks lain) |

**Rekod ujian (22 Nov):** Ujian manual W2 (stub) + banyak senario callback dilaksanakan ke staging; workflow async **tidak diterbitkan**. Lihat `docs/f1-dan-ujian-staging-2026-11-22.md`.

**Ringkas:** Backend F1 **berjaya dibina dan diuji**; **belum** disambung ke “customer join free plan” dari UI marketing.

---

## 13. n8n (VividAI) — adakah berjaya?

Instance: **n8n.vividai.my** (MCP `n8n-vividai`).

| Workflow | ID | Aktif | Fungsi | Status |
|----------|-----|-------|--------|--------|
| **VividAI - Design Agent** | `fg1kGKB6ciYADxu9` | **Ya** | Webhook `vividai/design-agent` → validasi → OpenRouter → SiteSpec v1.1 | **Berjaya (W1)** — sokong `package: free` |
| **VividAI - Design Agent (Async)** | `sdbCW1olkS0QeMFf` | **Tidak** | Webhook `vividai/design-agent-async` → 202 → callback Laravel | **Draf ujian** — stub Kedai Contoh; **Call Design Agent disabled** — butiran §8 dalam [`WORKFLOW-FREE-PLAN-TO-WEBSITE.md`](./WORKFLOW-FREE-PLAN-TO-WEBSITE.md) |
| TEMP - Auth Probe | `1qYUZKux874l2yxL` | Tidak | Ujian auth | Bukan produk |
| My workflow | `EpoNrALDshpy2D0a` | Ya | Tidak dikaitkan vividworks F1 | Abaikan untuk handoff |

**Tidak berjaya / belum siap sebagai produk pelanggan:**

- W2 **belum diterbitkan** (sengaja, selepas rentetan ujian 82–118 pada 22 Nov).
- Tiada workflow n8n untuk **pendaftaran akaun**, **billing**, atau **terbit HTML** kepada pelanggan.
- A7 runtime test (Ajv dalam nod Code produksi) — banyak item **UNVERIFIED** dalam pelan v10/v12; bukan blocker marketing site.

---

## 14. Supabase — adakah berjaya?

| Projek | Ref | Rantau | Status |
|--------|-----|--------|--------|
| **vividworks-staging** | `tnvjesekmmnvbfevlwmd` | ap-northeast-1 | **ACTIVE_HEALTHY** |

**Jadual awam (snapshot 25 Nov 2026):**

| Jadual | Baris (anggaran) | Peranan |
|--------|------------------|---------|
| `generations` | 47 | Projek penjanaan AI |
| `generation_attempts` | 50 | Attempt + `job_id` |
| `generation_contexts` | 47 | Konteks permintaan |
| `callback_inbox` | 38 | Callback dari n8n |
| `generation_outbox` | 49 | Outbox invoke |
| `generation_audit_events` | 219 | Audit |
| `inquiries` | 1 | Borang hubungan (staging) |

**Tiada** jadual `users`, `profiles`, atau `subscriptions` dalam `public` — **pendaftaran pelanggan free plan belum wujud dalam DB**.

**Kesimpulan:** Supabase staging **berjaya** sebagai DB F1 + ujian; **bukan** lagi sistem penuh pelanggan → website.

---

## 15. Platform production — langkah selepas marketing preview

Susunan disyorkan (melangkaui §7):

1. **Satu laluan data** — tentukan sama ada inquiries marketing + generations F1 sama Postgres prod atau asing.
2. **Auth + project** — Supabase Auth atau Laravel; `project_id` milik akaun.
3. **Sambung UI** — ganti `/akan-datang` dengan app daftar; panggil Laravel, bukan Express marketing sahaja.
4. **n8n** — terbitkan W2 hanya selepas kelulusan + hidupkan `Call Design Agent`; elak webhook prod tanpa guard.
5. **Renderer & editor** — SiteSpec → hosting; GrapesJS + kuota Starter.
6. **Marketing prod** — `vividworks.my` (Coolify atau lain) selari dengan `api.*` dan n8n.

Diagram terperinci: [`WORKFLOW-FREE-PLAN-TO-WEBSITE.md`](./WORKFLOW-FREE-PLAN-TO-WEBSITE.md).