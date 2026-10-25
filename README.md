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

## Frontpage redesign (Fasa C1)

The homepage on branch `cursor/vividworks-frontpage-redesign-3339` uses the indigo design tokens in `src/data/brand.ts` (`fpIndigo` and the related `fp*` colours). Privacy, terms, contact, pricing, templates, FAQ and how-it-works keep the previous header, footer and palette.

This branch is local only. Do not deploy it to preview.vividworks.my until that cutover is approved. `npm run dev` stays on this machine: the site on port 4321 and a local API on port 8787. Leave `DATABASE_URL` unset so contact inquiries stay in `data/inquiries.json`.

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

## Deployment (GitHub + Coolify)

Hosting uses **GitHub** as the deployment source and a **Coolify VPS** for the live preview. **Northflank is not used** and is no longer part of this project.

### GitHub

Canonical remote: [github.com/haizamk/vividworks-site](https://github.com/haizamk/vividworks-site). Pushes from Cursor’s project remote can be mirrored with:

```bash
bash .github/scripts/push-to-github.sh
```

That script needs `GITHUB_TOKEN` (classic PAT with `repo` and `workflow` scopes). It pushes `main`, and the current branch when it is not `main`.

### Preview server

https://preview.vividworks.my is the public marketing preview on the Coolify host. There is no preview password.

On every push to `main`, `.github/workflows/preview-deploy.yml` SSHs to `ubuntu@51.79.159.241`, uploads `deploy/preview/vividpreview-deploy.sh`, streams a source tarball, builds the repo `Dockerfile` on the host, and runs container `vividpreview-site` on the `coolify` Docker network with Traefik labels for `preview.vividworks.my`. Releases live under `/home/ubuntu/vividpreview`; a failed container health check rolls back to the previous image. The server host key is pinned in `.github/preview_known_hosts`.

The GitHub repository secret `VIVIDPREVIEW_SSH_KEY` must be the full OpenSSH private key (PEM with real newlines). One-line copies still work in CI because `.github/scripts/write-ssh-key.sh` re-wraps them before `ssh` runs. The matching public key (`vividworks-coolify`) lives in `/home/ubuntu/.ssh/authorized_keys` on the server.

First-time host setup: `deploy/preview/bootstrap-server.sh` on the VPS.

Preview inquiries are stored in the container filesystem (`storage: file` in `/api/health`) unless you add `DATABASE_URL` to the container environment later. Do not commit `ADMIN_TOKEN`, `DATABASE_URL`, or `data/inquiries.json`.

`GET /api/health` is the deploy health check. When `ADMIN_TOKEN` is set on the server, list inquiries with:

```bash
curl -s https://preview.vividworks.my/api/health
curl -H "Authorization: Bearer $ADMIN_TOKEN" https://preview.vividworks.my/api/inquiries
```

## Future actions

Log Masuk, Bina Website Percuma, Mula Percuma, and template selection open `/akan-datang` until an absolute `http` or `https` URL is set:

- `VITE_SAAS_LOGIN_URL`
- `VITE_SAAS_REGISTER_URL`
- `VITE_SAAS_EDITOR_URL`

## Scope

Included: the marketing pages, the contact API, and Postgres for those inquiries.

Not included: Laravel, GrapesJS, n8n, OpenRouter, authentication, dashboards, billing, or the website-builder product itself. A saved inquiry is not an email and not a customer account.
