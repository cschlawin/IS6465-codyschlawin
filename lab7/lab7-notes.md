# Lab 7 — Authentication and Authorization for the Peer Tutoring Hub

IS 6465  
Student: Cody Schlawin  
Repository: cschlawin/IS6465-codyschlawin (lab7 folder)

## Part 1: Generate the Lab 7 Application

### AI prompt used
```
Lab 7 Authentication
Review Peer_Tutoring_Hub_User_Management_With_Diagrams.docx., skills pack.zip, lab6.zip document and IS6465_Week8_Authentication_Lecture_v2.1.
Generate code for all uses cases UM-01 to UM-10 in the Peer_Tutoring_Hub_User_Management_With_Diagrams.docx.
Use skill pack docs - application builder, test-harness-generator, traceability auditor, and security pack I provided. Provided are the lab6.zip containing the current application files and the current weeks reading handout as reference.
Export all documents into a consolidated zip file to be downloaded.
```

### Uploaded source files
- Peer_Tutoring_Hub_User_Management_With_Diagrams.docx
- skills pack.zip (Application Builder, Test Harness Generator, Traceability Auditor, Security Pack)
- lab6.zip
- IS6465_Week8_Authentication_Lecture_v2.1

### How Lab 7 extends Lab 6
Lab 6 provided the Peer Tutoring Hub tutoring-request workflow (students, tutoring_requests, form validation, and the three-tier structure of pages, services, and PDO repositories). Lab 7 keeps that architecture and adds user accounts. A new `users` table stores full name, unique email, password hash, role (Student, Tutor, or Administrator), and status (Active or Inactive). The existing `students` table is linked to `users` through `students.user_id`. New pages handle registration, login, logout, profile updates, and password changes. `includes/auth.php` adds session management, CSRF tokens, `requireLogin`, and `requireRole`. Existing request pages now require a logged-in Student and enforce ownership so a student can only edit or cancel their own Open requests. Administrators can list and manage users and view all requests.

### Example of generated code verified against the design / handout
The Authentication Reading Handout requires that the session ID be regenerated after a successful login and that protected pages check the current user. This matches `includes/auth.php`:

```php
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['role'] = $user['role'];
    csrfToken();
}

function requireLogin(PDO $pdo): array {
    $user = currentUser($pdo);
    if (!$user) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? 'requests.php'));
        exit;
    }
    return $user;
}
```

Password hashing also matches the handout: `userService.php` stores only `password_hash(..., PASSWORD_DEFAULT)` and checks logins with `password_verify`. Public registration cannot create an Administrator, which matches the design document for UM-01.

## Part 2: Set Up the Database

Commands used from `lab7/database`:

```bash
sqlite3 tutoring.db < setup-database.sql
sqlite3 tutoring.db < upgrade-to-user-management.sql
sqlite3 tutoring.db
```

At the SQLite prompt:

```text
.tables
.schema
SELECT * FROM sqlite_schema;
.quit
```

**Confirmation:** All three required tables appeared: `students`, `tutoring_requests`, and `users`.

Screenshot evidence: `.tables` output shows `students  tutoring_requests  users`. `.schema` shows the `users` table (user_id, full_name, email, password_hash, role, status, created_at), the `students` table with `user_id` and the foreign key to `users`, and `tutoring_requests` with the foreign key to `students`.

Note: Running the upgrade script on a database that already had `students.user_id` produced `Error: near line 33: duplicate column name: user_id`. That message means the column was already present; the upgrade was already applied. The three tables were confirmed with `.tables`.

## Part 3: Create an Administrator Account

Command structure used (from `lab7/database`):

```bash
php create-admin.php "Full Name" email@example.edu "password"
```

Actual command used with test data:

```bash
php create-admin.php "Chesty Puller" chesty@usmc.edu "************"
```

**Confirmation:** The script printed `Administrator created.`

- Test email used: chesty@usmc.edu
- Role assigned: Administrator
- Password length: at least 12 characters (actual password not recorded here)

## Part 4: Run the Application

From the lab7 project root:

```bash
php -S 0.0.0.0:8000
```

Browser URL opened: the Codespaces forwarded port 8000 URL (or `http://127.0.0.1:8000/php/login.php`).

The application opened successfully. Visiting a protected page such as `php/requests.php` or `php/request-form.php` without a session redirected the browser to `login.php`. That behavior comes from `requireLogin()` in `includes/auth.php`, which checks for a valid active session and, if none exists, sends the user to the login page.

## Part 5: Test the Student Workflow

| Test | What I Did | Expected Result | Actual Result | Pass or Fail |
|------|------------|-----------------|---------------|--------------|
| Register student account | Used Create Account with test name, email, and password | Student account is created | Account created; success message and link to log in appeared | Pass |
| Log in as student | Submitted the student email and password on login.php | Student can access protected student pages | Redirected to requests.php; nav showed student links | Pass |
| View or update profile | Opened My Profile and changed the display name | Profile information is displayed or saved | Updated name appeared on the profile page | Pass |
| Create tutoring request | Filled the request form (course, topics, time, date, reason) and submitted | A new request is created with status Open | Request appeared in the list with status Open | Pass |
| Edit Open request | Opened the Open request, changed the reason, and saved | Updated request information is displayed | Updated reason was shown on the request | Pass |
| View my requests | Opened the Requests page while logged in as the student | Student sees the student’s own requests | Only that student’s requests were listed | Pass |
| Log out | Clicked Log Out | Session ends and login page appears | Redirected to login.php with a logged-out message; protected pages redirected to login | Pass |

Screenshot: insert one screenshot of registration, the student profile, a submitted tutoring request, or the student request list.

## Part 6: Test the Administrator Workflow

Logged in as chesty@usmc.edu (Administrator), viewed the user list on admin-users.php, viewed tutoring requests, then logged out.

**What is authentication in this application?**  
Authentication is verifying who the user is. In this application it happens at login: `authenticate()` in `userService.php` looks up the user by email, checks that the account is Active, and uses `password_verify` to compare the submitted password with the stored password hash. A successful match calls `loginUser()`, which stores the user id and role in the session.

**What is authorization in this application?**  
Authorization is deciding what an already authenticated user is allowed to do. It is enforced by `requireRole()` and ownership checks. A Student can only see and edit their own Open requests. An Administrator can open admin-users.php and admin-manage-user.php and can view all requests. A user with the wrong role receives a 403 Access denied response.

**How is student access different from administrator access?**  
A Student can register, log in, view and update their own profile, create a tutoring request, edit or cancel their own Open requests, and log out. An Administrator can do those account actions and can also list all users, change roles and status, and view requests across all students. The Student role is blocked from the admin pages; the Administrator role is not limited to a single student’s requests.

**Which functions or pages should be limited to administrators?**  
`php/admin-users.php` (view and search the user list) and `php/admin-manage-user.php` (change role, activate or deactivate, delete). Both call `requireRole($user, ['Administrator'])`.

**What happens after logout if a user tries to visit a protected page directly?**  
Logout destroys the session. Visiting a protected page such as requests.php or profile.php then calls `requireLogin()`, finds no valid session, and redirects the browser to login.php.

Screenshot: insert one screenshot of the administrator user list or the all-requests view.

## Part 7: Trace Registration and Login

### Student registration workflow
- Which file displays the Create Account form? `php/register.php` (the form is rendered in the same file when the request is not a successful POST).
- Which file receives the submitted registration form? `php/register.php` (the form posts back to itself).
- Which file or function creates and verifies the CSRF token? `includes/auth.php` — `csrfToken()` creates it and `verifyCsrfToken()` checks the posted token.
- Which service validates the registration information? `registerUser()` / `validateRegistration()` in `services/userService.php`.
- Where is the password hashed? In `registerUser()` in `services/userService.php`, with `password_hash($data['password'], PASSWORD_DEFAULT)`.
- Which repository files insert user and student data? `repositories/userPDO.php` (`insertUser`, `linkStudentToUser`) and `repositories/studentPDO.php` (`insertStudentIfAbsent`).
- Which database tables receive records when a student registers? `users` (the account) and `students` (the linked student profile).

Flow: Browser → register.php → CSRF check → userService registerUser → userPDO insertUser and studentPDO insertStudentIfAbsent → SQLite (`users` and `students`).

### User login workflow
- Which file displays the login form? `php/login.php`.
- Which file receives the submitted login credentials? `php/login.php` (posts back to itself).
- Which repository retrieves a user by email address? `findUserByEmail()` in `repositories/userPDO.php`.
- Where is the submitted password verified? In `authenticate()` in `services/userService.php`, with `password_verify`.
- What session actions occur after successful login? `loginUser()` in `includes/auth.php` regenerates the session id, stores `user_id` and `role` in the session, and ensures a CSRF token exists.
- Why should the application regenerate the session ID after login? To prevent session fixation. An attacker who set a session id before login cannot continue using that same id after the user authenticates.
- How does the application determine whether the logged-in user is a student or administrator? The role is stored in the session at login and is also re-read from the `users` table by `currentUser()`. Pages then call `requireRole()` with the allowed role list.

Flow: Browser → login.php → CSRF check → userService authenticate → userPDO findUserByEmail → password_verify → loginUser → SQLite (`users`).

### Why hashing belongs in the application layer
Password hashing and verification should happen in the application/service layer, not in the presentation page or the database setup script. The presentation page should only collect and display data. The service layer is the right place because it can enforce the password rules, call `password_hash` / `password_verify`, and keep the hash out of the page and out of SQL that a developer might accidentally log or commit. The database stores only the finished hash; it should not contain the plaintext password or the hashing logic.

## Part 8: Run the Automated Tests

From the lab7 project root:

```bash
php tests/validation-tests.php
php tests/user-management-tests.php
php tests/integration-tests.php
```

Paste the complete browser or terminal output from each command below. The three scripts produce an HTML results table when opened in the browser.

**validation-tests.php:** expected Pass — 10 of 10 (preferred time, topics, reason).  
*(paste complete output)*

**user-management-tests.php:** expected Pass — registration, duplicate email, admin denial, login, password change, profile update, inactive login.  
*(paste complete output)*

**integration-tests.php:** expected Pass — create, filter, edit, cancel, foreign key.  
*(paste complete output)*

If any integration test failed: name the failed test, describe what it checked, and note what code, requirement, or database behavior may need investigation. Do not change the test files unless the instructor directs you to do so.

## Part 9: Use the AI Skills Pack Process

### Application Builder
Prompt: Explain how a student registration request moves from register.php through authentication checks, PHP services, repositories, PDO, and SQLite in the Peer Tutoring Hub application. Identify the responsibility of each file or layer.

Response summary: The AI described the path Browser → register.php → CSRF check → userService → userPDO/studentPDO → SQLite, with the page handling the form, the service validating and hashing, and the repositories performing the inserts.

Evaluation: One accurate statement is that the password is hashed in the service layer before the repository insert. I verified this in `services/userService.php`: `registerUser()` calls `password_hash` and then passes only the hash to `insertUser()`. The page never sees the hash, and the repository never sees the plaintext password.

### Traceability Auditor

| Requirement | Presentation Page | Authentication or Authorization | Service or Validation | Repository or SQL | Database Table | Test Evidence |
|-------------|-------------------|--------------------------------|-----------------------|-------------------|----------------|---------------|
| Student registers an account | php/register.php | CSRF via verifyCsrfToken; public form | userService.php registerUser / validateRegistration | userPDO insertUser; studentPDO insertStudentIfAbsent | users, students | user-management-tests.php UM-01 |
| Student logs in | php/login.php | CSRF; Active status check; session created | userService.php authenticate (password_verify) | userPDO findUserByEmail | users | user-management-tests.php UM-09 |
| Student creates a tutoring request | php/request-form.php → process-request.php | requireLogin + requireRole Student; CSRF | requestService.php submitRequest / validateSubmission | requestPDO insert; studentPDO lookup | students, tutoring_requests | integration-tests.php UC01 |
| User logs out | php/logout.php (POST) | CSRF; logoutSession destroys session | auth.php logoutSession | none | none | Manual: protected page redirects to login after logout |

### Test Harness Generator
Suggested test: An Inactive student cannot log in.

Already covered? Yes. `user-management-tests.php` sets a user to Inactive and asserts that `authenticate()` returns null.

How I would test it: Manually, an administrator sets the student to Inactive on admin-manage-user.php, then the student tries to log in and should see “Invalid email, password, or inactive account.”

Is the AI test appropriate? Yes. It matches UM-07 and the lecture requirement that only Active accounts can authenticate.

### Skills Pack Reflection
The AI tool was useful for mapping the registration and login paths across the page, service, and repository layers and for suggesting a test that matched an existing requirement. It also produced a traceability outline that matched the real file names. Developers still have to verify every AI explanation against the source, because the AI can invent a file name, a function, or a table column that does not exist. Checking `registerUser()`, `authenticate()`, and the test scripts confirmed which statements were accurate and which details needed to be corrected before they were used in the notes.

## Part 10: Inspect the Database

Query run from `lab7`:

```bash
sqlite3 database/tutoring.db
```

```sql
SELECT
    u.user_id,
    u.full_name,
    u.email,
    u.role,
    u.status,
    s.student_id,
    s.student_name,
    s.student_email
FROM users AS u
LEFT JOIN students AS s
    ON u.user_id = s.user_id
ORDER BY u.user_id;
```

**What the LEFT JOIN does:**  
It returns every row from `users` and the matching row from `students` when `students.user_id` equals `users.user_id`. If a user has no matching student row, the student columns are NULL and the user row is still shown.

**How users connects to students:**  
`students.user_id` is a foreign key that references `users.user_id`. A Student account is linked by that column after registration. The join condition `ON u.user_id = s.user_id` is the connection.

**Why account information is stored separately from student profile information:**  
Account data (password hash, role, status) belongs to any user who can log in. Student profile data (student_id used by tutoring requests) is needed only for people who submit requests. Separating them lets Administrators and future Tutor accounts exist without a student profile, and keeps the password hash out of the student table.

**Why an administrator may not have a matching row in students:**  
`create-admin.php` inserts only into `users` with role Administrator. Registration creates a `students` row only when the role is Student. An administrator therefore has a `users` row and a NULL student side in the LEFT JOIN.
