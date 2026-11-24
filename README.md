# vividworks.my

Public marketing website for vividworks.my, a Malaysian AI-powered website builder. The interface is written in Bahasa Melayu (Malaysia).

The site is a small full-stack app: a React frontend and a Node API that stores contact inquiries. With `DATABASE_URL` set, inquiries go to PostgreSQL. Without it, they are written to `data/inquiries.json` on the server. The API does not send email.

This is still the marketing site. It does not include customer accounts, the visual editor, AI generation, or billing.

## Requirements

- Node.js 22
- npm

## Local development

```bash
npm install
npm run dev
```

The website runs at [http://127.0.0.1:4321](http://127.0.0.1:4321). Vite proxies `/api` to the API on port 8787.

```bash
npm test
npm run lint
npm run typecheck
npm run build
npm start
```

`npm start` serves the production build and the API on `PORT` (default 8787). `npm run build` checks brand colours and regenerates the template preview SVGs.

## Pages

| Path | Purpose |
| --- | --- |
| `/` | Homepage |
| `/pricing` | Package comparison |
| `/templates` | Template preview gallery |
| `/how-it-works` | Planned platform workflow |
| `/faq` | Frequently asked questions |
| `/contact` | Contact details and the inquiry form |
| `/privacy` | Draft privacy policy |
| `/terms` | Draft terms of service |
| `/akan-datang` | Placeholder for future SaaS actions |

## API

| Method | Path | Behaviour |
| --- | --- | --- |
| `GET` | `/api/health` | Reports storage mode. Returns 503 if Postgres cannot be reached. |
| `POST` | `/api/contact` | Validates and stores one inquiry. Does not send email. |
| `GET` | `/api/inquiries` | Last 100 inquiries. Requires `Authorization: Bearer <ADMIN_TOKEN>`. Hidden when the token is unset. |

## Laravel API (Fasa B1)

`laravel/` is an isolated Laravel 13 app with the same three API routes. It does not replace the Express server, and it is not wired to the preview site. The React app still talks to Express on port 8787.

PostgreSQL settings live in `laravel/.env` (see `laravel/.env.example`). Do not commit that file, and do not run `php artisan migrate` against the preview database. The inquiries migration does nothing when `inquiries` already exists, and its `down()` method does not drop the table.

PHPUnit uses SQLite in memory, so it does not need `pdo_pgsql` or a PostgreSQL server:

```bash
cd laravel
composer install
./vendor/bin/phpunit
```

A local smoke server, if needed, must stay on loopback:

```bash
cd laravel
php artisan serve --host=127.0.0.1 --port=8790
```

## Configuration

Edit these files rather than hard-coding values in components:

- `src/data/brand.ts` — brand colours (keep `src/styles/index.css` in sync; the build checks this)
- `src/data/site.ts` — name, navigation, contact fields, and future SaaS URLs
- `src/data/pricing.ts` — package names, prices, and features
- `src/data/templates.ts` — template gallery
- `src/data/faqs.ts` — FAQ copy
- `src/data/content.ts` — homepage benefits, workflow, and legal draft sections

Optional environment overrides are listed in `.env.example`. Copy it to `.env` only when you have real values. Empty contact fields are intentional: the site must not invent an email address, phone number, or office address.

Starter, Professional, and Business are proposed labels. Leave their price and features as “Akan disahkan” until an approved specification replaces them. Do not describe FREE as free for life unless that is explicitly confirmed.

## Preview server

https://preview.vividworks.my is the isolated preview on the existing VPS. It is public, with no preview password.

`.github/workflows/preview-deploy.yml` deploys `main` over SSH to the Coolify VPS (`ubuntu@51.79.159.241`). It uploads `deploy/preview/vividpreview-deploy.sh`, streams a source tarball, builds the repo `Dockerfile` on the host, and runs container `vividpreview-site` on the `coolify` Docker network with Traefik labels for `preview.vividworks.my`. Releases live under `/home/ubuntu/vividpreview`; a failed container health check rolls back to the previous image. Pin the server host key in `.github/preview_known_hosts`.

The repository secret `VIVIDPREVIEW_SSH_KEY` must be the full OpenSSH private key (PEM with real newlines). One-line copies still work in CI because `.github/scripts/write-ssh-key.sh` re-wraps them before `ssh` runs. The matching public key (`vividworks-coolify`) lives in `/home/ubuntu/.ssh/authorized_keys` on the server.

## Northflank

`northflank.json` is a template for one project in Asia Southeast (Singapore), one PostgreSQL 16 addon, and one combined service. The service builds `Dockerfile` from Git and serves the site on port 8080. The template aliases the addon `POSTGRES_URI` to `DATABASE_URL`, then restarts the service after the database and the first deployment are running.

Northflank shows each template node, build, and deployment as it runs. This repository cannot sign in to Northflank for you. The public plan catalog has no free tier: the site uses `nf-compute-10`, the database uses `nf-compute-20`, and image builds use `nf-compute-400-16` while a build is running.

1. Create a Northflank account and a team API token. In the environment used by Cursor, set `NORTHFLANK_API_TOKEN` to that token. `.cursor/mcp.json` points at `https://mcp.northflank.com/mcp` and reads the token from the environment. Do not commit the token.
2. Put this project on GitHub, GitLab, or Bitbucket. Use the **Create repo** control in Cursor if this project does not have one yet. Northflank builds from that repository, not from the temporary project remote.
3. In Northflank, create a template and paste `northflank.json`, or enable GitOps on that file. Before you run it, set these template arguments. Store `ADMIN_TOKEN` as an argument override so the generated value stays in Northflank and out of Git:

   - `REPOSITORY_URL`: the HTTPS URL of the Git repository
   - `VCS_PROVIDER`: `github`, `gitlab`, or `bitbucket`
   - `ADMIN_TOKEN`: `${fn.randomSecret(32)}`

4. Run the template and follow the node progress in the Northflank run view. The public site URL appears on the `vividworks-web` port after the service is running.

`GET /api/health` is the startup and readiness check. Read stored inquiries with the generated token:

```bash
curl -H "Authorization: Bearer $ADMIN_TOKEN" https://<your-service>/api/inquiries
```

If the service logs a TLS error while connecting to Postgres, set `DATABASE_SSL=false` on the secret group and restart the service. Do not commit `ADMIN_TOKEN`, `DATABASE_URL`, `NORTHFLANK_API_TOKEN`, or `data/inquiries.json`.

## Future actions

Log Masuk, Bina Website Percuma, Mula Percuma, and template selection open `/akan-datang` until an absolute `http` or `https` URL is set:

- `VITE_SAAS_LOGIN_URL`
- `VITE_SAAS_REGISTER_URL`
- `VITE_SAAS_EDITOR_URL`

## Scope

Included: the marketing pages, the contact API, and Postgres for those inquiries.

Not included: Laravel, GrapesJS, n8n, OpenRouter, authentication, dashboards, billing, or the website-builder product itself. A saved inquiry is not an email and not a customer account.
