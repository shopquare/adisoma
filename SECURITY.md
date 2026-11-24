# ADISOMA Solution — Application Security Policy & Threat Mitigation Guide

## 1. Security Architecture & Threat Model

The security model of ADISOMA Solution is founded on **defense-in-depth**, adhering to the OWASP Top 10 web application security guidelines. The platform enforces security boundaries at the web server level, session layer, controller inputs, file system, and database abstraction layer.

---

## 2. OWASP Top 10 Defense Matrix

| OWASP Vulnerability | Risk Level | Mitigation Strategy Implemented in ADISOMA |
|---|---|---|
| **A01: Broken Access Control** | High | All `/admin/` controllers enforce `is_logged_in()` check before any execution. Resume files are hidden outside web access and served strictly through authenticated binary streaming (`admin/download.php`). |
| **A02: Cryptographic Failures** | Medium | Passwords hashed using modern `password_hash()` (bcrypt with dynamic cost factors). Session cookies enforce `HttpOnly` and `SameSite=Lax`. Dynamic `secure` flag enforced under HTTPS. |
| **A03: Injection (SQL / Shell)** | Critical | 100% of parameterized database queries utilize PDO prepared statements with `PDO::ATTR_EMULATE_PREPARES => false`. Shell execution functions (`exec`, `system`, `shell_exec`) are entirely absent from the codebase. |
| **A04: Insecure Design** | Medium | Public upload endpoints inspect binary magic-bytes. Honeypot anti-spam filters protect forms without frustrating legitimate candidates with CAPTCHAs. |
| **A05: Security Misconfiguration** | High | Root `.htaccess` blocks direct file viewing of `.sql`, `.env`, `.git`, `.md`, `config/`, and `database/`. Directory indexing is explicitly disabled (`Options -Indexes`). Default production settings suppress verbose PHP errors. |
| **A06: Vulnerable & Outdated Components** | Low | Zero third-party dependency footprint. The codebase contains no npm or Composer packages subject to supply chain poisoning. |
| **A07: Identification & Authentication Failures** | High | 5-attempt brute-force rate limiting with 15-minute lockout. `usleep(250000)` timing attack mitigation. Complete session regeneration upon authentication (`session_regenerate_id(true)`). |
| **A08: Software & Data Integrity Failures** | Medium | GitHub Actions CI/CD uses encrypted repository secrets. File uploads are renamed to randomized hashes, preventing path and filename manipulation. |
| **A09: Security Logging & Monitoring Failures** | Medium | Dedicated `audit_logs` table logs all administrator actions, login events, and IP addresses. |
| **A10: Server-Side Request Forgery (SSRF)** | Low | No user-supplied URLs are fetched server-side. |

---

## 3. Web Server Hardening (`.htaccess`)

### 3.1 Root Directory Shielding
The root `.htaccess` file prevents directory browsing, blocks sensitive directories and configuration files, and applies security headers:
```apache
Options -Indexes

# Block sensitive directories and files
<FilesMatch "^(\.env|\.git|\.htaccess|\.htpasswd|composer\.|package\.|.*\.sql|.*\.md|.*\.log)$">
    Require all denied
</FilesMatch>

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(config|database)/ - [F,L]
</IfModule>

# Security HTTP Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

### 3.2 Upload Folder Execution Blocking (`uploads/.htaccess`)
Even if an attacker bypasses application-level filters to upload a malicious PHP file, the upload directory strictly prohibits script execution:
```apache
Options -Indexes -ExecCGI
<FilesMatch "\.(?i:php|php[0-9]|phtml|phar|pl|py|jsp|asp|htm|html|shtml|sh|cgi)$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order Deny,Allow
        Deny from all
    </IfModule>
    SetHandler None
</FilesMatch>

<IfModule mod_php.c>
    php_flag engine off
</IfModule>
```

---

## 4. Multi-Layer File Upload Security

1. **Extension Whitelisting**: Only extensions in `['pdf', 'doc', 'docx']` are permitted.
2. **Binary Magic-Bytes Verification**: Uses `finfo_open(FILEINFO_MIME_TYPE)` to verify the true file signature.
3. **Randomized Renaming**: Uploaded files are renamed using `bin2hex(random_bytes(16))` or SHA-256 timestamp hashing. Original file names are never preserved on disk.
4. **Isolated Storage**: Resumes are placed in `uploads/resumes/` where direct web browsing is blocked.
5. **Streaming via Controller**: Only authorized administrators can stream resume files via `admin/download.php`.

---

## 5. Cross-Site Scripting (XSS) & CSRF Defense

- **Contextual HTML Escaping**: All variables rendered into HTML attributes or tags pass through the `e()` helper:
  ```php
  function e(?string $str): string {
      return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
  ```
- **Cryptographic CSRF Tokens**: Each session generates a 256-bit cryptographically secure token. Every POST request is validated:
  ```php
  function csrf_ok(): bool {
      $t = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
      return !empty($t) && hash_equals($_SESSION['csrf_token'] ?? '', $t);
  }
  ```
