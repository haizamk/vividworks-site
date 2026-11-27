# MKT-03R — Final Validation

**Tarikh:** 27 November 2026 (Asia/Kuala_Lumpur)  
**Status:** Menunggu kelulusan Guru — **tiada** commit/push, tiada VPS deploy, tiada go-live

---

## 1. Pembetulan akhir

### Test counter
- Ujian checkout SHA dalam `test-mkt03r.sh` **tidak lagi menggunakan subshell**.
- `pass_test` / `fail_test` dikemas kini dalam shell utama; jumlah PASS/FAIL tepat.

### Volume failure handling (`vividworks-prod-deploy.sh`)
- `ensure_data_volume` gagal → `run_container` return **`2`** (**sebelum** `docker rm`).
- Return **`1`** kekal untuk kegagalan `docker run` (**selepas** container lama dibuang).
- `main`: pada `rc=2` → log `Deploy aborted before replacing container; rollback not attempted` dan **keluar** tanpa `attempt_rollback`.
- `rollback()`: pada `rc=2` → tidak anggap swap container; log eksplisit, container sedia ada dianggap masih berjalan.
- Mekanisme rollback untuk `docker run` / health **tidak diubah**.

---

## 2. Bash syntax

| Fail | `bash -n` |
|------|-----------|
| `deploy/production/vividworks-prod-deploy.sh` | OK |
| `deploy/production/test-mkt03r.sh` | OK |
| `deploy/production/validate-release-ref.sh` | OK |

---

## 3. Test harness

Perintah: `bash deploy/production/test-mkt03r.sh`  
Log penuh: `/opt/cursor/artifacts/mkt03r-test-output.log`

| # | Ujian | Hasil |
|---|--------|--------|
| 1 | Tolak `main` | PASS |
| 2 | Tolak SHA penuh | PASS |
| 3 | Tolak SHA pendek | PASS |
| — | Tolak tag sewenang-wenang | PASS |
| 4 | Terima format tag | PASS |
| 5 | Checkout SHA = tag (temp repo) | PASS |
| — | Temp tag dibersihkan | PASS |
| 6 | CURRENT → release baharu (deploy OK) | PASS |
| 7 | CURRENT dipulihkan (rollback OK) | PASS |
| 8 | Tiada previous CURRENT | PASS |
| 9 | `docker run` gagal → rollback + CURRENT | PASS |
| 10 | Tiada previous image | PASS |
| 11 | Named volume mount pada `docker run` | PASS |
| 12 | `ensure_data_volume` OK | PASS |
| 13 | Volume create gagal (fail-closed) | PASS |
| 15 | Volume gagal: tiada `docker rm`, rc=2 | PASS |
| 16 | Laluan deploy tidak rollback pada rc=2 | PASS |
| 14 | Candidate + Preview HTTP 200 | PASS |

**Jumlah:** **PASS=18, FAIL=0**

---

## 4. Skop tidak disentuh

Architecture, nama resource, routing candidate, DNS, workflow (melebihi MKT-03R), Preview container — **tidak diubah**.

---

## 5. Penutup

Pembetulan Final Review siap dan diuji. Berhenti untuk kelulusan Guru sebelum commit/push.