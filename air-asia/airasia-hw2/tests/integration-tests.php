<?php
// Lab 7-style service tests. All database writes use sqlite::memory: only.
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../services/redemptionService.php';
if (PHP_SAPI !== 'cli') {
  require_once __DIR__ . '/../includes/db.php';
  require_once __DIR__ . '/../includes/authorization.php';
  $user = requireLogin($pdo);
  requireRole($user, ['Admin']);
}
$testPdo = new PDO('sqlite::memory:', null, null, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$testPdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
$testPdo->exec(file_get_contents(__DIR__ . '/../database/seed.sql'));
$results = [];
function record(bool $passed, string $name): void
{
  global $results;
  $results[] = ['name' => $name, 'passed' => $passed];
}
$admin = authenticate($testPdo, 'bsmith', 'mysecret');
$customer = authenticate($testPdo, 'pjones', 'acrobat');
record($admin && $admin['role'] === 'Admin',
  'T09: Required Admin authenticates');
record($customer && $customer['role'] === 'Customer',
  'T10: Customer authenticates');
record(authenticate($testPdo, 'bsmith', 'wrong') === null,
  'T11: Incorrect password rejected');
record(authenticate($testPdo, 'unknown', 'wrong') === null,
  'T11: Unknown username rejected');
record(!isset($admin['password']) && !isset($customer['password']),
  'Authentication result does not return password hashes');
$stored = findUserByName($testPdo, 'pjones');
record($stored['password'] !== 'acrobat'
  && password_verify('acrobat', $stored['password']),
  'Seed password is hashed');
$input = [
  'userName' => 'newcustomer', 'firstName' => 'New', 'lastName' => 'Customer',
  'password' => 'NewPass123!', 'accountType' => 'Rewards', 'points' => '5000',
  'role' => 'Admin',
];
$newId = createCustomer($testPdo, $input);
$newUser = findUserByName($testPdo, 'newcustomer');
record($newUser['role'] === 'Customer',
  'T14: Posted Admin role cannot escalate');
record(password_verify($input['password'], $newUser['password']),
  'T14: New password hash verifies');
record(count(accountsForUser($testPdo, $newId)) === 1,
  'T14: Linked account created');
try {
  createCustomer($testPdo, array_replace($input, [
    'userName' => 'NEWCUSTOMER',
  ]));
  record(false, 'T14: Duplicate username rejected case-insensitively');
} catch (InvalidArgumentException $exception) {
  record(true, 'T14: Duplicate username rejected case-insensitively');
}
$before = accountsForUser($testPdo, 2)[0]['points'];
$cost = (int) find_card($testPdo, 1)['points'];
$redeemId = redeemCard($testPdo, 2, 1, 2);
$receipt = ownedRedemption($testPdo, 2, $redeemId);
record($receipt && (int) $receipt['pointsRedeemed'] === $cost,
  'T15: Redemption record created with database cost');
record((int) accountsForUser($testPdo, 2)[0]['points'] === $before - $cost,
  'T15: Matching points deducted');
record(ownedRedemption($testPdo, 3, $redeemId) === null,
  'Ownership: another user cannot retrieve the receipt');
record(ownedAccount($testPdo, 3, 2) === null,
  'Ownership: another user cannot select the account');
$count = (int) $testPdo->query(
  'SELECT COUNT(*) FROM REDEMPTION'
)->fetchColumn();
$balance = (int) accountsForUser($testPdo, 2)[0]['points'];
try {
  redeemCard($testPdo, 2, 1, 1);
  record(false, 'Ownership: redeem from someone else account denied');
} catch (InvalidArgumentException $exception) {
  record(true, 'Ownership: redeem from someone else account denied');
}
$testPdo->exec('UPDATE ACCOUNT SET points = 0 WHERE accountId = 2');
try {
  redeemCard($testPdo, 2, 1, 2);
  record(false, 'T16: Insufficient points rejected');
} catch (InvalidArgumentException $exception) {
  record(true, 'T16: Insufficient points rejected');
}
record((int) accountsForUser($testPdo, 2)[0]['points'] === 0
  && (int) $testPdo->query('SELECT COUNT(*) FROM REDEMPTION')->fetchColumn()
    === $count, 'T16: No deduction or redemption on failure');
$stmt = $testPdo->prepare('UPDATE ACCOUNT SET points = ? WHERE accountId = 2');
$stmt->execute([$balance]);
$testPdo->exec("CREATE TRIGGER fail_redemption BEFORE INSERT ON REDEMPTION "
  . "BEGIN SELECT RAISE(ABORT, 'Test failure'); END");
try {
  redeemCard($testPdo, 2, 1, 2);
  record(false, 'Transaction: forced insert failure propagates');
} catch (PDOException $exception) {
  record(true, 'Transaction: forced insert failure propagates');
}
record((int) accountsForUser($testPdo, 2)[0]['points'] === $balance
  && (int) $testPdo->query('SELECT COUNT(*) FROM REDEMPTION')->fetchColumn()
    === $count, 'Transaction: insert failure rolls back points deduction');
$testPdo->exec('DROP TRIGGER fail_redemption');
try {
  $testPdo->exec('DELETE FROM GIFTCARD WHERE cardId = 1');
  record(false, 'Foreign key preserves redeemed-card history');
} catch (PDOException $exception) {
  record(true, 'Foreign key preserves redeemed-card history');
}
// Test atomic customer creation if its related ACCOUNT insertion fails.
$testPdo->exec("CREATE TRIGGER fail_account BEFORE INSERT ON ACCOUNT "
  . "BEGIN SELECT RAISE(ABORT, 'Test failure'); END");
try {
  createCustomer($testPdo, array_replace($input, [
    'userName' => 'rollbackuser',
  ]));
  record(false, 'Customer transaction: forced account failure propagates');
} catch (PDOException $exception) {
  record(true, 'Customer transaction: forced account failure propagates');
}
record(findUserByName($testPdo, 'rollbackuser') === null,
  'Customer transaction: failed account rolls back USER insert');
$passed = count(array_filter($results, fn($row) => $row['passed']));
if (PHP_SAPI === 'cli') {
  foreach ($results as $result) {
    echo ($result['passed'] ? 'PASS: ' : 'FAIL: ') . $result['name'] . PHP_EOL;
  }
  echo $passed . '/' . count($results) . ' passed' . PHP_EOL;
  exit($passed === count($results) ? 0 : 1);
}
$basePath = '../';
$pageTitle = 'HW4 Integration Tests';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>
      <section class="panel form-panel">
        <h1>HW4 Integration Tests</h1>
        <p>These tests use a temporary in-memory database, not rewards.db.</p>
        <p><?= e($passed) ?>/<?= e(count($results)) ?> passed</p>
        <table class="test-table">
          <thead>
            <tr><th scope="col">Test</th><th scope="col">Result</th></tr>
          </thead>
          <tbody>
            <?php foreach ($results as $result): ?>
              <tr>
                <td><?= e($result['name']) ?></td>
                <?php $class = $result['passed'] ? 'test-pass' : 'test-fail'; ?>
                <td class="<?= e($class) ?>">
                  <?= $result['passed'] ? 'PASS' : 'FAIL' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
