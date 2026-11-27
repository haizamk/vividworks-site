# Marketing production deploy (Dedicated Docker)

- Container: `vividworks-prod-site`
- Default Traefik host: `marketing-prod-candidate.51-79-159-241.sslip.io` (no public `vividworks.my` until owner go-live)
- Volume: `vividworks-prod-inquiries-data` → `/app/data`
- Releases: `/home/ubuntu/vividworks-prod/releases/`
- Script: `/home/ubuntu/bin/vividworks-prod-deploy`

Go-live requires `VIVIDWORKS_PROD_GO_LIVE=approved-by-owner` and explicit `VIVIDWORKS_PROD_ROUTING_HOST=vividworks.my` (plus separate www redirect work — not enabled in MKT-03).

Tagged deploy: `.github/workflows/production-deploy.yml` on `marketing-prod/vYYYY.MM.DD.N`.