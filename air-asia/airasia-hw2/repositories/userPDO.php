<?php
// Prepared USER operations, following Lab 7's procedural repository style.
function findUserByName(PDO $pdo, string $userName): ?array
{
  $stmt = $pdo->prepare('SELECT * FROM USER WHERE userName = ?');
  $stmt->execute([$userName]);
  return $stmt->fetch() ?: null;
}

function findUserById(PDO $pdo, int $userId): ?array
{
  $stmt = $pdo->prepare(
    'SELECT userId, userName, firstName, lastName, role FROM USER '
    . 'WHERE userId = ?'
  );
  $stmt->execute([$userId]);
  return $stmt->fetch() ?: null;
}

function insertCustomer(PDO $pdo, array $data, string $hash): int
{
  $stmt = $pdo->prepare(
    'INSERT INTO USER (userName, password, firstName, lastName, role) '
    . "VALUES (?, ?, ?, ?, 'Customer')"
  );
  $stmt->execute([
    $data['userName'], $hash, $data['firstName'], $data['lastName'],
  ]);
  return (int) $pdo->lastInsertId();
}
