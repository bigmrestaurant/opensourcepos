#!/usr/bin/env bash
# Generate Ed25519 keypair for GitHub Actions → Hostinger deploy.
# Output: deploy/keys/ospos_deploy and ospos_deploy.pub (gitignored)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
KEY_DIR="${SCRIPT_DIR}/keys"
KEY_FILE="${KEY_DIR}/ospos_deploy"

mkdir -p "$KEY_DIR"

if [[ -f "$KEY_FILE" ]]; then
  echo "Key already exists: $KEY_FILE"
  echo "Delete it first if you want a new key."
  exit 1
fi

ssh-keygen -t ed25519 -f "$KEY_FILE" -C "github-actions-bigm-pos" -N ""

echo ""
echo "Public key (add to Hostinger hPanel → SSH Keys):"
echo "---"
cat "${KEY_FILE}.pub"
echo "---"
echo ""
echo "Private key (add to GitHub secret SSH_PRIVATE_KEY):"
echo "  cat ${KEY_FILE}"
echo ""
echo "See deploy/SSH_SETUP.md and deploy/GITHUB_SECRETS.md"
