<?php
// php/profile.php — UM-02 View Own Account, UM-04 Update Own Account
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';

$pdo = openDatabase();
$user = requireLogin($pdo);
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        updateOwnProfile($pdo, (int)$user['user_id'], (string)($_POST['full_name'] ?? ''), (string)($_POST['email'] ?? ''));
        $success = true;
        $user = currentUser($pdo);
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = 'Update failed.';
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>My Profile</h1>
    <?php if ($success): ?><div class="alert alert-success">Profile updated.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= escapeHtml($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= escapeHtml($user['full_name']) ?>" required maxlength="100">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= escapeHtml($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" value="<?= escapeHtml($user['role']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <input type="text" class="form-control" value="<?= escapeHtml($user['status']) ?>" readonly>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="change-password.php" class="btn btn-outline-secondary">Change Password</a>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
