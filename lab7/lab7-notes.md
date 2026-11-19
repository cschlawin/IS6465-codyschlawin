# Lab 7 — Authentication and User Management (IS 6465)

## Overview
Implements UM-01 through UM-10 from the Peer Tutoring Hub User Management design package on top of the Lab 6 architecture.

**Key decisions**
- Added `users` table (user_id PK, full_name, email UNIQUE, password_hash, role, status, created_at).
- Linked `students.user_id` FK so Student accounts own their tutoring requests.
- Self-registration creates Active Student or Tutor only (Administrator blocked).
- Sessions store minimal identity; session_regenerate_id after login.
- CSRF tokens on all state-changing forms.
- Role + ownership checks on every protected request page.
- Tutors receive 403 on the request workflow.
- Deletion blocked when related requests exist or last active admin would be removed (open policy from design).

## Files created or modified
- `database/setup-database.sql` — users table + students.user_id
- `includes/auth.php` — session, login/logout helpers, requireLogin, requireRole, CSRF
- `repositories/userPDO.php` — user CRUD
- `services/userService.php` — registration, auth, profile, password, role, status, delete rules
- `php/register.php`, `login.php`, `logout.php`, `profile.php`, `change-password.php`
- `php/admin-users.php`, `admin-manage-user.php`
- `php/seed-users.php` — creates sample Admin/Student/Tutor
- Updated existing request pages with auth, ownership, and CSRF
- `tests/user-management-tests.php` — provisional CLI tests

## How to run
1. Place the lab7 folder in your Codespaces / local PHP environment.
2. Run the setup SQL against a fresh `database/tutoring.db` (or let the app create it).
3. `php php/seed-users.php` to create sample accounts (passwords are in the script).
4. Start the PHP built-in server from the lab7 directory: `php -S localhost:8000`
5. Visit `http://localhost:8000/php/register.php` or `login.php`.

Sample credentials after seed:
- admin@example.edu / AdminPass1!
- alex@example.edu / StudentPass1!
- taylor@example.edu / TutorPass1!

## Traceability matrix (proposed — run tests in Codespaces for evidence)

| Requirement ID | Requirement | Implementation evidence | Test evidence | Status | Gap or action |
|---|---|---|---|---|---|
| UM-01 | Create User Account (Student/Tutor only, Active) | services/userService.php registerUser; php/register.php | tests/user-management-tests.php (positive + duplicate + admin denial) | Implemented, unverified | Execute tests; confirm hash stored |
| UM-02 | View Own Account | php/profile.php + requireLogin | Manual browser test | Implemented, unverified | Add automated profile assertion |
| UM-03 | View and Search User Accounts (Admin) | php/admin-users.php + listUsers | Manual | Implemented, unverified | Add search tests |
| UM-04 | Update Own Account (name/email) | updateOwnProfile | Manual | Implemented, unverified | Duplicate email case |
| UM-05 | Change Password | changePassword + password_verify | tests (partial) | Implemented, unverified | Confirm old password fails |
| UM-06 | Update User Role (Admin, protect last admin) | setUserRole | Manual | Implemented, unverified | Test last-admin guard |
| UM-07 | Activate or Deactivate | setUserStatus | Manual | Implemented, unverified | Inactive login denied |
| UM-08 | Delete User Account (blocked by requests / last admin) | attemptDeleteUser | Manual | Implemented, unverified | Policy still open; document |
| UM-09 | Log In (Active + hash verify + regenerate) | authenticate + loginUser | tests (partial) | Implemented, unverified | Session regenerate evidence |
| UM-10 | Log Out | logout.php + logoutSession | Manual | Implemented, unverified | Confirm cookie cleared |

## Security notes (from lecture + design)
- Passwords stored only as password_hash (PASSWORD_DEFAULT).
- All queries use prepared statements.
- Output escaped with htmlspecialchars.
- CSRF on POST forms.
- Session regenerated on login; destroyed on logout.
- Inactive users cannot authenticate.
- Public registration cannot create Administrators.
- Ownership enforced via students.user_id for Students; Admins see all; Tutors 403.

## Remaining limitations
- Exact password complexity beyond length 8 is open in the design.
- Permanent deletion policy is conservative (blocks if any related requests).
- Tests are provisional and have not been executed in this environment (no PHP runtime available in the generation sandbox).
- Existing sample data in tutoring.db is not auto-migrated; use a fresh database + seed.
- No rate limiting or account lockout.

## Next steps for the student
1. Run `php -l` on every changed PHP file.
2. Execute `php tests/user-management-tests.php` and capture output.
3. Manually walk through each UM with the sample accounts and record evidence.
4. Commit to the Lab 7 folder in the GitHub repository.
