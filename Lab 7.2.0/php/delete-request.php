<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repositories/requestPDO.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student','Administrator']);
$owner = $user['role'] === 'Student' ? (int)$user['user_id'] : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $id = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) { http_response_code(400); exit('Invalid request ID.'); }
    if (!findRequest($pdo, (int)$id, $owner)) { http_response_code(404); exit('Request not found.'); }
    if (!cancelOpenRequest($pdo, (int)$id, $owner)) { http_response_code(409); exit('Request is not Open.'); }
    header('Location: request-details.php?request_id=' . (int)$id, true, 303); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit('Method not allowed.'); }
$id = filter_var($_GET['request_id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid request ID.'); }
$request = findRequest($pdo, (int)$id, $owner);
if (!$request) { http_response_code(404); exit('Request not found.'); }
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4"><h1>Cancel Tutoring Request</h1>
    <p>Cancel request #<?= (int)$id ?>? The record will remain in the database with status Cancelled.</p>
    <?php if ($request['status'] === 'Open'): ?>
        <form method="post">
            <input type="hidden" name="request_id" value="<?= (int)$id ?>">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <button class="btn btn-danger" type="submit">Confirm Cancellation</button>
        </form>
    <?php else: ?><p>Only Open requests may be cancelled.</p><?php endif; ?>
    <a href="request-details.php?request_id=<?= (int)$id ?>">Keep Request</a>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
