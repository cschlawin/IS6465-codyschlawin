PRAGMA foreign_keys = ON;

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
    status TEXT NOT NULL DEFAULT 'Open'
        CHECK (status IN ('Open', 'Cancelled', 'Assigned', 'Completed')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);