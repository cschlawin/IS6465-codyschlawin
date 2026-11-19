<?php
// php/delete-request.php — protected cancel with CSRF
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/requestService.php';

$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student', 'Administrator']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('Invalid ID');
    }
    $ownerId = ($user['role'] === 'Student') ? (int)$user['user_id'] : null;
    $existing = findRequest($pdo, $id, $ownerId);
    if (!$existing) {
        http_response_code(404);
        exit('Request not found');
    }
    try {
        $ok = cancelOpenRequest($pdo, $id);
        if (!$ok) {
            http_response_code(409);
            exit('Request not found or not Open');
        }
        header('Location: request-details.php?request_id=' . $id, true, 303);
        exit;
    } catch (Throwable $e) {
        error_log($e->getMessage());
        http_response_code(500);
        exit('Unable to cancel');
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}
$id = filter_input(INPUT_GET, 'request_id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid ID');
}
$ownerId = ($user['role'] === 'Student') ? (int)$user['user_id'] : null;
$r = findRequest($pdo, $id, $ownerId);
if (!$r) {
    http_response_code(404);
    exit('Not found');
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Cancel tutoring request</h1>
    <p>Cancel request #<?= (int)$id ?>? The record will be retained with status Cancelled.</p>
    <?php if ($r['status'] === 'Open'): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <input type="hidden" name="request_id" value="<?= (int)$id ?>">
            <button class="btn btn-danger">Yes, cancel request</button>
        </form>
    <?php else: ?>
        <p>Only Open requests can be cancelled.</p>
    <?php endif; ?>
    <a href="request-details.php?request_id=<?= (int)$id ?>">Keep request</a>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
