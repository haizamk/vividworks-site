# Foundation & Identity Architecture v1.0

**Status:** Formal repository architecture record for I0 closure  
**Effective date:** 27 November 2026 (UTC)  
**Repository:** `haizamk/vividworks-site`  
**Authority:** Khairul (owner); validated via Guru review of A4.39 / A4.40  
**Binding decisions:** [`I0-DECISION-RECORD-2026-11-27.md`](./I0-DECISION-RECORD-2026-11-27.md) — if any conflict, the decision record wins.

---

## 1. Purpose and scope

This document is the **v1.0 architecture anchor** for VividWorks customer identity, tenancy, and project ownership **without changing the F1 design-generation contract** (state machine, callbacks, `project_id` / `request_id` formats, or existing F1 tables).

**In scope (architecture):**

- Laravel as the **sole customer identity authority** (not Supabase Auth for end users).
- Sanctum for SPA cookies and personal access tokens.
- Tenants, memberships, and projects linking F1 `project_id` to authorized users.
- Email verification gates before project creation and AI generation.
- Separation of marketing inquiries from tenant data.

**Out of scope for v1.0 architecture text:**

- F1 semantic or schema changes.
- n8n W2 publication or production API exposure.
- Marketing site production cutover.

Detailed schema DDL and ERD were elaborated in the 25 November repo audit; **v1.0 incorporates that design baseline by reference** where it does not contradict the I0 decision record (§4).

---

## 2. Principles

1. **Add identity tables; do not reshape F1.** Link via `projects.public_id` ≡ F1 `project_id` (≤64 chars, globally unique).
2. **No destructive staging cleanup.** Existing staging F1 rows remain; counts are not fixed in documentation (see D-LEGACY-47).
3. **Verification before generation.** Unverified users may authenticate for basic SaaS flows but **cannot** create projects or invoke F1 generation.
4. **Application-layer access control first.** Laravel policies and Sanctum gates are primary; Supabase RLS is assessed separately before any direct client DB access (D-RLS).
5. **Marketing data boundary.** Contact/inquiry flows stay outside tenant identity data (D-INQUIRIES).

---

## 3. Identity and authentication

| Concern | v1.0 choice |
|---------|-------------|
| Authority | Laravel `User` model + Sanctum |
| SPA | Stateful Sanctum cookies + CSRF; **stateful domains defined during I1** (D-SANCTUM) |
| API tokens | Sanctum personal access tokens where needed |
| Email verification | **Mandatory** before project create and `POST /api/ai/design-generations` (D-EMAIL-V) |
| Onboarding | **One personal tenant auto-created on registration** (D-ONBOARD) |
| Generation access | **Sanctum session/token + verified email + project ownership** (D-GEN-AUTH) |

Registration, login, logout, password reset, verify, and resend flows follow the acceptance criteria in `docs/FOUNDATION-IDENTITY-AUDIT-2026-11-25.md` §7.1, updated by I0 decisions above.

---

## 4. Tenancy, projects, and F1 linkage

### 4.1 Project public identifier (D-PID-FMT, D-PID-GLOBAL)

New projects receive:

```text
vw_<tenantSlug8>_<ulid>
```

- **Maximum length:** 64 characters (F1 enforced).
- **Uniqueness:** **Globally unique** `project_id` / `public_id` (aligned with current F1 unique index).

### 4.2 Entity model (incorporated baseline)

The following is the approved structural baseline (from audit §6; unchanged by I0 unless listed in §3–§4.1):

- `users` — Laravel standard + UUID PK recommended.
- `tenants` — `slug` unique; personal tenant on signup.
- `tenant_memberships` — roles at minimum `owner`, `admin`, `member`.
- `projects` — `public_id` (64), `tenant_id`, FK to tenant; **no DB FK from `generations.project_id` in phase 1** (application validation first).
- `subscriptions` — deferred to phase I4; plans align with F1 `package` enum.

See ERD and column notes in `docs/FOUNDATION-IDENTITY-AUDIT-2026-11-25.md` §6.1–6.2.

### 4.3 Staging legacy F1 data (D-LEGACY-47)

- **Preserve** all existing staging F1 records.
- **No backfill** and **no cleanup** as part of I0/I1 entry.
- **Do not** document a fixed generation count; A4.40 observed **49** `generations` (2026-11-27).

---

## 5. Data platform and RLS (D-RLS)

- **Laravel** (via its Postgres connection role) is the expected path for all application reads/writes.
- Staging F1 tables may have **RLS enabled with zero policies** (A4.40). That is **not** a signal to alter RLS during I0; it must be **recorded and reviewed during I1 configuration** (confirm the **application DB role** vs `BYPASSRLS`).
- Do **not** enable PostgREST/anon access to F1 tables without a dedicated policy design.
- Production RLS posture was **not** inventory-checked in A4.39; treat staging findings as staging-specific until production is verified.

---

## 6. API boundaries (pre-I3)

Until I3 implementation:

- F1 routes in repo remain as today; **production exposure** remains health-only per A4.31–A4.34.
- Architecture **requires** future protection of `POST /api/ai/design-generations` per D-GEN-AUTH and D-EMAIL-V.

---

## 7. Implementation phases (reference)

Execution order matches audit §10:

| Phase | Focus |
|-------|--------|
| **I0** | Documentation closure (this package) |
| **I1** | Users, Sanctum, auth routes, `MustVerifyEmail`, no F1 coupling |
| **I2** | Tenants, memberships, projects, policies |
| **I3** | F1 invoke authorization |
| **I4–I6** | Subscriptions, SaaS UI, optional staging backfill |

See [`I1-READINESS-CHECKLIST-2026-11-27.md`](./I1-READINESS-CHECKLIST-2026-11-27.md) before staging migrations.

---

## 8. Related documents

| Document | Role |
|----------|------|
| [`I0-DECISION-RECORD-2026-11-27.md`](./I0-DECISION-RECORD-2026-11-27.md) | Normative owner decisions |
| [`I0-CLOSURE-REPORT-2026-11-27.md`](./I0-CLOSURE-REPORT-2026-11-27.md) | I0 gate status |
| [`I1-READINESS-CHECKLIST-2026-11-27.md`](./I1-READINESS-CHECKLIST-2026-11-27.md) | Pre-migration checklist |
| `docs/FOUNDATION-IDENTITY-AUDIT-2026-11-25.md` | Deep audit & schema detail (§6–§11) |
| `docs/production/A4.31-A4.34-health-activation-handoff-2026-11-27.md` | Production exposure boundary |

---

## 9. Change control

- **v1.0 decisions** require owner approval to change.
- **Schema or F1 contract** changes require a new architecture revision and explicit F1 gate.
- If a longer canonical prose existed outside the repo before I0 closure, it must be **diffed** against this file; the I0 decision record remains authoritative for the nine locked decisions.