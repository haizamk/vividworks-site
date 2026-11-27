# I1 scaffold entry (Gate 5 — staging code)

**Date (UTC):** 2026-11-27  
**Branch target:** `cursor/i1-scaffold-6b21`

---

## Delivered (engineering **B**)

| Item | Status |
|------|--------|
| `laravel/sanctum` ^4 | Composer lock **v4.5.0** |
| Migrations | `users` (uuid PK), `password_reset_tokens`, `personal_access_tokens` |
| API routes | `POST register`, `POST login`, `GET email/verify`, `POST verification-notification`, `POST logout` |
| `User` | `MustVerifyEmail`, `HasApiTokens`, `HasUuids` |
| Middleware alias | `verified` (for future I2/I3 routes) |
| Tests | `tests/Feature/Auth/I1IdentityTest.php` — **pass** (sqlite) |
| F1 routes | **Unchanged** — design-generations not auth-gated (I3 later) |

---

## Staging migrate

See [A4.63](./A4.63-STAGING-I1-MIGRATE-2026-11-27.md) — applied on Coolify after grants to `laravel_staging`.

## Not done in this scaffold

- Password reset controllers (schema only)
- I2 tenants / projects / subscriptions
- F1 + `verified` middleware (I3)

---

## Staging deploy order

1. Merge I1 branch → sync **`haizamk/stitch-vividworks`** `vividworks/development-stitch`
2. Coolify redeploy staging API
3. `php artisan migrate --force` (backup per **A4.62** before migrate)
4. Verification mail test with real Resend (post-migrate)

---

**Prepared by:** Cloud Agent