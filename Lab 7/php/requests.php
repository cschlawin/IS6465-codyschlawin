<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/requestService.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student','Administrator']);
$course = (string)($_GET['course'] ?? '');
$status = (string)($_GET['status'] ?? '');
if (($course !== '' && !in_array($course, ['IS 6465','IS 6495','IS 6570'], true)) ||
    ($status !== '' && !in_array($status, ['Open','Cancelled','Assigned','Completed'], true))) {
    http_response_code(400); exit('Invalid filter.');
}
$owner = $user['role'] === 'Student' ? (int)$user['user_id'] : null;
$requests = listRequests($pdo, $course, $status, $owner);
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4"><h1>Tutoring Requests</h1>
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-5"><label class="form-label" for="course">Course</label>
            <select class="form-select" id="course" name="course"><option value="">All courses</option>
                <?php foreach (['IS 6465','IS 6495','IS 6570'] as $c): ?><option value="<?= $c ?>" <?= $course === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-5"><label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status"><option value="">All statuses</option>
                <?php foreach (['Open','Cancelled','Assigned','Completed'] as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary">Filter</button></div>
    </form>
    <?php if (!$requests): ?><p>No matching tutoring requests.</p><?php else: ?>
        <div class="table-responsive"><table class="table table-striped">
            <thead><tr><th>ID</th><th>Student</th><th>Course</th><th>Reason</th><th>Preferred Date</th><th>Status</th><th></th></tr></thead>
            <tbody><?php foreach ($requests as $request): $topic = decodeTopic($request['topic']); ?><tr>
                <td><?= (int)$request['request_id'] ?></td><td><?= escapeHtml($request['student_name']) ?></td>
                <td><?= escapeHtml($request['course']) ?></td><td><?= escapeHtml($topic['reason']) ?></td>
                <td><?= escapeHtml($request['preferred_date']) ?></td><td><?= escapeHtml($request['status']) ?></td>
                <td><a href="request-details.php?request_id=<?= (int)$request['request_id'] ?>">View</a></td>
            </tr><?php endforeach; ?></tbody>
        </table></div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
