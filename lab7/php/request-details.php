<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/requestService.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student','Administrator']);
$id = filter_var($_GET['request_id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid request ID.'); }
$owner = $user['role'] === 'Student' ? (int)$user['user_id'] : null;
$request = findRequest($pdo, (int)$id, $owner);
if (!$request) { http_response_code(404); exit('Request not found.'); }
$topic = decodeTopic($request['topic']);
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4"><h1>Request #<?= (int)$id ?></h1>
    <dl class="row">
        <dt class="col-sm-3">Student</dt><dd class="col-sm-9"><?= escapeHtml($request['student_name']) ?> (<?= escapeHtml($request['student_email']) ?>)</dd>
        <dt class="col-sm-3">Course</dt><dd class="col-sm-9"><?= escapeHtml($request['course']) ?></dd>
        <dt class="col-sm-3">Preferred Date</dt><dd class="col-sm-9"><?= escapeHtml($request['preferred_date']) ?></dd>
        <dt class="col-sm-3">Preferred Time</dt><dd class="col-sm-9"><?= escapeHtml($request['preferred_time']) ?></dd>
        <dt class="col-sm-3">Topics</dt><dd class="col-sm-9"><?= escapeHtml(implode(', ', $topic['topics'])) ?></dd>
        <dt class="col-sm-3">Reason</dt><dd class="col-sm-9"><?= escapeHtml($topic['reason']) ?></dd>
        <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><?= escapeHtml($request['status']) ?></dd>
    </dl>
    <?php if ($request['status'] === 'Open'): ?>
        <a class="btn btn-primary" href="edit-request.php?request_id=<?= (int)$id ?>">Edit</a>
        <a class="btn btn-danger" href="delete-request.php?request_id=<?= (int)$id ?>">Cancel Request</a>
    <?php endif; ?>
    <a class="btn btn-outline-secondary" href="requests.php">Back to Requests</a>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
