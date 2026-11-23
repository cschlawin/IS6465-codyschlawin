<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/authorization.php';
require_once __DIR__ . '/repositories/accountPDO.php';
$user = requireLogin($pdo);
requireRole($user, ['Customer']);
try {
  // URL user/account identifiers are intentionally ignored: session owns scope.
  $accounts = accountsForUser($pdo, (int) $user['userId']);
} catch (PDOException $exception) {
  error_log('Air Asia profile: ' . $exception->getMessage());
  show_error('Unable to load your accounts. Please try again later.', 503);
}
$pageTitle = 'My Profile and Points';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
      <section class="panel form-panel">
        <h1>My Profile and Points</h1>
        <dl class="specs">
          <div><dt>Username</dt><dd><?= e($user['userName']) ?></dd></div>
          <div><dt>First Name</dt><dd><?= e($user['firstName']) ?></dd></div>
          <div><dt>Last Name</dt><dd><?= e($user['lastName']) ?></dd></div>
        </dl>
        <?php if (!$accounts): ?>
          <p>No rewards account is linked. Please contact an Admin.</p>
        <?php endif; ?>
        <?php foreach ($accounts as $account): ?>
          <h2>Account <?= e($account['accountId']) ?></h2>
          <dl class="specs">
            <div>
              <dt>Account Type</dt>
              <dd><?= e($account['accountType']) ?></dd>
            </div>
            <div><dt>Available Points</dt>
              <dd><?= e(number_format($account['points'])) ?> pts</dd></div>
          </dl>
        <?php endforeach; ?>
        <a class="button" href="card-list.php">Browse Gift Cards</a>
      </section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
