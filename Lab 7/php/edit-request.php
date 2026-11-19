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
<main class="container py-4"><h1>Edit Request #<?= (int)$id ?></h1>
    <?php if ($request['status'] !== 'Open'): ?><p>Only Open requests can be edited.</p><?php else: ?>
        <form method="post" action="update-request.php">
            <input type="hidden" name="request_id" value="<?= (int)$id ?>">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <div class="mb-3"><label class="form-label" for="course">Course</label>
                <select class="form-select" id="course" name="course" required>
                    <?php foreach (['IS 6465','IS 6495','IS 6570'] as $c): ?><option value="<?= $c ?>" <?= $request['course'] === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?>
                </select></div>
            <div class="mb-3"><label class="form-label" for="preferreddate">Preferred Date</label>
                <input class="form-control" id="preferreddate" type="date" name="preferreddate" value="<?= escapeHtml($request['preferred_date']) ?>" required></div>
            <fieldset class="mb-3"><legend class="fs-6">Topics</legend>
                <?php foreach (['Exam review','Practice problems','Study plan'] as $t): ?>
                    <label class="me-3"><input type="checkbox" name="topics[]" value="<?= escapeHtml($t) ?>" <?= in_array($t, $topic['topics'], true) ? 'checked' : '' ?>> <?= escapeHtml($t) ?></label>
                <?php endforeach; ?>
            </fieldset>
            <div class="mb-3"><label class="form-label" for="reason">Reason / Details</label>
                <textarea class="form-control" name="reason" id="reason" minlength="10" maxlength="500" required><?= escapeHtml($topic['reason']) ?></textarea></div>
            <fieldset class="mb-3"><legend class="fs-6">Preferred Time</legend>
                <?php foreach (['Morning','Afternoon','Evening'] as $time): ?>
                    <label class="me-3"><input type="radio" name="preferredtime" value="<?= $time ?>" <?= $request['preferred_time'] === $time ? 'checked' : '' ?> required> <?= $time ?></label>
                <?php endforeach; ?>
            </fieldset>
            <button class="btn btn-primary" type="submit">Save Changes</button>
            <a class="btn btn-outline-secondary" href="request-details.php?request_id=<?= (int)$id ?>">Cancel</a>
        </form>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
