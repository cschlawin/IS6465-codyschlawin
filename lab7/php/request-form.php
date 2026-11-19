<?php
// Existing UC01 form. Student-only; submitted identity is taken from the session.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student']);
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Request a Tutoring Session</h1>
    <form class="tutoring-request-form" action="process-request.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="form_name" value="tutoring_request">
        <div class="mb-3"><label class="form-label" for="student_name">Student Name</label>
            <input class="form-control" id="student_name" value="<?= escapeHtml($user['full_name']) ?>" readonly></div>
        <div class="mb-3"><label class="form-label" for="student_email">Student Email</label>
            <input class="form-control" id="student_email" value="<?= escapeHtml($user['email']) ?>" readonly></div>
        <div class="mb-3"><label class="form-label" for="course">Course</label>
            <select class="form-select" id="course" name="course" required>
                <option value="">Choose a course</option>
                <?php foreach (['IS 6465','IS 6495','IS 6570'] as $course): ?><option value="<?= $course ?>"><?= $course ?></option><?php endforeach; ?>
            </select></div>
        <div class="mb-3"><label class="form-label" for="preferreddate">Preferred Date</label>
            <input class="form-control" id="preferreddate" name="preferreddate" type="date" min="<?= date('Y-m-d') ?>" required></div>
        <fieldset class="mb-3"><legend class="fs-6">Topics (select one or more)</legend>
            <?php foreach (['Exam review','Practice problems','Study plan'] as $topic): ?>
                <label class="d-block"><input type="checkbox" name="topics[]" value="<?= escapeHtml($topic) ?>"> <?= escapeHtml($topic) ?></label>
            <?php endforeach; ?>
        </fieldset>
        <div class="mb-3"><label class="form-label" for="reason">Reason / Details</label>
            <textarea class="form-control" id="reason" name="reason" rows="4" minlength="10" maxlength="500" required></textarea></div>
        <fieldset class="mb-3"><legend class="fs-6">Preferred Time</legend>
            <?php foreach (['Morning','Afternoon','Evening'] as $time): ?>
                <label class="me-3"><input type="radio" name="preferredtime" value="<?= $time ?>" required> <?= $time ?></label>
            <?php endforeach; ?>
        </fieldset>
        <button class="btn btn-primary" type="submit">Submit Request</button>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
