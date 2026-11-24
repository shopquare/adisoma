# ADISOMA Solution — Database Schema & Data Dictionary

## 1. Overview & Connection Architecture

The ADISOMA Solution database utilizes MySQL 5.7+ / MariaDB 10.4+ with the `utf8mb4_unicode_ci` character set to support full Unicode compliance (including emojis, symbols, and multilingual text).

Database access is mediated entirely through the **PHP Data Objects (PDO)** extension with strict security and error handling policies:
- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`: Forces all SQL anomalies to raise catchable exceptions rather than silent failures.
- `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`: Returns clean associative arrays without duplicate numerical indices.
- `PDO::ATTR_EMULATE_PREPARES => false`: Enforces native prepared statements on the database engine, eliminating second-order SQL injection vectors.

---

## 2. Entity-Relationship Diagram (ERD)

```
+--------------------+            +------------------------+
|      admins        |            |       audit_logs       |
+--------------------+            +------------------------+
| id (PK)            |<-----------| admin_id (FK nullable) |
| username           |            | action                 |
| password           |            | entity_type            |
| created_at         |            | entity_id              |
+--------------------+            | details                |
                                  | ip_address             |
+--------------------+            | created_at             |
|       jobs         |            +------------------------+
+--------------------+
| id (PK)            |
| title              |            +------------------------+
| location           |            |    job_applications    |
| job_type           |            +------------------------+
| department         |            | id (PK)                |
| description        |            | job_id (FK nullable)   |
| requirements       |            | full_name              |
| status             |            | email                  |
| created_at         |            | phone                  |
+--------------------+            | resume_path            |
         │                        | cover_letter           |
         └───────────────────────►| status                 |
                                  | admin_notes            |
                                  | created_at             |
                                  +------------------------+

+--------------------+     +--------------------+     +--------------------+
|      services      |     |       blogs        |     |    testimonials    |
+--------------------+     +--------------------+     +--------------------+
| id (PK)            |     | id (PK)            |     | id (PK)            |
| title              |     | title              |     | name               |
| slug               |     | slug               |     | role               |
| excerpt            |     | excerpt            |     | company            |
| description        |     | content            |     | content            |
| icon               |     | image_url          |     | rating             |
| image_url          |     | author             |     | active             |
| active             |     | views              |     | created_at         |
| sort_order         |     | status             |     +--------------------+
| created_at         |     | published_at       |
+--------------------+     | created_at         |
                           +--------------------+

+--------------------+     +--------------------+
|     menu_items     |     |   site_settings    |
+--------------------+     +--------------------+
| id (PK)            |     | id (PK)            |
| label              |     | setting_key (UQ)   |
| url                |     | setting_value      |
| sort_order         |     | updated_at         |
| active             |     +--------------------+
| is_dynamic         |
| created_at         |
+--------------------+
```

---

## 3. Data Dictionary & Table Specifications

### 3.1 `admins`
Stores authenticated user accounts for the administrative sub-system.
| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `username` | VARCHAR(50) | NO | | Unique username for administrative login |
| `password` | VARCHAR(255) | NO | | Modern `password_hash()` cryptographic hash (bcrypt) |
| `created_at` | DATETIME | NO | CURRENT_TIMESTAMP | Account creation timestamp |

### 3.2 `audit_logs`
Maintains an immutable historical record of security-sensitive operations and modifications.
| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `admin_id` | INT UNSIGNED | YES | NULL | Foreign Key referencing `admins.id` (NULL for unauthenticated events) |
| `action` | VARCHAR(100) | NO | | Description of action (e.g., 'Logged In', 'Created Job', 'Deleted Blog') |
| `entity_type` | VARCHAR(50) | YES | NULL | Target resource model (e.g., 'Job', 'Admin', 'Service') |
| `entity_id` | INT UNSIGNED | YES | NULL | Target resource primary key |
| `details` | TEXT | YES | NULL | Contextual information or change summary |
| `ip_address` | VARCHAR(45) | YES | NULL | Client IPv4 or IPv6 address |
| `created_at` | DATETIME | NO | CURRENT_TIMESTAMP | Event occurrence timestamp |

### 3.3 `jobs`
Maintains job openings and vacancies displayed on the public careers portal.
| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `title` | VARCHAR(150) | NO | | Requisition job title |
| `location` | VARCHAR(100) | NO | | Geographic location or 'Remote / Hybrid' |
| `job_type` | VARCHAR(50) | NO | 'Full Time' | Employment category ('Full Time', 'Contract', 'Part Time') |
| `department` | VARCHAR(100) | YES | NULL | Functional department (e.g., 'Engineering', 'HR Consulting') |
| `description` | TEXT | NO | | Comprehensive job description |
| `requirements` | TEXT | YES | NULL | Job qualifications and experience requirements |
| `status` | ENUM | NO | 'published' | Visibility state: `'published'`, `'draft'`, `'closed'` |
| `created_at` | DATETIME | NO | CURRENT_TIMESTAMP | Timestamp when vacancy was registered |

### 3.4 `job_applications`
Tracks candidate applications and securely references submitted resumes.
| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `job_id` | INT UNSIGNED | YES | NULL | Associated vacancy ID from `jobs.id` (or NULL for general application) |
| `full_name` | VARCHAR(100) | NO | | Candidate full name |
| `email` | VARCHAR(150) | NO | | Candidate contact email |
| `phone` | VARCHAR(30) | NO | | Candidate telephone number |
| `resume_path` | VARCHAR(255) | NO | | Unique anonymized filename stored in `uploads/resumes/` |
| `cover_letter` | TEXT | YES | NULL | Optional candidate cover notes |
| `status` | ENUM | NO | 'new' | Application lifecycle stage: `'new'`, `'reviewed'`, `'shortlisted'`, `'rejected'` |
| `admin_notes` | TEXT | YES | NULL | Private internal remarks made by recruiters |
| `created_at` | DATETIME | NO | CURRENT_TIMESTAMP | Application submission timestamp |

### 3.5 `services`
Stores talent acquisition service offerings displayed on the website and dedicated service pages.
| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `title` | VARCHAR(100) | NO | | Service title (e.g., 'Permanent Recruitment', 'Executive Search') |
| `slug` | VARCHAR(120) | YES | NULL | URL-safe identifier for clean SEO routing |
| `excerpt` | VARCHAR(255) | YES | NULL | Summary snippet displayed in service grid cards |
| `description` | TEXT | NO | | Rich body text explaining the service methodology |
| `icon` | VARCHAR(50) | YES | NULL | FontAwesome or Unicode icon designation |
| `image_url` | VARCHAR(255) | YES | NULL | Featured promotional image URL or path |
| `active` | TINYINT(1) | NO | 1 | Boolean toggle (1 = visible, 0 = hidden) |
| `sort_order` | INT | NO | 0 | Display sequence ordering |
| `created_at` | DATETIME | NO | CURRENT_TIMESTAMP | Record creation timestamp |

### 3.6 `blogs`
Maintains editorial recruitment insights and news articles.
| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `title` | VARCHAR(255) | NO | | Article headline |
| `slug` | VARCHAR(255) | YES | NULL | URL slug for indexable permalinks |
| `excerpt` | TEXT | YES | NULL | Brief synopsis displayed on article listings |
| `content` | LONGTEXT | NO | | Article body content (Markdown & HTML supported) |
| `image_url` | VARCHAR(255) | YES | NULL | Primary article hero image path |
| `author` | VARCHAR(100) | YES | 'ADISOMA Team' | Author byline |
| `views` | INT UNSIGNED | NO | 0 | Public reading analytics counter |
| `status` | ENUM | NO | 'published' | Editorial status: `'published'` or `'draft'` |
| `published_at` | DATETIME | YES | NULL | Date and time when published |
| `created_at` | DATETIME | NO | CURRENT_TIMESTAMP | Record creation timestamp |

### 3.7 `menu_items`
Controls the public navigation bar links and hierarchy dynamically.
| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `label` | VARCHAR(100) | NO | | Display anchor text |
| `url` | VARCHAR(255) | NO | | Destination hyperlink |
| `sort_order` | INT | NO | 0 | Left-to-right display sequence index |
| `active` | TINYINT(1) | NO | 1 | Boolean visibility switch |
| `is_dynamic` | TINYINT(1) | NO | 0 | Indicates dynamically synthesized items |
| `created_at` | DATETIME | NO | CURRENT_TIMESTAMP | Record creation timestamp |

### 3.8 `site_settings`
Stores centralized site-wide key-value configuration variables.
| Column | Type | Null | Default | Description |
|---|---|---|---|---|
| `id` | INT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `setting_key` | VARCHAR(100) | NO | | Unique setting identifier (UNIQUE index) |
| `setting_value` | TEXT | YES | NULL | Textual value (e.g., phone, email, address, Facebook URL) |
| `updated_at` | DATETIME | NO | CURRENT_TIMESTAMP | Last modification timestamp |

---

## 4. Automated Migration & Auto-Provisioning

To ensure smooth operations across local testing and live deployment, the application embeds non-destructive schema verification inside `config/database.php`. On every database connection, the engine checks for necessary tables and columns (such as `site_settings` and `blogs`), automatically provisioning them if not yet present.
