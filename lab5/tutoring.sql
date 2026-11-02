-- Lab 5: Build and Query the Peer Tutoring Hub Database
-- File: tutoring.sql
-- Run with: sqlite3 tutoring.db < tutoring.sql
-- Or interactively in the SQLite shell: sqlite3 tutoring.db
-- Then paste or .read tutoring.sql

-- Enable foreign key enforcement (important for integrity)
PRAGMA foreign_keys = ON;


-- Part 1: Create the database tables

CREATE TABLE IF NOT EXISTS students (
  student_id INTEGER PRIMARY KEY,
  student_name TEXT NOT NULL,
  student_email TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS tutoring_requests (
  request_id INTEGER PRIMARY KEY,
  student_id INTEGER NOT NULL,
  course TEXT NOT NULL,
  topic TEXT NOT NULL,
  preferred_date TEXT NOT NULL,
  preferred_time TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'Open',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(student_id)
);

-- After creating the tables, run these commands in the SQLite shell:
-- .tables
-- .schema


-- Part 2: Insert students and tutoring requests

-- Insert each student once (OR IGNORE prevents duplicates on re-run)
INSERT OR IGNORE INTO students (student_name, student_email)
VALUES
  ('Jordan Lee', 'jordan.lee@example.edu'),
  ('Avery Patel', 'avery.patel@example.edu');

-- Insert tutoring requests. 
-- On a fresh database these will receive student_id 1 (Jordan) and 2 (Avery).
-- If your student_ids differ, replace the numbers after checking:
-- SELECT student_id, student_name, student_email FROM students;

-- Jordan Lee - Request 1: Forms
INSERT INTO tutoring_requests (student_id, course, topic, preferred_date, preferred_time)
VALUES (1, 'IS 6465', 'Forms', '2026-11-10', '14:00');

-- Jordan Lee - Request 2: SQL SELECT
INSERT INTO tutoring_requests (student_id, course, topic, preferred_date, preferred_time)
VALUES (1, 'IS 6465', 'SQL SELECT', '2026-11-12', '10:30');

-- Avery Patel - Request: SQLite CRUD
INSERT INTO tutoring_requests (student_id, course, topic, preferred_date, preferred_time)
VALUES (2, 'IS 6465', 'SQLite CRUD', '2026-11-15', '13:00');


-- Part 3: Read the stored data

-- 1. Display all rows in students
SELECT * FROM students;

-- 2. Display all rows in tutoring_requests
SELECT * FROM tutoring_requests;

-- 3. Display only requests with a status of Open
SELECT * FROM tutoring_requests WHERE status = 'Open';

-- 4. Display one request by its request_id using a WHERE clause
-- (request_id 1 is Jordan's first request on a fresh DB)
SELECT * FROM tutoring_requests WHERE request_id = 1;

-- 5. JOIN query: request list with student name and email, ordered by preferred_date
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
JOIN students AS s ON r.student_id = s.student_id
ORDER BY r.preferred_date;


-- Part 4: Update one request

-- Choose one of Jordan's open requests (request_id = 1) and revise its topic and preferred time
UPDATE tutoring_requests
SET topic = 'Forms and validation',
    preferred_time = '15:30'
WHERE request_id = 1;

-- Change Avery's request status to Cancelled (request_id = 3 on a fresh DB)
UPDATE tutoring_requests
SET status = 'Cancelled'
WHERE request_id = 3;

-- Part 5: Delete one test request

-- Insert one additional request that is clearly a test record
INSERT INTO tutoring_requests (student_id, course, topic, preferred_date, preferred_time)
VALUES (1, 'IS 6465', 'Delete Practice', '2026-12-01', '09:00');

-- Delete only that test request by its request_id
-- On a fresh DB this will be request_id = 4. Verify first with:
-- SELECT request_id, topic FROM tutoring_requests WHERE topic = 'Delete Practice';
DELETE FROM tutoring_requests
WHERE request_id = 4;

-- Verify the test record is gone while other requests remain
SELECT * FROM tutoring_requests;