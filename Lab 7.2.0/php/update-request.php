<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/requestService.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST required.'); }
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student','Administrator']);
verifyCsrfToken($_POST);
$id = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid request ID.'); }
$owner = $user['role'] === 'Student' ? (int)$user['user_id'] : null;
if (!findRequest($pdo, (int)$id, $owner)) { http_response_code(404); exit('Request not found.'); }
try {
    if (!reviseRequest($pdo, (int)$id, $_POST, $owner)) { http_response_code(409); exit('Request is not Open.'); }
    header('Location: request-details.php?request_id=' . (int)$id, true, 303); exit;
} catch (InvalidArgumentException $e) {
    http_response_code(422); exit(escapeHtml($e->getMessage()));
} catch (Throwable $e) {
    error_log($e->getMessage()); http_response_code(500); exit('Unable to save changes.');
}
