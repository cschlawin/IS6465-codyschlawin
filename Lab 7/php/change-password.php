<?php
// UM-05: verify current password before hashing/storing replacement.
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
        changePassword($pdo, (int)$user['user_id'],
            (string)($_POST['current_password'] ?? ''),
            (string)($_POST['new_password'] ?? ''),
            (string)($_POST['confirm_password'] ?? ''));
        session_regenerate_id(true);
        $saved = true;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Password could not be changed.';
    }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4"><h1>Change Password <small class="text-muted fs-5">UM-05</small></h1>
    <?php if ($saved): ?><p class="alert alert-success">Password changed. The new password will be required at your next login.</p><?php endif; ?>
    <?php if ($error): ?><p class="alert alert-danger"><?= escapeHtml($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <div class="mb-3"><label class="form-label" for="current_password">Current Password</label>
            <input id="current_password" name="current_password" type="password" class="form-control" required autocomplete="current-password"></div>
        <div class="mb-3"><label class="form-label" for="new_password">New Password</label>
            <input id="new_password" name="new_password" type="password" class="form-control" minlength="8" maxlength="1024" required autocomplete="new-password"></div>
        <div class="mb-3"><label class="form-label" for="confirm_password">Confirm New Password</label>
            <input id="confirm_password" name="confirm_password" type="password" class="form-control" minlength="8" maxlength="1024" required autocomplete="new-password"></div>
        <button class="btn btn-primary" type="submit">Update Password</button>
        <a class="btn btn-outline-secondary" href="profile.php">Cancel</a>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
