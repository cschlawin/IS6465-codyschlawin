<?php
// php/seed-users.php — one-time helper to create sample accounts (run from CLI or browser once)
// Usage: php seed-users.php   or visit once then delete
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../repositories/userPDO.php';
require_once __DIR__ . '/../repositories/studentPDO.php';

$pdo = openDatabase();

$samples = [
    ['Admin User', 'admin@example.edu', 'xyzxyzxyz', 'Administrator'],
    ['Alex Student', 'alex@example.edu', 'xyzxyzxyz', 'Student'],
    ['Taylor Tutor', 'taylor@example.edu', 'xyzxyzxyz', 'Tutor'],
];

foreach ($samples as [$name, $email, $pass, $role]) {
    if (!findUserByEmail($pdo, $email)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $uid = insertUser($pdo, $name, $email, $hash, $role);
        echo "Created $role: $email (id $uid)\n";
        if ($role === 'Student') {
            insertStudentIfAbsent($pdo, $name, $email);
            $st = findStudentByEmail($pdo, $email);
            if ($st) {
                linkStudentToUser($pdo, (int)$st['student_id'], $uid);
            }
        }
    } else {
        echo "Already exists: $email\n";
    }
}
echo "Done. Passwords are the ones shown in the script source.\n";
