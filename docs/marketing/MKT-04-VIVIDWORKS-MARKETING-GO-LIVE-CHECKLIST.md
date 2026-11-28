# MKT-04 — Checklist go-live `vividworks.my` (Marketing)

**Untuk:** Guru / Khairul (kelulusan berperingkat)  
**Skop:** Laman marketing `khairul-haizam/vividworks-site` pada VPS Coolify (`51.79.159.241`) — **bukan** Laravel API (`api.vividworks.my`)  
**Rujukan:** MKT-01, MKT-03, PR Origin **#13**, `deploy/production/README.md`  
**Status awam apex/www:** **TIDAK aktif** sehingga langkah 5 diluluskan dan dijalankan

---

## Ringkasan tahap

| Tahap | Apa yang berlaku | Kelulusan |
|-------|------------------|-----------|
| **A** | Merge kod deploy ke `main` | Guru |
| **B** | Release bertag → workflow production | Guru (tag + trigger) |
| **C** | Ujian pada **candidate** (bukan apex) | Guru / ops |
| **D** | Env `go-live` + deploy dengan host awam | **Khairul sahaja** (`approved-by-owner`) |
| **E** | Smoke apex + Cloudflare SSL | Guru |
| **F** | Kandungan / produk (pilihan) | Produk |

Preview `preview.vividworks.my` boleh kekal hidup; **jangan** ubah tanpa kelulusan.

---

## Apa Cursor boleh buat vs bila perlu ChatGPT / Guru

**ChatGPT / Guru** di sini = kelulusan manusia untuk keputusan produk, merge, VPS, DNS, tag production, dan env go-live. Cursor agent **teruskan sendiri** sehingga langkah itu.

| Cursor **boleh** (tanpa ChatGPT) | **Perlu** ChatGPT / Guru / akses luar |
|----------------------------------|--------------------------------------|
| Uji `test-mkt03r.sh`, `npm test`, `typecheck`, `build` | Merge PR #13 ke `main` |
| Baiki kod/docs dalam branch PR | Push tag `marketing-prod/v*` + jalankan deploy SSH |
| Tambah workflow **verify** (tanpa secret VPS) | SSH ke `51.79.159.241`, bootstrap host |
| Kemas kini MKT checklist, laporan semakan PR | Set `VIVIDWORKS_PROD_GO_LIVE=approved-by-owner` |
| Sedia mesej / patch untuk Guru semak | Cloudflare SSL mode, DNS, www redirect |
| Cipta/kemas kini PR di Origin | **Live** `vividworks.my` / smoke apex |
| Langgan notifikasi CI/PR (jika tool tersedia) | Mirror repo ke GitHub `haizamk/*`, rotate secret |
| Semak diff PR #13, cadang fix review | Kebenaran preview deploy `main` (auto GHA) |

**Status agent (branch PR #13, selepas push terkini):** harness **28/28**, `npm test` **3/3**, `typecheck` + `build` **OK** — boleh kekal kerja prep merge tanpa ChatGPT.

**Halangan seterusnya (berhenti & minta Guru):** bila A–E checklist memerlukan merge, tag, SSH, atau go-live env.

---

## A — Pra-merge (kod)

- [ ] Semak & merge [PR #13](https://cursor.com/codebase/khairul-haizam/vividworks-site/pull/13) → `main` (tanpa merge automatik agent)
- [ ] Harness lokal: `bash deploy/production/test-mkt03r.sh` → **PASS** (28/28 selepas MKT-03R) — **agent: OK** 2026-11-28, lihat [MKT-04-PR13-MERGE-READINESS](./MKT-04-PR13-MERGE-READINESS.md)
- [ ] `npm test` / `npm run build` pada commit release — **PASS** — **agent: OK** 2026-11-28
- [ ] Pastikan workflow `.github/workflows/production-deploy.yml` ada pada `main` dan secret deploy (`VIVIDPREVIEW_SSH_KEY` atau setara) masih sah — **NOT VERIFIED** semasa merge; semak sebelum tag

**Dilarang tanpa kelulusan:** push tag production, set `VIVIDWORKS_PROD_GO_LIVE`, tukar DNS, mirror paksa ke GitHub `haizamk/*`.

---

## B — Release production (pasca-merge)

- [ ] Pada commit `main` yang diluluskan, cipta tag: `marketing-prod/vYYYY.MM.DD.N` (contoh `marketing-prod/v2026.11.28.1`)
- [ ] Push tag ke **Origin** (sumber kebenaran workflow) atau jalankan **workflow_dispatch** dengan input tag yang sama
- [ ] Workflow: checkout tag → tarball → `~/bin/vividworks-prod-deploy` pada host (default host Traefik: **candidate** `marketing-prod-candidate.51-79-159-241.sslip.io`)
- [ ] Semak log Actions / SSH: container `vividworks-prod-site` **running**, release dir di `/home/ubuntu/vividworks-prod/releases/`

---

## C — Ujian candidate (wajib sebelum apex)

Jalankan pada URL candidate — **bukan** `vividworks.my`.

- [ ] `GET https://<candidate>/api/health` → **200**, `ok: true`, `r.ok` + `j.ok` (skrip deploy)
- [ ] `/`, `/pricing`, `/contact` → **200**; `POST /api/contact` → **201** (data ujian)
- [ ] Volume `vividworks-prod-inquiries-data` — inquiry kekal selepas restart
- [ ] **Pilihan disyorkan:** deploy tag kedua, sahkan rollback automatik jika health gagal (MKT-03 §10 #10 masih PARTIAL)
- [ ] `preview.vividworks.my` dan `api-staging.vividworks.my` masih **200** (tiada regression)

---

## D — Gate go-live awam (apex / www)

**Kod menolak** Traefik untuk `vividworks.my` / `www` melainkan:

```bash
export VIVIDWORKS_PROD_GO_LIVE=approved-by-owner
export VIVIDWORKS_PROD_ROUTING_HOST=vividworks.my   # atau www — satu host setiap deploy semasa MKT-03
```

- [ ] Khairul meluluskan secara eksplisit (mesej kelulusan direkod)
- [ ] Jalankan deploy dengan env di atas (manual SSH atau perluasan workflow — **belum** default dalam GHA; keputusan ops)
- [ ] **www → apex:** redirect belum didayakan dalam MKT-03 — [ ] keputusan: middleware Traefik berasingan (luar skop minimum)

---

## E — DNS, TLS, smoke awam

Keadaan MKT-01: apex DNS → VPS; **526** via Cloudflare; origin **503** tanpa router apex.

- [ ] Selepas router + Let's Encrypt pada origin untuk `vividworks.my`: Cloudflare SSL **Full (strict)** — semak per-host (sebelum ini **NOT VERIFIED**)
- [ ] `https://vividworks.my/api/health` → **200**
- [ ] `https://www.vividworks.my/` — [ ] 301 ke apex atau TLS sah (ikut keputusan D)
- [ ] Banding SHA/image: tag release = yang diharapkan (label OCI / env `VIVIDWORKS_GIT_SHA` pada deploy)

---

## F — Produk & env build (tidak menyekat teknikal minimum)

- [ ] `VITE_SITE_URL`, `VITE_CONTACT_EMAIL`, SaaS URLs (`VITE_SAAS_*`) untuk production build
- [ ] `DATABASE_URL` / `ADMIN_TOKEN` jika inquiries admin atau Postgres diperlukan
- [ ] Navbar frontpage vs halaman dalam (MKT-03A — keputusan reka bentuk)

---

## Rollback pantas

1. Skrip simpan `previous_image` sebelum deploy baharu; gagal health → auto rollback.  
2. Manual: image tag lama (cth. `vividworks-prod:<UTC>`) + `VIVIDWORKS_PROD_ROUTING_HOST` yang sama.  
3. Volume inquiries **tidak** dipadam pada rollback image.

Rujuk: `deploy/production/vividworks-prod-deploy.sh`, MKT-03 §11.

---

## Sign-off

| Langkah | Nama | Tarikh (MYT) | OK |
|---------|------|--------------|-----|
| A Merge PR #13 | | | ☐ |
| B Tag / workflow production | | | ☐ |
| C Candidate PASS | | | ☐ |
| D Go-live gate (`approved-by-owner`) | Khairul | | ☐ |
| E Apex smoke + SSL | | | ☐ |

**Selepas E lengkap:** marketing production dianggap **live** pada `vividworks.my` (tertakluk F untuk kandungan operasi).

---

*Dokumen ini tidak menjalankan deploy. Agent tidak merge/tag/go-live tanpa arahan Guru explisit.*