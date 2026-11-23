<?php
// Session and CSRF helpers follow the pattern in lab7/includes/auth.php.
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/../repositories/userPDO.php';

function appUrl(string $page): string
{
  $directory = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
  if (basename($directory) === 'tests') {
    $directory = dirname($directory);
  }
  return rtrim($directory, '/\\') . '/' . $page;
}

if (session_status() === PHP_SESSION_NONE) {
  session_name('AIRASIAREWARDS');
  ini_set('session.use_strict_mode', '1');
  ini_set('session.use_only_cookies', '1');
  session_set_cookie_params([
    'httponly' => true,
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
    'samesite' => 'Lax',
    'path' => rtrim(dirname(appUrl('login.php')), '/\\') . '/',
  ]);
  session_start();
}
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; form-action 'self'; "
  . "frame-ancestors 'none'; base-uri 'none'");

function csrfToken(): string
{
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrfField(): string
{
  return '<input type="hidden" name="csrf_token" value="'
    . e(csrfToken()) . '">';
}

function verifyCsrfToken(array $post): void
{
  $token = $post['csrf_token'] ?? null;
  $stored = $_SESSION['csrf_token'] ?? '';
  if (!is_string($token) || $stored === '' || !hash_equals($stored, $token)) {
    show_error(
      'Invalid or expired form token. Reload the form and try again.', 403
    );
  }
}

function requirePost(): void
{
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    show_error('This action requires a submitted POST form.', 405);
  }
}

function currentUser(?PDO $pdo): ?array
{
  $id = positive_integer($_SESSION['userId'] ?? null);
  if ($id === null) {
    return null;
  }
  if ($pdo === null) {
    show_error('The database is unavailable. Please try again later.', 503);
  }
  try {
    $user = findUserById($pdo, $id);
  } catch (PDOException $exception) {
    error_log('Air Asia session lookup: ' . $exception->getMessage());
    show_error('Unable to verify your session. Please try again later.', 503);
  }
  if (!$user || !in_array($user['role'], ['Admin', 'Customer'], true)) {
    logoutSession();
    return null;
  }
  return $user;
}

function requireLogin(?PDO $pdo): array
{
  $user = currentUser($pdo);
  if ($user === null) {
    header('Location: ' . appUrl('login.php'), true, 303);
    exit;
  }
  return $user;
}

function loginUser(array $user): void
{
  session_regenerate_id(true);
  $_SESSION = [
    'userId' => (int) $user['userId'],
    'userName' => $user['userName'],
    'role' => $user['role'],
  ];
  csrfToken();
}

function logoutSession(): void
{
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
      'expires' => time() - 42000,
      'path' => $params['path'], 'secure' => $params['secure'],
      'httponly' => $params['httponly'], 'samesite' => 'Lax',
    ]);
  }
  session_destroy();
}
