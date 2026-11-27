#!/usr/bin/env bash
# One-time host paths for marketing production (candidate routing by default).
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
DEPLOY_BIN="/home/ubuntu/bin/vividworks-prod-deploy"

mkdir -p /home/ubuntu/bin /home/ubuntu/vividworks-prod/releases
install -m 755 "${REPO_ROOT}/deploy/production/vividworks-prod-deploy.sh" "$DEPLOY_BIN"
echo "Bootstrap complete. Deploy with tarball piped to vividworks-prod-deploy."