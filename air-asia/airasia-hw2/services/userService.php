<?php
// Validation and user/account workflow, like lab7/services/userService.php.
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../repositories/userPDO.php';
require_once __DIR__ . '/../repositories/accountPDO.php';

function authenticate(PDO $pdo, string $userName, string $password): ?array
{
  $user = findUserByName($pdo, $userName);
  if (!$user || !in_array($user['role'], ['Admin', 'Customer'], true)
      || !password_verify($password, $user['password'])) {
    return null;
  }
  unset($user['password']);
  return $user;
}

function createCustomer(PDO $pdo, array $input): int
{
  $result = validateCustomer($input);
  if ($result['errors']) {
    throw new InvalidArgumentException('Please correct the customer fields.');
  }
  $data = $result['values'];
  if (findUserByName($pdo, $data['userName'])) {
    throw new InvalidArgumentException('That username is already in use.');
  }
  $hash = password_hash($input['password'], PASSWORD_DEFAULT);
  $pdo->beginTransaction();
  try {
    $userId = insertCustomer($pdo, $data, $hash);
    insertAccount($pdo, $userId, $data['accountType'], (int) $data['points']);
    $pdo->commit();
    return $userId;
  } catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    throw $exception;
  }
}
