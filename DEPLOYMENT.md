# ADISOMA Solution — Deployment & CI/CD Pipeline Documentation

## 1. Environment Architecture

ADISOMA Solution operates in a dual-stage environment:

| Attribute | Local Development | Live Production |
|---|---|---|
| **Host** | Local Workstation (Windows 11) | InfinityFree Cloud Hosting |
| **Domain** | `http://localhost/adisoma/` | `http://adisoma.infy.click/` |
| **Web Server** | Apache 2.4.58 (XAMPP) | Apache 2.4+ Cloud Linux |
| **PHP Version** | PHP 8.2.12 | PHP 8.2+ |
| **Database** | MySQL / MariaDB (Localhost) | MySQL Remote Instance |
| **Deployment Method**| Direct Filesystem Editing | Automated GitHub Actions CI/CD |

---

## 2. CI/CD Automated Deployment Pipeline

The application incorporates automated Continuous Integration and Continuous Deployment via **GitHub Actions**. Every commit pushed to the `main` branch automatically triggers file synchronization directly to the live InfinityFree hosting account.

### Workflow Configuration (`.github/workflows/deploy.yml`)
```yaml
name: Deploy to Production via FTP

on:
  push:
    branches:
      - main
  workflow_dispatch:

jobs:
  web-deploy:
    name: Sync Repository to Hosting Server
    runs-on: ubuntu-latest
    steps:
      - name: Checkout Source Code
        uses: actions/checkout@v4
        with:
          fetch-depth: 2

      - name: FTP Deploy Action
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          server-dir: htdocs/
          dangerous-clean-slate: false
          exclude: |
            **/.git*
            **/.git*/**
            **/database/**
            uploads/resumes/**
```

---

## 3. GitHub Secrets Configuration

To maintain security, zero credentials exist within repository source code. The following encrypted secrets are configured under **Settings > Secrets and variables > Actions** in the GitHub repository:

- `FTP_SERVER`: The FTP hostname provided by InfinityFree (e.g., `ftpupload.net`).
- `FTP_USERNAME`: The designated FTP user account.
- `FTP_PASSWORD`: The encrypted account password.

---

## 4. Production Database Configuration & Auto-Migration

1. **Database Schema Setup**:
   - Log into the live InfinityFree control panel (vPanel).
   - Create a MySQL database and import `database/adisoma.sql` via phpMyAdmin.
2. **Environment Parameterization**:
   - `config/database.php` detects the hostname. When running under `adisoma.infy.click`, it uses live database constants.
3. **Automated Schema Provisioning**:
   - If tables such as `site_settings` or `blogs` do not exist, `config/database.php` executes `CREATE TABLE IF NOT EXISTS` automatically upon the first HTTP request, eliminating manual SQL overhead.

---

## 5. Deployment Verification & Troubleshooting

After any push to `main`:
1. Check the **Actions** tab on GitHub:
   - Verify that the workflow `Deploy to Production via FTP` completes with a green checkmark (`completed - success`).
2. Verify live HTTP endpoints:
   - `curl -I http://adisoma.infy.click/` -> Expect `HTTP/1.1 200 OK`.
   - `curl -I http://adisoma.infy.click/sitemap.xml` -> Expect `HTTP/1.1 200 OK`.
   - `curl -I http://adisoma.infy.click/404.php` -> Expect `HTTP/1.1 404 Not Found`.
3. In case of FTP failure:
   - Ensure the hosting server did not enforce aggressive connection limits.
   - Re-run the failed workflow via the GitHub Actions UI with **Re-run all jobs**.
