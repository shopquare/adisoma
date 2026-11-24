# ADISOMA Solution — System Architecture & Design Documentation

## 1. Architectural Philosophy

ADISOMA Solution is designed with a **High-Performance Modular Procedural Architecture**. By intentionally eschewing bulky frameworks (such as Laravel or Symfony), the application eliminates overhead such as autoloader lookups, heavy dependency injection containers, and redundant middleware stacks.

### Core Architectural Tenets
1. **Zero Runtime Bloat**: Zero third-party Composer packages and zero NPM runtime bundles. All rendering is completed in single-digit milliseconds directly on standard PHP runtimes.
2. **Defensive by Default**: All user-facing inputs, database queries, file uploads, and session operations pass through centralized defensive utility functions.
3. **Transparent Request Flow**: Every URL maps directly to an indexable PHP script or rewrites cleanly through Apache, preventing opaque controller cascades.
4. **Resilient Production Tolerance**: Automatic schema adjustments, environment detection (local XAMPP vs. cloud production), and dynamic fallback handlers ensure zero downtime during iterative deployments.

---

## 2. High-Level System Architecture Diagram

```
+-----------------------------------------------------------------------------------+
|                                  USER / CLIENT                                    |
|             (Mobile Browser, Tablet, Desktop, Search Engine Crawler)              |
+-----------------------------------------------------------------------------------+
                                         │
                                         ▼
+-----------------------------------------------------------------------------------+
|                             APACHE 2.4 WEB SERVER                                 |
|  - Root .htaccess Protection (Deny .git, .env, .sql, config/, database/)          |
|  - Security Headers (X-Content-Type-Options, X-Frame-Options, Referrer-Policy)    |
|  - Performance Optimization (mod_deflate Gzip, mod_expires Cache-Control)         |
+-----------------------------------------------------------------------------------+
                                         │
                    ┌────────────────────┴────────────────────┐
                    ▼                                         ▼
+---------------------------------------+ +---------------------------------------+
|          PUBLIC CONTROLLERS           | |          ADMIN SUB-SYSTEM             |
|  - index.php, about.php, services.php | |  - admin/login.php (Rate Limiting)    |
|  - jobs.php, apply.php, contact.php   | |  - admin/manage-*.php (Role-Based)    |
|  - blog-detail.php, service-detail.php| |  - admin/download.php (Secure Stream) |
+---------------------------------------+ +---------------------------------------+
                    │                                         │
                    └────────────────────┬────────────────────┘
                                         ▼
+-----------------------------------------------------------------------------------+
|                            SHARED CORE SERVICE LAYER                              |
|  - config/database.php: Singleton PDO Connection, Dynamic Environment Config      |
|  - includes/functions.php: Security, CSRF Tokens, Flash Alerts, Sanitization      |
|  - includes/header.php / footer.php: Dynamic Metadata, Open Graph, Schema.org     |
+-----------------------------------------------------------------------------------+
                                         │
                                         ▼
+-----------------------------------------------------------------------------------+
|                              MYSQL / MARIADB DATABASE                             |
|  - admins, audit_logs, blogs, jobs, job_applications, menu_items,                 |
|    services, site_settings, testimonials                                          |
+-----------------------------------------------------------------------------------+
```

---

## 3. Directory Layout & Module Responsibilities

### 3.1 Public Presentation Layer
- **`index.php`**: Primary brand showcase, hero callouts, dynamic services carousel/grid, featured vacancies, client testimonials, and latest blog insights.
- **`about.php`**: In-depth company background, corporate mission, core recruitment values, leadership team, and credentials.
- **`services.php` & `service-detail.php`**: Dynamic catalog of talent acquisition offerings (e.g., Executive Search, Contract Staffing, IT Recruitment) loaded directly from the database with custom rich content formatting.
- **`jobs.php` & `apply.php`**: Candidate recruitment portal with real-time job filters (job type, location, department) and multipart application submission with resume upload handling.
- **`blogs.php` & `blog-detail.php`**: Content marketing engine supporting Markdown and formatted articles, view count incrementing, related posts, and Open Graph sharing.
- **`contact.php`**: Interactive contact and inquiry channel feeding directly to company operations.

### 3.2 Administrative Sub-System (`admin/`)
- **`admin/login.php`**: Authenticates administrative staff using `password_verify()`, enforces 5-attempt brute-force rate limiting with 15-minute lockout, and logs audit events.
- **`admin/manage-jobs.php`**: Full CRUD interface for job requisitions with state toggling (`published`, `draft`, `closed`).
- **`admin/manage-applications.php`**: Candidate application review interface with status workflows (`new`, `reviewed`, `shortlisted`, `rejected`) and note tracking.
- **`admin/download.php`**: Secure streaming endpoint verifying active admin session before streaming binary resumes from the protected `uploads/resumes/` folder.
- **`admin/manage-services.php`**: Manage service offerings, icons, images, descriptions, and dynamic slug generation.
- **`admin/manage-blogs.php`**: Article publication workflow with rich content rendering and slug management.
- **`admin/manage-menu.php`**: Visual site navigation reordering, label customization, and custom routing.
- **`admin/manage-settings.php`**: Centralized configuration of phone numbers, email addresses, corporate headquarters, and social links.
- **`admin/manage-logs.php`**: Security and operational audit log viewer.

### 3.3 Core Shared Infrastructure
- **`config/database.php`**: Houses the singleton database factory `db()`, session parameter configuration, and environment detection (`localhost` vs. production).
- **`includes/functions.php`**: Encapsulates all global helper logic:
  - `clean_input($v)`: Recursive string trimming and null-byte elimination.
  - `e($str)`: Contextual HTML entity escaping (`htmlspecialchars`).
  - `csrf()` & `csrf_ok()`: Cryptographic CSRF token issuance and verification.
  - `flash($msg, $type)`: Persistent, flashable alert messages rendered with ARIA support.
  - `make_slug($text)`: URL slugification for search-engine-friendly URLs.
  - `save_application()`: Candidate file upload and database persistence.
  - `get_site_settings()`: Cached retrieval of company contact information and social links.

---

## 4. Data Flow & Request Lifecycle

```
[Incoming HTTP GET/POST Request]
             │
             ▼
[Apache .htaccess Check] ──► (Blocked if .sql, .env, .git, or direct config request)
             │
             ▼
[Target PHP Script Executes]
             │
             ├──► require_once 'config/database.php'
             │       └── Establishes secure session cookies & PDO singleton
             │
             ├──► require_once 'includes/functions.php'
             │       └── Imports security, CSRF, and helper utilities
             │
             ├──► [Authentication / Authorization Check (if in /admin/)]
             │       └── is_logged_in() or check_login_lockout()
             │
             ├──► [Database Queries via PDO Prepared Statements]
             │
             ├──► [Render Template: includes/header.php]
             │       └── Outputs dynamic <title>, <meta description>, <link rel="canonical">,
             │           Open Graph, Twitter Cards, and Schema.org JSON-LD
             │
             ├──► [Render Page Body & Interactive Elements]
             │
             └──► [Render Template: includes/footer.php]
                     └── Injects site settings, navigation links, and closes DOM cleanly
```

---

## 5. Frontend & Asset Architecture

1. **Modular CSS Hierarchy**:
   - `assets/css/style.css`: Houses CSS custom property tokens (`--o: #f57c00`, `--n: #1e293b`), base typography, button components, grid utilities, and form styling.
   - `assets/css/responsive.css`: Implements responsive breakpoints (`@media (max-width: 991px)`, `768px`, `480px`) and styles the slide-out mobile drawer navigation.
2. **Accessible Interaction**:
   - Touch targets are calibrated to meet WCAG AA standards (minimum 44x44px).
   - A dedicated `.skip-link` enables keyboard navigation directly to `#main-content`.
   - Alert notifications utilize `role="alert"` and `aria-live="polite"` for screen reader announcements.
3. **Performance Optimization**:
   - Native browser lazy-loading (`loading="lazy"`) is implemented on all non-hero imagery.
   - Font preconnect headers accelerate Google Fonts typography rendering.
