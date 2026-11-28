# MKT-04 — PR #13 merge readiness (final sync)

**PR:** https://cursor.com/codebase/khairul-haizam/vividworks-site/pull/13  
**Base `main`:** `8cb65814127ff437c27d428d728322a19335578d` (`8cb6581`)  
**Branch:** `cursor/marketing-mkt03r-6b21`  
**Tarikh sync:** 28 November 2026, ~19:12 (Asia/Kuala_Lumpur)

---

## 1. Pengesahan HEAD

| Sumber | SHA |
|--------|-----|
| `git rev-parse HEAD` | _sama dengan Origin `headRefOid` (commit #11 di bawah)_ |
| `origin/cursor/marketing-mkt03r-6b21` | _sama_ |
| Origin PR #13 `headRefOid` | _sama_ |

**HEAD (canonical):** SHA penuh commit #11 dalam jadual §2 — **bukan** `bdfe953` / `beacc65` / `e0d5431` sebagai tip deploy.

---

## 2. Jumlah commits: **11**

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
| 10 | `e0d5431` | docs(marketing): final PR #13 merge readiness sync for Guru |
| 11 | _(lihat `git rev-parse --short=7 HEAD`)_ | docs(marketing): final merge readiness metadata sync |

---

## 3. Origin PR #13 — mergeable

`mergeable: true`, `hasMergeConflicts: false`, `MERGE_CONFLICT_STATE_CLEAN`.

---

## 4. Ujian (pada tip branch / commit #11)

| Ujian | Hasil |
|-------|--------|
| `bash deploy/production/test-mkt03r.sh` | **28 / 0** |
| `npm test` | **3 / 3** |
| `npm run typecheck` | **OK** |
| `npm run build` | **OK** |

Dijalankan pada tip branch 28 Nov 2026 MYT. Kod deploy: `f118ab3` terakhir; commit #11 doc sahaja.

---

## 6. H1, M1–M5 (`f118ab3`)

| ID | Fail diubah | Harness |
|----|-------------|---------|
| H1 | `.github/workflows/production-deploy.yml` | 1–4 |
| M1 | `deploy/production/vividworks-prod-deploy.sh` | 23–25 |
| M2 | `deploy/production/validate-release-ref.sh` | 1–4, 22 |
| M3 | `vividworks-prod-deploy.sh` | 26 |
| M4 | `deploy/production/test-mkt03r.sh` | 27 |
| M5 | `vividworks-prod-deploy.sh` | 28, 17–21 |

---

## 7. Guru

| HEAD | Commits | Merge | Ujian | Kod deploy |
|------|---------|-------|-------|------------|
| commit #11 | 11 | mergeable | lulus §4 | tiada perubahan |

Merge PR #13 → `main`. Tiada merge/tag/deploy oleh agent.

*Commit #11 = penyelarasan metadata doc (tiada kod deploy).*