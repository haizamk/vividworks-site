#!/usr/bin/env bash
# MKT-03R remediation 2 tests — no VPS deploy, no real repo mutation.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
# shellcheck source=deploy/production/validate-release-ref.sh
source "$ROOT/deploy/production/validate-release-ref.sh"

pass=0
fail=0

pass_test() {
  echo "PASS $1"
  pass=$((pass + 1))
}

fail_test() {
  echo "FAIL $1"
  fail=$((fail + 1))
}

assert_reject() {
  local name="$1"
  local ref="$2"
  if validate_marketing_prod_ref "$ref" >/dev/null 2>&1; then
    fail_test "$name: expected reject for '$ref'"
  else
    pass_test "$name"
  fi
}

assert_accept_format() {
  local name="$1"
  local ref="$2"
  if validate_marketing_prod_ref "$ref" >/dev/null 2>&1; then
    pass_test "$name"
  else
    fail_test "$name: expected accept format for '$ref'"
  fi
}

echo "=== workflow ref validation ==="
assert_reject "1 workflow rejects main" "main"
assert_reject "2 workflow rejects raw SHA" "8cb65814127ff437c27d428d728322a19335578d"
assert_reject "3 workflow rejects short SHA" "8cb6581"
assert_reject "4 workflow rejects arbitrary tag" "v1.0.0"
assert_accept_format "5 workflow accepts tag format" "marketing-prod/v2026.11.27.1"

echo "=== tag checkout (temporary git repo) ==="
TEMP_REPO="$(mktemp -d)"
TAG_REPO_CLEANUP() { rm -rf "$TEMP_REPO"; }
trap TAG_REPO_CLEANUP EXIT

git -C "$TEMP_REPO" init -q
git -C "$TEMP_REPO" config user.email "mkt03r@test.invalid"
git -C "$TEMP_REPO" config user.name "mkt03r"
printf 'probe\n' >"$TEMP_REPO/seed.txt"
git -C "$TEMP_REPO" add seed.txt
git -C "$TEMP_REPO" commit -qm "seed"
TEST_TAG="marketing-prod/v2099.01.01.0"
git -C "$TEMP_REPO" tag "$TEST_TAG"
sha_at_tag="$(git -C "$TEMP_REPO" rev-parse "refs/tags/$TEST_TAG")"
orig_head="$(git -C "$TEMP_REPO" rev-parse HEAD)"
git -C "$TEMP_REPO" checkout -q "refs/tags/$TEST_TAG"
checkout_sha="$(git -C "$TEMP_REPO" rev-parse HEAD)"
git -C "$TEMP_REPO" checkout -q "$orig_head"
if [[ "$sha_at_tag" == "$checkout_sha" ]]; then
  pass_test "6 checkout SHA matches tag in temp repo ($checkout_sha)"
else
  fail_test "6 checkout SHA mismatch tag=$sha_at_tag checkout=$checkout_sha"
fi
git -C "$TEMP_REPO" tag -d "$TEST_TAG" >/dev/null
if ! git -C "$TEMP_REPO" show-ref --tags 2>/dev/null | grep -q "$TEST_TAG"; then
  pass_test "7 temp tag cleaned up"
else
  fail_test "7 temp tag still present"
fi

echo "=== rollback logic (mock docker) ==="
MKT03R_TMP="$(mktemp -d)"
MOCK_DIR="$(mktemp -d)"
MOCK_STATE_DIR="$MKT03R_TMP/state"
mkdir -p "$MOCK_STATE_DIR"
MOCK_CLEANUP() { rm -rf "$MOCK_DIR" "$MKT03R_TMP"; }
trap 'TAG_REPO_CLEANUP; MOCK_CLEANUP' EXIT

cat >"$MOCK_DIR/docker" <<'MOCK'
#!/usr/bin/env bash
if [[ "$1" == "sudo" ]]; then shift; fi
cmd="$1"; shift || true
case "$cmd" in
  network) [[ "$1" == "inspect" ]] && exit 0 ;;
  volume)
    if [[ "$1" == "create" ]]; then
      if [[ "${MOCK_VOLUME_FAIL:-}" == "1" ]]; then exit 1; fi
      touch "${MOCK_STATE_DIR:?}/volume"
    fi
    [[ "$1" == "inspect" ]] && exit 0
    exit 0
    ;;
  rm)
    if [[ "$1" == "-f" ]]; then touch "${MOCK_STATE_DIR:?}/docker-rm"; fi
    exit 0
    ;;
  inspect)
    if [[ "${MOCK_HAS_CONTAINER:-}" != "1" ]]; then
      exit 1
    fi
    if [[ "$*" == *"Config.Image"* ]] || [[ "$*" == *"format"* ]]; then
      echo 'vividworks-prod:good'
      exit 0
    fi
    exit 0
    ;;
  run)
    joined="$*"
    vol="${DATA_VOLUME:?}:${VIVIDWORKS_EXPECTED_MOUNT:?}"
    if [[ "$joined" == *"-v"*"${vol}"* ]] || [[ "$joined" == *"-v ${vol}"* ]] || [[ "$joined" == *"-v"*"${DATA_VOLUME}:/app/data"* ]]; then
      touch "${MOCK_STATE_DIR:?}/mount-ok"
    fi
    img="${@: -1}"
    if [[ "$img" == *bad-run* ]]; then exit 1; fi
    if [[ "$img" == *bad-health* ]]; then
      echo bad-health > "${MOCK_STATE_DIR:?}/unhealthy"
    else
      rm -f "${MOCK_STATE_DIR:?}/unhealthy"
    fi
    exit 0
    ;;
  exec)
    if [[ -f "${MOCK_STATE_DIR:?}/unhealthy" ]]; then exit 1; fi
    if [[ "${MOCK_HTTP_NOT_OK:-}" == "1" ]]; then exit 1; fi
    exit 0
    ;;
  build) exit 0 ;;
esac
exit 0
MOCK
chmod +x "$MOCK_DIR/docker"
cat >"$MOCK_DIR/sudo" <<SUDO
#!/usr/bin/env bash
if [[ "\$1" == "docker" ]]; then
  shift
  exec "$MOCK_DIR/docker" "\$@"
fi
exit 1
SUDO
chmod +x "$MOCK_DIR/sudo"
export PATH="$MOCK_DIR:$PATH"
export MOCK_STATE_DIR

export VIVIDWORKS_PROD_ROOT="$MKT03R_TMP/prod"
export VIVIDWORKS_EXPECTED_MOUNT="/app/data"
mkdir -p "$VIVIDWORKS_PROD_ROOT/releases/old" "$VIVIDWORKS_PROD_ROOT/releases/new"
# shellcheck disable=SC1091
source "$ROOT/deploy/production/vividworks-prod-deploy.sh"

capture_previous_image_like_main() {
  previous_image=""
  if sudo docker inspect "$CONTAINER_NAME" >/dev/null 2>&1; then
    previous_image="$(sudo docker inspect "$CONTAINER_NAME" --format '{{.Config.Image}}')"
  fi
  export previous_image
}

export DATA_VOLUME=vividworks-prod-inquiries-data-test
export MOCK_HAS_CONTAINER=1
previous_image="vividworks-prod:good"

# CURRENT: successful deployment updates symlink
ln -sfn "$VIVIDWORKS_PROD_ROOT/releases/old" "$CURRENT"
capture_previous_current
if run_container "vividworks-prod:good"; then
  container_health && set_current_release "$VIVIDWORKS_PROD_ROOT/releases/new"
  if [[ "$(readlink -f "$CURRENT")" == "$(readlink -f "$VIVIDWORKS_PROD_ROOT/releases/new")" ]]; then
    pass_test "8 CURRENT points to new release after successful deploy"
  else
    fail_test "8 CURRENT not updated to new release"
  fi
else
  fail_test "8 run_container for successful deploy"
fi

# CURRENT: rollback restores previous (failed deploy must not advance CURRENT to new)
ln -sfn "$VIVIDWORKS_PROD_ROOT/releases/old" "$CURRENT"
capture_previous_current
previous_image="vividworks-prod:good"
if run_container "vividworks-prod:bad-health"; then
  if ! container_health; then
    if rollback "$previous_image"; then
      if [[ "$(readlink -f "$CURRENT")" == "$(readlink -f "$VIVIDWORKS_PROD_ROOT/releases/old")" ]]; then
        pass_test "9 CURRENT restored after rollback success"
      else
        fail_test "9 CURRENT not restored (got $(readlink -f "$CURRENT"))"
      fi
    else
      fail_test "9 rollback failed"
    fi
  else
    fail_test "9 expected unhealthy container"
  fi
else
  fail_test "9 bad-health run"
fi

# No previous CURRENT
rm -f "$CURRENT"
capture_previous_current
if restore_current_symlink >/dev/null 2>&1; then
  if [[ ! -L "$CURRENT" ]]; then
    pass_test "10 no previous CURRENT handled explicitly"
  else
    fail_test "10 unexpected CURRENT after no-previous restore"
  fi
else
  fail_test "10 no previous CURRENT restore returned error"
fi

# docker run failure + rollback
export MOCK_HAS_CONTAINER=1
previous_image="vividworks-prod:good"
ln -sfn "$VIVIDWORKS_PROD_ROOT/releases/old" "$CURRENT"
capture_previous_current
rm -f "$CURRENT" # simulate deploy not yet committed
if ! run_container "vividworks-prod:bad-run"; then
  if attempt_rollback "docker run failure"; then
    pass_test "11 docker run failure triggers rollback with CURRENT restore"
  else
    fail_test "11 rollback after docker run failure"
  fi
else
  fail_test "11 expected bad-run failure"
fi

export MOCK_HAS_CONTAINER=0
capture_previous_image_like_main
if [[ -n "$previous_image" ]]; then
  fail_test "12 previous_image should be empty when container absent (got '${previous_image}')"
elif ! attempt_rollback "docker run failure"; then
  pass_test "12 no previous image when container absent (inspect fail-closed)"
else
  fail_test "12 should fail without previous image"
fi

if [[ -f "$MOCK_STATE_DIR/mount-ok" ]]; then
  pass_test "13 docker run uses named volume mount ${DATA_VOLUME}:/app/data"
else
  fail_test "13 volume mount assertion missing"
fi

unset MOCK_VOLUME_FAIL
export MOCK_HAS_CONTAINER=1
if ensure_data_volume; then
  pass_test "14 ensure_data_volume succeeds"
else
  fail_test "14 ensure_data_volume should succeed"
fi

export MOCK_VOLUME_FAIL=1
if ensure_data_volume; then
  fail_test "15 volume create failure should abort"
else
  pass_test "15 volume create failure fail-closed"
fi

echo "=== live smoke (candidate + preview) ==="
cand="$(curl -sS -o /dev/null -w '%{http_code}' --max-time 20 https://marketing-prod-candidate.51-79-159-241.sslip.io/api/health || echo 000)"
prev="$(curl -sS -o /dev/null -w '%{http_code}' --max-time 20 https://preview.vividworks.my/api/health || echo 000)"
if [[ "$cand" == "200" && "$prev" == "200" ]]; then
  pass_test "16 live smoke candidate=$cand preview=$prev"
else
  fail_test "16 live smoke candidate=$cand preview=$prev"
fi

export MOCK_VOLUME_FAIL=1
rm -f "$MOCK_STATE_DIR/docker-rm"
export MOCK_HAS_CONTAINER=1
run_rc=0
run_container "vividworks-prod:good" || run_rc=$?
if [[ "$run_rc" -eq 2 ]] && [[ ! -f "$MOCK_STATE_DIR/docker-rm" ]]; then
  pass_test "17 volume failure before docker rm (no container removal)"
else
  fail_test "17 expected rc=2 without docker rm (rc=$run_rc rm=$([[ -f "$MOCK_STATE_DIR/docker-rm" ]] && echo yes || echo no))"
fi
unset MOCK_VOLUME_FAIL

echo "=== main deploy path (volume failure) ==="
MKT03R_ROLLBACK_CALLS=0
attempt_rollback() {
  MKT03R_ROLLBACK_CALLS=$((MKT03R_ROLLBACK_CALLS + 1))
  return 1
}
export MOCK_VOLUME_FAIL=1
export MOCK_HAS_CONTAINER=1
capture_previous_image_like_main
rm -f "$MOCK_STATE_DIR/docker-rm"
main_start_rc=0
deploy_start_container_or_abort "vividworks-prod:new-fail-vol" || main_start_rc=$?
if [[ "$main_start_rc" -eq 2 && "$MKT03R_ROLLBACK_CALLS" -eq 0 && ! -f "$MOCK_STATE_DIR/docker-rm" ]]; then
  pass_test "18 deploy_start_container_or_abort: no attempt_rollback on volume failure"
else
  fail_test "18 main path (rc=$main_start_rc rollbacks=$MKT03R_ROLLBACK_CALLS rm=$([[ -f "$MOCK_STATE_DIR/docker-rm" ]] && echo yes || echo no))"
fi
unset MOCK_VOLUME_FAIL

MKT03R_ROLLBACK_CALLS=0
export MOCK_HAS_CONTAINER=1
capture_previous_image_like_main
if [[ "$previous_image" != "vividworks-prod:good" ]]; then
  fail_test "19 previous_image from mock inspect expected vividworks-prod:good got '${previous_image:-<empty>}'"
else
  deploy_start_container_or_abort "vividworks-prod:bad-run" >/dev/null 2>&1 || true
  if [[ "$MKT03R_ROLLBACK_CALLS" -ge 1 ]]; then
    pass_test "19 deploy_start_container_or_abort calls attempt_rollback on post-rm run failure (previous_image=$previous_image)"
  else
    fail_test "19 attempt_rollback should run after bad-run (calls=$MKT03R_ROLLBACK_CALLS)"
  fi
fi

echo "=== rollback log messages ==="
export MOCK_VOLUME_FAIL=1
export MOCK_HAS_CONTAINER=1
previous_image="vividworks-prod:good"
rollback_log="$(rollback "vividworks-prod:good" 2>&1 || true)"
unset MOCK_VOLUME_FAIL
if [[ "$rollback_log" == *"not removed in this rollback attempt"* ]] \
  && [[ "$rollback_log" != *"left running"* ]]; then
  pass_test "20 rollback volume-failure log (no unverified running claim)"
else
  fail_test "20 rollback log message"
fi

export MOCK_VOLUME_FAIL=1
export MOCK_HAS_CONTAINER=1
capture_previous_image_like_main
deploy_log="$(deploy_start_container_or_abort "vividworks-prod:new" 2>&1 || true)"
unset MOCK_VOLUME_FAIL
if [[ "$deploy_log" == *"rollback not attempted"* ]] \
  && [[ "$deploy_log" == *"before removing existing container"* ]]; then
  pass_test "21 deploy volume-failure log distinct from rollback"
else
  fail_test "21 deploy log message"
fi

echo "=== review fix coverage (hostname, cleanup, harness, health) ==="
assert_reject "22 release ref rejects shell metachar" 'marketing-prod/v2026.11.27.1;echo pwned'
if ( ROUTING_HOST="VIVIDWORKS.MY."; unset VIVIDWORKS_PROD_GO_LIVE; refuse_public_hosts ) 2>/dev/null; then
  fail_test "23 public host VIVIDWORKS.MY. without approval should refuse"
else
  pass_test "23 uppercase trailing-dot public host blocked without approval"
fi
ROUTING_HOST="marketing-prod-candidate.51-79-159-241.sslip.io"
unset VIVIDWORKS_PROD_GO_LIVE
if ( ROUTING_HOST="marketing-prod-candidate.51-79-159-241.sslip.io"; refuse_public_hosts ); then
  pass_test "24 candidate host allowed without go-live approval"
else
  fail_test "24 candidate host should be allowed"
fi
export VIVIDWORKS_PROD_GO_LIVE=approved-by-owner
if ( ROUTING_HOST="VIVIDWORKS.MY"; refuse_public_hosts ); then
  pass_test "25 public host allowed with owner approval (normalized)"
else
  fail_test "25 public host with approval should pass"
fi
ROUTING_HOST="marketing-prod-candidate.51-79-159-241.sslip.io"
unset VIVIDWORKS_PROD_GO_LIVE

active_rel="$VIVIDWORKS_PROD_ROOT/releases/active-keep"
rollback_rel="$VIVIDWORKS_PROD_ROOT/releases/rollback-keep"
failed_rel="$VIVIDWORKS_PROD_ROOT/releases/failed-remove"
mkdir -p "$active_rel" "$rollback_rel" "$failed_rel"
ln -sfn "$active_rel" "$CURRENT"
previous_current="$rollback_rel"
prune_failed_release_dir "$active_rel"
prune_failed_release_dir "$rollback_rel"
prune_failed_release_dir "$failed_rel"
if [[ -d "$active_rel" && -d "$rollback_rel" && ! -d "$failed_rel" ]]; then
  pass_test "26 failed release cleanup preserves active and rollback paths"
else
  fail_test "26 prune_failed_release_dir preservation"
fi
case "$MKT03R_TMP" in
  /tmp/* | /var/tmp/*) pass_test "27 harness uses isolated mktemp root ($MKT03R_TMP)" ;;
  *) fail_test "27 harness temp root unexpected: $MKT03R_TMP" ;;
esac
unset MOCK_HTTP_NOT_OK
export MOCK_HAS_CONTAINER=1
run_container "vividworks-prod:good" || true
export MOCK_HTTP_NOT_OK=1
if container_health; then
  fail_test "28 container health rejects non-OK HTTP response"
else
  pass_test "28 container health rejects non-OK HTTP response"
fi
unset MOCK_HTTP_NOT_OK

echo "=== summary pass=$pass fail=$fail ==="
[[ "$fail" -eq 0 ]]