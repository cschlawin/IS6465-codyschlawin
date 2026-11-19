<?php
// php/logout.php — UM-10 Log Out
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('POST required');
}
verifyCsrfToken($_POST);
logoutSession();
header('Location: login.php?logged_out=1', true, 303);
exit;
