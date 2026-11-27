# Cara semak lokasi host `vividworks.my` (operator)

**Kemas kini:** 2026-11-27  
**Sumber rujukan:** rekod teknikal infrastruktur VividWorks + pemerhatian DNS/health.

---

## 1. Peta ringkas (bukan pengganti audit)

| Host / peranan | Di mana tetapkan / jalan | IP / platform (calon) | Nota |
|----------------|--------------------------|----------------------|------|
| **DNS zon** | **Cloudflare** | NS: `*.ns.cloudflare.com` | Authoritative untuk semua subdomain |
| **`vividworks.my` / `www`** | Marketing (static / Node) | A → Cloudflare proxy (104.x / 172.x) | Biasanya **orange cloud** |
| **`preview.vividworks.my`** | Coolify (sama VPS staging) | Cloudflare → origin | Lihat `deploy/preview/` |
| **`api-staging.vividworks.my`** | **Coolify** Docker Laravel | Origin **Monalisa VPS** (lihat Cloudflare `api-staging` / rekod teknikal) | Health 200, I1 staging |
| **`api.vividworks.my`** | **Production Laravel API** | Origin **VM100** `vividworks-vm100` (lihat Cloudflare `api` A/CNAME) | Health 200; nginx + app Laravel di host prod |
| **Mail staging** | Resend (bukan server) | — | From `mail.staging.vividworks.my` |
| **Mail prod** | Resend + Cloudflare DNS | — | On hold (A4.61) |

Jika Cloudflare **proxy on**, `dig A` hanyalah IP Cloudflare — **bukan** IP server sebenar.

---

## 2. Semakan pantas (terminal)

```bash
# Delegation
dig NS vividworks.my +short

# Apa yang resolver awam nampak (mungkin Cloudflare)
dig A api.vividworks.my +short
dig A api-staging.vividworks.my +short

# Adakah trafik melalui Cloudflare?
curl -sI https://api.vividworks.my/api/health | grep -iE 'server:|cf-ray'

# Health tanpa bocor rahsia
curl -sS https://api.vividworks.my/api/health
curl -sS https://api-staging.vividworks.my/api/health
```

**Tafsiran:**

- `cf-ray` header → permintaan melalui **Cloudflare edge**.
- IP asal sebenar → **Cloudflare DNS** → rekod A/CNAME **content** (boleh grey cloud untuk nampak IP terus).

---

## 3. Semakan wajib di Cloudflare (paling tepat)

1. Log masuk **Cloudflare** → zon **`vividworks.my`**.
2. **DNS** → per rekod:

| Rekod | Yang perlu semak |
|-------|------------------|
| `api` | Content (A atau CNAME), **Proxy status** (orange vs grey) |
| `api-staging` | Sama |
| `@` / `www` | Target hosting marketing |
| `mail` / `mail.staging` / `send.mail.*` | **DNS only** (Resend) — jangan proxy |

3. **SSL/TLS** → mode (Full strict vs lain) untuk origin betul.

---

## 4. Semakan di server (SSH — jika ada akses)

| Peranan | Host SSH / akses | Apa disahkan |
|---------|------------------|--------------|
| Staging API | Coolify VPS (`VIVIDPREVIEW_SSH_*` / rekod teknikal) | `docker ps`, app `vividworks-laravel-staging-api` |
| Production API | VM100 (`CURSOR_VM100_SSH_*` / rekod teknikal) | nginx site `api.vividworks.my`, path app Laravel |
| Proxmox / VM | Panel Proxmox (luar skop fail ini) | VM100 guest |

**Jangan** campur Coolify staging dengan key/env production.

---

## 5. Semakan repo (kod vs runtime)

| Perkara | Repo / build |
|---------|----------------|
| Staging Laravel image | **`haizamk/stitch-vividworks`** branch `vividworks/development-stitch` |
| Dokumen + sebahagian Laravel | **`khairul-haizam/vividworks-site`** |
| Marketing / preview deploy | `vividworks-site` + skrip `deploy/preview/` |

Coolify **source of truth** untuk staging API ialah **stitch**, bukan `main` vividworks-site sahaja.

---

## 6. Apa yang perlu Khairul putuskan untuk production mail (hold)

Bila sambung A4.61: pastikan **`RESEND_API_KEY` prod** dan `MAIL_*` hanya pada **host production API (VM100)**, bukan Coolify staging.

---

**Disediakan untuk:** rujukan operasi dalaman VividWorks.