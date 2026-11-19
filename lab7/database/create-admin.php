<?php
/** Create a private administrator via CLI. Never allow browser access. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit('Not found'); }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/userService.php';
$name = trim((string)($argv[1] ?? ''));
$email = normalizedEmail((string)($argv[2] ?? ''));
if ($name === '' || userTextLength($name) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php database/create-admin.php 'Admin Name' admin@example.edu\n");
    exit(1);
}
// A no-echo password prompt on Unix or password via private stdin piping.
if (stream_isatty(STDIN) && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    fwrite(STDERR, 'New administrator password: ');
    system('stty -echo');
    try { $password = rtrim((string)fgets(STDIN), "\r\n"); }
    finally { system('stty echo'); fwrite(STDERR, "\n"); }
} else {
    fwrite(STDERR, "Enter administrator password using stdin (not a command-line argument):\n");
    $password = rtrim((string)fgets(STDIN), "\r\n");
}
if (!validPassword($password)) { fwrite(STDERR, "Password must be 8 to 1024 bytes.\n"); exit(1); }
$pdo = openDatabase();
if (findUserByEmail($pdo, $email)) { fwrite(STDERR, "An account already uses this email.\n"); exit(1); }
$id = insertUser($pdo, $name, $email, password_hash($password, PASSWORD_DEFAULT), 'Administrator');
echo "Created Active Administrator ID $id.\n";
