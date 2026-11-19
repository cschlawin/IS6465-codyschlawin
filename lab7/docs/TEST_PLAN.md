# Lab 7.2.0 — Requirements-Based Test Plan

**Sources:** Peer Tutoring Hub User Management CRUD Design Package, sections *Detailed use case specifications* and *Traceability and acceptance tests*; Week 8 Authentication lecture, pages covering session guard, access matrix, ownership, CSRF, and minimum lab checklist; supplied `test-harness-generator.txt`.

**Test isolation:** `tests/user-management-tests.php` and the retained Lab 6 regression tests create `sqlite::memory:` databases and load `database/setup-database.sql`. They **do not use or modify** `database/tutoring.db`. PHP's `pdo_sqlite` extension is required.

| Test ID | Use case | Fixture / input | Expected result | Evidence checked |
|---|---|---|---|---|
| UM01-A | UM-01 | Valid Student/Tutor signup | Active user, hash not plaintext, student linked | `registerUser`, `findUserById`, `findStudentByUserId` |
| UM01-B | UM-01 | Duplicate email, Administrator self-registration, mismatched passwords | Reject without changes | Exceptions, absent user |
| UM01-C | UM-01 | Legacy Student email without verified user link | No account takeover | Rollback and absent user |
| UM02-A | UM-02 | Authenticated Student session | Own profile loaded, hash withheld | `loginUser`, `currentUser` |
| UM02-B | UM-02 | Session idle over 1,200 seconds | Login required | `currentUser` expires session |
| UM03-A | UM-03 | Admin role/filtered search | Exactly matching rows | `listUsers` |
| UM03-B | UM-03 | No matches / Student requester | Empty results / administrative action rejected | `listUsers`, `assertAdministrator` |
| UM04-A | UM-04 | Update own name/email | Name/email saved, Student identity synchronized | DB re-read |
| UM04-B | UM-04 | Duplicate email / attempt role update through profile | Reject conflict / role unchanged | DB re-read |
| UM05-A | UM-05 | Incorrect old password or mismatch | Reject | `changePassword` exception |
| UM05-B | UM-05 | Correct old and matching new password | New works, old fails | `authenticate` |
| UM06-A | UM-06 | Admin changes Tutor to Student | Role updated, Student link created | DB re-read |
| UM06-B | UM-06 | Student attempts change or last admin demoted | Reject, preserve role | Exceptions and DB re-read |
| UM07-A | UM-07 | Admin deactivates and reactivates Tutor | Inactive login fails, later login succeeds | DB status + `authenticate` |
| UM07-B | UM-07 | Student attempts change or last admin deactivated | Reject | Exceptions |
| UM08-A | UM-08 | Attempt to delete user with requests | Reject, all rows retained | DB re-read |
| UM08-B | UM-08 | Delete unreferenced Tutor | Deletes account | DB re-read |
| UM08-C | UM-08 | Attempt to delete last admin | Reject | Exception |
| UM09-A | UM-09 | Successful credentials | New session ID, CSRF token, session identity | `session_id`, `currentUserId` |
| UM09-B | UM-09 | Inactive user, off-site redirect | Reject authentication and external redirect | `authenticate`, `safeLocalRedirect` |
| UM10-A | UM-10 | Authenticated user logs out | Cleared session and destroyed server session | `logoutSession`, `session_status` |
| REG-A | Lab 6 UC01 | Submit, edit, cancel | Request persists and status transitions | `tests/integration-tests.php` |
| REG-B | Lab 6 UC02 | Course/status filters | Matching rows or empty state | `tests/integration-tests.php` |
| SEC-A | Week 8 | Two different students and one request | Other student cannot read/edit/cancel | Owner-restricted SQL and service assertions |

## Manual browser authorization checklist (required before submission)

Run the app, create Student A, Student B, Tutor, and Administrator accounts. Use **separate browser sessions/incognito windows** to distinguish cookies. Record actual HTTP status, screenshot/response, account role, request owner, and expected result. These are **manual checks, not claimed completed tests**.

1. Open `php/profile.php` logged out: redirected to login; afterward the profile displays the signed-in account only (UM-02).
2. Log in as Tutor and open `php/requests.php` directly: **403**, no request data exposed (Week 8).
3. Log in as Student B and request Student A's `request_id` through `request-details.php`, `edit-request.php`, and POST actions: **404** (or no change), no request data disclosed (Week 8).
4. Logged-in student submits a POST to `php/update-request.php` without `csrf_token`: **403**, unchanged DB row (Week 8).
5. From Student session, open `php/admin-users.php` and `php/admin-manage-user.php?id=1`: **403** (UM-03/06/07/08).
6. From Admin session, search for a Tutor, deactivate, confirm login fails, reactivate; verify confirmation before status changes (UM-03/07/09).
7. With an account that has requests, attempt admin deletion; verify block and no cascade (UM-08).
8. Submit logout POST then reload `php/profile.php`: redirect to login; old cookie must not restore user (UM-10).
9. Verify idle session after 20 minutes of no activity redirects to login (UM-02/09).
10. Verify malformed registration, duplicate email and Administrator role POST leave database unchanged (UM-01).

## Expected CLI output

```
PASS: UM-01 Student is Active
...
PASS: UM-10 server session destroyed
Results: <number> passed, 0 failed
```

**No execution claims are made solely because tests exist.** Run `bash tests/run-all.sh` in your actual Codespaces environment and save the console output. Exit status `0` means all invoked checks passed; nonzero means an assertion failed or setup could not run.

## Known gaps

- The automated harness exercises services, repositories, and session helpers, **not a real browser**. CSRF rejection status codes and page-role responses require the manual checklist.
- Authentication rate limiting, password reset, and email verification are outside the supplied approved scope. These would be essential before exposing a production system to the internet.
- Historical Lab 6 records without verified account linkage remain unowned by a Student account until an administrator reconciles them.
- UM-08 retention is unresolved in the design, so the implemented block-on-dependent-requests rule is explicitly **provisional**.
