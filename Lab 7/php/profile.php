<?php
// UM-02 view own account; UM-04 edit own name/email only.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
$error = '';
$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        updateOwnProfile($pdo, (int)$user['user_id'], (string)($_POST['full_name'] ?? ''), (string)($_POST['email'] ?? ''));
        $user = requireLogin($pdo);
        $saved = true;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Unable to update profile. Please check your input.';
    }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4"><h1>My Profile <small class="text-muted fs-5">UM-02 / UM-04</small></h1>
    <?php if ($saved): ?><p class="alert alert-success">Your profile was updated.</p><?php endif; ?>
    <?php if ($error): ?><p class="alert alert-danger"><?= escapeHtml($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <div class="mb-3"><label class="form-label" for="full_name">Full Name</label>
            <input id="full_name" name="full_name" class="form-control" required maxlength="100" value="<?= escapeHtml($user['full_name']) ?>"></div>
        <div class="mb-3"><label class="form-label" for="email">Email</label>
            <input id="email" name="email" type="email" class="form-control" required maxlength="254" value="<?= escapeHtml($user['email']) ?>"></div>
        <div class="mb-3"><label class="form-label" for="role">Role (read-only)</label>
            <input id="role" class="form-control" value="<?= escapeHtml($user['role']) ?>" readonly></div>
        <div class="mb-3"><label class="form-label" for="status">Status (read-only)</label>
            <input id="status" class="form-control" value="<?= escapeHtml($user['status']) ?>" readonly></div>
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <a class="btn btn-outline-secondary" href="change-password.php">Change Password</a>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
