<?php
// php/request-details.php — protected with ownership
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/requestService.php';

$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student', 'Administrator']);

$id = filter_input(INPUT_GET, 'request_id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid request ID');
}
$ownerId = ($user['role'] === 'Student') ? (int)$user['user_id'] : null;
$r = findRequest($pdo, $id, $ownerId);
if (!$r) {
    http_response_code(404);
    exit('Request not found');
}
$topic = decodeTopic($r['topic']);
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Request #<?= (int)$id ?></h1>
    <dl>
        <dt>Student</dt><dd><?= escapeHtml($r['student_name']) ?> (<?= escapeHtml($r['student_email']) ?>)</dd>
        <dt>Course</dt><dd><?= escapeHtml($r['course']) ?></dd>
        <dt>Preferred date</dt><dd><?= escapeHtml($r['preferred_date']) ?></dd>
        <dt>Preferred time</dt><dd><?= escapeHtml($r['preferred_time']) ?></dd>
        <dt>Status</dt><dd><?= escapeHtml($r['status']) ?></dd>
        <dt>Topics</dt><dd><?= escapeHtml(implode(', ', $topic['topics'])) ?></dd>
        <dt>Details</dt><dd><?= escapeHtml($topic['reason']) ?></dd>
    </dl>
    <?php if ($r['status'] === 'Open' && ($user['role'] === 'Administrator' || (int)$r['user_id'] === (int)$user['user_id'])): ?>
        <a href="edit-request.php?request_id=<?= (int)$id ?>" class="btn btn-primary">Edit</a>
        <a href="delete-request.php?request_id=<?= (int)$id ?>" class="btn btn-danger">Cancel</a>
    <?php endif; ?>
    <a href="requests.php" class="btn btn-secondary">Back to list</a>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
