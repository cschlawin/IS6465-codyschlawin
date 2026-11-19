<?php
// tests/user-management-tests.php — provisional test harness for UM-01..UM-10
// Run: php tests/user-management-tests.php
// Uses in-memory SQLite; does not touch tutoring.db

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../repositories/userPDO.php';

function assertTrue($cond, $msg) {
    if (!$cond) {
        echo "FAIL: $msg\n";
        return false;
    }
    echo "PASS: $msg\n";
    return true;
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys = ON');
$schema = file_get_contents(__DIR__ . '/../database/setup-database.sql');
$pdo->exec($schema);

$passed = 0;
$total = 0;

$total++;
try {
    $id = registerUser($pdo, [
        'full_name' => 'Test Student',
        'email' => 'test@example.edu',
        'password' => 'Passw0rd!',
        'confirm_password' => 'Passw0rd!',
        'role' => 'Student'
    ]);
    $passed += assertTrue($id > 0, 'UM-01 valid registration creates Active user') ? 1 : 0;
} catch (Throwable $e) {
    echo "FAIL: UM-01 " . $e->getMessage() . "\n";
}

$total++;
try {
    registerUser($pdo, [
        'full_name' => 'Dup',
        'email' => 'test@example.edu',
        'password' => 'Passw0rd!',
        'confirm_password' => 'Passw0rd!',
        'role' => 'Student'
    ]);
    echo "FAIL: UM-01 duplicate email should be rejected\n";
} catch (InvalidArgumentException $e) {
    $passed += assertTrue(true, 'UM-01 duplicate email rejected') ? 1 : 0;
}

$total++;
try {
    registerUser($pdo, [
        'full_name' => 'Bad Role',
        'email' => 'badrole@example.edu',
        'password' => 'Passw0rd!',
        'confirm_password' => 'Passw0rd!',
        'role' => 'Administrator'
    ]);
    echo "FAIL: UM-01 public Admin registration should be denied\n";
} catch (InvalidArgumentException $e) {
    $passed += assertTrue(true, 'UM-01 Administrator self-registration denied') ? 1 : 0;
}

$total++;
$user = authenticate($pdo, 'test@example.edu', 'Passw0rd!');
$passed += assertTrue($user && $user['status'] === 'Active', 'UM-09 valid credentials authenticate Active user') ? 1 : 0;

$total++;
$bad = authenticate($pdo, 'test@example.edu', 'wrong');
$passed += assertTrue($bad === null, 'UM-09 invalid password rejected') ? 1 : 0;

$total++;
try {
    changePassword($pdo, (int)$user['user_id'], 'Passw0rd!', 'NewPass1!', 'NewPass1!');
    $newAuth = authenticate($pdo, 'test@example.edu', 'NewPass1!');
    $passed += assertTrue($newAuth !== null, 'UM-05 new password works; old fails') ? 1 : 0;
} catch (Throwable $e) {
    echo "FAIL: UM-05 " . $e->getMessage() . "\n";
}

echo "\n$passed / $total tests passed\n";
exit($passed === $total ? 0 : 1);
