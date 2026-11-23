<?php
function find_card(PDO $pdo, int $cardId): ?array
{
  $statement = $pdo->prepare(
    'SELECT cardId, cardName, cardType, cardValue, points '
    . 'FROM GIFTCARD WHERE cardId = :cardId'
  );
  $statement->bindValue(':cardId', $cardId, PDO::PARAM_INT);
  $statement->execute();
  $card = $statement->fetch();
  return $card === false ? null : $card;
}


function listCards(PDO $pdo): array
{
  return $pdo->query(
    'SELECT cardId, cardName, cardType, cardValue, points '
    . 'FROM GIFTCARD ORDER BY cardId'
  )->fetchAll();
}

function insertGiftCard(PDO $pdo, array $values): int
{
  $stmt = $pdo->prepare(
    'INSERT INTO GIFTCARD (cardName, cardType, cardValue, points) '
    . 'VALUES (?, ?, ?, ?)'
  );
  $stmt->execute([
    $values['cardName'], $values['cardType'], $values['cardValue'],
    (int) $values['points'],
  ]);
  return (int) $pdo->lastInsertId();
}

function updateGiftCard(PDO $pdo, int $cardId, array $values): bool
{
  $stmt = $pdo->prepare(
    'UPDATE GIFTCARD SET cardName = ?, cardType = ?, cardValue = ?, '
    . 'points = ? WHERE cardId = ?'
  );
  $stmt->execute([
    $values['cardName'], $values['cardType'], $values['cardValue'],
    (int) $values['points'], $cardId,
  ]);
  return $stmt->rowCount() === 1;
}

function deleteGiftCard(PDO $pdo, int $cardId): bool
{
  $stmt = $pdo->prepare('DELETE FROM GIFTCARD WHERE cardId = ?');
  $stmt->execute([$cardId]);
  return $stmt->rowCount() === 1;
}
