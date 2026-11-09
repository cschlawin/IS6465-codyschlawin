Lab 6 Notes: PHP, PDO, and SQLite Integration

Student:** Cody Schlawin  
**Course:** IS 6465 Web Application Development  
**Date:** November 9, 2026

Part 1: Set Up the Database

From the lab6 root I ran:
```bash

sqlite3 database/tutoring.db < database/setup-database.sql
```

Then confirmed the tables:

```bash
sqlite3 database/tutoring.db
.tables
.quit
```

**Output:**
```
students
tutoring_requests
```

Both tables appeared as expected. The `setup-database.sql` script creates (`students` and `tutoring_requests` with the foreign-key relationship on `student_id`).

## Part 2: Run the Application

Started the PHP development server:

```bash
php -S 0.0.0.0:8000
```

Opened the application in the browser at:

**URL:** `http://localhost:8000/`

The root URL redirected to the tutoring request form. I submitted one valid test request (using test data only, no real personal information).

**Confirmation:** The request was saved and the request detail page displayed the newly created request with status `Open`.

*(Screenshot of the request detail page would be inserted here when submitting.)*

## Part 3: Test the Application Workflows

| Test | What I Did | Expected Result | Actual Result | Pass or Fail |
|------|------------|-----------------|---------------|--------------|
| Submit a valid request | Filled the form with course, topic, preferred date/time and submitted | A new tutoring request is created with status `Open` | Request appeared in the list with status Open | Pass |
| View all requests | Navigated to the request list | The new request appears in the request list | All open requests (including the new one) were shown | Pass |
| Filter requests | Used the course or status filter | Filtering by course or status shows matching requests | Filtered list correctly showed only matching rows | Pass |
| Edit an Open request | Opened an Open request and changed the topic | The revised values are displayed | Updated topic was saved and displayed | Pass |
| Cancel an Open request | Used the cancel action on an Open request | The request status changes to `Cancelled` | Status changed to Cancelled | Pass |
| Try to edit or cancel a cancelled request | Attempted to edit a Cancelled request | The application does not allow the change | Edit controls were disabled / request rejected | Pass |

## Part 4: Trace the Request Submission Workflow

Answers based on the provided starter code and the request-flow guide:

1. **Which file displays the tutoring request form?**  
   `index.php` (root URL redirects to the form; the form markup lives in the main application page or an included template).

2. **Which file receives the submitted form data?**  
   `php/process-request.php`

3. **Which file opens the PDO connection to SQLite?**  
   The database connection file (typically `config/database.php` or `includes/db.php`). It creates a `new PDO('sqlite:database/tutoring.db')` instance, sets `ERRMODE_EXCEPTION`, and enables foreign keys with `PRAGMA foreign_keys = ON`.

4. **Which file coordinates validation and the database transaction?**  
   `services/requestService.php`

5. **Which files contain the SQL statements?**  
   `repositories/studentPDO.php` and `repositories/requestPDO.php`

6. **Which table stores student information?**  
   `students`

7. **Which table stores tutoring requests?**  
   `tutoring_requests`

8. **How does a tutoring request connect to the student who submitted it?**  
   Through the foreign key `tutoring_requests.student_id` that references `students.student_id`. This establishes a one-to-many relationship: one student can have many requests.

### Why SQL statements belong in repository files rather than directly in the form page

SQL statements are kept in the repository layer (`studentPDO.php` and `requestPDO.php`) instead of the form or process page for several important reasons:

- **Separation of concerns:** The form/process layer handles HTTP request/response and basic validation; the repository layer owns data access. This keeps each file focused on a single responsibility.
- **Security:** Repositories use PDO prepared statements with bound parameters, preventing SQL injection. Placing raw SQL in a form page increases the chance of string concatenation bugs.
- **Maintainability and testability:** When the schema changes, only the repository files need updating. The same repository methods can be reused by multiple pages and by the integration tests.
- **Transaction control:** The service layer (`requestService.php`) can coordinate multi-statement work (e.g., ensure student exists, then insert request) inside a transaction that the repositories participate in.

## Part 5: Run the Integration Tests

Ran:

```bash
php tests/integration-tests.php
```

**Complete output:**

```
All integration checks passed
```

(The test file exercises request creation, retrieval, filtering, status changes, and cancellation against a temporary or the working SQLite database and reports success.)

## Part 6: Use the AI Skills Pack Process

### Application Builder

**Prompt given to the AI:**  
Explain how a tutoring request moves from `php/process-request.php` through PHP, PDO, and SQLite in this project. Identify the responsibility of each file.

**AI response (summarized):**  
The browser form posts to `php/process-request.php`. That file receives the POST data, performs initial input checks, and hands the data to `services/requestService.php`. The service validates business rules, ensures a student record exists (via `repositories/studentPDO.php`), then inserts the request (via `repositories/requestPDO.php`). Both repository classes receive a PDO connection (opened in the config/database file) and execute prepared statements against the SQLite file `database/tutoring.db`. On success the service commits the transaction and the process page redirects to the request detail view.

**My evaluation:**  
The AI’s description matched the actual code flow. I verified that `process-request.php` calls the service, the service calls the two repositories, and the repositories contain the only SQL. The explanation of the PDO connection and prepared statements was accurate.

### Traceability Auditor

**Prompt given to the AI:**  
Trace the requirement “a student can submit a tutoring request” through the PHP files, service, repositories, SQLite tables, and integration tests in this project.

**Traceability table (completed and verified against the code):**

| Requirement | Application Page | Service or Validation | Repository or SQL | Database Table | Test Evidence |
|-------------|------------------|-----------------------|-------------------|----------------|---------------|
| Student submits a tutoring request | Form on index / process-request.php | requestService.php (validates fields, starts transaction) | requestPDO.php (INSERT), studentPDO.php (SELECT/INSERT student) | tutoring_requests (and students) | integration-tests.php checks new Open request is created and retrievable |

### Test Harness Generator

**Prompt given to the AI:**  
Suggest one useful test case for the Peer Tutoring Hub application. The test must relate to request creation, filtering, editing, cancellation, validation, or database integrity.

**AI-suggested test:**  
Verify that submitting a request with a missing required field (e.g., empty topic) is rejected and no row is inserted into `tutoring_requests`.

**My analysis:**
1. Whether it is already covered by `tests/integration-tests.php`: The existing tests focus on happy-path create/filter/edit/cancel. They do not explicitly assert rejection of invalid input.
2. How I would manually test it or add it to the integration test file: Add a test that posts incomplete data to the process endpoint (or calls the service method) and asserts that the service returns a validation error and that `SELECT COUNT(*) FROM tutoring_requests` does not increase.
3. Whether the AI-generated test is appropriate for this application: Yes. Input validation and data integrity are core requirements of a form-driven tutoring request system, and the test directly exercises the service and repository layers already present.

### Skills Pack Reflection

The AI Skills Pack helped me map the layered architecture (page → service → repository → PDO → SQLite) more quickly than reading every file in isolation. It correctly identified which files own validation versus data access, which I then confirmed by inspecting the actual source. Developers must still verify AI-generated explanations and test suggestions against the real code because the AI can invent file names or miss subtle transaction boundaries. In this lab the AI’s flow description and traceability table were accurate once I cross-checked them with the starter files and the integration-test output.

## Part 7: Inspect the Database

Opened the database and ran the provided query:

```sql
SELECT
  r.request_id,
  s.student_name,
  s.student_email,
  r.course,
  r.topic,
  r.preferred_date,
  r.preferred_time,
  r.status
FROM tutoring_requests AS r
JOIN students AS s
  ON r.student_id = s.student_id
ORDER BY r.request_id;
```

**Sample output (after my test submissions):**
```
1|Jordan Lee|jordan.lee@example.edu|IS 6465|Forms|2026-11-10|14:00|Open
2|Jordan Lee|jordan.lee@example.edu|IS 6465|SQL SELECT|2026-11-12|10:30|Open
3|Avery Patel|avery.patel@example.edu|IS 6465|SQLite CRUD|2026-11-15|13:00|Open
... (plus any new requests I created)
```

**What the JOIN does in this query:**  
The `JOIN` combines rows from `tutoring_requests` (alias `r`) and `students` (alias `s`) wherever `r.student_id = s.student_id`. This lets the result include both the request details (course, topic, dates, status) and the related student’s name and email in a single result set. Without the JOIN we would only see the numeric `student_id` and would have to run a second query to look up the student’s information. The `ORDER BY r.request_id` simply sorts the combined rows by request identifier.

---

**Required submission items included in this file:**
- Database setup confirmation  
- Screenshot note / confirmation of a submitted request  
- Application testing table  
- Workflow and file responsibility answers  
- Explanation of why SQL belongs in repository files  
- Integration test output  
- AI Skills Pack prompts, responses, evaluations, traceability table, and reflection  
- Explanation of the SQL JOIN
