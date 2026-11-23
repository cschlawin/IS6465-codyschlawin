<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
$user = requireLogin($pdo);
requirePost();
verifyCsrfToken($_POST);
logoutSession();
header('Location: login.php', true, 303);
exit;
