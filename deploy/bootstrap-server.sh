#!/usr/bin/env bash
# One-time server bootstrap for OSPOS on Hostinger.
# Run over SSH after the deploy path exists (mkdir if needed):
#   cd ~/domains/bigmrestaurant.com/public_html/opensourcepos
#
# Usage:
#   1. Copy .env.example to .env and edit DB credentials + encryption.key
#   2. Run: bash bootstrap-server.sh

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [[ ! -f .env.example ]]; then
  echo "Run this from the OSPOS root (public_html/opensourcepos) on the server."
  exit 1
fi

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env from .env.example — edit before first login:"
  echo "  - CI_ENVIRONMENT = production"
  echo "  - app.allowedHostnames = 'pos.bigmrestaurant.com'"
  echo "  - database.default.* (Hostinger MySQL creds for bigm_pos)"
  echo "  - encryption.key (32+ random characters)"
fi

mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar
chmod -R 755 writable

echo "Bootstrap complete. Ensure .env is configured, then visit https://pos.bigmrestaurant.com"
