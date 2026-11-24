# Changelog & Version History — ADISOMA Solution

All notable changes to the ADISOMA Solution recruitment platform are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [2.1.0] - 2026-11-24

### Security
- **Root Apache Hardening**: Created comprehensive `.htaccess` in repository root blocking direct web requests to `.sql`, `.env`, `.git`, `.md`, `config/`, and `database/`.
- **Directory Indexing Protection**: Disabled directory listing globally using `Options -Indexes`.
- **Upload Script Execution Blocking**: Upgraded `uploads/.htaccess` with Apache 2.4 `<FilesMatch>` directives, `SetHandler None`, and `php_flag engine off` to thwart Web Shell upload exploits.
- **Brute-Force Rate Limiting**: Implemented 5-attempt rate limiting with a 15-minute lockout window and timing-attack mitigation (`usleep(250000)`) in `admin/login.php`.
- **Session Cookie Hardening**: Configured `HttpOnly`, `SameSite=Lax`, and dynamic `secure` flags under HTTPS in `config/database.php`.
- **Binary MIME Verification**: Integrated `finfo_open(FILEINFO_MIME_TYPE)` magic-byte inspection for all resume uploads in `apply.php` and `includes/functions.php`.
- **SQL Prepared Statements**: Refactored raw SQL string interpolations to 100% PDO prepared statements across `admin/manage-admins.php`, `admin/manage-jobs.php`, `admin/manage-services.php`, `admin/manage-testimonials.php`, and `admin/manage-menu.php`.

### Added
- **Technical SEO Engine**:
  - Auto-generated `sitemap.xml` registering 18 primary static and dynamic routes.
  - Standardized `robots.txt` granting crawler access while blocking admin and upload directories.
  - Injected dynamic canonical tags (`<link rel="canonical">`) and unique meta descriptions across all views.
  - Complete Open Graph and Twitter Card social metadata.
  - Schema.org JSON-LD Structured Data for `Organization` and `WebSite` entities in `includes/header.php`.
- **Branded Custom Error Pages**:
  - Implemented `404.php` (Page Not Found) with branded visual design and search guidance.
  - Implemented `403.php` (Access Forbidden) with security notice and return shortcuts.
- **Accessibility Enhancements**:
  - Added `.skip-link` allowing screen reader and keyboard users to jump directly to `#main-content`.
  - Added `role="alert"` and `aria-live="polite"` attributes to flash alerts in `includes/functions.php`.
- **Performance Optimizations**:
  - Added native lazy-loading (`loading="lazy"`) to all non-hero imagery across `index.php`, `services.php`, `blogs.php`, and `about.php`.
  - Added Google Fonts preconnect tags for accelerated web font rendering.
  - Added Apache `mod_deflate` Gzip compression and `mod_expires` caching directives.
- **Complete Project Documentation**:
  - Authored comprehensive documentation suite covering Architecture, Database, APIs, Security, Authentication, SEO, Responsiveness, Testing, Deployment, Changelog, and Bug Remediation.

### Changed
- Refactored `blog-detail.php` and `service-detail.php` to supply dynamic canonical URLs and article metadata.
- Closed `</main>` wrapper in `includes/footer.php` to ensure fully valid HTML5 DOM semantics.
- Updated `admin/login.php` to use cryptographically generated CSRF tokens.

---

## [2.0.0] - 2026-11-23

### Added
- **Dynamic Blogs & Insights Module**: Full database-backed editorial article engine with listing, detail view, reading counter, and admin management.
- **Dynamic Site Settings**: Admin interface to customize telephone numbers, email addresses, corporate addresses, and social media links.
- **Navigation Menu Manager**: Visual drag-and-drop / ordering manager for public navbar items.
- **Mobile Responsive Drawer**: Off-canvas slide-out navigation for mobile and tablet screens.
- **GitHub Actions CI/CD**: Automated FTP deployment pipeline syncing changes directly to InfinityFree hosting.

### Fixed
- Fixed redeclaration error for `make_slug()` in `admin/manage-blogs.php`.
- Corrected missing image paths and broken thumbnails across service catalog.
- Resolved layout shift and button misalignment on mobile screens.

---

## [1.0.0] - 2026-11-20

### Added
- Initial project release with core recruitment features:
  - Public homepage, about page, and contact form.
  - Searchable jobs directory and candidate resume submission.
  - Basic administrative dashboard for jobs and candidate application review.
  - MySQL database schema (`database/adisoma.sql`).
