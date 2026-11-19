# Lab 7.2.0 — Forward and Backward Traceability Audit

**Baseline:** the uploaded *Peer Tutoring Hub User Management CRUD Design Package*, not assumptions inferred from code. Implementation is based on the supplied Lab 6 ZIP. The Week 8 lecture establishes explicit secure authentication and role/ownership rules. Auditing method follows the supplied `traceability-auditor.txt` skill. Tests are not marked *Verified* unless their checks were actually executed.

## Forward traceability

| Requirement | User-facing UI/controller | Business logic | SQL / persistent field | Test evidence | Status |
|---|---|---|---|---|---|
| **UM-01 Create Account** | `php/register.php` | `validateRegistration`, `registerUser` | `repositories/userPDO.php:insertUser`, `students.user_id`, `users.password_hash`, `users.status` | `tests/user-management-tests.php` UM01-A/B/C | Implemented; database test unverified |
| **UM-02 View Own Account** | `php/profile.php` | `requireLogin`, `currentUser` | `users` filtered by session user ID | UM02-A/B + manual role test | Implemented; database test unverified |
| **UM-03 Search Users** | `php/admin-users.php`, `admin-manage-user.php` | `requireRole`, `assertAdministrator` | `listUsers` prepared name/email/role/status query | UM03-A/B + manual 403 check | Implemented; database test unverified |
| **UM-04 Update Own Account** | `php/profile.php` POST | `updateOwnProfile` | `users.full_name`, `users.email`; linked `students` values | UM04-A/B | Implemented; database test unverified |
| **UM-05 Change Password** | `php/change-password.php` | `changePassword` | `users.password_hash` | UM05-A/B | Implemented; database test unverified |
| **UM-06 Update Role** | `php/admin-manage-user.php` | `setUserRole`, `assertAdministrator` | `users.role`, optionally `students.user_id` | UM06-A/B | Implemented; database test unverified |
| **UM-07 Activate/Deactivate** | `php/admin-manage-user.php` | `setUserStatus`, `assertAdministrator` | `users.status` | UM07-A/B | Implemented; database test unverified |
| **UM-08 Delete Account** | `php/admin-manage-user.php` (confirmation) | `attemptDeleteUser` | `linkedRequestCount`, `DELETE students`, `DELETE users` in transaction | UM08-A/B/C | **Provisional**: final retention policy not approved |
| **UM-09 Log In** | `php/login.php` | `authenticate`, `loginUser`, `safeLocalRedirect` | `findUserByEmail`, `password_verify` | UM09-A/B + manual test | Implemented; database test unverified |
| **UM-10 Log Out** | `php/logout.php` POST + nav form | `verifyCsrfToken`, `logoutSession` | Invalidates session; no DB deletion | UM10-A + manual test | Implemented; browser test unverified |

## Existing use case regression traceability

| UC | Required behavior | Files | Tests | Status |
|---|---|---|---|---|
| UC01 Request tutoring | Course select, multiple topics, reason textarea, preferred time radio controls, student identification, Open status, edit and cancel Open only | `php/request-form.php`, `php/process-request.php`, `php/edit-request.php`, `php/update-request.php`, `php/delete-request.php`, `services/requestService.php`, `repositories/requestPDO.php` | `tests/validation-tests.php`, `tests/integration-tests.php` | Syntax reviewed; validation executed |
| UC02 View/filter requests | No filters, course, status, both, empty result; JOIN for student info | `php/requests.php`, `repositories/requestPDO.php:listRequests` | `tests/integration-tests.php` | Implemented; DB tests unverified |

## Backward traceability / architecture

- `users` and `students.user_id` are justified by the user-management design's proposed USER entity and the lecture's explicit schema relationship. Existing `tutoring_requests.student_id` is **unchanged**.
- Registration stores exactly one role and Active status; Administrator creation is through CLI tool `database/create-admin.php` rather than public registration.
- Role and ownership restrictions on tutoring requests are justified by Week 8 access matrix. Tutor access to request workflow is denied.
- Profile changes cannot modify role/status; administrative changes require verified administrator, CSRF token, and explicit confirmation for status/deletion.
- Database creation and migration are separated from request pages; SQL is in repositories; transactions live in services; HTML is in controllers/templates.
- The Lab 6 `topic` JSON convention with `reason` and `topics` is preserved. Legacy plain-string topics remain readable through `decodeTopic`.
- `references/` contains the source deliverables used. The supplied skills ZIP contains **three** instructions (application-builder, test-harness-generator, traceability-auditor), not a distinct file called security pack. Security controls come from the lecture and a separately authored security review.

## Findings and recommended actions

| Severity | Finding | Impact / next action |
|---|---|---|
| **Major** | Final UM-08 retention policy is explicitly unresolved in source | Confirm approval before treating the provisional deletion behavior as campus policy; never cascade-delete tutoring requests |
| **Major** | Historical Lab 6 student rows have no verified user ownership | Require administrative identity proof and manual reconciliation; never auto-assign old requests based on unverified email alone |
| **Major** | No shared-IP login rate limit or verification/reset workflow | Add protections prior to public deployment; avoid using this classroom app as production auth |
| **Major** | Database-backed and HTTP authorization tests still require execution in a PHP environment with PDO SQLite | Execute `bash tests/run-all.sh` and manual matrix; save actual output and screenshots |
| **Minor** | Exact minimum password complexity beyond 8 bytes is not defined | Confirm instructor policy; update `validPassword` and tests as needed |
| **Informational** | Code preserves Lab 6 architecture and legacy JSON mapping | Positive compatibility finding; keep regression suite |

## Evidence discipline

- **Verified:** actual local PHP syntax lint completed; original validation assertions were executed and printed PASS.
- **Implemented, unverified:** code and test scripts exist for UM-01..UM-10, but PDO SQLite is missing in the code-generation environment. Do not equate syntax success with integration success.
- **Provisional:** UM-08 final retention authority and historical record linking still need a policy decision.
