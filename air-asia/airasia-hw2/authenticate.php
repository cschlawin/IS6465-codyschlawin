<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/services/userService.php';
requirePost();
verifyCsrfToken($_POST);
if ($dbError !== null) {
  show_error($dbError, 503);
}
$userName = text_value($_POST['userName'] ?? null);
$password = $_POST['password'] ?? null;
$user = null;
try {
  if ($userName !== '' && character_count($userName) <= 100
      && is_string($password) && strlen($password) <= 72
      && !str_contains($password, "\0")) {
    $user = authenticate($pdo, $userName, $password);
  }
} catch (PDOException $exception) {
  error_log('Air Asia login: ' . $exception->getMessage());
  show_error('Unable to sign in. Please try again later.', 503);
}
if ($user === null) {
  $_SESSION['loginError'] = 'Invalid username or password.';
  $_SESSION['loginName'] = character_count($userName) <= 100 ? $userName : '';
  header('Location: login.php', true, 303);
  exit;
}
loginUser($user);
header('Location: card-list.php', true, 303);
exit;
