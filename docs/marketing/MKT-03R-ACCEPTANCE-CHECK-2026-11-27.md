# MKT-03R — Acceptance Check

**Tarikh:** 27 November 2026 (Asia/Kuala_Lumpur)  
**Status:** Siap untuk keputusan akhir Guru — **tiada** commit/push, VPS deploy, DNS, atau go-live

---

## 1. Test main path

**Perubahan:** Fungsi `deploy_start_container_or_abort` (logik yang sama seperti `main`) untuk ujian mock.

**Ujian 16 — bukti `attempt_rollback` tidak dipanggil:**
- `MOCK_VOLUME_FAIL=1`, `deploy_start_container_or_abort` dengan imej baharu.
- Pemalar `MKT03R_ROLLBACK_CALLS` — **0** selepas panggilan.
- `return` = **2**, fail `/tmp/mkt03r-docker-rm` tidak wujud.
- **PASS** (bukan hanya assert `run_container` return code).

**Ujian 17 — kawalan:** `bad-run` melalui laluan yang sama → `MKT03R_ROLLBACK_CALLS` ≥ **1**.

---

## 2. Rollback log message

| Konteks | Mesej |
|---------|--------|
| Deploy, volume gagal **sebelum** `docker rm` | `Aborting before removing existing container...` + `Deploy aborted before replacing container; rollback not attempted` |
| Rollback, volume gagal **sebelum** `docker rm` | `Rollback aborted before removing container for image swap...; container was not removed in this rollback attempt` |
| Dibuang | Frasa **"existing container left running"** (andaian tidak disahkan) |

**Ujian 18–19:** grep log mock — **PASS** (tiada "left running").

---

## 3. Validation

### `bash -n`
| Fail | Hasil |
|------|--------|
| `vividworks-prod-deploy.sh` | OK |
| `test-mkt03r.sh` | OK |
| `validate-release-ref.sh` | OK |

### Test harness

```bash
bash deploy/production/test-mkt03r.sh
```

| Metrik | Nilai |
|--------|-------|
| **PASS** | **21** |
| **FAIL** | **0** |

Log: `/opt/cursor/artifacts/mkt03r-acceptance-test-output.log`

| # | Ujian utama (baru/dikemas kini) |
|---|----------------------------------|
| 16 | `deploy_start_container_or_abort` — tiada `attempt_rollback` pada volume fail |
| 17 | `attempt_rollback` dipanggil pada post-rm run fail |
| 18 | Mesej log rollback (tanpa dakwaan running) |
| 19 | Mesej log deploy vs rollback dibezakan |

---

## 4. Tidak disentuh

Workflow, architecture, nama resource, DNS, VPS, Preview.

---

## 5. Penutup

Acceptance check selesai. **PASS=21 / FAIL=0.** Berhenti untuk keputusan akhir Guru (commit/push).