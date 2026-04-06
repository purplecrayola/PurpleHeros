# HostGator Production CI/CD

This repo uses GitHub Actions workflow:

- `.github/workflows/deploy-hostgator-production.yml`

It deploys automatically on push to `main`.

## Required GitHub Secrets

Add these in **GitHub -> Settings -> Secrets and variables -> Actions**:

- `HG_HOST`: SSH host (for example `heros.purplecrayola.com` or server hostname)
- `HG_PORT`: SSH port (usually `22`)
- `HG_USER`: SSH username
- `HG_SSH_KEY`: Private SSH key content for deploy user
- `HG_APP_PATH`: Absolute app base path on server, for example:
  - `/home2/mic5005/apps/heros.purplecrayola.com`

## Server structure expected

- `$HG_APP_PATH/releases`
- `$HG_APP_PATH/shared`
- `$HG_APP_PATH/current` (symlink managed by workflow)
- Subdomain document root should already point to:
  - `$HG_APP_PATH/current/public`

## Notes

- First deploy creates `shared/.env` from `.env.example` if missing.
- Update `shared/.env` on server with real production values.
- Workflow keeps the latest 5 releases for rollback.
