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

/*  Remove the comment to use the INSERT OR IGNORE statement to insert a sample student record into the students table. Initial statement
provides the location of the INSERT OR IGNORE statement, the second statement VALUES provides the values to be inserted into the students table. The INSERT OR IGNORE statement will insert the record only if it does not already exist in the table, preventing duplicate entries.

INSERT OR IGNORE INTO students (
        student_id, 
        student_name, 
        student_email
) 

VALUES (
        '1', 
        'Alice Johnson', 
        'alice.johnson@example.com'
);

INSERT OR IGNORE INTO tutoring_requests (
        student_id, 
        course, 
        topic, 
        preferred_date, 
        preferred_time, 
)

VALUES (
        '1', 
        'IS 6465', 
        'PHP arrays and forms', 
        '2026-11-03', 
        '14:00', 
);

*/