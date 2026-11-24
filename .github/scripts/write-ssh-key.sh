#!/usr/bin/env bash
# Write VIVIDPREVIEW_SSH_KEY to a PEM file OpenSSH accepts (including one-line secrets).
set -euo pipefail

dest="${1:?usage: write-ssh-key.sh /path/to/key}"
test -n "${SSH_KEY:-}"

python3 - "$dest" <<'PY'
import os
import re
import sys
import textwrap

dest = sys.argv[1]
raw = os.environ["SSH_KEY"].strip()
if not raw:
    raise SystemExit("SSH_KEY is empty")

if "BEGIN OPENSSH PRIVATE KEY" in raw and raw.count("\n") <= 2:
    body = raw.replace("-----BEGIN OPENSSH PRIVATE KEY-----", "")
    body = body.replace("-----END OPENSSH PRIVATE KEY-----", "")
    body = re.sub(r"\s+", "", body)
    raw = (
        "-----BEGIN OPENSSH PRIVATE KEY-----\n"
        + "\n".join(textwrap.wrap(body, 70))
        + "\n-----END OPENSSH PRIVATE KEY-----\n"
    )
elif not raw.endswith("\n"):
    raw += "\n"

with open(dest, "w", encoding="utf-8") as fh:
    fh.write(raw)
os.chmod(dest, 0o600)
PY

ssh-keygen -y -f "$dest" >/dev/null