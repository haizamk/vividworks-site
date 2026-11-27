#!/usr/bin/env bash
# Marketing production deploy — separate from preview. Default routing uses candidate host only (MKT-03 go-live gate).
set -euo pipefail

APP_ROOT="${VIVIDWORKS_PROD_ROOT:-/home/ubuntu/vividworks-prod}"
CURRENT="${APP_ROOT}/current"
RELEASES="${APP_ROOT}/releases"
KEEP_RELEASES="${VIVIDWORKS_PROD_KEEP_RELEASES:-4}"
CONTAINER_NAME="${VIVIDWORKS_PROD_CONTAINER:-vividworks-prod-site}"
DOCKER_NETWORK="${VIVIDWORKS_PROD_NETWORK:-coolify}"
IMAGE_REPO="${VIVIDWORKS_PROD_IMAGE:-vividworks-prod}"
DATA_VOLUME="${VIVIDWORKS_PROD_DATA_VOLUME:-vividworks-prod-inquiries-data}"
ROUTING_HOST="${VIVIDWORKS_PROD_ROUTING_HOST:-marketing-prod-candidate.51-79-159-241.sslip.io}"
GIT_SHA="${VIVIDWORKS_GIT_SHA:-unknown}"
RELEASE_TAG="${VIVIDWORKS_RELEASE_TAG:-}"
previous_image=""
previous_current=""

log() {
  printf '[vividworks-prod-deploy] %s\n' "$*"
}

refuse_public_hosts() {
  case "${ROUTING_HOST}" in
    vividworks.my | www.vividworks.my)
      if [[ "${VIVIDWORKS_PROD_GO_LIVE:-}" != "approved-by-owner" ]]; then
        log "Refusing Traefik routing for public host '${ROUTING_HOST}' without VIVIDWORKS_PROD_GO_LIVE=approved-by-owner"
        exit 1
      fi
      ;;
  esac
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

capture_previous_current() {
  previous_current=""
  if [[ -L "$CURRENT" ]]; then
    previous_current="$(readlink -f "$CURRENT" 2>/dev/null || readlink "$CURRENT")"
    log "Captured previous CURRENT -> ${previous_current}"
  elif [[ -e "$CURRENT" ]]; then
    log "CURRENT exists but is not a symlink (${CURRENT}); no previous target recorded"
  else
    log "No existing CURRENT symlink"
  fi
}

set_current_release() {
  local release_path="$1"
  ln -sfn "$release_path" "$CURRENT"
  log "CURRENT -> ${release_path}"
}

restore_current_symlink() {
  if [[ -z "$previous_current" ]]; then
    log "No previous CURRENT symlink to restore"
    return 0
  fi
  if [[ ! -e "$previous_current" ]]; then
    log "Previous CURRENT target is missing (${previous_current}); cannot restore symlink"
    return 1
  fi
  ln -sfn "$previous_current" "$CURRENT"
  log "Restored CURRENT -> ${previous_current}"
  return 0
}

run_container_post_remove_failure=1
run_container_volume_failure=2

rollback() {
  local rollback_image="$1"
  if [[ -z "$rollback_image" ]]; then
    log "No previous image to roll back to"
    return 1
  fi
  log "Rolling back to ${rollback_image}"
  local run_rc=0
  run_container "$rollback_image" || run_rc=$?
  if [[ "$run_rc" -eq "$run_container_volume_failure" ]]; then
    log "Rollback aborted before removing container for image swap (data volume unavailable); container was not removed in this rollback attempt"
    return 1
  fi
  if [[ "$run_rc" -ne 0 ]]; then
    log "Rollback container failed to start"
    return 1
  fi
  sleep 3
  if container_health; then
    log "Rollback healthy"
  else
    log "Rollback health check failed"
    return 1
  fi
  if ! restore_current_symlink; then
    log "Rollback container healthy but CURRENT symlink restore failed"
    return 1
  fi
  return 0
}

ensure_data_volume() {
  if ! sudo docker volume create "$DATA_VOLUME" >/dev/null 2>&1; then
    log "docker volume create failed for ${DATA_VOLUME}"
    return 1
  fi
  if ! sudo docker volume inspect "$DATA_VOLUME" >/dev/null 2>&1; then
    log "docker volume inspect failed for ${DATA_VOLUME}"
    return 1
  fi
  return 0
}

run_container() {
  local image="$1"
  refuse_public_hosts
  if ! ensure_data_volume; then
    log "Aborting before removing existing container (data volume unavailable)"
    return "$run_container_volume_failure"
  fi
  sudo docker rm -f "$CONTAINER_NAME" >/dev/null 2>&1 || true
  if ! sudo docker run -d \
    --name "$CONTAINER_NAME" \
    --restart unless-stopped \
    --network "$DOCKER_NETWORK" \
    -e NODE_ENV=production \
    -e PORT=8080 \
    -v "${DATA_VOLUME}:/app/data" \
    --label traefik.enable=true \
    --label traefik.http.middlewares.vividworks-prod-gzip.compress=true \
    --label traefik.http.middlewares.vividworks-prod-redirect-to-https.redirectscheme.scheme=https \
    --label traefik.http.routers.vividworks-prod-http.entrypoints=http \
    --label traefik.http.routers.vividworks-prod-http.middlewares=vividworks-prod-redirect-to-https \
    --label "traefik.http.routers.vividworks-prod-http.rule=Host(\`${ROUTING_HOST}\`)" \
    --label traefik.http.routers.vividworks-prod-http.service=vividworks-prod-svc \
    --label traefik.http.routers.vividworks-prod-http.priority=95 \
    --label traefik.http.routers.vividworks-prod-https.entrypoints=https \
    --label traefik.http.routers.vividworks-prod-https.middlewares=vividworks-prod-gzip \
    --label "traefik.http.routers.vividworks-prod-https.rule=Host(\`${ROUTING_HOST}\`)" \
    --label traefik.http.routers.vividworks-prod-https.service=vividworks-prod-svc \
    --label traefik.http.routers.vividworks-prod-https.tls=true \
    --label traefik.http.routers.vividworks-prod-https.tls.certresolver=letsencrypt \
    --label traefik.http.routers.vividworks-prod-https.priority=95 \
    --label traefik.http.services.vividworks-prod-svc.loadbalancer.server.port=8080 \
    --label coolify.managed=false \
    --label vividworks.release.tag="${RELEASE_TAG}" \
    --label vividworks.git.sha="${GIT_SHA}" \
    "$image" >/dev/null; then
    log "docker run failed for image ${image}"
    return "$run_container_post_remove_failure"
  fi
  return 0
}

deploy_start_container_or_abort() {
  local image_tag="$1"
  log "Starting container ${CONTAINER_NAME} (routing host ${ROUTING_HOST}); rollback_source=${previous_image:-none}"
  local run_rc=0
  run_container "$image_tag" || run_rc=$?
  if [[ "$run_rc" -eq "$run_container_volume_failure" ]]; then
    log "Deploy aborted before replacing container; rollback not attempted"
    return "$run_container_volume_failure"
  fi
  if [[ "$run_rc" -ne 0 ]]; then
    attempt_rollback "docker run failure" || return 1
    return 1
  fi
  return 0
}

attempt_rollback() {
  local reason="$1"
  if [[ -z "$previous_image" ]]; then
    log "No previous image available for rollback (${reason})"
    return 1
  fi
  log "Attempting rollback after ${reason} (previous_image=${previous_image})"
  if rollback "$previous_image"; then
    log "Rollback succeeded after ${reason}"
    return 0
  fi
  if [[ -z "$previous_current" ]]; then
    log "CURRENT symlink unchanged because deployment never marked successful (${reason})"
  fi
  log "Rollback failed after ${reason}"
  return 1
}

main() {
  mkdir -p "$RELEASES"
  ensure_docker
  capture_previous_current

  stamp="$(date -u +%Y%m%dT%H%M%SZ)"
  target="${RELEASES}/${stamp}"
  image_tag="${IMAGE_REPO}:${stamp}"
  mkdir -p "$target"

  previous_image=""
  if sudo docker inspect "$CONTAINER_NAME" >/dev/null 2>&1; then
    previous_image="$(sudo docker inspect "$CONTAINER_NAME" --format '{{.Config.Image}}')"
  fi
  export previous_image

  log "Receiving source into ${target}"
  tar -xzf - -C "$target"

  log "Building image ${image_tag} (git ${GIT_SHA})"
  sudo docker build \
    --build-arg "VIVIDWORKS_GIT_SHA=${GIT_SHA}" \
    --build-arg "VIVIDWORKS_RELEASE_TAG=${RELEASE_TAG}" \
    -t "$image_tag" \
    -t "${IMAGE_REPO}:latest" \
    "$target"

  local start_rc=0
  deploy_start_container_or_abort "$image_tag" || start_rc=$?
  if [[ "$start_rc" -ne 0 ]]; then
    exit 1
  fi
  sleep 5

  if container_health; then
    set_current_release "$target"
    log "Deploy healthy; previous_image=${previous_image:-none}"
  else
    log "Health check failed after deploy"
    attempt_rollback "health check failure" || exit 1
    exit 1
  fi

  mapfile -t old_releases < <(ls -1dt "$RELEASES"/* 2>/dev/null | tail -n +"$((KEEP_RELEASES + 1))" || true)
  if ((${#old_releases[@]} > 0)); then
    rm -rf "${old_releases[@]}"
  fi

  mkdir -p /home/ubuntu/bin
  install -m 755 "${target}/deploy/production/vividworks-prod-deploy.sh" /home/ubuntu/bin/vividworks-prod-deploy

  log "Done release=${stamp} image=${image_tag}"
}

if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
  main
fi