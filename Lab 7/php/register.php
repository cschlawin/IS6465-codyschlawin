<?php
// UM-01: create Active Student/Tutor account, never Administrator.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
$pdo = openDatabase();
$errors = [];
$registered = false;
$values = ['full_name' => '', 'email' => '', 'role' => 'Student'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $values = [
        'full_name' => trim((string)($_POST['full_name'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'role' => (string)($_POST['role'] ?? ''),
    ];
    try {
        registerUser($pdo, $_POST);
        $registered = true;
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errors[] = 'Registration could not be completed. Try again or contact an administrator.';
    }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Create User Account <small class="text-muted fs-5">UM-01</small></h1>
    <?php if ($registered): ?>
        <p class="alert alert-success">Account created and active. <a href="login.php">Log in</a> to continue.</p>
    <?php else: ?>
        <?php foreach ($errors as $error): ?><p class="alert alert-danger"><?= escapeHtml($error) ?></p><?php endforeach; ?>
        <form method="post" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
            <div class="mb-3"><label for="full_name" class="form-label">Full Name</label>
                <input id="full_name" name="full_name" class="form-control" required maxlength="100" autocomplete="name" value="<?= escapeHtml($values['full_name']) ?>"></div>
            <div class="mb-3"><label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" class="form-control" required maxlength="254" autocomplete="email" value="<?= escapeHtml($values['email']) ?>"></div>
            <div class="mb-3"><label for="password" class="form-label">Password (at least 8 characters)</label>
                <input id="password" type="password" name="password" class="form-control" required minlength="8" maxlength="1024" autocomplete="new-password"></div>
            <div class="mb-3"><label for="confirm_password" class="form-label">Confirm Password</label>
                <input id="confirm_password" type="password" name="confirm_password" class="form-control" required minlength="8" maxlength="1024" autocomplete="new-password"></div>
            <fieldset class="mb-3"><legend class="fs-6">Account type</legend>
                <?php foreach (['Student', 'Tutor'] as $role): ?>
                    <label class="me-4"><input type="radio" name="role" value="<?= $role ?>" <?= $values['role'] === $role ? 'checked' : '' ?> required> <?= $role ?></label>
                <?php endforeach; ?>
            </fieldset>
            <button class="btn btn-primary" type="submit">Create Account</button>
        </form>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
