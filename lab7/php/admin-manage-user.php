<?php
// UM-06 role, UM-07 status, UM-08 provisional conditional delete.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
$pdo = openDatabase();
$admin = requireLogin($pdo);
requireRole($admin, ['Administrator']);
$id = filter_var($_GET['id'] ?? $_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid account ID.'); }
$target = findUserById($pdo, (int)$id);
if (!$target) { http_response_code(404); exit('Account not found.'); }
$error = '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        switch ($_POST['action'] ?? '') {
            case 'role':
                setUserRole($pdo, (int)$admin['user_id'], (int)$id, (string)($_POST['role'] ?? ''));
                $message = 'Role updated.';
                break;
            case 'status':
                if (($_POST['confirm'] ?? '') !== 'CONFIRM') throw new InvalidArgumentException('Type CONFIRM to update status.');
                setUserStatus($pdo, (int)$admin['user_id'], (int)$id, (string)($_POST['status'] ?? ''));
                $message = 'Status updated.';
                break;
            case 'delete':
                if (($_POST['confirm'] ?? '') !== 'DELETE') throw new InvalidArgumentException('Type DELETE to confirm deletion.');
                attemptDeleteUser($pdo, (int)$admin['user_id'], (int)$id);
                header('Location: admin-users.php?deleted=1', true, 303); exit;
            default: throw new InvalidArgumentException('Invalid action.');
        }
        $target = findUserById($pdo, (int)$id);
    } catch (InvalidArgumentException|DomainException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Unable to complete the action.';
    }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4"><h1>Manage <?= escapeHtml($target['full_name']) ?></h1>
    <p class="text-muted"><?= escapeHtml($target['email']) ?> · <?= escapeHtml($target['role']) ?> · <?= escapeHtml($target['status']) ?></p>
    <?php if ($message): ?><p class="alert alert-success"><?= escapeHtml($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="alert alert-danger"><?= escapeHtml($error) ?></p><?php endif; ?>
    <h2>Update Role (UM-06)</h2>
    <form method="post" class="mb-4">
        <input type="hidden" name="user_id" value="<?= (int)$id ?>">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="action" value="role">
        <label class="form-label" for="new_role">New role</label>
        <select id="new_role" name="role" class="form-select mb-2">
            <?php foreach (['Student','Tutor','Administrator'] as $role): ?><option value="<?= $role ?>" <?= $role === $target['role'] ? 'selected' : '' ?>><?= $role ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-primary" type="submit">Save Role</button>
    </form>
    <h2>Activate or Deactivate (UM-07)</h2>
    <form method="post" class="mb-4">
        <input type="hidden" name="user_id" value="<?= (int)$id ?>">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="action" value="status">
        <label class="form-label" for="new_status">New status</label>
        <select id="new_status" name="status" class="form-select mb-2">
            <?php foreach (['Active','Inactive'] as $status): ?><option value="<?= $status ?>" <?= $status === $target['status'] ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?>
        </select>
        <label class="form-label" for="confirm_status">Type CONFIRM to apply</label>
        <input id="confirm_status" name="confirm" class="form-control mb-2" required>
        <button class="btn btn-warning" type="submit">Confirm Status Change</button>
    </form>
    <h2>Delete Account (UM-08)</h2>
    <p>Conservative provisional policy: deletion is blocked if the account has related tutoring requests or is the last active administrator. Final retention rules remain open in the design package.</p>
    <form method="post">
        <input type="hidden" name="user_id" value="<?= (int)$id ?>">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <input type="hidden" name="action" value="delete">
        <label class="form-label" for="confirm_delete">Type DELETE to confirm</label>
        <input id="confirm_delete" name="confirm" class="form-control mb-2" required>
        <button class="btn btn-danger" type="submit">Confirm Delete</button>
        <a class="btn btn-outline-secondary" href="admin-users.php">Cancel</a>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
