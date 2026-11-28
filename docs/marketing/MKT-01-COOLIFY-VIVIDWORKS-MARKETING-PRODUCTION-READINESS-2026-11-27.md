# MKT-01 — Coolify Read-Only Audit: VividWorks Marketing Production Readiness

**Tarikh audit:** 27 November 2026 (Asia/Kuala_Lumpur)
**Projek:** VividWorks Marketing Website
**Domain Production (sasaran):** `vividworks.my`, `www.vividworks.my`
**Domain Preview (sedia ada):** `preview.vividworks.my`
**Mod:** READ-ONLY — tiada perubahan dibuat pada Coolify, DNS, Docker, atau repositori.

---

## 1. Ringkasan keadaan server

| Item | Ditemui |
|------|---------|
| **Host** | `vps-eaf0f2c8` (Ubuntu, kernel `6.8.0-147-generic`) |
| **Origin VPS Coolify (marketing preview/apex sasaran)** | `51.79.159.241` |
| **Coolify** | Image `docker.io/coollabsio/coolify:4.7.9`, status **healthy** (~20 jam uptime pada masa audit) |
| **Reverse proxy** | `coolify-proxy` (Traefik) — port `80`/`443` pada host |
| **Coolify projek (DB)** | Satu projek bernama **VividWorks Production** |
| **Coolify server record (DB)** | `localhost` → `host.docker.internal` (single-node) |
| **CPU** | 4 vCPU |
| **RAM** | 7.6 GiB total; ~1.7 GiB used; ~5.9 GiB available |
| **Disk (`/`)** | 72G total, 17G used, **55G free** (~24% used) |

**Container utama (ringkas):**

| Container | Peranan |
|-----------|---------|
| `coolify`, `coolify-db`, `coolify-redis`, `coolify-sentinel` | Panel & data Coolify |
| `coolify-proxy` (+ logrotate, error-pages) | Traefik / TLS termination umum |
| `vividpreview-site` | Marketing **Preview** (manual Docker, bukan rekod `applications` Coolify) |
| `cuasxybecmgbxocno5unto0x-*` | Laravel **staging API** (`vividworks-laravel-staging-api`) |
| `gupd5txktpzj8am0ok2zjhj3-*`, `5aqgnhiz4qxpvs56kyik36el-*` | Aplikasi Coolify lain (Freshmarket/POS) |

**Panel Coolify:** `https://coolify.vividworks.my` → HTTP **302** (DNS **A** ke `51.79.159.241`, **proxied: false**).

---

## 2. Butiran Preview resource semasa

| Field | Nilai disahkan |
|-------|----------------|
| **Nama container** | `vividpreview-site` |
| **Pengurusan Coolify** | `coolify.managed=false` — **bukan** application dalam jadual `applications` |
| **Domain Traefik** | Hanya `Host(\`preview.vividworks.my\`)` (HTTP→HTTPS redirect + TLS `letsencrypt`) |
| **Image semasa** | `vividpreview:20261124T163143Z` |
| **Status** | **running** (uptime ~2 hari pada masa audit) |
| **Port dalaman** | `8080` |
| **Rangkaian Docker** | `coolify` |
| **Release path host** | `/home/ubuntu/vividpreview/releases/20261124T163143Z` → symlink `current` |
| **Kaedah deploy** | Tarball + `vividpreview-deploy.sh` (bukan git pull pada host) |
| **Health semasa** | `GET https://preview.vividworks.my/api/health` → **200**, body `{ ok: true, service: "vividworks-my", storage: "file" }` |

**Environment variables pada container (nama sahaja — nilai tidak didedahkan):**

- `NODE_ENV`
- `PORT`
- (tiada `DATABASE_URL`, `ADMIN_TOKEN` pada instance semasa — stor fail untuk inquiries)

---

## 3. Repository dan branch yang disahkan

| Sumber | Bukti |
|--------|-------|
| **Repositori** | `khairul-haizam/vividworks-site` (workflow deploy + checkout dalam repo; host deploy **tidak** menyimpan `.git`) |
| **Branch auto-preview** | **`main`** — `.github/workflows/preview-deploy.yml` (`on.push.branches: [main]`) |
| **Trigger lain** | `workflow_dispatch` |
| **Deploy host** | `ubuntu@51.79.159.241` via SSH (secret `VIVIDPREVIEW_SSH_KEY` — nilai tidak diperiksa) |
| **Commit/source pada VPS** | **NOT VERIFIED** — deploy berasaskan tarball; cap masa release terakhir **`20261124T163143Z`** disahkan pada image/filesystem |

---

## 4. Build dan deployment configuration

### Stack aplikasi (dari repository)

| Komponen | Disahkan |
|----------|----------|
| **Framework UI** | React 19, React Router 7 |
| **Build tool** | Vite 8 |
| **Bahasa** | TypeScript |
| **CSS** | Tailwind CSS 4 (`@tailwindcss/vite`) |
| **Runtime** | Node.js **22.x** (`engines.node` dalam `package.json`) |
| **Build command** | `npm run build` (token check, template previews, `tsc -b`, `vite build`) |
| **Output** | `dist/` (default Vite); dihidangkan oleh Express dalam `server/index.js` |
| **Production start** | `npm start` → `node server/index.js`, port default **8080** dalam image Docker (`ENV PORT=8080`) |
| **Imej build** | Root `Dockerfile`: `npm ci` → `npm run build` → `npm prune --omit=dev` → `CMD npm start` |

### Aliran preview (sedia ada)

1. GitHub Actions checkout repo → `tar` (exclude `node_modules`, `dist`, `.git`, `.env`) → SSH ke host.
2. `~/bin/vividpreview-deploy.sh` terima tarball, build image `vividpreview:<UTC stamp>`, label Traefik untuk **preview** sahaja, health check roll-back jika gagal.

**Tiada** workflow atau skrip dalam repo yang deploy ke `vividworks.my` / `www` — hanya `preview.vividworks.my` didokumentasikan dan dikonfigurasi.

### Coolify `applications` (marketing vs API)

| ID | Nama (DB) | Git | Branch | FQDN | Status |
|----|-----------|-----|--------|------|--------|
| 4 | `vividworks-laravel-staging-api` | `haizamk/stitch-vividworks` | `vividworks/development-stitch` | `api-staging.vividworks.my` | **running:healthy** |
| 3 | `stitch-vividworks:…` | `haizamk/stitch-vividworks` | `vividworks/development-stitch` | `dev.vividworks.my` | **exited** |
| 1–2 | Freshmarket / POS | repo lain | `main` | `freshmarket.my`, `pos.freshmarket.my` | running |

**Tiada** baris `applications` untuk `vividworks.my`, `www.vividworks.my`, atau `preview.vividworks.my`.

---

## 5. DNS dan domain findings (Cloudflare, read-only)

Zone: **`vividworks.my`** (NS Cloudflare — disahkan melalui API zon).

| Hostname | Type | Content | Cloudflare proxy |
|----------|------|---------|------------------|
| `vividworks.my` | A | `51.79.159.241` | **true** |
| `www.vividworks.my` | CNAME | `vividworks.my` | **true** |
| `preview.vividworks.my` | A | `51.79.159.241` | **true** |
| `coolify.vividworks.my` | A | `51.79.159.241` | **false** |
| `api-staging.vividworks.my` | A | `51.79.159.241` | **true** |
| `api.vividworks.my` | A | Laravel production API origin (bukan VPS Coolify marketing) | **false** (DNS-only; IP origin tidak didokumentasikan dalam MKT) |
| `dev.vividworks.my` | A | `51.79.159.241` | **false** (app Coolify stitch **exited**) |

**Penggunaan domain semasa:**

- **Preview:** DNS + Traefik + container — **berfungsi**.
- **Apex / www:** DNS sudah menunjuk ke VPS yang sama seperti preview, tetapi **tiada router Traefik** untuk host tersebut → lihat §6.
- **Laravel production API** (`api.vividworks.my`): origin **bukan** VPS Coolify marketing — selari dengan keperluan **jangan gabung** laman pemasaran dengan backend identiti/tenant.

---

## 6. HTTPS dan routing findings

### Preview

- Traefik pada container: `tls.certresolver=letsencrypt`, peraturan `Host(\`preview.vividworks.my\`)`.
- Ujian: HTTPS melalui Cloudflare dan `--resolve` terus ke origin → **200** pada `/api/health`.

### Apex / www (production sasaran)

| Ujian | Hasil |
|-------|-------|
| `https://vividworks.my/` (via Cloudflare) | **526** (Invalid SSL certificate) |
| `https://www.vividworks.my/` | **526** |
| `https://vividworks.my/` (`--resolve` → origin) | **503** (tiada backend/router untuk host ini) |

**Interpretasi:** Origin belum mempunyai sijil/router Traefik untuk `vividworks.my` / `www.vividworks.my`. Cloudflare **proxied** pada apex memerlukan sijil valid di origin (biasanya **Full (strict)** dengan Let's Encrypt pada Traefik) — keadaan semasa belum production-ready.

### Staging API (Coolify-managed)

- `https://api-staging.vividworks.my` — TLS via Traefik `letsencrypt` pada container Laravel; health JSON endpoint wujud (storage **postgres** pada API Laravel — bukan marketing).

### Potensi konflik

| Risiko | Tahap |
|--------|-------|
| Port 80/443 | Satu `coolify-proxy` — **normal**; banyak router Host-based |
| Domain preview vs production | Preview guna **priority 100** dan host khusus — production boleh router berasingan dengan nama/priority berbeza **jika** container atau service berasingan |
| `dev.vividworks.my` | DNS aktif, app Coolify **exited** — boleh mengelirukan; **bukan** skop marketing tetapi perlu dibersihkan kemudian (luar MKT-01) |

---

## 7. Kapasiti server

**Ringkasan penggunaan sumber (snapshot `docker stats --no-stream`):**

| Container | CPU | RAM |
|-----------|-----|-----|
| `vividpreview-site` | ~0% | ~41 MiB |
| Laravel staging | ~0% | ~48 MiB |
| `coolify` | ~0.2% | ~452 MiB |
| App Freshmarket/POS | <0.1% | ~53–125 MiB |

**Penilaian:** Dengan **4 vCPU**, **~6 GiB RAM available**, dan **55 GiB disk free**, host **mampu** menempatkan **satu lagi** stack marketing production (Docker image + container serupa preview atau Coolify application) **bersama** preview dan staging API, dengan syarat:

- Hadkan beban build serentak (image build pada host seperti preview adalah CPU/disk spike).
- Jangan kongsi container dengan Laravel API atau n8n (tiada n8n container ditemui pada audit ini).

---

## 8. Cadangan konfigurasi Production (berdasarkan keadaan sebenar)

### Seni bina sasaran (untuk kelulusan Guru)

| Komponen | Domain | Peranan | Status semasa |
|----------|--------|---------|---------------|
| Marketing Production | `vividworks.my` | Laman utama | DNS → VPS; **tiada** app/router |
| WWW Redirect | `www.vividworks.my` | Redirect → apex | CNAME apex; **tiada** redirect di origin |
| Marketing Preview | `preview.vividworks.my` | Preview/dev | **Live** — kekal |
| Laravel Production API | `api.vividworks.my` | Backend prod | **Bukan VPS Coolify marketing** — kekal berasingan |
| Laravel Staging API | `api-staging.vividworks.my` | Backend staging | Coolify — kekal |
| Coolify Panel | `coolify.vividworks.my` | Pengurusan | **Live** |

### Opsyen deployment production (nilai tanpa melaksanakan)

**A — Cermin preview (disyorkan untuk konsistensi repo sedia ada)**

- Resource **berasingan**: container baru (cth. `vividworks-prod-site` atau nama setara), image tag berasingan, **Traefik labels** untuk `vividworks.my` (+ middleware redirect `www` → apex).
- Deploy: salinan pattern `vividpreview-deploy.sh` **atau** workflow GitHub berasingan (branch/tag production, bukan auto-setiap `main`).
- Preview **tidak** disentuh; label preview kekal `preview.vividworks.my` sahaja.

**B — Coolify Application (GitHub + Dockerfile)**

- Application baru dalam projek **VividWorks Production**, repo `khairul-haizam/vividworks-site`, branch/tag ditetapkan (contoh `production` atau release tag — **keputusan produk**, NOT VERIFIED dalam audit).
- FQDN Coolify: `https://vividworks.my` (+ domain www atau redirect di Traefik).
- Kekalkan preview sebagai manual Docker **atau** migrate preview ke Coolify kemudian (keluar skop MKT-01).

**Cloudflare**

- Semasa proxied **true** pada apex: pastikan mod SSL **Full (strict)** selepas sijil Let's Encrypt aktif di Traefik untuk host production.
- **Jangan** ubah DNS semasa audit; nota: apex sudah → `51.79.159.241` — tiada perubahan DNS diperlukan untuk “arahkan trafik” selain membetulkan origin.

**Environment (nama sahaja)**

| Variable | Keperluan |
|----------|-----------|
| `PORT` | Set dalam image (8080) |
| `NODE_ENV` | `production` |
| `DATABASE_URL` | Optional — jika unset, inquiries fail (seperti preview semasa) |
| `ADMIN_TOKEN` | Optional — untuk `/api/inquiries` admin |
| `DATABASE_SSL` | Optional — jika Postgres digunakan |

---

## 9. Isu atau perkara NOT VERIFIED

| Item | Status |
|------|--------|
| Commit SHA tepat pada release `20261124T163143Z` vs `main` GitHub | **NOT VERIFIED** (tiada `.git` pada host) |
| Sama ada workflow GHA preview gagal/berjaya pada tarikh terakhir | **NOT VERIFIED** (audit tidak akses run log Actions) |
| Legacy nginx/php-fpm `vividpreview` pada host masih dipasang atau tidak | **NOT VERIFIED** (skrip deploy disable systemd `vividpreview` jika enabled) |
| Konfigurasi SSL mode Cloudflare per-host (Flexible/Full/Strict) | **NOT VERIFIED** (hanya gejala 526 diuji) |
| n8n pada host ini | **NOT VERIFIED** — tiada container n8n dalam senarai `docker ps` |
| Laravel production API (`api.vividworks.my`) topology / origin | **NOT VERIFIED** dalam audit marketing ini (peranan API sahaja; tiada IP dalam dokumen) |
| Coolify versi dalam DB `coolify_settings` | **NOT VERIFIED** (query gagal); versi image **4.7.9** disahkan |

---

## 10. Keputusan readiness

### **READY WITH CONDITIONS**

**Sebab:**

1. **Server Coolify** operasi, proxy Traefik berfungsi, **kapasiti mencukupi** untuk preview + staging API + marketing production tambahan.
2. **Preview** stabil, routing/TLS/`preview.vividworks.my` disahkan.
3. **Repository** (`khairul-haizam/vividworks-site`, branch `main` → preview) dan **Dockerfile/build pipeline** jelas untuk static+Express.
4. **DNS apex/www** sudah menunjuk ke **VPS Coolify marketing** (`51.79.159.241`).

**Syarat sebelum go-live production:**

1. Wujudkan **resource production berasingan** (container atau Coolify app) — **jangan** gabung dengan preview atau Laravel API.
2. Tambah **Traefik routers + Let's Encrypt** untuk `vividworks.my` (dan redirect `www`).
3. Tentukan **branch/tag deploy production** (berbeza daripada auto-deploy preview `main` jika diperlukan).
4. Selesaikan **526** (sijil origin) sebelum menganggap production accessible melalui Cloudflare proxied.
5. Putuskan sama ada production inquiries guna **file** atau **Postgres** (`DATABASE_URL`) dan set env tanpa meniru preview secara buta.

**Bukan NOT READY** kerana infrastruktur host dan pattern deploy sudah terbukti untuk preview; **bukan READY** tanpa syarat kerana apex/www belum mempunyai backend/TLS.

---

## Lampiran: metod audit

- SSH read-only ke host preview (`ubuntu@51.79.159.241`) — `docker ps`, `docker inspect`, `docker stats`, `psql` dalam `coolify-db`, `df`/`free`/`nproc`.
- HTTP(S) `curl` dari VM audit (Cloudflare + `--resolve` origin).
- Cloudflare API read-only untuk rekod DNS.
- Semakan fail dalam `khairul-haizam/vividworks-site` (workflow, Dockerfile, deploy scripts) — **tanpa** commit/push.

**Audit menamatkan di sini. Tiada deployment atau perubahan konfigurasi dilakukan.**
