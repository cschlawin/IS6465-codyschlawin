<?php
// php/request-form.php — protected, Student only, prefilled from session
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Student']);

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Request a tutoring session</h1>
    <form class="tutoring-request-form" action="process-request.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="form_name" value="tutoring_request">
        <div class="mb-3">
            <label class="form-label">Student name</label>
            <input class="form-control" name="student_name" value="<?= escapeHtml($user['full_name']) ?>" readonly required>
        </div>
        <div class="mb-3">
            <label class="form-label">Student email</label>
            <input class="form-control" name="student_email" type="email" value="<?= escapeHtml($user['email']) ?>" readonly required>
        </div>
        <div class="mb-3">
            <label for="course" class="form-label">Course</label>
            <select id="course" name="course" class="form-select" required>
                <option value="">Choose a course</option>
                <option value="IS 6465">IS 6465</option>
                <option value="IS 6495">IS 6495</option>
                <option value="IS 6570">IS 6570</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="preferreddate" class="form-label">Preferred date</label>
            <input id="preferreddate" name="preferreddate" type="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Preferred time</label>
            <select name="preferredtime" class="form-select" required>
                <option value="">Choose</option>
                <option value="Morning">Morning</option>
                <option value="Afternoon">Afternoon</option>
                <option value="Evening">Evening</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Topics</label><br>
            <label><input type="checkbox" name="topics[]" value="Exam review"> Exam review</label><br>
            <label><input type="checkbox" name="topics[]" value="Practice problems"> Practice problems</label><br>
            <label><input type="checkbox" name="topics[]" value="Study plan"> Study plan</label>
        </div>
        <div class="mb-3">
            <label for="reason" class="form-label">Reason / details</label>
            <textarea id="reason" name="reason" class="form-control" rows="4" required minlength="10" maxlength="500"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
