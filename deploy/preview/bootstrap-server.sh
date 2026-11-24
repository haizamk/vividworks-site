#!/usr/bin/env bash
# One-time host setup for preview.vividworks.my behind Coolify Traefik.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
DEPLOY_BIN="/home/ubuntu/bin/vividpreview-deploy"

mkdir -p /home/ubuntu/bin /home/ubuntu/vividpreview/releases
install -m 755 "${REPO_ROOT}/deploy/preview/vividpreview-deploy.sh" "$DEPLOY_BIN"

echo "Bootstrap complete. Run a deploy (tarball + vividpreview-deploy.sh) to start vividpreview-site."