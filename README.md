# ADISOMA Solution — Enterprise Recruitment & Workforce Platform

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)]()
[![Production Status](https://img.shields.io/badge/Deployment-Live%20Production-success.svg)](http://adisoma.infy.click)

ADISOMA Solution is a high-performance, lightweight recruitment and talent consulting web platform developed with pure PHP 8.2+, MySQL (PDO), modern CSS3 with mobile-first responsive architecture, and vanilla JavaScript. The platform operates without external framework overhead, delivering near-instant page response times (< 250ms), hardened security, granular admin management, technical SEO optimization, and complete cross-device responsiveness.

---

## 🌟 Key Highlights & Features

- **Blazing Fast Performance**: Zero framework bloat (no Composer/NPM runtime dependency), minimal asset size, Gzip/Deflate compression, and browser caching enabled.
- **Enterprise Security Hardening**: Root Apache protection (`.htaccess`), rate-limited authentication with 15-minute brute-force lockout, timing-attack mitigation (`usleep`), secure session management (`HttpOnly`, `SameSite=Lax`, HTTPS `secure` cookies), strict file upload validation with MIME magic-byte verification, and PDO prepared statements throughout.
- **Role-Based Admin Dashboard**: Comprehensive management interface for jobs, applications, dynamic services, blogs, testimonials, navigation menus, admins, audit logs, and global site settings.
- **Dynamic Content & Navigation**: Database-backed navigation menus with drag-and-drop reordering, active page tracking, customizable social media links, and company contact details managed centrally.
- **Technical SEO Ready**: Dynamic canonical URLs, auto-generated XML Sitemap (`sitemap.xml`), crawler directives (`robots.txt`), Open Graph / Twitter Card social metadata, and Schema.org JSON-LD Structured Data (`Organization`, `WebSite`).
- **Complete Mobile Responsiveness**: Fluid layouts supporting devices from 320px smartphones to 4K displays, with an accessible slide-over mobile navigation drawer and touch-friendly interactive targets.
- **Automated CI/CD Pipeline**: GitHub Actions automated FTP synchronization directly deploying to the live production server on every `main` branch push.

---

## 🏗️ Architecture & Technology Stack

| Layer | Technology |
|---|---|
| **Backend Language** | PHP 8.2+ (Procedural / Modular Architecture) |
| **Database Engine** | MySQL 5.7+ / MariaDB 10.4+ via PHP Data Objects (PDO) |
| **Web Server** | Apache 2.4+ (`mod_rewrite`, `mod_deflate`, `mod_expires`, `mod_headers`) |
| **Frontend Styling** | Custom CSS3 with CSS Custom Properties, Flexbox, and CSS Grid |
| **Frontend Scripting** | Pure Vanilla JavaScript (ES6+) |
| **Deployment & CI/CD** | GitHub Actions (`.github/workflows/deploy.yml`) over FTPS / FTP |
| **Local Environment** | XAMPP for Windows (`C:\xampp\htdocs\adisoma`) |
| **Live Production** | InfinityFree Web Hosting (`http://adisoma.infy.click`) |

---

## 📁 Repository Structure

```
adisoma/
├── .github/
│   └── workflows/
│       └── deploy.yml              # CI/CD deployment automation pipeline
├── admin/
│   ├── assets/                     # Admin CSS and JavaScript
│   ├── download.php                # Protected resume streaming script
│   ├── footer.php                  # Shared admin footer
│   ├── header.php                  # Shared admin header & mobile drawer
│   ├── index.php                   # Admin index redirection
│   ├── login.php                   # Rate-limited admin authentication
│   ├── logout.php                  # Secure session destruction
│   ├── manage-admins.php           # Admin accounts CRUD
│   ├── manage-applications.php     # Candidate job submissions & resume review
│   ├── manage-blogs.php            # Article publisher with Markdown support
│   ├── manage-jobs.php             # Job postings CRUD (draft/published)
│   ├── manage-logs.php             # System activity & audit logging
│   ├── manage-menu.php             # Site navigation menu manager & reordering
│   ├── manage-services.php         # Dynamic service catalog CRUD
│   ├── manage-settings.php         # Contact info & social links settings
│   └── manage-testimonials.php     # Client testimonials manager
├── assets/
│   ├── css/
│   │   ├── responsive.css          # Mobile drawer & adaptive breakpoints
│   │   └── style.css               # Design system, branding & layout tokens
│   ├── images/                     # Logos, branding assets, and icons
│   └── js/
│       └── main.js                 # Mobile navigation, tabs, modals & UI helpers
├── config/
│   └── database.php                # Database connection, credentials & session config
├── database/
│   └── adisoma.sql                 # Initial schema & starter dataset
├── includes/
│   ├── footer.php                  # Public site footer & navigation
│   ├── functions.php               # Core helper library, security, CSRF, flash
│   └── header.php                  # Public header, metadata, SEO schema & nav
├── uploads/
│   ├── .htaccess                   # Strict PHP execution block for uploads
│   ├── blog/                       # Uploaded article featured images
│   ├── resumes/                    # Securely stored candidate PDF/DOC resumes
│   └── services/                   # Uploaded service cover images
├── .htaccess                       # Root Apache security, headers, rewrites & caching
├── 403.php                         # Custom branded 403 Forbidden page
├── 404.php                         # Custom branded 404 Not Found page
├── about.php                       # Company story, values & leadership
├── apply.php                       # Candidate application & resume upload form
├── blog-detail.php                 # Single article reader view with SEO tags
├── blogs.php                       # Public article archive & insights
├── contact.php                     # Client & candidate inquiry form
├── employers.php                   # Employer staffing solutions & talent request
├── index.php                       # Public homepage & hero presentation
├── it-recruitment.php              # Specialized IT recruitment page
├── job-seekers.php                 # Career advisory & candidate resources
├── jobs.php                        # Searchable job openings directory
├── non-it-recruitment.php          # Specialized Non-IT recruitment page
├── robots.txt                      # Search engine crawler permissions & sitemap link
├── service-detail.php              # Dynamic single service page
├── services.php                    # Public services directory
├── sitemap.xml                     # Full XML Sitemap for search indexing
└── [Documentation Files]           # Complete system documentation (see below)
```

---

## 🚀 Local Installation & Setup (XAMPP on Windows)

### 1. Prerequisites
- **XAMPP** with PHP 8.2+ and Apache / MySQL services installed.
- **Git** installed on your workstation.

### 2. Clone the Repository
Clone the repository directly into your local XAMPP web root directory:
```bash
cd C:\xampp\htdocs
git clone https://github.com/shopquare/adisoma.git adisoma
cd adisoma
```

### 3. Database Setup
1. Launch **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open your web browser and navigate to `http://localhost/phpmyadmin`.
3. Create a new database named `adisoma` with collation `utf8mb4_unicode_ci`.
4. Click the **Import** tab, choose `database/adisoma.sql`, and click **Go**.

### 4. Configuration
Open `config/database.php` and verify the database connection credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'adisoma');
define('DB_USER', 'root');
define('DB_PASS', '');
```
*(On local XAMPP, the application auto-detects `localhost` and dynamically provisions database schema adjustments on boot).*

### 5. Access the Platform
- **Public Website**: [http://localhost/adisoma/](http://localhost/adisoma/)
- **Admin Control Panel**: [http://localhost/adisoma/admin/login.php](http://localhost/adisoma/admin/login.php)
  - *Default local admin*: `admin` / `admin123` (or set up a new administrator on initial prompt).

---

## 🔒 Security Architecture Highlights

1. **Deny-All Direct Directory Access**: Root `.htaccess` blocks direct web requests to `.sql`, `.env`, `.git`, `.md`, `config/`, and `database/`.
2. **Upload Execution Prevention**: `uploads/.htaccess` utilizes Apache 2.4 `<FilesMatch>` directives to disable PHP script execution (`SetHandler None`, `php_flag engine off`), preventing Web Shell attacks.
3. **MIME Magic-Bytes Validation**: Uploaded resumes are inspected using PHP `finfo_open(FILEINFO_MIME_TYPE)` to verify binary file headers, rejecting masked executables.
4. **Brute-Force Rate Limiting**: The admin login enforces a 5-attempt threshold with a 15-minute security lockout, accompanied by audit logging and `usleep` timing defense.
5. **Session Hardening**: Sessions enforce `HttpOnly`, `SameSite=Lax`, strict cookie lifetime, and dynamic `secure` flags under HTTPS.
6. **Strict SQL Parameterization**: 100% of SQL statements with external parameters utilize PDO prepared statements.

---

## 📚 Complete Documentation Index

For detailed guides, please refer to the specialized documentation files:

- [Project Architecture](file:///c:/xampp/htdocs/adisoma/PROJECT_ARCHITECTURE.md)
- [Database Schema & Migration](file:///c:/xampp/htdocs/adisoma/DATABASE_DOCUMENTATION.md)
- [API & Form Specifications](file:///c:/xampp/htdocs/adisoma/API_DOCUMENTATION.md)
- [Application Security Guide](file:///c:/xampp/htdocs/adisoma/SECURITY.md)
- [Authentication & Access Control](file:///c:/xampp/htdocs/adisoma/AUTHENTICATION.md)
- [Technical SEO Guide](file:///c:/xampp/htdocs/adisoma/SEO_GUIDE.md)
- [Responsive Design System](file:///c:/xampp/htdocs/adisoma/RESPONSIVE_DESIGN.md)
- [Quality Assurance & Testing](file:///c:/xampp/htdocs/adisoma/TESTING.md)
- [Deployment & CI/CD Pipeline](file:///c:/xampp/htdocs/adisoma/DEPLOYMENT.md)
- [Project Changelog](file:///c:/xampp/htdocs/adisoma/CHANGELOG.md)
- [Bug Audit & Remediation Log](file:///c:/xampp/htdocs/adisoma/BUG_REPORT.md)

---

## 📄 License & Maintainer

- **Developer**: ADISOMA Technical Engineering Team
- **Production URL**: [http://adisoma.infy.click](http://adisoma.infy.click)
- **Repository**: [https://github.com/shopquare/adisoma](https://github.com/shopquare/adisoma)
