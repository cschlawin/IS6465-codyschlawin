# Lab 7.2.0 — Authentication and User Management

**IS 6465 Web Application Development · Peer Tutoring Hub**

A self-contained PHP 8 + SQLite application extending the **uploaded Lab 6 source ZIP** with all ten CRUD/authentication use cases from the *Peer Tutoring Hub User Management CRUD Design Package* and the Week 8 lecture. Uses procedural PHP, PDO prepared statements, Bootstrap/CSS, and the existing student/request tables and JSON-in-topic convention.

## Requirements / prerequisites

- PHP **8.0+** with **PDO SQLite** (`pdo_sqlite` / `sqlite3`) enabled. Verify with `php -v` and `php -m`.
- Local terminal or GitHub Codespaces (no GitHub upload is required to run the ZIP).
- Avoid public hosting of the PHP built-in server; use it only for the lab.

## Quick start (after extracting `Lab 7.2.0.zip`)

```bash
cd 'Lab 7.2.0'
php -v
php -m | grep -i sqlite
# The ZIP contains an original Lab 6 sample database with the user schema upgraded.
# To repair/re-apply migration without dropping tutoring rows:
php database/upgrade.php
# Create the first Administrator (password prompted securely in terminal):
php database/create-admin.php 'Lab Administrator' admin@example.edu
# Optional: generate random-password demonstration Student/Tutor/Admin accounts:
php database/seed-demo.php --demo
# Start a local server (DO NOT expose it to the public internet):
php -S 127.0.0.1:8000 php/router.php
```

Open **http://127.0.0.1:8000/php/login.php**. To create your own Student/Tutor account, use `/php/register.php`. Public registration cannot create an Administrator.

> If `php -m` does not show `pdo_sqlite` or `sqlite3`, install/enable the PHP SQLite extension in your Codespace/container before launching. The app cannot connect without it.

### Database options

- `database/tutoring.db` is a **copy** of the supplied Lab 6 sample SQLite file, upgraded to add `users` and nullable unique `students.user_id`. The original ZIP's data was **not** overwritten.
- To use a **new empty database** without touching the included sample, set `TUTORING_DB_PATH` to another writable filename, then run `php database/init.php`. For example:

```bash
TUTORING_DB_PATH=/tmp/lab7-fresh.db php database/init.php
TUTORING_DB_PATH=/tmp/lab7-fresh.db php database/create-admin.php 'Admin Example' admin@example.edu
TUTORING_DB_PATH=/tmp/lab7-fresh.db php -S 127.0.0.1:8000 php/router.php
```

- If migrating a *separate* existing Lab 6 database, back up the file, set `TUTORING_DB_PATH` to its absolute path and execute `php database/upgrade.php` once. It checks whether `students.user_id` exists and is safe to rerun.
- **Legacy ownership caution:** historical Lab 6 student rows have no verified `users` link. Do **not** claim another student's historical requests merely by self-registering with their email. A Student attempting to register with that legacy email is blocked pending manual administrator verification. Newly registered Students receive a fresh linked student record. Administrators can still review historical requests.

## Use-case coverage

| ID | Use case | Page |
|---|---|---|
| UM-01 | Create Active Student/Tutor | `php/register.php` |
| UM-02 | View own account | `php/profile.php` |
| UM-03 | Admin list/search/filter users | `php/admin-users.php` |
| UM-04 | Update own name/email | `php/profile.php` |
| UM-05 | Change own password | `php/change-password.php` |
| UM-06 | Admin change role | `php/admin-manage-user.php` |
| UM-07 | Admin activate/deactivate | `php/admin-manage-user.php` |
| UM-08 | Admin conditional deletion | `php/admin-manage-user.php` |
| UM-09 | Log in | `php/login.php` |
| UM-10 | Log out | `php/logout.php` (POST from nav) |

**Permission model (Week 8 lecture):** Student may submit/read/edit/cancel **own** tutoring requests; Tutor may maintain an account but cannot use request workflow; Administrator may see/edit/cancel any request and manage accounts. Only **Open** requests can be edited or cancelled. User and request writes use transactions as appropriate; authorization is enforced in server code and SQL, not just UI navigation.

## Testing

```bash
cd 'Lab 7.2.0'
bash tests/run-all.sh
```

The runner checks all PHP syntax, validates PDO SQLite availability, reruns Lab 6 validation/integration tests and runs `tests/user-management-tests.php` (UM-01..UM-10 assertions on **in-memory** SQLite). The tests do **not** modify the packaged `database/tutoring.db`. Read `docs/TEST_PLAN.md` for the browser-based authorization/CSRF checklist, expected PASS/FAIL format and remaining coverage gaps.

**Test evidence when package was generated:** PHP syntax lint passed across all 34 `.php` files. The original form validation test printed 10 PASS checks. Database-dependent tests could **not** run here because the generation runtime had no PDO SQLite driver (`could not find driver`); they are included but **unverified** until run in Codespaces.

## Folder layout

```
Lab 7.2.0/
  config/          SQLite connection factory
  database/        Upgraded starter database, fresh schema, migration, CLI admin/demo tools
  includes/        Navigation, shared templates, session/auth/CSRF, validation
  php/             Registration/login/logout/profile/admin and retained request pages
  repositories/    Prepared SQLite queries for users, students, requests
  services/        User management and tutoring request business rules
  tests/           User management, Lab 6 regression tests, test runner
  docs/            Traceability audit, test plan, security review
  references/      The provided Lab 6 ZIP, design document, lecture PDF, and three skills docs
  css/             Existing Lab 6 styles
```

## Important policy and security limits

- The design package explicitly leaves **permanent deletion policy and related-record retention** open. This lab uses a **conservative provisional policy**: an Administrator can delete only an account with **no** related tutoring requests and cannot remove the last active Administrator. Deactivation remains the safe alternative. Seek policy approval before using deletion with actual student records.
- Exact password complexity is open in the design. This lab enforces a documented **minimum of 8 bytes**, with no recovery workflow.
- Sessions expire after **20 minutes of inactivity**, are regenerated on login, and are cleared on logout. Cookie flags include `HttpOnly` and `SameSite=Lax`; `Secure` is applied when HTTPS is detected. POST actions require CSRF tokens.
- The supplied `skills pack.zip` contains **three** text instructions: `application-builder.txt`, `test-harness-generator.txt`, and `traceability-auditor.txt`. It did **not** include a fourth security-pack file. `docs/SECURITY_REVIEW.md` applies the Week 8 lecture security requirements and identifies remaining production gaps.
- Public deployment requires HTTPS, rate limiting, email verification/recovery, stronger operational security, and an approved data-retention policy.

## Source-to-code explanation

**Browser → PHP controller → Service → Repository → SQLite.** For example, Student registration posts to `php/register.php`, which verifies CSRF and calls `services/userService.php:registerUser()`. The service validates inputs, starts a transaction and calls `repositories/userPDO.php:insertUser()` and `repositories/studentPDO.php` to create the linked Student record. SQLite stores the hashed password and Active status. Authentication pages call the same service and shared `includes/auth.php` session checks; administrator and request pages enforce role and ownership on each action.

The full implementation mapping, actual vs planned test distinctions, and priority findings are in `docs/TRACEABILITY_AUDIT.md`.
