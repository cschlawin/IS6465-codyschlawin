<?php
// Original use case 8 allows Customer and Admin, using their own accounts.
require_once __DIR__ . '/../repositories/cardPDO.php';
require_once __DIR__ . '/../repositories/accountPDO.php';
require_once __DIR__ . '/../repositories/redemptionPDO.php';

function redeemCard(
  PDO $pdo, int $userId, int $cardId, int $accountId,
  ?int $confirmedPoints = null
): int {
  $pdo->beginTransaction();
  try {
    $card = find_card($pdo, $cardId);
    if (!$card) {
      throw new InvalidArgumentException('This gift card was not found.');
    }
    $account = ownedAccount($pdo, $userId, $accountId);
    if (!$account) {
      throw new InvalidArgumentException('Choose one of your own accounts.');
    }
    $cost = (int) $card['points'];
    if ($confirmedPoints !== null && $cost !== $confirmedPoints) {
      throw new InvalidArgumentException(
        'The required points changed. Please review the card and try again.'
      );
    }
    if ((int) $account['points'] < $cost) {
      throw new InvalidArgumentException(
        'Insufficient points. No points were deducted.'
      );
    }
    if (!deductPoints($pdo, $userId, $accountId, $cost)) {
      throw new RuntimeException('The balance changed. Please try again.');
    }
    $redeemId = insertRedemption($pdo, $accountId, $cardId, $cost);
    $pdo->commit();
    return $redeemId;
  } catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    throw $exception;
  }
}
