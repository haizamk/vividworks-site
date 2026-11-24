#!/usr/bin/env bash
# Push main (and current branch) to github.com/haizamk/vividworks-site when GITHUB_TOKEN is set.
set -euo pipefail

: "${GITHUB_TOKEN:?Set GITHUB_TOKEN (classic PAT with repo and workflow scopes)}"

REMOTE_URL="https://x-access-token:${GITHUB_TOKEN}@github.com/haizamk/vividworks-site.git"

if git remote get-url github >/dev/null 2>&1; then
  git remote set-url github "$REMOTE_URL"
else
  git remote add github "$REMOTE_URL"
fi

git push -u github main
current="$(git branch --show-current)"
if [[ "$current" != "main" ]]; then
  git push -u github "$current"
fi