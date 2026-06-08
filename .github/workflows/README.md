# GitHub Actions (BIGM fork)

This fork uses a single deployment workflow for Hostinger shared hosting.

## Workflows

| File | Trigger | Purpose |
|------|---------|---------|
| `deploy-hostinger.yml` | Push to `production`, manual dispatch | Build OSPOS (composer + npm/gulp) and rsync to Hostinger |

## Setup

1. [deploy/SSH_SETUP.md](../../deploy/SSH_SETUP.md) — SSH key and Hostinger access
2. [deploy/GITHUB_SECRETS.md](../../deploy/GITHUB_SECRETS.md) — repository secrets
3. [deploy/bootstrap-server.sh](../../deploy/bootstrap-server.sh) — one-time `.env` and `writable/` on server

Upstream OSPOS CI workflows (Docker, release, lint) were removed for this fork.
