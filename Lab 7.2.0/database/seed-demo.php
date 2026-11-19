<?php
/** Optional local-only example accounts. Generates one-time random passwords. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit('Not found'); }
if (($argv[1] ?? '') !== '--demo') {
    fwrite(STDERR, "Usage: php database/seed-demo.php --demo\n"); exit(1);
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/userService.php';
$pdo = openDatabase();
foreach ([
    ['Example Student', 'student-demo@example.test', 'Student'],
    ['Example Tutor', 'tutor-demo@example.test', 'Tutor'],
    ['Example Administrator', 'admin-demo@example.test', 'Administrator'],
] as [$name, $email, $role]) {
    if (findUserByEmail($pdo, $email)) { echo "Already exists: $email\n"; continue; }
    $password = bin2hex(random_bytes(12)) . '!';
    if ($role === 'Administrator') {
        insertUser($pdo, $name, $email, password_hash($password, PASSWORD_DEFAULT), $role);
    } else {
        registerUser($pdo, ['full_name' => $name, 'email' => $email, 'password' => $password,
            'confirm_password' => $password, 'role' => $role]);
    }
    echo "$role / $email / password: $password\n";
}
echo "Save these generated passwords privately; do not use demo accounts on public deployments.\n";
