# Draf untuk Guru / ChatGPT — Tahap B (release production candidate)

**Untuk:** Khairul → forward kepada Guru  
**Repo:** `khairul-haizam/vividworks-site` (Origin)  
**Tarikh draf:** 28 November 2026 (MYT)  
**Rujukan:** [MKT-04 go-live checklist](./MKT-04-VIVIDWORKS-MARKETING-GO-LIVE-CHECKLIST.md) §B–C

---

## Konteks (Tahap A — siap)

| Item | Nilai |
|------|--------|
| PR #13 | Kod sudah pada `main` (merge commit `68f73cb`; asas branch PR `27f07c1`) |
| Skop | Marketing site — **bukan** Laravel `api.vividworks.my` |
| VPS | `51.79.159.241` (Coolify / Docker) |
| Apex `vividworks.my` | **Belum** — deploy pertama ke **candidate** sahaja |

**Dilarang dalam Tahap B:** `VIVIDWORKS_PROD_GO_LIVE`, routing `vividworks.my` / `www`, tag bukan format, DNS/Cloudflare.

---

## Mesej siap salin (ChatGPT / Guru)

```
Assalamualaikum — sambungan MKT-04 selepas merge PR #13.

Status:
- main sudah ada kod production marketing (merge 68f73cb, asal PR 27f07c1).
- Kita mahu TERUSKAN TAHAP B sahaja: release bertag ke candidate, bukan apex.

Mohon Guru:
1) Sahkan secret deploy masih wujud di Origin Actions untuk repo ini:
   - VIVIDPREVIEW_SSH_KEY (workflow production-deploy.yml)

2) Pada commit main yang diluluskan (contoh HEAD semasa: git rev-parse main),
   cipta tag release:
   marketing-prod/v2026.11.28.1
   (format wajib: marketing-prod/vYYYY.MM.DD.N — lihat validate-release-ref.sh)

3) Push tag ke Origin ATAU jalankan workflow_dispatch
   "Deploy marketing production (tagged)" dengan input ref = tag sama.

4) Semak deploy:
   - HOST: 51.79.159.241 (ubuntu@)
   - ROUTING_HOST default: marketing-prod-candidate.51-79-159-241.sslip.io
   - Container: vividworks-prod-site
   - Release dir: /home/ubuntu/vividworks-prod/releases/
   - Script remote: ~/bin/vividworks-prod-deploy

5) Laporkan balik:
   - URL candidate HTTPS yang hidup
   - GET /api/health → 200, ok:true
   - Ringkas log Actions jika gagal (SSH, docker, health)

Jangan set VIVIDWORKS_PROD_GO_LIVE, jangan route vividworks.my/www, jangan DNS —
itu Tahap D/E selepas candidate (Tahap C) lulus.

Rujukan repo: deploy/production/README.md, MKT-04 checklist §B–C.
```

---

## Checklist operasi (Guru)

### B1 — Pra-tag

- [ ] `main` = commit yang disetujui deploy (cadangan: `68f73cb` atau `git rev-parse origin/main`)
- [ ] Fail wujud: `.github/workflows/production-deploy.yml`, `deploy/production/vividworks-prod-deploy.sh`
- [ ] Secret `VIVIDPREVIEW_SSH_KEY` diset pada repo Origin (semak panel / CLI)

### B2 — Tag & trigger

```bash
git fetch origin main
git checkout main
git pull origin main

# Contoh (sesuaikan tarikh/N):
git tag -a marketing-prod/v2026.11.28.1 -m "Marketing prod candidate release 2026-11-28"
git push origin marketing-prod/v2026.11.28.1
```

**Alternatif:** Origin Actions → **Deploy marketing production (tagged)** → `workflow_dispatch` → input `ref` = `marketing-prod/v2026.11.28.1`

### B3 — Semakan selepas workflow

- [ ] Workflow tamat hijau
- [ ] SSH: `docker ps` — `vividworks-prod-site` **Up**
- [ ] `curl -sfS "https://marketing-prod-candidate.51-79-159-241.sslip.io/api/health"` → JSON `ok: true`

### B4 — Hantar semula ke Khairul / Cursor

Isi template balasan:

| Medan | Nilai |
|-------|--------|
| Tag | `marketing-prod/v…` |
| Source SHA (dari tag) | `git rev-list -1 <tag>` |
| Candidate URL | `https://marketing-prod-candidate.51-79-159-241.sslip.io` |
| Health | PASS / FAIL + snippet |
| Preview regression | `preview.vividworks.my` masih OK? (ya/tidak) |

---

## Tahap C (ringkas — selepas B berjaya)

Uji pada **candidate** sahaja (bukan apex):

- [ ] `/`, `/pricing`, `/contact` → 200
- [ ] `POST /api/contact` → 201
- [ ] Volume inquiries kekal selepas restart container (pilihan)
- [ ] Tiada regresi ringkas pada `preview.vividworks.my`

Selepas C lulus → baru bincang **D** (`VIVIDWORKS_PROD_GO_LIVE=approved-by-owner`) dengan **Khairul**.

---

## Apa Cursor boleh buat selepas Guru lapor

| Guru hantar | Cursor |
|-------------|--------|
| Log Actions gagal | Diagnose, patch workflow/skrip, PR kecil jika perlu |
| Health FAIL pada candidate | Debug harness / deploy script (dalam repo) |
| Candidate PASS | Kemas kini checklist MKT-04, sediakan draf Tahap D |
| Minta apex/DNS | Berhenti — keputusan Khairul + Guru, bukan agent |

---

## Nota token agent

Merge PR #13 dibuat melalui push `main` (token `origin pr merge` tiada scope). Jika UI PR #13 masih **open**, tutup/tandakan merged pada Origin bagi rekod bersih — **kandungan sudah pada `main`**.

---

*Draf oleh Cursor Cloud Agent — boleh edit tarikh tag dan SHA sebelum hantar.*