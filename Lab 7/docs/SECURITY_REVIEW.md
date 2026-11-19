# Lab 7.2.0 — Authentication and Security Review

**Primary source:** *IS6465 Week 8 Authentication, Sessions and Authorization* lecture (Nov 16, 2026), including the 20-minute idle timeout, secure-cookie guidance, password hashing, POST/CSRF, role+ownership matrix, and session lifecycle. The ZIP named `skills pack.zip` includes application-builder, test-harness-generator, and traceability-auditor only; **no separate security-pack file was present**. This review applies the lecture rather than implying a nonexistent security-pack audit was run.

| Security principle | Code evidence | Check / concern |
|---|---|---|
| Passwords hashed, never stored plaintext | `services/userService.php:registerUser`, `changePassword` using `password_hash(..., PASSWORD_DEFAULT)` | Check user table and compare with submitted secret |
| Password checked safely | `authenticate` uses `password_verify`, status Active | Inactive or incorrect credentials return same login error |
| SQL injection protection | PDO prepared statements in `repositories/*.php` | Keep query parameters separate from SQL |
| Session fixation resistance | `includes/auth.php:loginUser` rotates ID | Compare session ID before and after login |
| 20-minute idle timeout | `includes/auth.php:currentUser` checks >1200 seconds | Test idle invalidation with CLI/session and browser |
| Secure browser cookie options | `session_set_cookie_params`: HttpOnly, SameSite Lax, Secure only over HTTPS | **Require HTTPS in deployment**; over HTTP, cookie has no Secure flag |
| Server-side authorization | Every protected page uses `requireLogin`, `requireRole`; admin service functions independently check DB role/status | Direct URL 403 checks, not just hidden nav links |
| Student record ownership | `requestPDO.php` accepts and enforces `ownerId` for reads and **atomic writes** | Other student gets 404/no changes |
| Cross-site request forgery | Every state-changing controller uses POST + `verifyCsrfToken` | Missing token must result in 403 before mutation |
| Cross-site scripting | Dynamic HTML uses `escapeHtml` with ENT_QUOTES | Review future added outputs |
| Invalid redirect protection | `safeLocalRedirect` allowlists local page names | Open redirect attempts fall back to `profile.php` |
| Clean logout | POST `php/logout.php` calls `logoutSession` which clears session and expires cookie | Refresh protected page requires login |
| Database files inaccessible via dev router | `php/router.php` public-page allowlist excludes `database`, `references`, `config` etc. | A real deployment needs proper web server rules/document root |
| SQL foreign key integrity | PDO `PRAGMA foreign_keys=ON` and schema FK | Test invalid request student IDs |
| Admin-only actions | `setUserRole`, `setUserStatus`, `attemptDeleteUser` verify current DB admin | Prevent last active administrator demotion, deactivation, deletion |

## Critical deployment caveats

This is a **coursework implementation, not a production identity provider**. It has no shared-IP or distributed login throttling, email verification, password reset with secure token lifecycle, audit log, MFA, or automated backup/restore. Session cookies get their Secure flag only when PHP detects HTTPS; use TLS and trusted reverse-proxy settings for a real deployment. The PHP development server router is intended solely for local/Codespaces use, not as a public server security layer.

**Recommended before public launch:** (1) enforce HTTPS and production web-root restrictions; (2) add rate limiting and logs, email ownership verification and account recovery; (3) approve deletion/retention policy; (4) implement verified migration linking for legacy student records; (5) execute browser authorization/CSRF tests and a dependency/security review.
