<?php
// php/admin-manage-user.php — UM-06 Update Role, UM-07 Activate/Deactivate, UM-08 Delete
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';

$pdo = openDatabase();
$admin = requireLogin($pdo);
requireRole($admin, ['Administrator']);

$id = (int)($_GET['id'] ?? $_POST['user_id'] ?? 0);
$target = findUserById($pdo, $id);
if (!$target) {
    http_response_code(404);
    exit('User not found.');
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $action = (string)($_POST['action'] ?? '');
    try {
        if ($action === 'role') {
            setUserRole($pdo, (int)$admin['user_id'], $id, (string)($_POST['role'] ?? ''));
            $message = 'Role updated.';
        } elseif ($action === 'status') {
            setUserStatus($pdo, $id, (string)($_POST['status'] ?? ''));
            $message = 'Status updated.';
        } elseif ($action === 'delete') {
            if (($_POST['confirm'] ?? '') !== 'DELETE') {
                throw new InvalidArgumentException('Type DELETE to confirm.');
            }
            attemptDeleteUser($pdo, $id);
            header('Location: admin-users.php?deleted=1');
            exit;
        }
        $target = findUserById($pdo, $id);
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        $error = 'Action failed.';
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Manage User: <?= escapeHtml($target['full_name']) ?></h1>
    <p>Email: <?= escapeHtml($target['email']) ?> | Current role: <?= escapeHtml($target['role']) ?> | Status: <?= escapeHtml($target['status']) ?></p>
    <?php if ($message): ?><div class="alert alert-success"><?= escapeHtml($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= escapeHtml($error) ?></div><?php endif; ?>

    <h2>Update Role (UM-06)</h2>
    <form method="post" class="mb-4">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="user_id" value="<?= (int)$target['user_id'] ?>">
        <input type="hidden" name="action" value="role">
        <div class="mb-3">
            <select name="role" class="form-select">
                <?php foreach (['Student', 'Tutor', 'Administrator'] as $r): ?>
                    <option value="<?= escapeHtml($r) ?>" <?= $target['role'] === $r ? 'selected' : '' ?>><?= escapeHtml($r) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary">Save Role</button>
    </form>

    <h2>Account Status (UM-07)</h2>
    <form method="post" class="mb-4">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="user_id" value="<?= (int)$target['user_id'] ?>">
        <input type="hidden" name="action" value="status">
        <div class="mb-3">
            <select name="status" class="form-select">
                <option value="Active" <?= $target['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $target['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <button class="btn btn-warning">Update Status</button>
    </form>

    <h2>Delete Account (UM-08)</h2>
    <p class="text-danger">Deletion is blocked if the user has related tutoring requests. Deactivate instead when possible. Last active administrator cannot be removed.</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="user_id" value="<?= (int)$target['user_id'] ?>">
        <input type="hidden" name="action" value="delete">
        <div class="mb-3">
            <label>Type DELETE to confirm</label>
            <input type="text" name="confirm" class="form-control" required>
        </div>
        <button class="btn btn-danger">Confirm Delete</button>
        <a href="admin-users.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
