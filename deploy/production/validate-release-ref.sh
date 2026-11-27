#!/usr/bin/env bash
# Shared validation for marketing-prod release refs (workflow + local tests).
set -euo pipefail

validate_marketing_prod_ref() {
  local ref="$1"
  if [[ ! "$ref" =~ ^marketing-prod/v[0-9]{4}\.[0-9]{2}\.[0-9]{2}\.[0-9]+$ ]]; then
    echo "rejected: format"
    return 1
  fi
  if [[ "$ref" == "main" ]] || [[ "$ref" =~ ^[0-9a-f]{7,40}$ ]]; then
    echo "rejected: branch-or-sha"
    return 1
  fi
  echo "accepted: $ref"
  return 0
}

if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
  validate_marketing_prod_ref "${1:?ref required}"
fi