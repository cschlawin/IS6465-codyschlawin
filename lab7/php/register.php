<?php
// php/register.php — UM-01 Create User Account
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';

$pdo = openDatabase();
$errors = [];
$success = false;
$values = ['full_name' => '', 'email' => '', 'role' => 'Student'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $values = [
        'full_name' => trim((string)($_POST['full_name'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'role' => (string)($_POST['role'] ?? 'Student')
    ];
    try {
        registerUser($pdo, $_POST);
        $success = true;
    } catch (InvalidArgumentException $e) {
        $errors = [$e->getMessage()];
    } catch (Throwable $e) {
        $errors = ['Registration failed. Please try again.'];
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Create Account</h1>
    <?php if ($success): ?>
        <div class="alert alert-success">Account created successfully. You may now <a href="login.php">log in</a>.</div>
    <?php else: ?>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= escapeHtml($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
        <form method="post" class="needs-validation">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?= escapeHtml($values['full_name']) ?>" required maxlength="100">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= escapeHtml($values['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label">Account type</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="role" value="Student" id="roleStudent" <?= $values['role'] === 'Student' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="roleStudent">Student</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="role" value="Tutor" id="roleTutor" <?= $values['role'] === 'Tutor' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="roleTutor">Tutor</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
