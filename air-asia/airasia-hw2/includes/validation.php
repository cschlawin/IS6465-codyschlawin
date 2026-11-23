<?php
// HW3 BR-01 through BR-08: pure validation and safe HTML output.
const CARD_TYPES = ['Retail', 'Dining', 'Entertainment', 'Travel'];

function e($value): string
{
  return htmlspecialchars(
    (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'
  );
}

function text_value($value): string
{
  return is_string($value) ? trim($value) : '';
}

function character_count(string $value): int
{
  // Count UTF-8 characters without requiring the mbstring extension.
  $length = preg_match_all('/./us', $value);
  return $length === false ? PHP_INT_MAX : $length;
}

function positive_integer($value): ?int
{
  if (!is_string($value) && !is_int($value)) {
    return null;
  }
  $value = (string) $value;
  if (!preg_match('/^[1-9][0-9]*$/D', $value)) {
    return null;
  }
  $integer = filter_var($value, FILTER_VALIDATE_INT);
  return $integer !== false && $integer > 0 ? $integer : null;
}

function validate_card(array $input): array
{
  $values = [];
  foreach (['cardName', 'cardType', 'cardValue', 'points'] as $field) {
    $values[$field] = text_value($input[$field] ?? null);
  }
  $errors = [];
  if ($values['cardName'] === '') {
    $errors['cardName'] = 'Enter a card name.';
  } elseif (character_count($values['cardName']) > 100) {
    $errors['cardName'] = 'Card name must be 100 characters or fewer.';
  }
  if (character_count($values['cardType']) > 100
      || !in_array($values['cardType'], CARD_TYPES, true)) {
    $errors['cardType'] = 'Select an allowed card type.';
  }
  $amount = $values['cardValue'];
  if (!is_numeric($amount) || !is_finite((float) $amount)
      || (float) $amount <= 0) {
    $errors['cardValue'] = 'Enter a finite card value greater than zero.';
  }
  if (positive_integer($values['points']) === null) {
    $errors['points'] = 'Enter a positive whole number of points.';
  }
  return ['values' => $values, 'errors' => $errors];
}

function field_error(array $errors, string $field): void
{
  if (isset($errors[$field])) {
    echo '<p class="field-error" id="' . e($field) . '-error">'
      . e($errors[$field]) . '</p>';
  }
}

function error_attributes(array $errors, string $field): string
{
  return isset($errors[$field])
    ? ' aria-invalid="true" aria-describedby="' . e($field) . '-error"'
    : '';
}

function show_error(string $message, int $status = 400): void
{
  http_response_code($status);
  $pageTitle = 'Unable to Complete Request';
  $user = $GLOBALS['user'] ?? null;
  $basePath = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/tests/')
    ? '../' : '';
  require_once __DIR__ . '/header.php';
  require_once __DIR__ . '/nav.php';
  echo '<section class="panel form-panel"><h1>Unable to Complete Request</h1>';
  echo '<p role="alert">' . e($message) . '</p>';
  $returnUrl = function_exists('appUrl') ? appUrl('card-list.php')
    : 'card-list.php';
  echo '<a class="button" href="' . e($returnUrl)
    . '">Return to Gift Card List</a>';
  echo '</section>';
  require_once __DIR__ . '/footer.php';
  exit;
}


function nonnegative_integer($value): ?int
{
  return $value === '0' || $value === 0 ? 0 : positive_integer($value);
}

function validateCustomer(array $input): array
{
  $values = [];
  $errors = [];
  foreach (['userName', 'firstName', 'lastName', 'accountType'] as $field) {
    $values[$field] = text_value($input[$field] ?? null);
    if ($values[$field] === '' || character_count($values[$field]) > 100) {
      $errors[$field] = 'Enter 1 to 100 characters.';
    }
  }
  $values['points'] = text_value($input['points'] ?? null);
  if (nonnegative_integer($values['points']) === null) {
    $errors['points'] = 'Enter zero or a positive whole number of points.';
  }
  $password = $input['password'] ?? null;
  if (!is_string($password) || strlen($password) < 8
      || strlen($password) > 72 || str_contains($password, "\0")) {
    $errors['password'] = 'Use a password of 8 to 72 bytes.';
  }
  // Passwords are never returned as form values or put into the session.
  return ['values' => $values, 'errors' => $errors];
}
