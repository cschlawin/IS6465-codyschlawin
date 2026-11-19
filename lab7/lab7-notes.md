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

Browser URL opened: *(fill in)*

The application opened successfully: *(yes/no)*

What happened when I first accessed a protected page without being logged in:
*(Expected: redirected to login.php. Record what you observed.)*

## Part 5: Test the Student Workflow

| Test | What I Did | Expected Result | Actual Result | Pass or Fail |
|------|------------|-----------------|---------------|--------------|
| Register student account | | Student account is created | | |
| Log in as student | | Student can access protected student pages | | |
| View or update profile | | Profile information is displayed or saved | | |
| Create tutoring request | | A new request is created with status Open | | |
| Edit Open request | | Updated request information is displayed | | |
| View my requests | | Student sees the student’s own requests | | |
| Log out | | Session ends and login page appears | | |

Screenshot: *(insert one screenshot of registration, profile, a submitted request, or the request list)*

## Part 6: Test the Administrator Workflow

Log in as chesty@usmc.edu (Administrator), view the user list, view tutoring requests, then log out.

**What is authentication in this application?**
*(Answer after testing)*

**What is authorization in this application?**
*(Answer after testing)*

**How is student access different from administrator access?**
*(Answer after testing)*

**Which functions or pages should be limited to administrators?**
*(Answer after testing)*

**What happens after logout if a user tries to visit a protected page directly?**
*(Answer after testing)*

Screenshot: *(insert one screenshot of the user list or all tutoring requests while logged in as administrator)*

## Part 7: Trace Registration and Login

### Student registration workflow
- Which file displays the Create Account form?
- Which file receives the submitted registration form?
- Which file or function creates and verifies the CSRF token?
- Which service validates the registration information?
- Where is the password hashed?
- Which repository files insert user and student data?
- Which database tables receive records when a student registers?

### User login workflow
- Which file displays the login form?
- Which file receives the submitted login credentials?
- Which repository retrieves a user by email address?
- Where is the submitted password verified?
- What session actions occur after successful login?
- Why should the application regenerate the session ID after login?
- How does the application determine whether the logged-in user is a student or administrator?

### Why hashing belongs in the application layer
*(Write 2–4 sentences)*

## Part 8: Run the Automated Tests

From the lab7 project root:

```bash
php tests/validation-tests.php
php tests/user-management-tests.php
php tests/integration-tests.php
```

Paste the complete output from each command below.

**validation-tests.php:** passed / failed
*(paste output)*

**user-management-tests.php:** passed / failed
*(paste output)*

**integration-tests.php:** passed / failed
*(paste output)*

If any integration test failed: failed test name, what it checked, and what may need investigation.

## Part 9: Use the AI Skills Pack Process

### Application Builder
Prompt:
Response:
Evaluation (one accurate statement verified against actual code):

### Traceability Auditor

| Requirement | Presentation Page | Authentication or Authorization | Service or Validation | Repository or SQL | Database Table | Test Evidence |
|-------------|-------------------|--------------------------------|-----------------------|-------------------|----------------|---------------|
| Student registers an account | | | | | | |
| Student logs in | | | | | | |
| Student creates a tutoring request | | | | | | |
| User logs out | | | | | | |

### Test Harness Generator
Suggested test:
Already covered?
How I would test it:
Is the AI test appropriate?

### Skills Pack Reflection
*(3–5 sentences)*

## Part 10: Inspect the Database

Query run:

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
**How users connects to students:**
**Why account information is stored separately from student profile information:**
**Why an administrator may not have a matching row in students:**
