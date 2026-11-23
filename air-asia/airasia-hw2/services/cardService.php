<?php
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../repositories/cardPDO.php';

function addGiftCard(PDO $pdo, array $input): int
{
  $result = validate_card($input);
  if ($result['errors']) {
    throw new InvalidArgumentException('Please correct the gift-card fields.');
  }
  return insertGiftCard($pdo, $result['values']);
}

function reviseGiftCard(PDO $pdo, int $cardId, array $input): bool
{
  $result = validate_card($input);
  if ($result['errors']) {
    throw new InvalidArgumentException('Please correct the gift-card fields.');
  }
  return updateGiftCard($pdo, $cardId, $result['values']);
}
