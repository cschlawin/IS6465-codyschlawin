<?php
require_once dirname(__DIR__) . '/services/userService.php';
require_once dirname(__DIR__) . '/repositories/userPDO.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec(file_get_contents(dirname(__DIR__) . '/database/setup-database.sql'));

$results = [];

function record(bool $ok, string $label): void
{
    global $results;
    $results[] = ['name' => $label, 'passed' => $ok];
}

// UM-01 Create
try {
    $id = registerUser($pdo, [
        'full_name' => 'Test Student',
        'email' => 'test@example.edu',
        'password' => 'Passw0rd!',
        'confirm_password' => 'Passw0rd!',
        'role' => 'Student',
    ]);
    record($id > 0, 'UM-01: Valid registration creates Active user');
} catch (Throwable $e) {
    record(false, 'UM-01: Valid registration creates Active user');
}

try {
    registerUser($pdo, [
        'full_name' => 'Dup',
        'email' => 'test@example.edu',
        'password' => 'Passw0rd!',
        'confirm_password' => 'Passw0rd!',
        'role' => 'Student',
    ]);
    record(false, 'UM-01: Duplicate email rejected');
} catch (InvalidArgumentException $e) {
    record(true, 'UM-01: Duplicate email rejected');
}

try {
    registerUser($pdo, [
        'full_name' => 'Bad Role',
        'email' => 'badrole@example.edu',
        'password' => 'Passw0rd!',
        'confirm_password' => 'Passw0rd!',
        'role' => 'Administrator',
    ]);
    record(false, 'UM-01: Administrator self-registration denied');
} catch (InvalidArgumentException $e) {
    record(true, 'UM-01: Administrator self-registration denied');
}

// UM-09 Login
$user = authenticate($pdo, 'test@example.edu', 'Passw0rd!');
record($user && $user['status'] === 'Active', 'UM-09: Valid credentials authenticate Active user');

$bad = authenticate($pdo, 'test@example.edu', 'wrong');
record($bad === null, 'UM-09: Invalid password rejected');

// UM-05 Change password
try {
    changePassword($pdo, (int)$user['user_id'], 'Passw0rd!', 'NewPass1!', 'NewPass1!');
    $newAuth = authenticate($pdo, 'test@example.edu', 'NewPass1!');
    $oldAuth = authenticate($pdo, 'test@example.edu', 'Passw0rd!');
    record($newAuth !== null && $oldAuth === null, 'UM-05: New password works and old password fails');
} catch (Throwable $e) {
    record(false, 'UM-05: New password works and old password fails');
}

// UM-04 Own profile
try {
    updateOwnProfile($pdo, (int)$user['user_id'], 'Updated Student', 'updated@example.edu');
    $updated = findUserById($pdo, (int)$user['user_id']);
    record($updated && $updated['full_name'] === 'Updated Student' && $updated['email'] === 'updated@example.edu', 'UM-04: Own profile name and email update');
} catch (Throwable $e) {
    record(false, 'UM-04: Own profile name and email update');
}

// UM-07 Status and inactive login
try {
    setUserStatus($pdo, (int)$user['user_id'], 'Inactive');
    $inactiveLogin = authenticate($pdo, 'updated@example.edu', 'NewPass1!');
    record($inactiveLogin === null, 'UM-07: Inactive user cannot log in');
    setUserStatus($pdo, (int)$user['user_id'], 'Active');
} catch (Throwable $e) {
    record(false, 'UM-07: Inactive user cannot log in');
}

$passed = 0;
foreach ($results as $row) {
    if ($row['passed']) {
        $passed++;
    }
}
$failed = count($results) - $passed;
$total = count($results);
$allPassed = $failed === 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>User Management Test Results</title>
</head>
<body class="bg-light">
  <main class="container py-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3">User Management Test Results</h1>

        <?php if ($allPassed): ?>
          <div class="alert alert-success">All <?= (int)$total ?> user management tests passed.</div>
        <?php else: ?>
          <div class="alert alert-danger"><?= (int)$failed ?> of <?= (int)$total ?> user management tests failed.</div>
        <?php endif; ?>

        <p class="mb-3">
          <span class="badge bg-success">Passed: <?= (int)$passed ?></span>
          <span class="badge bg-danger">Failed: <?= (int)$failed ?></span>
          <span class="badge bg-dark">Total: <?= (int)$total ?></span>
        </p>

        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th style="width: 4rem;">#</th>
                <th>User management test</th>
                <th style="width: 8rem;">Result</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $i => $row): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php if ($row['passed']): ?>
                      <span class="badge bg-success">PASS</span>
                    <?php else: ?>
                      <span class="badge bg-danger">FAIL</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
