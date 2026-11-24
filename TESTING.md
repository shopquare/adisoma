# ADISOMA Solution — Quality Assurance & Testing Suite Documentation

## 1. QA Strategy & Testing Philosophy

The testing framework for ADISOMA Solution combines automated PHP syntax analysis, Python-based route and regression testing, security attack simulation, and manual cross-device verification.

---

## 2. Automated Test Suites

### 2.1 Full-Project Syntax Linting (`lint_all.py`)
Scans all 38 PHP files across the repository to verify that zero syntax errors or unclosed blocks exist.
```bash
python scratch/lint_all.py
```
**Results**:
- Total PHP Files Checked: **38**
- Syntax Errors Detected: **0**
- Status: **PASSED**

### 2.2 HTTP Route & Regression Suite (`run_regression_tests.py`)
Executes live HTTP requests against local and production endpoints, checking for:
- Correct HTTP status codes (200 OK, 404 Not Found, 403 Forbidden).
- Absences of PHP Warnings, Notices, or Fatal Errors in DOM output.
- Presence of mandatory SEO tags (`<link rel="canonical">`, `<meta name="description">`, Open Graph tags).
- XML Sitemap parse validity.
- Crawler permissions in `robots.txt`.

#### Test Results Matrix:
| Route | Expected Code | Actual Code | SEO Tags | PHP Notices | Status |
|---|---|---|---|---|---|
| `/` (Homepage) | 200 | 200 | Present & Valid | None | **PASS** |
| `/about.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/services.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/jobs.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/blogs.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/employers.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/job-seekers.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/contact.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/apply.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/it-recruitment.php` | 200 | 200 | Present & Valid | None | **PASS** |
| `/non-it-recruitment.php`| 200 | 200 | Present & Valid | None | **PASS** |
| `/sitemap.xml` | 200 | 200 | Valid XML (18 URLs) | None | **PASS** |
| `/robots.txt` | 200 | 200 | Directives Present | None | **PASS** |
| `/404.php` | 404 | 404 | Custom Branded UI | None | **PASS** |
| `/403.php` | 403 | 403 | Custom Branded UI | None | **PASS** |

### 2.3 Security & Authentication Verification (`test_admin_security.py`)
Automates session negotiation, CSRF token extraction, and brute-force attempt simulation:
- CSRF Token Extraction: **PASSED**
- Invalid Credential Rejection: **PASSED**
- Rate Limiting Decrement Counter: **PASSED**
- Lockout Activation on Threshold: **PASSED**

---

## 3. Manual Verification Checklist

1. **Candidate Application Workflow**:
   - Navigate to `/jobs.php`, choose a vacancy, and click **Apply Now**.
   - Verify that the job title pre-populates automatically in `/apply.php`.
   - Submit invalid file (e.g., `.exe` or `.txt`) -> Verified error alert displays: *"Please upload a valid PDF or Word document."*
   - Submit valid `.pdf` resume -> Verified success message displays and record appears in `admin/manage-applications.php`.
2. **Admin Stream Security**:
   - Access `/uploads/resumes/` directly via browser -> Verified HTTP 403 Forbidden.
   - Stream resume via `admin/download.php` as logged-in admin -> Verified binary download initiates.
   - Log out and attempt download URL -> Verified redirect to `admin/login.php`.
3. **Cross-Browser Verification**:
   - Tested on Google Chrome (Desktop & Mobile emulation), Mozilla Firefox, Microsoft Edge, and Apple Safari (WebKit).
   - Zero layout shifts, no horizontal scrolling, and touch interactions operate smoothly.
