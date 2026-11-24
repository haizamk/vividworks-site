#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="${VIVIDPREVIEW_ROOT:-/home/ubuntu/vividpreview}"
CURRENT="${APP_ROOT}/current"
RELEASES="${APP_ROOT}/releases"
KEEP_RELEASES="${VIVIDPREVIEW_KEEP_RELEASES:-4}"
CONTAINER_NAME="${VIVIDPREVIEW_CONTAINER:-vividpreview-site}"
DOCKER_NETWORK="${VIVIDPREVIEW_NETWORK:-coolify}"
IMAGE_REPO="${VIVIDPREVIEW_IMAGE:-vividpreview}"

log() {
  printf '[vividpreview-deploy] %s\n' "$*"
}

ensure_docker() {
  if ! command -v docker >/dev/null 2>&1; then
    log "docker not found"
    exit 1
  fi
  if ! sudo docker network inspect "$DOCKER_NETWORK" >/dev/null 2>&1; then
    log "docker network ${DOCKER_NETWORK} missing"
    exit 1
  fi
}

container_health() {
  sudo docker exec "$CONTAINER_NAME" node -e \
    "fetch('http://127.0.0.1:'+(process.env.PORT||8080)+'/api/health').then(r=>r.json()).then(j=>process.exit(j.ok?0:1)).catch(()=>process.exit(1))"
}

rollback() {
  local previous_image="$1"
  if [[ -z "$previous_image" ]]; then
    log "No previous image to roll back to"
    return 1
  fi
  log "Rolling back to ${previous_image}"
  run_container "$previous_image"
  sleep 3
  if container_health; then
    log "Rollback healthy"
    return 0
  fi
  log "Rollback health check failed"
  return 1
}

run_container() {
  local image="$1"
  sudo docker rm -f "$CONTAINER_NAME" >/dev/null 2>&1 || true
  sudo docker run -d \
    --name "$CONTAINER_NAME" \
    --restart unless-stopped \
    --network "$DOCKER_NETWORK" \
    -e NODE_ENV=production \
    -e PORT=8080 \
    --label traefik.enable=true \
    --label traefik.http.middlewares.vividpreview-gzip.compress=true \
    --label traefik.http.middlewares.vividpreview-redirect-to-https.redirectscheme.scheme=https \
    --label traefik.http.routers.vividpreview-http.entrypoints=http \
    --label traefik.http.routers.vividpreview-http.middlewares=vividpreview-redirect-to-https \
    --label 'traefik.http.routers.vividpreview-http.rule=Host(`preview.vividworks.my`)' \
    --label traefik.http.routers.vividpreview-http.service=vividpreview-svc \
    --label traefik.http.routers.vividpreview-http.priority=100 \
    --label traefik.http.routers.vividpreview-https.entrypoints=https \
    --label traefik.http.routers.vividpreview-https.middlewares=vividpreview-gzip \
    --label 'traefik.http.routers.vividpreview-https.rule=Host(`preview.vividworks.my`)' \
    --label traefik.http.routers.vividpreview-https.service=vividpreview-svc \
    --label traefik.http.routers.vividpreview-https.tls=true \
    --label traefik.http.routers.vividpreview-https.tls.certresolver=letsencrypt \
    --label traefik.http.routers.vividpreview-https.priority=100 \
    --label traefik.http.services.vividpreview-svc.loadbalancer.server.port=8080 \
    --label coolify.managed=false \
    "$image" >/dev/null
}

mkdir -p "$RELEASES"
ensure_docker

stamp="$(date -u +%Y%m%dT%H%M%SZ)"
target="${RELEASES}/${stamp}"
image_tag="${IMAGE_REPO}:${stamp}"
mkdir -p "$target"

previous_image=""
if sudo docker inspect "$CONTAINER_NAME" >/dev/null 2>&1; then
  previous_image="$(sudo docker inspect "$CONTAINER_NAME" --format '{{.Config.Image}}')"
fi

log "Receiving source into ${target}"
tar -xzf - -C "$target"

log "Building image ${image_tag}"
sudo docker build -t "$image_tag" "$target"

ln -sfn "$target" "$CURRENT"

log "Starting container ${CONTAINER_NAME}"
run_container "$image_tag"
sleep 4

if container_health; then
  log "Deploy healthy"
else
  log "Health check failed after deploy"
  rollback "$previous_image" || true
  exit 1
fi

mapfile -t old_releases < <(ls -1dt "$RELEASES"/* 2>/dev/null | tail -n +"$((KEEP_RELEASES + 1))" || true)
if ((${#old_releases[@]} > 0)); then
  rm -rf "${old_releases[@]}"
fi

mkdir -p /home/ubuntu/bin
install -m 755 "${target}/deploy/preview/vividpreview-deploy.sh" /home/ubuntu/bin/vividpreview-deploy

# Legacy systemd route (pre-Coolify); safe to disable when Docker routing works.
if systemctl is-enabled vividpreview >/dev/null 2>&1; then
  sudo systemctl disable --now vividpreview >/dev/null 2>&1 || true
fi

# Remove stale file-provider route if it exists (Docker labels own routing now).
sudo rm -f /data/coolify/proxy/dynamic/vividpreview-preview.yaml

log "Done"