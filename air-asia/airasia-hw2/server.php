<?php
// PHP built-in-server file guard, not a framework/application router.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if ($path === '/') {
  header('Location: login.php', true, 303);
  exit;
}
$pages = [
  '/login.php', '/authenticate.php', '/logout.php', '/card-list.php',
  '/card-details.php', '/card-add.php', '/card-update.php', '/card-delete.php',
  '/cust-add.php', '/customer-profile.php', '/redeem.php',
  '/process-redemption.php', '/tests/validation-tests.php',
  '/tests/integration-tests.php', '/css/styles.css', '/images/gift-card.png',
];
if ($path === '/') {
  header('Location: login.php', true, 303);
  exit;
}
if (!in_array($path, $pages, true)) {
  http_response_code(404);
  exit('Not found');
}
return false;
