# ADISOMA Solution — API & Form Integration Specifications

## 1. Overview & Protocol Standard

ADISOMA Solution exposes procedural HTTP form endpoints designed for standard HTML5 multipart submissions and asynchronous AJAX integrations.

### Security Pre-Conditions
All state-modifying requests (`POST`) require:
1. An active session initialized via `config/database.php`.
2. A valid CSRF token passed either via:
   - Form body: `name="csrf"`
   - Or HTTP Header: `X-CSRF-Token: <token>`
3. Passing anti-bot honeypot evaluation (the hidden field `website` must remain completely empty).

---

## 2. Public Form Endpoints

### 2.1 Candidate Job Application Submission
- **Endpoint**: `POST /apply.php`
- **Content-Type**: `multipart/form-data`
- **Access Level**: Public (No authentication required)

#### Parameters:
| Field | Type | Required | Description |
|---|---|---|---|
| `csrf` | string | YES | Valid CSRF security token |
| `website` | string | NO | Honeypot field (must be left empty) |
| `job_id` | integer | NO | Vacancy identifier from `jobs.id` |
| `full_name` | string | YES | Candidate full name (max 100 characters) |
| `email` | string | YES | Valid RFC 5322 email address |
| `phone` | string | YES | Candidate phone number (max 30 characters) |
| `resume` | binary file | YES | Resume file (PDF, DOC, DOCX up to 5MB) |
| `cover_letter`| string | NO | Optional candidate introductory remarks |

#### Validation Rules:
- The file must be verified using MIME magic bytes (`finfo_open(FILEINFO_MIME_TYPE)`). Acceptable MIME types:
  - `application/pdf`
  - `application/msword`
  - `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
- The file is renamed to a cryptographically random SHA-256 hash before storage in `uploads/resumes/`.

#### Response:
- **Success**: Sets session flash message (`flash('Thank you! Your application has been submitted successfully.')`) and redirects to `apply.php?success=1`.
- **Validation Failure**: Sets error flash and redirects back to `apply.php` with populated errors.

---

### 2.2 General Contact & Inquiries
- **Endpoint**: `POST /contact.php`
- **Content-Type**: `application/x-www-form-urlencoded`
- **Access Level**: Public

#### Parameters:
| Field | Type | Required | Description |
|---|---|---|---|
| `csrf` | string | YES | Valid CSRF token |
| `website` | string | NO | Anti-spam honeypot (must be blank) |
| `name` | string | YES | Sender full name |
| `email` | string | YES | Sender email address |
| `phone` | string | NO | Contact telephone number |
| `subject` | string | YES | Inquiry topic |
| `message` | string | YES | Inquiry message body |

#### Response:
- **Success**: Redirects with confirmation flash banner.

---

## 3. Administrative Control Endpoints

### 3.1 Administrator Authentication
- **Endpoint**: `POST /admin/login.php`
- **Content-Type**: `application/x-www-form-urlencoded`
- **Access Level**: Unauthenticated / Public

#### Parameters:
| Field | Type | Required | Description |
|---|---|---|---|
| `csrf` | string | YES | Valid CSRF token |
| `username` | string | YES | Administrator username |
| `password` | string | YES | Administrator password |

#### Responses:
- **HTTP 302**: On valid credentials, regenerates session ID and redirects to `admin/dashboard.php`.
- **HTTP 200 (Error)**: On invalid credentials, increments failed attempt counter and displays remaining attempts. Lockout activated after 5 failures.

---

### 3.2 Secure Resume Download Stream
- **Endpoint**: `GET /admin/download.php?file={filename}`
- **Access Level**: Protected (Active Administrator Session Required)

#### Query Parameters:
| Parameter | Type | Required | Description |
|---|---|---|---|
| `file` | string | YES | Basename of the file (e.g., `resume_abc123.pdf`) |

#### Behavior:
- Verifies `is_logged_in()`. If false, returns `HTTP 403 Forbidden` or redirects to `admin/login.php`.
- Sanitizes file input with `basename($_GET['file'])` to prevent directory traversal (`../`).
- Checks `file_exists()` within `uploads/resumes/`.
- Emits binary stream headers:
  ```http
  Content-Description: File Transfer
  Content-Type: application/octet-stream
  Content-Disposition: attachment; filename="Candidate_Resume.pdf"
  Content-Length: 123456
  ```
- Reads binary content directly to the output buffer without exposing filesystem paths.

---

### 3.3 Site Navigation Reordering API
- **Endpoint**: `POST /admin/manage-menu.php`
- **Content-Type**: `application/x-www-form-urlencoded`
- **Access Level**: Protected (Administrator)

#### Parameters:
| Field | Type | Required | Description |
|---|---|---|---|
| `csrf` | string | YES | Valid CSRF token |
| `action` | string | YES | Action selector: `'reorder'`, `'add'`, `'edit'`, `'delete'` |
| `order` | string / JSON | CONDITIONAL | Comma-separated list or JSON array of item IDs in desired sequence |
