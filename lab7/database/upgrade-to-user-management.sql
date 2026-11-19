-- upgrade-to-user-management.sql
-- Migrates an existing Lab 6 Peer Tutoring Hub database to Lab 7.
--
-- From the lab7 directory:
--   sqlite3 database/tutoring.db < database/upgrade-to-user-management.sql
--
-- What this does
--   1. Creates the users table if it does not exist.
--   2. Adds students.user_id if the column is missing.
--
-- Re-running this file is safe for step 1.
-- Step 2 will report "duplicate column name: user_id" if the column
-- is already present. That message means the upgrade is already applied;
-- it is not an error you need to fix.
--
-- Existing student and tutoring_request rows are left unchanged.
-- After the upgrade, create an administrator with:
--   php database/create-admin.php "Full Name" email@example.edu "password"

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('Student', 'Tutor', 'Administrator')),
    status TEXT NOT NULL DEFAULT 'Active' CHECK(status IN ('Active', 'Inactive')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- One-time column addition. Ignore "duplicate column name: user_id" on re-run.
ALTER TABLE students ADD COLUMN user_id INTEGER REFERENCES users(user_id);
