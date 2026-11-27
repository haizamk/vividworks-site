# I0 Decision Record

**Status:** Owner-approved baseline for VividWorks Foundation & Identity  
**Date:** 27 November 2026 (UTC)  
**Owner:** Khairul  
**Preparation:** Cursor Cloud Agent (I0 Formal Documentation Closure)  
**Verification:** Guru review of A4.39 (Production) and A4.40 (Staging)

This record is **normative** for implementation phases I1 onward. It does **not** authorize implementation by itself; I1 requires a separate checkpoint approval after this I0 closure is accepted.

---

## Decision summary

| ID | Decision | Approved baseline |
|----|----------|-------------------|
| **D-PID-FMT** | New project ID format | `vw_<tenantSlug8>_<ulid>`, maximum **64** characters |
| **D-PID-GLOBAL** | `project_id` uniqueness | **Globally unique** (maintain current F1 global unique index semantics) |
| **D-LEGACY-47** | Staging F1 legacy data | **Preserve all existing staging F1 records.** No backfill, no cleanup. **Do not fix generation counts in docs** (e.g. not permanently “47”). A4.40 observed **49** `generations` on 2026-11-27. |
| **D-RLS** | Row-level security strategy | **Laravel controls application data access.** Assess Supabase RLS separately **before** allowing direct DB access. **Do not change RLS during I0/I1 entry** without an explicit gate. Record A4.40: eight F1 tables on staging have RLS **enabled**, **zero** named policies; inspection used role `postgres` (`BYPASSRLS` true) — **confirm application connection role during I1 configuration**. |
| **D-ONBOARD** | Registration onboarding | **Automatically create one personal tenant** on registration |
| **D-EMAIL-V** | Email verification | **Mandatory before AI generation** (and before project creation per architecture §3) |
| **D-SANCTUM** | SPA / API auth transport | **Define Sanctum stateful domains during I1 configuration** (not preset in v1.0 doc) |
| **D-INQUIRIES** | Marketing inquiries | **Remain separate from tenant data** (marketing inquiry store not merged into tenant schema) |
| **D-GEN-AUTH** | F1 invoke authorization | **Sanctum authentication and project ownership validation** before generation |

---

## Additional approved decisions (post–I0 closure)

Approved by Khairul. These entries **do not modify** the nine rows in **Decision summary** above.

| ID | Status | Decision |
|----|--------|----------|
| **D-PHASE-SUB** | **APPROVED** | **I2** creates the Free subscription entitlement: `subscriptions.plan_code = free`, `status = active`. **I4** is reserved for billing, payment gateways, upgrades, and commercial subscription features. Free entitlement is **not** deferred to I4. |
| **D-ONBOARD-PHASE** | **APPROVED** | **I1** manages user identity and authentication only (register, login, logout, email verification, password reset, resend). **I2** creates personal tenant, owner membership, and Free subscription entitlement. **I1** and **I2** are separate phases — **not** one atomic database transaction. Project creation requires email verification (**D-EMAIL-V**) and is **not** automatic during registration or I2 onboarding. |
| **D-SUB-SCHEMA** | **CLOSED** | Official subscription column name: **`subscriptions.plan_code`** (not `plan`). |

**Relationship to D-ONBOARD:** **D-ONBOARD** retains the intent that each user receives a personal tenant through onboarding. **D-ONBOARD-PHASE** clarifies that tenant, membership, and subscription are created in **I2**, not in the same request or transaction as **I1** registration.

### Decisions still open

| ID | Topic | Status |
|----|-------|--------|
| **D-PROJECT-COL** | External project string column on `projects`: `project_id` vs `public_id` | **OPEN — OWNER DECISION REQUIRED** |
| **D-TENANT-RES** | Tenant resolution mechanism (header, subdomain, session default, hybrid) | **OPEN — OWNER DECISION REQUIRED** |

---

## Previously approved (retained)

| ID | Status | Note |
|----|--------|------|
| **D-EMAIL-V** (detailed product rules) | Approved 25 Nov 2026 | See `docs/FOUNDATION-IDENTITY-AUDIT-2026-11-25.md` §8.1 for unverified vs verified capability matrix |

---

## Superseded or closed audit items

The following `docs/FOUNDATION-IDENTITY-AUDIT-2026-11-25.md` §8.2 rows are **closed** by the table above (do not treat as open in new work):

- D-PID-FMT, D-PID-GLOBAL, D-LEGACY-47, D-RLS, D-ONBOARD, D-INQUIRIES, D-GEN-AUTH (merged with Sanctum + ownership)

**Sanctum** was not a separate row in the audit; **D-SANCTUM** is added here per owner direction (A4.40 task baseline).

---

## Evidence references

| Checkpoint | Evidence |
|------------|----------|
| A4.39 | Production: five F1 migrations; identity absent; health 200; env integration gaps documented |
| A4.40 | Staging DB: five migrations; identity absent; **49** generations; RLS enabled / no policies on F1 tables; health 200 |

---

## Sign-off

| Role | Action | Date |
|------|--------|------|
| Khairul | **Gate 3:** Approve `FOUNDATION-IDENTITY-ARCHITECTURE-v1.0.1.md` | 2026-11-27 (UTC) |
| Khairul | **Gate 4:** I0 formal sign-off (`I0-CLOSURE-REPORT`) | 2026-11-27 (UTC) |
| Guru | Documentation review (PR #7 + I0.2h) | 2026-11-27 (UTC) |