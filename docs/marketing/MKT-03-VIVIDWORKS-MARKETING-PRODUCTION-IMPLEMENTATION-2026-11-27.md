# MKT-03 — VividWorks Marketing Production Implementation

**Tarikh:** 27 November 2026 (Asia/Kuala_Lumpur)  
**Kelulusan:** Khairul (MKT-03)  
**Rujukan:** MKT-01, MKT-02  
**Commit SHA (workspace deploy):** `8cb65814127ff437c27d428d728322a19335578d`  
**Status go-live awam `vividworks.my`:** **TIDAK diaktifkan** (sengaja)

---

## 1. Website review & screenshots

### Kaedah

- Audit kod + `npm run typecheck` / `build` / `test` pada VM.
- Pratonton visual: `npm run dev` pada `http://127.0.0.1:4321` (bukan preview/production awam).

### Screenshot

| Fail | Keterangan |
|------|------------|
| `/opt/cursor/artifacts/mkt03-home-desktop.png` | Homepage desktop |
| `/opt/cursor/artifacts/mkt03-home-mobile.png` | Homepage mobile (~390px) |
| `/opt/cursor/artifacts/mkt03-pricing.png` | Halaman Pakej |
| `/opt/cursor/artifacts/mkt03-templates.png` | Galeri template |
| `/opt/cursor/artifacts/mkt03-contact.png` | Borang hubungi |

<img alt="Homepage desktop" src="/opt/cursor/artifacts/mkt03-home-desktop.png" />
<img alt="Homepage mobile" src="/opt/cursor/artifacts/mkt03-home-mobile.png" />

### Jadual audit (MKT-03A)

| Bahagian | Status | Catatan |
|----------|--------|---------|
| Homepage | **Complete** | Landing frontpage dengan CTA, features, pricing anchor |
| Navigation | **Partial** | Dua navbar: frontpage (`/#harga`, label BM lain) vs halaman dalam (`/pricing`, 6 pautan) |
| Pricing | **Complete** | `/pricing` — lazy route |
| Features / showcase | **Complete** | Homepage sections + `/how-it-works`, `/templates` |
| Contact | **Complete** | `/contact` → `POST /api/contact` |
| Login / Register | **Partial (by design)** | CTA → `/akan-datang?...`; tiada URL SaaS luar (`VITE_SAAS_*` kosong) |
| Responsive | **Pass** | Mobile screenshot + layout stack |
| Loading / error | **Pass** | `RouteFallback`, `NotFoundPage`, health 503 jika store gagal |
| Build | **Pass** | `typecheck`, `build`, 3 unit tests |
| GitHub | **Verified** | Repo `khairul-haizam/vividworks-site`, default `main`, SHA di atas |

### Halaman berdaftar (`AppRoutes.tsx`)

`/`, `/pricing`, `/templates`, `/how-it-works`, `/faq`, `/contact`, `/privacy`, `/terms`, `/akan-datang`, `*`

### Isu / cadangan pembaikan (tanpa dilaksanakan)

1. **Penyelarasan navbar** frontpage vs Header — kelulusan reka bentuk diperlukan.
2. **E-mel/telefon hubungan** kosong jika `VITE_CONTACT_EMAIL` tidak diset pada build — pertimbangan env production pada go-live.
3. Dev sekali: Vite `504 Outdated Optimize Dep` pada `/pricing` sehingga restart dev — bukan bug production build.

Tiada perubahan reka bentuk atau kandungan dibuat.

---

## 2. GitHub repository & commit SHA

| Item | Nilai |
|------|-------|
| Repository | `khairul-haizam/vividworks-site` |
| Default branch | `main` |
| HEAD (audit/deploy) | `8cb65814127ff437c27d428d728322a19335578d` |
| Mesej commit | A4.64 staging register test; host location check guide |
| Preview workflow | `.github/workflows/preview-deploy.yml` — push `main` |
| Production workflow (baru, belum push) | `.github/workflows/production-deploy.yml` — tag `marketing-prod/v*` sahaja |
| Dockerfile | Root; label `org.opencontainers.image.revision` via build-arg |
| Env (nama sahaja) | `NODE_ENV`, `PORT`, `HOST`, `DATABASE_URL`, `DATABASE_SSL`, `ADMIN_TOKEN`; build: `VITE_SITE_URL`, `VITE_CONTACT_EMAIL`, `VITE_SAAS_LOGIN_URL`, `VITE_SAAS_REGISTER_URL`, `VITE_SAAS_EDITOR_URL` |

**NOT VERIFIED:** sama ada preview VPS = commit terkini `main` (preview image masih `20261124T163143Z`).

---

## 3. Senarai halaman & status pembangunan

| Halaman | Status | Catatan |
|---------|--------|---------|
| Home | Complete | `HomePage` + frontpage components |
| Pricing | Complete | |
| Templates | Complete | 12 template previews (build step) |
| How it works | Complete | |
| FAQ | Complete | Accordion |
| Contact | Complete | Validation BM, rate limit API |
| Privacy / Terms | Complete | Legal template |
| Akan datang | Complete | Login/register/editor intents |
| 404 | Complete | SPA fallback |

---

## 4. Build & code review

| Semakan | Hasil |
|---------|-------|
| TypeScript | **Pass** |
| `npm run build` | **Pass** (~360ms local) |
| `npm test` | **3/3 pass** |
| TODO/FIXME dalam `src/` | Tiada item kritikal; placeholder input CSS sahaja |
| Laravel / Supabase | **Tidak disentuh** |

---

## 5. Production resource yang diwujudkan

| Sumber | Nilai |
|--------|-------|
| Container | `vividworks-prod-site` |
| Image | `vividworks-prod:20261127T134242Z` (+ `vividworks-prod:latest`) |
| Network | `coolify` |
| Port | `8080` (internal) |
| Release | `/home/ubuntu/vividworks-prod/releases/20261127T134242Z` |
| Deploy script (host) | `/home/ubuntu/bin/vividworks-prod-deploy` |
| Volume | `vividworks-prod-inquiries-data` → `/app/data` |

**Tidak disentuh:** `vividpreview-site`, Laravel staging/production API, Freshmarket/POS, DNS, Cloudflare SSL.

---

## 6. Docker & Traefik configuration

### Traefik (production candidate sahaja)

```
Host(`marketing-prod-candidate.51-79-159-241.sslip.io`)
tls.certresolver=letsencrypt
priority=95
```

### Go-live gate

- **Tiada** label `Host(\`vividworks.my\`)` atau `www.vividworks.my` pada container production.
- Skrip menolak host awam tanpa `VIVIDWORKS_PROD_GO_LIVE=approved-by-owner`.
- `https://vividworks.my/` masih **526** (Cloudflare) — tidak become go-live.

### Persistent inquiries (fasa 1)

- File store `data/inquiries.json` pada volume bernama.
- Disahkan: **1** rekod ujian dalam volume selepas `POST` (data ujian sahaja).

---

## 7. GitHub workflow yang disediakan

Fail: `.github/workflows/production-deploy.yml`

- Trigger: push tag `marketing-prod/vYYYY.MM.DD.N` atau `workflow_dispatch` dengan `ref`.
- **Tiada** deploy pada setiap push `main`.
- Merekod `GITHUB_SHA` → `VIVIDWORKS_GIT_SHA` pada build.
- SSH sama host preview; candidate host default dalam workflow.
- **Tag production sebenar tidak dipush** dalam MKT-03 (kelulusan Khairul).

Fail repo lain yang ditambah:

- `deploy/production/vividworks-prod-deploy.sh`
- `deploy/production/bootstrap-server.sh`
- `deploy/production/README.md`
- `Dockerfile` — build-args/labels release identity

**Status git:** per arahan MKT-03, perubahan **belum** di-commit/push — menunggu semakan Guru.

---

## 8. Persistent inquiry storage

| Item | Status |
|------|--------|
| Volume | `vividworks-prod-inquiries-data` |
| Mode | `file` (`DATABASE_URL` unset) |
| D-INQUIRIES | Tiada Supabase/Laravel tenant |
| Ujian | `201` pada `POST /api/contact` (data ujian) |
| Persist | Fail `inquiries.json` dalam volume (1 rekod) |

`/api/inquiries` memerlukan `ADMIN_TOKEN` — tidak diuji dengan token (tiada dedahan).

---

## 9. Ujian — berjaya & gagal

| # | Ujian | Hasil |
|---|--------|-------|
| 1 | Container `vividworks-prod-site` running | **PASS** |
| 2 | Health 200 | **PASS** (candidate HTTPS) |
| 3 | Homepage | **PASS** (200, 1729 B) |
| 4 | SPA `/pricing` | **PASS** (200) |
| 5 | Static assets | **PASS** (200) |
| 6 | Contact (data ujian) | **PASS** (201) |
| 7 | Inquiry persistent | **PASS** (volume file wujud) |
| 8 | Preview | **PASS** (200) |
| 9 | Staging API | **PASS** (200 `/api/health`) |
| 10 | Rollback image tersedia | **PARTIAL** — imej `vividworks-prod:20261127T134242Z`; tiada release kedua untuk uji rollback automatik (mekanisme dalam skrip sama preview) |

**Ujian melalui:** `https://marketing-prod-candidate.51-79-159-241.sslip.io` — **bukan** `vividworks.my`.

---

## 10. Preview & perkhidmatan lain

| Perkhidmatan | Status |
|--------------|--------|
| `preview.vividworks.my` | 200 |
| `api-staging.vividworks.my` | 200 |
| `vividworks.my` awam | 526 (tiada go-live production) |
| `vividpreview-site` | Up (tidak diubah) |
| n8n pada host | **NOT VERIFIED** |

Tiada restart pada container lain yang disengajakan kecuali `vividworks-prod-site` untuk ujian persist.

---

## 11. Prosedur rollback

1. Skrip menyimpan `previous_image` sebelum deploy baharu.
2. Jika health gagal selepas deploy → auto rollback ke image sebelumnya.
3. Manual: `sudo docker run` dengan tag lama (cth. `vividworks-prod:20261127T134242Z`) melalui skrip atau `VIVIDWORKS_PROD_ROUTING_HOST` yang sama.
4. Volume inquiry **kekal** melintasi rollback image (nama volume tetap).

---

## 12. Perkara pending untuk go-live

| Item | Tindakan masa depan |
|------|---------------------|
| Traefik `vividworks.my` + redirect `www` | `VIVIDWORKS_PROD_GO_LIVE=approved-by-owner` + host labels + middleware www |
| TLS apex proxied (CF strict) | Let's Encrypt pada origin selepas router apex |
| Kelulusan tag | Push `marketing-prod/vYYYY.MM.DD.N`; jalankan workflow |
| Commit/push repo | Kelulusan Guru untuk merge workflow & skrip |
| `VITE_*` production | Tetapkan pada build (URL, contact email, SaaS URLs bila sedia) |
| `ADMIN_TOKEN` | Opsional untuk senarai inquiries |
| Uji rollback dua release | Deploy kedua, kemudian rollback |
| Penyelarasan navbar | Keputusan reka bentuk |

---

## Penutup

MKT-03 **persediaan production** selesai pada **hostname candidate** dengan volume inquiries, tanpa go-live awam.

Serahkan kepada Guru untuk semakan akhir. **Khairul** mesti meluluskan secara berasingan sebelum mengaktifkan `vividworks.my` / `www` dan sebelum push tag production.