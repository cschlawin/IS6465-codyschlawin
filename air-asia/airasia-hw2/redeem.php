<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/authorization.php';
require_once __DIR__ . '/services/redemptionService.php';
$user = requireLogin($pdo);
requireRole($user, ['Admin', 'Customer']);
$receipt = null;
try {
  if (isset($_GET['redemption_id'])) {
    $redeemId = positive_integer($_GET['redemption_id']);
    $receipt = $redeemId === null ? null
      : ownedRedemption($pdo, (int) $user['userId'], $redeemId);
    if (!$receipt) {
      show_error('This redemption was not found for your account.', 404);
    }
  } else {
    $cardId = positive_integer($_GET['card_id'] ?? null);
    if ($cardId === null) {
      show_error('Choose a gift card using a valid positive integer card ID.');
    }
    $card = find_card($pdo, $cardId);
    if (!$card) {
      show_error('This gift card was not found.', 404);
    }
    $accounts = accountsForUser($pdo, (int) $user['userId']);
    // One-use confirmation prevents a browser refresh replaying a purchase.
    $confirmation = bin2hex(random_bytes(32));
    $_SESSION['redemption_forms'] = array_slice(
      $_SESSION['redemption_forms'] ?? [], -9, null, true
    );
    $_SESSION['redemption_forms'][$confirmation] = [
      'cardId' => $cardId, 'points' => (int) $card['points'],
    ];
  }
} catch (PDOException $exception) {
  error_log('Air Asia redeem view: ' . $exception->getMessage());
  show_error('Unable to load redemption information. Please try later.', 503);
}
$pageTitle = $receipt ? 'Redemption Successful' : 'Redeem Gift Card';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
      <section class="panel form-panel">
        <?php if ($receipt): ?>
          <h1>Redemption Successful</h1>
          <p class="success-message" role="status">
            You redeemed <?= e($receipt['cardName']) ?>.
          </p>
          <dl class="specs">
            <div>
              <dt>Redemption ID</dt>
              <dd><?= e($receipt['redeemId']) ?></dd>
            </div>
            <div><dt>Points Redeemed</dt>
              <dd><?= e(number_format($receipt['pointsRedeemed'])) ?></dd></div>
            <div><dt>Current Available Points</dt>
              <dd><?= e(number_format($receipt['points'])) ?></dd></div>
          </dl>
        <?php else: ?>
          <h1>Redeem Gift Card</h1>
          <img class="redemption-image" src="images/gift-card.png"
            alt="Rewards gift card">
          <dl class="specs">
            <div><dt>Card Name</dt><dd><?= e($card['cardName']) ?></dd></div>
            <div><dt>Card Value</dt>
              <dd>$<?= e(number_format($card['cardValue'], 2)) ?> USD</dd></div>
            <div><dt>Points Required</dt>
              <dd><?= e(number_format($card['points'])) ?></dd></div>
          </dl>
          <?php if ($accounts): ?>
            <form method="post" action="process-redemption.php">
              <?= csrfField() ?>
              <input type="hidden" name="card_id" value="<?= e($cardId) ?>">
              <input type="hidden" name="confirmation"
                value="<?= e($confirmation) ?>">
              <div class="field">
                <label for="account">Your Rewards Account</label>
                <select name="accountId" id="account" required>
                  <?php foreach ($accounts as $account): ?>
                    <option value="<?= e($account['accountId']) ?>">
                      <?= e($account['accountType']) ?>
                      #<?= e($account['accountId']) ?>
                      — <?= e(number_format($account['points'])) ?> points
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button class="button" name="confirm" value="yes" type="submit">
                Confirm Redemption
              </button>
            </form>
          <?php else: ?>
            <p>No rewards account is linked. Please contact an Admin.</p>
          <?php endif; ?>
        <?php endif; ?>
        <a class="back-link return-link" href="card-list.php">
          Return to Gift Card List
        </a>
      </section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
