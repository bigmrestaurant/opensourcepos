# Hostinger SSH Setup (one-time)

## 1. Enable SSH in hPanel

1. Log in to **hPanel** → **Websites** → **bigmrestaurant.com**
2. Go to **Advanced** → **SSH Access**
3. Enable SSH and note:
   - **Host** (e.g. `145.79.30.112` or `ssh.hostinger.com`)
   - **Port** (usually `65002` on Hostinger)
   - **Username** (e.g. `u597458177`)

## 2. Generate a deploy key (local machine)

```bash
ssh-keygen -t ed25519 -f ospos_deploy -C "github-actions-bigm-pos" -N ""
```

This creates:

- `ospos_deploy` — private key (for GitHub secret `SSH_PRIVATE_KEY`)
- `ospos_deploy.pub` — public key (add to Hostinger)

## 3. Add public key to Hostinger

1. hPanel → **Advanced** → **SSH Access** → **SSH Keys**
2. Paste contents of `ospos_deploy.pub`
3. Save

## 4. Test SSH login

```bash
ssh -p 65002 -i ospos_deploy u597458177@YOUR_SSH_HOST
```

Replace port/host/username with your values from hPanel.

## 5. Add private key to GitHub

Repo: **bigmrestaurant/opensourcepos** → **Settings** → **Secrets and variables** → **Actions**

| Secret | Value |
|--------|--------|
| `SSH_HOST` | Host from hPanel |
| `SSH_PORT` | e.g. `65002` |
| `SSH_USER` | e.g. `u597458177` |
| `SSH_PRIVATE_KEY` | Full contents of `ospos_deploy` (including `BEGIN`/`END` lines) |
| `DEPLOY_PATH` | `domains/bigmrestaurant.com/public_html/opensourcepos` |

See [GITHUB_SECRETS.md](./GITHUB_SECRETS.md) for details.
