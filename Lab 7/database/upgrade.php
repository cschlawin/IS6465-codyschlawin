<?php
/** Idempotent migration for existing Lab 6 database; do not discard old rows. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}
require_once __DIR__ . '/../config/db.php';
$pdo = openDatabase();
try {
    $pdo->beginTransaction();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INTEGER PRIMARY KEY, full_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE COLLATE NOCASE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL CHECK(role IN ('Student', 'Tutor', 'Administrator')),
        status TEXT NOT NULL DEFAULT 'Active' CHECK(status IN ('Active','Inactive')),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $columns = $pdo->query('PRAGMA table_info(students)')->fetchAll();
    if (!$columns) {
        throw new RuntimeException('No students table. For a NEW DB use database/init.php.');
    }
    if (!in_array('user_id', array_column($columns, 'name'), true)) {
        $pdo->exec('ALTER TABLE students ADD COLUMN user_id INTEGER REFERENCES users(user_id) ON DELETE RESTRICT');
    }
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_students_user_id ON students(user_id)');
    $pdo->commit();
    echo "Upgrade successful. Existing tutoring records preserved.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Upgrade failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
