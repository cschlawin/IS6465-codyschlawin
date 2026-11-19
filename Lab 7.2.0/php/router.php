<?php
// Safe educational PHP built-in server router. NOT a substitute for production server hardening.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($path === '/') $path = '/index.php';
$publicPages = [
    '/index.php', '/php/register.php', '/php/login.php', '/php/logout.php',
    '/php/profile.php', '/php/change-password.php', '/php/admin-users.php',
    '/php/admin-manage-user.php', '/php/requests.php', '/php/request-form.php',
    '/php/process-request.php', '/php/request-details.php',
    '/php/edit-request.php', '/php/update-request.php', '/php/delete-request.php',
    '/css/styles.css',
];
if (!in_array($path, $publicPages, true)) {
    http_response_code(404);
    exit('Not found');
}
$base = realpath(dirname(__DIR__));
$full = realpath($base . $path);
if (!$full || !str_starts_with($full, $base . DIRECTORY_SEPARATOR) || !is_file($full)) {
    http_response_code(404);
    exit('Not found');
}
return false;
