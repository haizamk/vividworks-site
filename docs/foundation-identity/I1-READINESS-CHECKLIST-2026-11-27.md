# I1 Readiness Checklist

**Purpose:** Gate before **staging** Identity migrations and I1 code (users + Sanctum + verify flows).  
**Date:** 27 November 2026 (UTC)  
**Owner decisions:** [`I0-DECISION-RECORD-2026-11-27.md`](./I0-DECISION-RECORD-2026-11-27.md)

Legend: ✅ Confirmed | ⚠️ Gap / action required | ❌ Blocker | ➖ Not required for staging I1 start | 🔍 Unknown (needs read-only verification)

---

## 1. Repository and F1 baseline

| Item | Status | Notes |
|------|--------|-------|
| `main` at approved checkpoint | ✅ | `3fc402ac81a4485225da1ae639c56223e0bb49c8` at I0 prep |
| Five F1 migrations only in repo | ✅ | No Identity migrations in repo |
| F1 code unchanged for I0 | ✅ | Documentation-only I0 closure |
| I0 decision record committed | ✅ | This package |

---

## 2. Staging database (live verification A4.40)

| Item | Status | Notes |
|------|--------|-------|
| Five Laravel migrations on staging | ✅ | Same names as repo; batch 1+2 split only |
| Eight F1 tables present | ✅ | |
| Identity tables absent | ✅ | Expected pre-I1 |
| Legacy F1 data preserved | ✅ | **49** generations (count may grow); no cleanup per D-LEGACY-47 |
| Unexpected migration drift | ✅ | None observed |

---

## 3. Production database (A4.39 baseline — no re-check in I0)

| Item | Status | Notes |
|------|--------|-------|
| Five F1 migrations | ✅ | A4.39 |
| Identity tables absent | ✅ | A4.39 |
| F1 rows empty | ✅ | A4.39 |
| Production backup before **production** I1 migrate | ⚠️ | Mandatory before any **production** identity migration; A4.21B pattern |

---

## 4. Staging environment and runtime

| Item | Status | Notes |
|------|--------|-------|
| API health + Postgres | ✅ | A4.40 — `GET /api/health` 200 |
| Staging `.env` / mail presence | 🔍 | Not available without staging host access; **verify before I1 migration** |
| Staging SMTP / mailer can send verification email | 🔍 | **Required to test D-EMAIL-V** |
| Staging PHP / Laravel versions | 🔍 | Host not inspected in A4.40 |
| `artisan migrate:status` on staging host | 🔍 | Use Supabase + host verify before migrate |
| Staging DB backup or snapshot | ⚠️ | **Confirm in Supabase dashboard / snapshot policy before I1 staging migrate** |

---

## 5. RLS and database role (D-RLS)

| Item | Status | Notes |
|------|--------|-------|
| Document staging RLS-on, zero-policy posture | ✅ | A4.40 + I0 decision record |
| Confirm Laravel DB **role** on staging (bypass vs enforced RLS) | ⚠️ | Not confirmed in A4.40; **I1 config task** |
| Change RLS / policies during I1 entry | ➖ | **Not authorized** without separate gate |

---

## 6. Production environment (not blocking staging I1)

| Item | Status | Notes |
|------|--------|-------|
| Production SMTP complete | ➖ | Not required to **start I1 on staging** |
| Production `VIVIDAI_*` / `ADMIN_TOKEN` | ➖ | A4.39 empty; separate from I1 |
| Production health-only exposure | ✅ | A4.31–A4.34 handoff |

---

## 7. Architecture and documentation

| Item | Status | Notes |
|------|--------|-------|
| `FOUNDATION-IDENTITY-ARCHITECTURE-v1.0.md` in repo | ✅ | I0 closure package |
| I0 decision record | ✅ | Nine decisions including D-LEGACY-47 wording |
| Owner I0 sign-off | ⚠️ | Pending after Guru/Khairul review |

---

## 8. Explicit non-goals before I1 code merge

- ❌ No F1 schema or behavior changes in I1 phase scope (per audit §10 I1).
- ❌ No production identity migrations without backup + gate.
- ❌ No n8n W2 publish, no new API exposure, no production deploy as part of I1 **documentation** closure.

---

## 9. Recommended actions before I1 implementation checkpoint

1. **Owner:** Approve [`I0-CLOSURE-REPORT-2026-11-27.md`](./I0-CLOSURE-REPORT-2026-11-27.md).
2. **Ops (read-only then approved):** Staging env presence scan — mail vars **present**; send test verification mail.
3. **Ops:** Staging DB backup/snapshot immediately before first I1 migration on staging.
4. **Ops:** Record staging Laravel DB username and whether role bypasses RLS (compare to A4.40 `postgres` session note).
5. **Engineering:** Open **I1 implementation** branch only after steps 1–3 are acceptable.