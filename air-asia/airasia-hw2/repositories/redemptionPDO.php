<?php
function insertRedemption(PDO $pdo, int $accountId, int $cardId, int $cost): int
{
  $stmt = $pdo->prepare(
    'INSERT INTO REDEMPTION (date, accountId, cardId, pointsRedeemed) '
    . 'VALUES (?, ?, ?, ?)'
  );
  $stmt->execute([gmdate('Y-m-d H:i:s'), $accountId, $cardId, $cost]);
  return (int) $pdo->lastInsertId();
}

function ownedRedemption(PDO $pdo, int $userId, int $redeemId): ?array
{
  $stmt = $pdo->prepare(
    'SELECT r.redeemId, r.date, r.pointsRedeemed, a.accountId, a.points, '
    . 'g.cardName, g.cardValue FROM REDEMPTION r '
    . 'JOIN ACCOUNT a ON a.accountId = r.accountId '
    . 'JOIN GIFTCARD g ON g.cardId = r.cardId '
    . 'WHERE r.redeemId = ? AND a.userId = ?'
  );
  $stmt->execute([$redeemId, $userId]);
  return $stmt->fetch() ?: null;
}
