# MKT-03R — Production Deployment Script & Workflow Corrections

**Tarikh:** 27 November 2026 (Asia/Kuala_Lumpur)  
**Repository:** `khairul-haizam/vividworks-site`  
**Status:** Guru review remediations — **belum** commit/push

---

## 1. Perubahan kod

### 1.1 `deploy/production/vividworks-prod-deploy.sh` (rollback)

| Keperluan | Pelaksanaan |
|-----------|-------------|
| Kegagalan `docker run` tidak keluar tanpa rollback | `run_container` return 1; `attempt_rollback` dipanggai |
| Health gagal → rollback | `attempt_rollback "health check failure"` |
| Kekalkan volume | `-v "${DATA_VOLUME}:/app/data"` pada setiap `docker run` (rollback termasuk) |
| Tiada previous image | Log eksplisit: `No previous image available for rollback (...)` |
| Nama container/network/volume/candidate host | **Tidak diubah** |

Fungsi baharu/berubah: `attempt_rollback`, `run_container` (conditional docker run), `previous_image` global untuk rollback, `main()` dibungkus; skrip tidak auto-run apabila di-source (untuk ujian).

### 1.2 `.github/workflows/production-deploy.yml` (workflow_dispatch + SHA)

| Keperluan | Pelaksanaan |
|-----------|-------------|
| Tag push `marketing-prod/v*` | Kekal |
| `workflow_dispatch` format | Step **Resolve release ref** — regex `marketing-prod/vYYYY.MM.DD.N` sahaja |
| Tolak `main` / SHA | Regex + penolakan eksplisit branch/hex SHA |
| Ref mesti wujud | `checkout@v4` dengan `refs/tags/<ref>`; step **Record source SHA** — `git describe --exact-match` |
| SHA deploy | `GIT_SHA` dari `git rev-parse HEAD` selepas checkout (`steps.source.outputs.sha`) — **bukan** `github.sha` untuk dispatch |
| Tolak manual `main` | Ref tidak lulus validator |

Helper: `deploy/production/validate-release-ref.sh` (sama regex, boleh dipanggil tempatan).

### 1.3 Ujian tempatan

`deploy/production/test-mkt03r.sh` — mock `sudo`/`docker`, tanpa deploy VPS.

---

## 2. Keputusan ujian (wajib)

| # | Ujian | Keputusan | Kaedah |
|---|--------|-----------|--------|
| 1 | Workflow menolak input `main` | **PASS** | `validate_marketing_prod_ref main` |
| 2 | Workflow menolak SHA biasa | **PASS** | full + short SHA ditolak |
| 3 | Format tag betul jika tag wujud | **PASS** | tag tempatan `marketing-prod/v2099.01.01.0` + checkout (dipadam selepas ujian; **tiada push**) |
| 4 | SHA checkout = SHA build | **PASS** | `rev-parse refs/tags/...` = `rev-parse HEAD` pada checkout |
| 5 | Simulasi kegagalan `docker run` → rollback | **PASS** | mock docker, imej `bad-run` |
| 5b | Tiada previous image | **PASS** | log + exit path |
| 6 | Simulasi health gagal → rollback | **PASS** | imej `bad-health` + mock exec gagal |
| — | Volume persist pada `run_container` | **PASS** | `docker volume create` dipanggai dalam mock |
| 7 | Candidate + Preview | **PASS** | HTTPS 200 `/api/health` |

**Jalanan:** `bash deploy/production/test-mkt03r.sh` → `summary pass=11 fail=0`

**Tidak dilakukan:** deploy semula VPS, tag production push, aktifkan `vividworks.my`, ubah DNS/Preview/Laravel.

---

## 3. Fail disentuh (workspace sahaja)

- `deploy/production/vividworks-prod-deploy.sh`
- `.github/workflows/production-deploy.yml`
- `deploy/production/validate-release-ref.sh` (baru)
- `deploy/production/test-mkt03r.sh` (baru, ujian tempatan)

---

## 4. Pending

| Item | Nota |
|------|------|
| Commit/push | Menunggu kelulusan Guru (MKT-03R + MKT-03) |
| Skrip pada VPS host | Host masih versi lama sehingga deploy semula dibenarkan |
| Go-live `vividworks.my` | Belum dibenarkan |

---

## 5. Penutup

Tiga penemuan Guru — rollback, validasi `workflow_dispatch`, dan SHA daripada checkout — telah dibetulkan dan diuji tempatan. Candidate dan Preview kekal **200**.

Berhenti untuk semakan Guru. **Tiada** commit atau push.