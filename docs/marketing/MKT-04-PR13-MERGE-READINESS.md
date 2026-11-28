# MKT-04 — PR #13 merge readiness (final sync)

**Branch:** `cursor/marketing-mkt03r-6b21`  
**HEAD (canonical):** sahkan `git rev-parse HEAD` atau Origin PR #13 `headRefOid` — pada sync **28 Nov 2026 (MYT)** tip ialah **`eef0748e4d37c704267119dba050fdbbe8831fb9`** (`eef0748`)  
**HEAD sebelum sync doc akhir:** `bdfe953`; `beacc65` bukan terkini  
**Base `main`:** `8cb65814127ff437c27d428d728322a19335578d` (`8cb6581`)  
**PR:** https://cursor.com/codebase/khairul-haizam/vividworks-site/pull/13  
**Tarikh sync akhir:** 28 November 2026, ~18:46 (Asia/Kuala_Lumpur)

---

## 1. Pengesahan HEAD

| Sumber | Nilai (sync 28 Nov 2026 MYT) |
|--------|-------------------------------|
| `git rev-parse HEAD` / `origin/cursor/marketing-mkt03r-6b21` | `eef0748e4d37c704267119dba050fdbbe8831fb9` |
| Origin PR #13 `headRefOid` | `eef0748e4d37c704267119dba050fdbbe8831fb9` |

**Keputusan:** HEAD tip **`eef0748`** — **bukan** `bdfe953` atau `beacc65` (keduanya ialah commit terdahulu pada branch).

---

## 2. Jumlah commits (10)

Commits atas `main` (`8cb6581` → tip), tertib kronologi (lama → baru):

| # | SHA | Subjek |
|---|-----|--------|
| 1 | `8d407f5` | Marketing production deploy (MKT-03/03R): scripts, workflow, and docs |
| 2 | `b0a4904` | docs(marketing): add MKT-01 Coolify readiness audit; clarify MKT-02 API host |
| 3 | `fa396a6` | test(marketing): strengthen MKT-03R container rollback harness |
| 4 | `f118ab3` | fix(marketing): address PR #13 Guru review (H1/M1–M5) |
| 5 | `eab5fc6` | docs(marketing): add MKT-04 go-live checklist for vividworks.my |
| 6 | `275b7ce` | chore(marketing): document agent scope and add verify CI workflow |
| 7 | `c7c9be8` | docs(marketing): PR #13 merge readiness pre-flight; verify CI on cursor branches |
| 8 | `beacc65` | docs(marketing): fix HEAD SHA in merge readiness |
| 9 | `bdfe953` | docs(marketing): sync HEAD in merge readiness |
| 10 | `eef0748` | docs(marketing): final PR #13 merge readiness sync for Guru |

**Kiraan:** `git rev-list --count origin/main..HEAD` = **10**; Origin PR #13 memaparkan **10** commits selepas push doc sync.

---

## 3. Origin PR #13 — mergeable

| Medan | Nilai (sync 28 Nov 2026 MYT) |
|-------|-------------------------------|
| `mergeable` | **true** |
| `hasMergeConflicts` | **false** |
| `mergeConflictState` | `MERGE_CONFLICT_STATE_CLEAN` |
| `conflictedPaths` | `[]` |

Tiada merge, tag, deploy VPS, atau DNS dilaksanakan.

---

## 4. Ujian pada HEAD tip `eef0748` (tanpa VPS)

Dijalankan pada `eef0748e4d37c704267119dba050fdbbe8831fb9` (kod deploy tidak berubah selepas `f118ab3`; commit #10 doc sahaja):

| Ujian | Hasil |
|-------|--------|
| `bash deploy/production/test-mkt03r.sh` | **PASS 28 / FAIL 0** |
| `npm test` | **3 / 3** |
| `npm run typecheck` | **OK** |
| `npm run build` | **OK** (`✓ built`) |

---

## 5. Skop diff PR (`main...HEAD`)

**17 files changed, 2302 insertions(+)**

| Kawasan | Fail |
|---------|------|
| Deploy | `vividworks-prod-deploy.sh`, `validate-release-ref.sh`, `test-mkt03r.sh`, `bootstrap-server.sh`, `deploy/production/README.md` |
| CI | `.github/workflows/production-deploy.yml`, `.github/workflows/marketing-verify.yml` |
| Image | `Dockerfile` |
| Docs | MKT-01, MKT-02, MKT-03, MKT-03R×4, MKT-04 checklist, **MKT-04-PR13-MERGE-READINESS** |

**Untracked (luar PR):** audit drift / hosting correction — kekal tempatan.

---

## 6. Jadual pengesahan Guru review — H1, M1–M5

Pembetulan utama: commit **`f118ab3`**. Bukti: **`deploy/production/test-mkt03r.sh`** (mock, tanpa VPS).

| ID | Pembetulan | Fail diubah | Bukti ujian |
|----|------------|-------------|-------------|
| **H1** | Workflow: `INPUT_REF` / `EXPECTED_TAG` via `env`; `source deploy/production/validate-release-ref.sh` — tiada `${{ }}` dalam shell untuk ref | `.github/workflows/production-deploy.yml` | Harness **1–4** (tolak `main`, SHA, tag arbitrari); langkah workflow “Resolve and validate release ref” |
| **M1** | `normalize_routing_host()` — lowercase, buang trailing `.` | `deploy/production/vividworks-prod-deploy.sh` | **23** `VIVIDWORKS.MY.` ditolak tanpa approval; **24** host candidate dibenarkan; **25** public + `VIVIDWORKS_PROD_GO_LIVE=approved-by-owner` |
| **M2** | Validasi ref dipusatkan; tolak shell metachar dan newline | `deploy/production/validate-release-ref.sh` (+ H1) | **1–4** format tag; **22** `marketing-prod/v2026.11.27.1;echo pwned` ditolak |
| **M3** | `prune_failed_release_dir()` — padam release gagal, kekalkan active/rollback | `vividworks-prod-deploy.sh` | **26** |
| **M4** | Harness: `mktemp` (`MKT03R_TMP`, `MOCK_STATE_DIR`) | `deploy/production/test-mkt03r.sh` | **27** |
| **M5** | `container_health`: HTTP `r.ok` **dan** JSON `j.ok` | `vividworks-prod-deploy.sh` | **28** (+ laluan health rollback **17–21**, **19**) |

Harness **1–21** (rollback/volume/`previous_image`): **`fa396a6`** + **`f118ab3`** — **PASS** pada tip `eef0748`.

---

## 7. Keputusan untuk Guru

| Item | Status |
|------|--------|
| HEAD (Origin + git) | **`eef0748`** — bukan `bdfe953` / `beacc65` |
| Commits | **10** |
| Merge | **Mergeable**, tiada konflik |
| Ujian pada tip | **Lulus** (28/28, npm 3/3, typecheck, build) |
| H1 / M1–M5 | Jadual §6 |

**Kelulusan seterusnya:** merge PR #13 → `main` ([MKT-04](./MKT-04-VIVIDWORKS-MARKETING-GO-LIVE-CHECKLIST.md) tahap A).

---

*Final sync — tiada ciri baharu, tiada merge/tag/deploy/DNS.*