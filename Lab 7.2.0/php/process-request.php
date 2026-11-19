<?php
// UC01 write: authenticate -> role -> CSRF -> own student record -> transaction.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/requestService.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST required.'); }
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student']);
verifyCsrfToken($_POST);
if (($_POST['form_name'] ?? '') !== 'tutoring_request') { http_response_code(400); exit('Invalid form.'); }
$data = $_POST;
$data['student_name'] = $user['full_name'];
$data['student_email'] = $user['email'];
try {
    $id = submitRequest($pdo, $data, (int)$user['user_id']);
    header('Location: request-details.php?request_id=' . $id, true, 303); exit;
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    exit('<h1>Request not saved</h1><p>' . escapeHtml($e->getMessage()) . '</p><a href="request-form.php">Back</a>');
} catch (DomainException $e) {
    http_response_code(403); exit('Student account is not linked. Contact an administrator.');
} catch (Throwable $e) {
    error_log($e->getMessage()); http_response_code(500); exit('Unable to create request.');
}
