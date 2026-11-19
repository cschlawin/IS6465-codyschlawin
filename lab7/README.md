# Lab 7 — Authentication & User Management
IS 6465 Peer Tutoring Hub

Extends the Lab 6 application with user accounts, login/logout, role-based access, and ownership checks for the existing tutoring request workflow.

See `lab7-notes.md` for the full traceability matrix, setup instructions, and remaining limitations.

## Quick start
```bash
php php/seed-users.php          # create sample Admin / Student / Tutor
php -S localhost:8000
# visit http://localhost:8000/php/login.php
```

Sample passwords are documented in `php/seed-users.php` and the notes file.
