<?php
function accountsForUser(PDO $pdo, int $userId): array
{
  $stmt = $pdo->prepare(
    'SELECT accountId, userId, accountType, points FROM ACCOUNT '
    . 'WHERE userId = ? ORDER BY accountId'
  );
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

function ownedAccount(PDO $pdo, int $userId, int $accountId): ?array
{
  $stmt = $pdo->prepare(
    'SELECT accountId, userId, accountType, points FROM ACCOUNT '
    . 'WHERE accountId = ? AND userId = ?'
  );
  $stmt->execute([$accountId, $userId]);
  return $stmt->fetch() ?: null;
}

function insertAccount(PDO $pdo, int $userId, string $type, int $points): int
{
  $stmt = $pdo->prepare(
    'INSERT INTO ACCOUNT (userId, accountType, points) VALUES (?, ?, ?)'
  );
  $stmt->execute([$userId, $type, $points]);
  return (int) $pdo->lastInsertId();
}

function deductPoints(PDO $pdo, int $userId, int $accountId, int $cost): bool
{
  $stmt = $pdo->prepare(
    'UPDATE ACCOUNT SET points = points - ? '
    . 'WHERE accountId = ? AND userId = ? AND points >= ?'
  );
  $stmt->execute([$cost, $accountId, $userId, $cost]);
  return $stmt->rowCount() === 1;
}
