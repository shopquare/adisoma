# ADISOMA Solution — Authentication & Access Control Architecture

## 1. Authentication Lifecycle Overview

The administrative portal (`/admin/`) utilizes a stateful, session-based authentication model. No third-party OAuth or external token providers are required, keeping credentials fully sovereign.

```
[Unauthenticated Admin Request]
             │
             ▼
      Is session active?
      (isset($_SESSION['admin']))
             │
      ┌──────┴──────┐
     YES            NO
      │              │
      ▼              ▼
[Proceed to     [Redirect to /admin/login.php]
 Controller]         │
                     ▼
          [Submit Credentials]
                     │
          [Evaluate Lockout Status]
          (Is failed_attempts >= 5 & lockout_time > now?)
                     │
              ┌──────┴──────┐
             YES            NO
              │              │
              ▼              ▼
       [Show Lockout]   [Fetch Admin Record via PDO]
       (Retry in X min)      │
                             ▼
                      [usleep(250000)] (Mitigate timing analysis)
                             │
                      [password_verify()]
                             │
                      ┌──────┴──────┐
                     MATCH       NO MATCH
                      │              │
                      ▼              ▼
             [session_regenerate_id] [Increment failed_attempts]
             [Set $_SESSION['admin']] [Show remaining attempts]
             [Log action in audit_logs]
             [Redirect to dashboard]
```

---

## 2. Password Storage & Cryptographic Standards

- **Algorithm**: `PASSWORD_DEFAULT` (currently utilizing bcrypt `$2y$` with standard cost factor 10).
- **One-Way Hashing**: Passwords are never stored in plain text, reversible formats, or reversible encryption.
- **Verification**: `password_verify($password, $hash)` provides timing-attack-resistant verification.

---

## 3. Session Security Configuration

Session cookie configuration is established in `config/database.php` prior to calling `session_start()`:

```php
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
);

session_set_cookie_params([
    'lifetime' => 0,               // Browser session expiration
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,        // Sent exclusively over HTTPS in production
    'httponly' => true,            // Completely inaccessible to JavaScript document.cookie
    'samesite' => 'Lax'            // Defends against Cross-Site Request Forgery
]);
```

### Session ID Regeneration
Upon successful authentication, `session_regenerate_id(true)` is immediately invoked. This invalidates the old unauthenticated session ID, preventing Session Fixation attacks.

---

## 4. Brute-Force Rate Limiting & Lockout Engine

To defend against automated dictionary and credential-stuffing attacks:
- **Maximum Consecutive Failures**: 5 attempts.
- **Lockout Window**: 900 seconds (15 minutes).
- **Granular Feedback**: Informs the user of remaining attempts (e.g., `"Invalid username or password. (3 attempts remaining)"`).
- **Timing Defense**: A 250ms artificial sleep (`usleep(250000)`) runs during authentication processing, smoothing out response times and preventing username enumeration via timing attacks.
- **Lockout Reset**: Any successful login clears both `$_SESSION['failed_attempts']` and `$_SESSION['lockout_time']`.

---

## 5. Logout & Session Invalidation

When an administrator logs out (`admin/logout.php`):
1. An audit event is recorded in the `audit_logs` table.
2. `$_SESSION` is completely wiped (`$_SESSION = []`).
3. The session cookie is expired in the past:
   ```php
   if (ini_get("session.use_cookies")) {
       $params = session_get_cookie_params();
       setcookie(session_name(), '', time() - 42000,
           $params["path"], $params["domain"],
           $params["secure"], $params["httponly"]
       );
   }
   ```
4. `session_destroy()` terminates the server-side session file.
5. The administrator is redirected to `admin/login.php` with a logout confirmation message.
