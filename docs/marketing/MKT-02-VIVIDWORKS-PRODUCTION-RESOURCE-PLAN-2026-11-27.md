# MKT-02 — VividWorks Marketing Production Resource Plan

**Tarikh:** 27 November 2026 (Asia/Kuala_Lumpur)  
**Berdasarkan:** [MKT-01](./MKT-01-COOLIFY-VIVIDWORKS-MARKETING-PRODUCTION-READINESS-2026-11-27.md)  
**Status:** Planning & read-only assessment — **tiada** resource, deploy, DNS, atau kod diubah.

---

## 1. Current infrastructure validation

Validasi ringkas (MKT-02, read-only) dibanding baseline MKT-01:

| Semakan | MKT-01 | MKT-02 (semula) | Perbezaan |
|---------|--------|------------------|-----------|
| Preview health | 200, `storage: file` | **200**, `storage: file` | Tiada |
| Apex `https://vividworks.my/` (via CF) | 526 | **526** | Tiada |
| Apex origin (`--resolve` → VPS) | 503 | **503** | Tiada — masih tiada Traefik router production |
| `www` | 526 | **301** → `https://vividworks.my/` (tanpa follow) | **www** kini redirect di edge; apex masih 526 selepas follow |
| DNS apex/www/preview → `51.79.159.241` | Ya | **Ya** (proxied apex/www/preview) | Tiada |
| Disk free `/` | ~55 GiB | **~55 GiB** (24% used) | Tiada material |
| RAM available | ~5.9 GiB | **~5.9 GiB** | Tiada material |
| `vividpreview-site` | Running | **Up ~2 days** | Tiada |
| Laravel staging API | healthy | **healthy** (~30 min pada container semasa) | Container ID baru (redeploy staging — dijangka) |
| Coolify | 4.7.9 | **NOT RE-CHECKED** (image tag) | Anggap sama melainkan audit lanjut |
| Zone Cloudflare SSL mode | NOT VERIFIED | **`strict`** (zon) | **Baru disahkan** — origin mesti sijil valid apabila proxied |

**Kesimpulan validasi:** Asas MKT-01 masih tepat. Penambahan: mode SSL zon **Full (strict)** mewajibkan sijil Let's Encrypt (atau setara) pada Traefik untuk `vividworks.my` sebelum lalu lintas proxied stabil. `www` mungkin sudah ada redirect di Cloudflare (**NOT VERIFIED** sama ada Page Rule / Redirect Rule vs origin); pelaksanaan **juga** di Traefik disyorkan untuk kawalan eksplisit dan mengelak gelung.

---

## 2. Perbandingan Pilihan A dan B

### Pilihan A — Dedicated Docker Production Resource

Corak serupa preview: container berasingan, image `vividworks-prod:<stamp>`, label Traefik untuk `vividworks.my`, skrip `vividworks-prod-deploy.sh`, workflow GHA berasingan, direktori release `/home/ubuntu/vividworks-prod/` (cadangan nama).

### Pilihan B — Coolify Application

Application baharu dalam projek **VividWorks Production**, repo `khairul-haizam/vividworks-site`, build Dockerfile, FQDN `vividworks.my`, branch/tag production, deploy/rollback melalui UI Coolify.

### Matriks perbandingan

| Kriteria | A — Dedicated Docker | B — Coolify Application |
|----------|----------------------|-------------------------|
| **1. Kesederhanaan operasi** | SSH + skrip + GHA; kurang UI | Panel Coolify, satu tempat untuk env/domain |
| **2. Keserasian dengan Preview** | **Tinggi** — mirror `vividpreview-deploy.sh` | **Rendah** — preview kekal manual, dua model |
| **3. Kemudahan rollback** | **Tinggi** — image/tag sebelumnya + health check (sudah terbukti) | Baik — redeploy revision lama dalam Coolify |
| **4. Domain & SSL** | Label Traefik + `letsencrypt` (sama preview) | Coolify/Traefik terurus untuk FQDN app |
| **5. Pengurusan release** | Tag GHA + stamp UTC pada image | Tag/branch + deployment ID Coolify |
| **6. Risiko ganggu perkhidmatan** | **Rendah** — tiada sentuh app Coolify lain jika nama container/router unik | **Sederhana** — redeploy/proxy misconfig boleh ganggu proxy kongsi |
| **7. Penyelenggaraan jangka panjang** | Dua laluan deploy (preview + prod) untuk dijaga | Satu standard Coolify untuk prod; preview masih laggard |

---

## 3. Cadangan deployment architecture (cadangan utama)

### **Cadangan utama: Pilihan A — Dedicated Docker Production**

**Alasan:**

1. Preview sudah **production-proven** pada VPS yang sama (health, TLS, rollback).
2. Memenuhi keperluan **resource berasingan** tanpa menyentuh `vividworks-laravel-staging-api`, Freshmarket apps, atau `vividpreview-site`.
3. **Gangguan rendah** kepada Coolify-managed workloads — tiada create application yang mungkin picu restart rangkaian proxy.
4. Laravel Production API (`api.vividworks.my` pada host berbeza) dan n8n **tidak** disentuh.

### Seni bina sasaran (konsep)

```
Internet → Cloudflare (proxied) → coolify-proxy (Traefik)
                                    ├─ Host(preview.vividworks.my) → vividpreview-site:8080
                                    ├─ Host(vividworks.my)       → vividworks-prod-site:8080  [BARU]
                                    ├─ Host(www.vividworks.my)     → redirect middleware → apex
                                    └─ Host(api-staging...)        → Laravel staging container
```

| Komponen | Identiti cadangan |
|----------|-------------------|
| Container production | `vividworks-prod-site` (bukan `vividpreview-site`) |
| Image repo | `vividworks-prod:<UTC stamp>` + label `org.opencontainers.image.revision=<git-sha>` (cadangan metadata) |
| Network | `coolify` (sama preview — diperlukan Traefik) |
| `coolify.managed` | `false` (konsisten preview; elak konflik dengan DB Coolify) |
| Release root | `/home/ubuntu/vividworks-prod/releases/<stamp>` |
| Deploy entrypoint | `/home/ubuntu/bin/vividworks-prod-deploy` |

**Bukan skop pelaksanaan MKT-02:** mencipta container, skrip, atau workflow.

---

## 4. Branch dan release strategy

### Prinsip

| Saluran | Trigger | Matlamat |
|---------|---------|----------|
| **Preview** | Push **`main`** (sedia ada) | Pantau perubahan harian |
| **Production** | **Tag semantik** atau **release branch** + kelulusan manual | Tiada auto-publish setiap commit `main` |

### Cadangan tag production

Format contoh: `marketing-prod/vYYYY.MM.DD.N` atau `vividworks-site-v1.0.0` — dipilih satu konvensyen oleh Guru.

| Langkah | Tindakan (masa implementasi) |
|---------|------------------------------|
| 1 | Merge ke `main` → preview deploy (sedia ada) |
| 2 | Ujian pada `preview.vividworks.my` |
| 3 | Cipta **annotated tag** pada commit yang diluluskan |
| 4 | Workflow production (baru, kemudian) hanya `on: push: tags: ['marketing-prod/*']` + `workflow_dispatch` dengan input `ref` (tag/SHA) |
| 5 | Deploy script catat **tag + SHA** dalam nama release folder dan OCI label image |

### Rollback identity

Setiap release = satu image Docker + folder release; rollback = jalankan semula image tag terakhir yang sihat (sama pola preview).

**Larangan MKT-02:** tiada perubahan branch, tag, atau workflow pada repo.

---

## 5. Domain, DNS dan TLS plan

### DNS (read-only — kekal seperti semasa)

| Host | Rekod | Proxy | Catatan |
|------|-------|-------|---------|
| `vividworks.my` | A → `51.79.159.241` | true | Tiada perubahan DNS diperlukan untuk arah trafik |
| `www.vividworks.my` | CNAME → `vividworks.my` | true | |
| `preview.vividworks.my` | A → `51.79.159.241` | true | **Jangan ubah** |

### TLS & Cloudflare

| Item | Plan |
|------|------|
| **Origin certificate** | Traefik `tls.certresolver=letsencrypt` untuk router `Host(\`vividworks.my\`)` (dan router `www` untuk redirect sahaja) |
| **Cloudflare SSL** | Zon sudah **`strict`** — origin mesti present valid cert untuk apex sebelum go-live proxied |
| **Preview** | Kekal router/sijil sedia ada — jangan kongsi router Host dengan production |
| **Redirect www** | Traefik middleware `redirectregex` atau `redirectscheme` + rule `Host(\`www.vividworks.my\`)` → `https://vividworks.my$1` (satu hop, 308/301) |
| **Redirect loop** | Elak: jangan redirect apex → www; uji `curl -I` untuk www dan apex berasingan |
| **Ujian pre-flight** | `curl --resolve vividworks.my:443:<VPS> https://vividworks.my/api/health` tanpa `-k` selepas deploy |

### Perbezaan ditemui (www)

`https://www.vividworks.my/` memaparkan **301** ke apex pada masa MKT-02. Sumber redirect (**Cloudflare rule vs origin**) — **NOT VERIFIED**. Pelan: terapkan redirect www di Traefik supaya tingkah laku deterministik walaupun CF rule dikeluarkan kemudian.

---

## 6. Marketing backend dan database integration recommendation

### Fungsi yang memerlukan backend (kod sedia ada)

| Endpoint | Peranan | Kebergantungan |
|----------|---------|----------------|
| `GET /api/health` | Health / orchestration | Store ping (file atau Postgres) |
| `POST /api/contact` | Borang hubungi awam | Rate limit in-memory; simpan inquiry |
| `GET /api/inquiries` | Senarai admin | `ADMIN_TOKEN` Bearer (404 jika token tidak set) |
| Static `dist/` | SPA React | Tiada backend |

Frontend: `ContactPage` → `POST /api/contact` (same-origin).

### Seni bina yang dipersetujui vs marketing

| Lapisan | Peranan bagi SaaS | Marketing site hari ini |
|---------|-------------------|-------------------------|
| React/Vite | UI | ✅ |
| Laravel 13 | API identiti/F1/inquiries API produk | **Tidak** dipanggil oleh marketing SPA |
| Supabase PG | Database utama tenant | **Tidak** sepatutnya dihubung terus dari container marketing |
| Express (Node) | Host statik + inquiries tempatan | ✅ dalam satu image |

**Keputusan sempadan data (D-INQUIRIES):** inquiries marketing **kekal berasingan** daripada data tenant Supabase/Laravel.

### Pilihan penyimpanan inquiries (cadangan — bukan pelaksanaan)

| Opsyen | Keterangan | Cadangan fasa |
|--------|------------|---------------|
| **F1 — File (`data/inquiries.json`)** | Sama seperti preview semasa | **Fasa go-live minimum** jika volume rendah; **risiko**: data hilang pada container replace tanpa volume |
| **F2 — Postgres khusus marketing** | `DATABASE_URL` ke instance/schema **bukan** Supabase production tenant (cth. DB kecil pada VPS / Supabase project berasingan read-write terhad) | **Fasa 2** jika perlu durability + backup |
| **F3 — Laravel public API** | `POST https://api.vividworks.my/api/contact` (polisi CORS, rate limit, tiada credential DB di browser) | **Fasa 3** penyatuan API; memerlukan kerja Laravel & tidak menggabungkan container marketing dengan **host Laravel production API** secara fizikal |
| **F4 — Frontend → Supabase langsung** | ❌ **Tidak disyorkan** — melanggar arahan: jangan sambung frontend dengan privileged credentials | Tolak |

### Integrasi lain

| Sistem | Hubungan dengan marketing production |
|--------|-------------------------------------|
| **n8n** | Notifikasi inquiry (e-mel/automasi) — **NOT VERIFIED** sama ada webhook sedia ada; cadangan: event selepas simpan (F2/F3), bukan dalam path kritikal request |
| **Resend / mail production** | Transaksi identiti (**D-INQUIRIES** excludes marketing inquiry mail from Laravel `MAIL_FROM_*`) | Inquiry auto-reply berasingan dari Resend prod identity — keputusan produk kemudian |

**MKT-02:** jangan set `DATABASE_URL`, `ADMIN_TOKEN`, atau cipta jadual/migration.

---

## 7. Resource capacity assessment

### Rujukan empirik (preview)

| Metrik | Nilai |
|--------|-------|
| Image `vividpreview:20261124T163143Z` | **~754 MB** |
| RAM runtime `vividpreview-site` | **~41 MiB** |
| CPU runtime | ~0% pada idle |

### Anggaran tambahan production

| Sumber | Anggaran |
|--------|----------|
| Satu image production aktif | ~750 MB (build serupa Dockerfile) |
| Retained releases (cadangan 4) | ~3 GB disk (boleh dikurangkan dengan prune image) |
| RAM container tambahan | ~40–80 MiB idle |
| Build semasa deploy | Spike CPU; jadualkan di luar waktu puncak; **jangan** build serentak preview+prod tanpa koordinasi |

### Kapasiti VPS (MKT-02)

| | Jumlah | Selepas +1 production (anggaran) |
|--|--------|-----------------------------------|
| vCPU | 4 | Mencukupi |
| RAM available | ~5.9 GiB | ~5.8 GiB+ (masih mencukupi) |
| Disk free | ~55 GiB | ~52 GiB+ dengan 4 release |

**n8n:** tiada container n8n pada host semasa audit MKT-01 / semakan ringkas MKT-02 — **NOT VERIFIED** jika n8n dipasang di luar Docker.

---

## 8. Deployment sequence (pelaksanaan masa depan)

Urutan dicadangkan selepas kelulusan Guru (tiada tindakan dalam MKT-02):

1. **Semak** `main` pada preview — smoke `/`, `/contact`, `/api/health`.
2. **Sediakan** skrip/host path production (mirror preview) tanpa menyentuh `vividpreview-site`.
3. **Deploy percubaan** ke container production dengan Traefik **tanpa** menukar DNS (uji via `Host` header / sslip atau `/etc/hosts` dalaman) — **NOT VERIFIED** sama ada sslip.io dipakai; alternatif: label sementara hostname ujian.
4. **Sahkan** Let's Encrypt untuk `vividworks.my` pada origin (strict mode).
5. **Tambah** router `www` → redirect apex.
6. **Smoke** `https://vividworks.my` dan `https://www.vividworks.my` melalui Cloudflare.
7. **Pastikan** preview masih 200.
8. **Tag** release production dan rekod SHA dalam dokumentasi operasi.
9. **Pantau** RAM/disk 24–48 jam.

---

## 9. Rollback strategy

| Tahap | Mekanisme |
|-------|-----------|
| **Segera** | Skrip production: kekalkan `previous_image`; health gagal → `docker run` image sebelumnya (sama preview) |
| **Disengaja** | Redeploy tag/SHA production terakhir yang diketahui baik |
| **DNS** | Tidak perlu rollback DNS (tiada perubahan dirancang) |
| **Preview** | Tidak libat rollback preview semasa rollback prod |

Simpan sekurang-kurangnya **2** image production pada host semasa stabilisasi awal.

---

## 10. Risiko dan perkara NOT VERIFIED

| Item | Status |
|------|--------|
| Sumber redirect `www` (CF rule vs lain) | **NOT VERIFIED** |
| Cloudflare per-host SSL override vs zon `strict` | **NOT VERIFIED** |
| Commit SHA release preview semasa | **NOT VERIFIED** (tarball deploy) |
| Sama ada volume Docker untuk `data/inquiries.json` production diperlukan | Keputusan produk (disyorkan jika F1) |
| Endpoint Laravel `api.vividworks.my` untuk inquiry awam — ketersediaan & CORS | **NOT VERIFIED** dalam repo marketing sahaja |
| n8n placement / integration | **NOT VERIFIED** |
| Coolify minor version drift selepas MKT-01 | **NOT VERIFIED** |
| Traefik router priority vs app Coolify lain | Semak semasa implement (elak catch-all) |

---

## 11. Keputusan readiness

| Skop | Keputusan |
|------|-----------|
| **Pelan MKT-02** | **COMPLETE** — sedia untuk semakan Guru |
| **Production go-live** | **NOT READY** — menunggu kelulusan pelan + pelaksanaan (router TLS, container, workflow, keputusan inquiries storage) |
| **Pilihan deploy** | **Pilihan A (Dedicated Docker)** sebagai cadangan utama |
| **Kapasiti VPS** | **READY** untuk satu container production tambahan dengan syarat disiplin release image |

---

## Rujukan dalaman

- MKT-01 audit: `MKT-01-COOLIFY-VIVIDWORKS-MARKETING-PRODUCTION-READINESS-2026-11-27.md`
- Deploy preview: `deploy/preview/vividpreview-deploy.sh`, `.github/workflows/preview-deploy.yml`
- Store inquiries: `server/store.js`, `server/index.js`
- D-INQUIRIES: `docs/foundation-identity/FOUNDATION-IDENTITY-ARCHITECTURE-v1.0.md`

**MKT-02 tamat — tiada perubahan infrastruktur atau repositori dibuat.**