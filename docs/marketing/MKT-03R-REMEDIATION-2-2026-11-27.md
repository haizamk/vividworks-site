# MKT-03R — Guru Review Remediation 2

**Tarikh:** 27 November 2026 (Asia/Kuala_Lumpur)  
**Status:** **NOT APPROVED FOR COMMIT/PUSH** — menunggu Guru  
**Tiada:** VPS redeploy, DNS, go-live `vividworks.my`, commit, push

---

## 1. Pembetulan dilaksanakan

### 1.1 CURRENT symlink rollback (`vividworks-prod-deploy.sh`)

| Keperluan | Pelaksanaan |
|-----------|-------------|
| Tangkap target `CURRENT` sebelum deploy | `capture_previous_current` pada permulaan `main` |
| Pulihkan `CURRENT` selepas rollback berjaya | `restore_current_symlink` dalam `rollback()` |
| Tiada symlink terdahulu | Log `No previous CURRENT symlink to restore` |
| Deploy berjaya | `set_current_release "$target"` **selepas** health pass sahaja (bukan sebelum `docker run`) |
| Gagal sebelum health | `CURRENT` tidak maju ke release baharu |

### 1.2 Docker volume fail-closed

| Keperluan | Pelaksanaan |
|-----------|-------------|
| Tiada `\|\| true` pada volume create | `ensure_data_volume()` — gagal → return 1 |
| Volume sebelum `docker rm` | `ensure_data_volume` dipanggil di awal `run_container` |
| Nama volume & mount `/app/data` | **Tidak diubah** (`vividworks-prod-inquiries-data`) |

### 1.3 Test harness (`test-mkt03r.sh`)

| Keperluan | Pelaksanaan |
|-----------|-------------|
| Jangan ganggu repo sebenar | Tag ujian dalam **temp git repo** (`mktemp -d`) |
| Tiada andaian `git checkout main` | `git checkout` balik ke `orig_head` dalam temp repo |
| Bersihkan tag | `git tag -d` + pengesahan `show-ref` |
| Assertion mount volume | Mock `docker run` semak `-v ${DATA_VOLUME}:/app/data` |
| Ujian `CURRENT` | Deploy berjaya, rollback berjaya, tiada previous symlink |
| Volume fail-closed | `MOCK_VOLUME_FAIL=1` |

**Architecture / nama resource / routing candidate:** tidak diubah.

---

## 2. Validation

### Bash syntax

```text
bash -n deploy/production/vividworks-prod-deploy.sh   → OK
bash -n deploy/production/test-mkt03r.sh             → OK
bash -n deploy/production/validate-release-ref.sh      → OK
```

### Test harness (`bash deploy/production/test-mkt03r.sh`)

| # | Ujian | Keputusan |
|---|--------|-----------|
| 1 | Tolak `main` | **PASS** |
| 2 | Tolak SHA penuh | **PASS** |
| 3 | Tolak SHA pendek | **PASS** | 
| 4 | Terima format tag | **PASS** |
| 5 | Checkout SHA = tag (temp repo) | **PASS** |
| — | Temp tag dibersihkan | **PASS** |
| 6 | `CURRENT` → release baharu selepas deploy berjaya | **PASS** |
| 7 | `CURRENT` dipulihkan selepas rollback health | **PASS** |
| 8 | Tiada previous `CURRENT` | **PASS** |
| 9 | `docker run` gagal → rollback + restore `CURRENT` | **PASS** |
| 10 | Tiada previous image | **PASS** |
| 11 | Named volume mount pada `docker run` | **PASS** |
| 12 | `ensure_data_volume` berjaya | **PASS** |
| 13 | Volume create gagal → fail-closed | **PASS** |
| 14 | Candidate + Preview health | **PASS** (200) |

**Ringkasan:** `pass=15 fail=0`

---

## 3. Workflow (semakan Guru — tidak diubah selain MKT-03R asal)

Fail penuh untuk semakan:

```yaml
name: Deploy marketing production (tagged)

on:
  workflow_dispatch:
    inputs:
      ref:
        description: Marketing production release tag (marketing-prod/vYYYY.MM.DD.N only)
        required: true
        type: string
  push:
    tags:
      - "marketing-prod/v*"

concurrency:
  group: vividworks-prod
  cancel-in-progress: false

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Resolve release ref
        id: release
        run: |
          set -euo pipefail
          if [[ "${{ github.event_name }}" == "push" ]]; then
            REF="${GITHUB_REF_NAME}"
          else
            REF="${{ inputs.ref }}"
          fi
          if [[ ! "$REF" =~ ^marketing-prod/v[0-9]{4}\.[0-9]{2}\.[0-9]{2}\.[0-9]+$ ]]; then
            echo "::error::Rejected ref '$REF': must match marketing-prod/vYYYY.MM.DD.N"
            exit 1
          fi
          if [[ "$REF" == "main" ]] || [[ "$REF" =~ ^[0-9a-f]{7,40}$ ]]; then
            echo "::error::Rejected ref '$REF': branches and raw SHAs are not allowed"
            exit 1
          fi
          echo "ref=$REF" >> "$GITHUB_OUTPUT"
          echo "Validated release ref: $REF"

      - uses: actions/checkout@v4
        with:
          ref: refs/tags/${{ steps.release.outputs.ref }}
          fetch-depth: 1

      - name: Record source SHA from checkout
        id: source
        run: |
          set -euo pipefail
          SHA="$(git rev-parse HEAD)"
          if ! git describe --exact-match --tags HEAD >/dev/null 2>&1; then
            echo "::error::Checkout HEAD is not an exact release tag match"
            exit 1
          fi
          TAG="$(git describe --exact-match --tags HEAD)"
          if [[ "$TAG" != "${{ steps.release.outputs.ref }}" ]]; then
            echo "::error::Checked out tag '$TAG' does not match requested '${{ steps.release.outputs.ref }}'"
            exit 1
          fi
          echo "sha=$SHA" >> "$GITHUB_OUTPUT"
          echo "Source SHA for deployment: $SHA"

      - name: Deploy to production candidate host
        env:
          SSH_KEY: ${{ secrets.VIVIDPREVIEW_SSH_KEY }}
          HOST: 51.79.159.241
          GIT_SHA: ${{ steps.source.outputs.sha }}
          RELEASE_TAG: ${{ steps.release.outputs.ref }}
          ROUTING_HOST: marketing-prod-candidate.51-79-159-241.sslip.io
        run: |
          set -euo pipefail
          test -n "$SSH_KEY"
          mkdir -p ~/.ssh
          export SSH_KEY
          bash .github/scripts/write-ssh-key.sh ~/.ssh/id_ed25519
          cp .github/preview_known_hosts ~/.ssh/known_hosts
          ssh -i ~/.ssh/id_ed25519 \
            -o IdentitiesOnly=yes \
            -o UserKnownHostsFile="$HOME/.ssh/known_hosts" \
            "ubuntu@${HOST}" 'mkdir -p ~/bin'
          scp -i ~/.ssh/id_ed25519 \
            -o IdentitiesOnly=yes \
            -o UserKnownHostsFile="$HOME/.ssh/known_hosts" \
            deploy/production/vividworks-prod-deploy.sh \
            "ubuntu@${HOST}:~/bin/vividworks-prod-deploy"
          ssh -i ~/.ssh/id_ed25519 \
            -o IdentitiesOnly=yes \
            -o UserKnownHostsFile="$HOME/.ssh/known_hosts" \
            "ubuntu@${HOST}" 'chmod +x ~/bin/vividworks-prod-deploy'
          tar -czf - \
            --exclude node_modules \
            --exclude dist \
            --exclude .env \
            --exclude .git \
            . | ssh -T \
            -i ~/.ssh/id_ed25519 \
            -o IdentitiesOnly=yes \
            -o UserKnownHostsFile="$HOME/.ssh/known_hosts" \
            "ubuntu@${HOST}" \
            "VIVIDWORKS_GIT_SHA=${GIT_SHA} VIVIDWORKS_RELEASE_TAG=${RELEASE_TAG} VIVIDWORKS_PROD_ROUTING_HOST=${ROUTING_HOST} ~/bin/vividworks-prod-deploy"
```

---

## 4. Fail disentuh (workspace)

- `deploy/production/vividworks-prod-deploy.sh`
- `deploy/production/test-mkt03r.sh`

---

## 5. Penutup

Semua pemeriksaan Remediation 2 selesai. **Tunggu kelulusan Guru** sebelum commit/push atau deploy semula VPS.