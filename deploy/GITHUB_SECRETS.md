# GitHub Actions secrets

Configure in **bigmrestaurant/opensourcepos** → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**.

| Secret | Description | Example |
|--------|-------------|---------|
| `SSH_HOST` | Hostinger SSH hostname or IP | `145.79.30.112` |
| `SSH_PORT` | SSH port from hPanel | `65002` |
| `SSH_USER` | SSH username | `u597458177` |
| `SSH_PRIVATE_KEY` | Ed25519 private key (full PEM) | Contents of `ospos_deploy` |
| `DEPLOY_PATH` | Remote deploy directory | `domains/bigmrestaurant.com/public_html/opensourcepos` |

The workflow **Deploy to Hostinger** runs on push to `production` and on manual **workflow_dispatch**.

It does **not** deploy `.env` or `writable/` — those must exist on the server (see [bootstrap-server.sh](./bootstrap-server.sh)).
