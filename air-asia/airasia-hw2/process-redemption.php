<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/authorization.php';
require_once __DIR__ . '/services/redemptionService.php';
$user = requireLogin($pdo);
requireRole($user, ['Admin', 'Customer']);
requirePost();
verifyCsrfToken($_POST);
$cardId = positive_integer($_POST['card_id'] ?? null);
$accountId = positive_integer($_POST['accountId'] ?? null);
$confirmation = $_POST['confirmation'] ?? null;
if (!$cardId || !$accountId || !is_string($confirmation)
    || ($_POST['confirm'] ?? null) !== 'yes'
    || ($_SESSION['redemption_forms'][$confirmation]['cardId'] ?? null)
      !== $cardId) {
  show_error('Invalid or already used confirmation. Select the card again.');
}
$confirmedPoints = $_SESSION['redemption_forms'][$confirmation]['points'];
unset($_SESSION['redemption_forms'][$confirmation]);
try {
  $redeemId = redeemCard(
    $pdo, (int) $user['userId'], $cardId, $accountId, $confirmedPoints
  );
  header('Location: redeem.php?redemption_id=' . $redeemId, true, 303);
  exit;
} catch (InvalidArgumentException $exception) {
  show_error($exception->getMessage(), 422);
} catch (Throwable $exception) {
  error_log('Air Asia redemption: ' . $exception->getMessage());
  show_error('Redemption could not be completed. Nothing was changed.', 503);
}
