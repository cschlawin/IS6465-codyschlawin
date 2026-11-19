<?php
/**
 * Create an Administrator account from the command line.
 *
 * Usage:
 *   php database/create-admin.php "Full Name" email@example.edu "password"
 *
 * Example:
 *   php database/create-admin.php "James Ho" bh@cc.com "1234567890112"
 *
 * Run from the lab7 directory (or pass the correct relative path).
 */

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

if ($argc < 4) {
    fwrite(STDERR, "Usage: php database/create-admin.php \"Full Name\" email@example.edu \"password\"\n");
    exit(1);
}

$fullName = trim($argv[1]);
$email = trim($argv[2]);
$password = $argv[3];

if ($fullName === '') {
    fwrite(STDERR, "Full name is required.\n");
    exit(1);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "A valid email address is required.\n");
    exit(1);
}
if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    exit(1);
}

require_once __DIR__ . '/../config/db.php';

$pdo = openDatabase();

// Ensure the users table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('Student', 'Tutor', 'Administrator')),
    status TEXT NOT NULL DEFAULT 'Active' CHECK(status IN ('Active', 'Inactive')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)");

// Ensure students.user_id exists (safe if the column is already present)
$cols = $pdo->query("PRAGMA table_info(students)")->fetchAll(PDO::FETCH_ASSOC);
$hasUserId = false;
foreach ($cols as $col) {
    if (($col['name'] ?? '') === 'user_id') {
        $hasUserId = true;
        break;
    }
}
if (!$hasUserId) {
    $pdo->exec('ALTER TABLE students ADD COLUMN user_id INTEGER REFERENCES users(user_id)');
}

$stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    fwrite(STDERR, "An account with that email already exists.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$insert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, 'Administrator', 'Active')");
$insert->execute([$fullName, $email, $hash]);

echo "Administrator created.\n";
exit(0);
