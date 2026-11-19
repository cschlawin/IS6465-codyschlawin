<?php
// php/admin-users.php — UM-03 View and Search User Accounts
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repositories/userPDO.php';

$pdo = openDatabase();
$user = requireLogin($pdo);
requireRole($user, ['Administrator']);

$search = trim((string)($_GET['search'] ?? ''));
$role = (string)($_GET['role'] ?? '');
$status = (string)($_GET['status'] ?? '');
$users = listUsers($pdo, $search, $role, $status);

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Manage Users</h1>
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search name or email" value="<?= escapeHtml($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                <?php foreach (['Student', 'Tutor', 'Administrator'] as $r): ?>
                    <option value="<?= escapeHtml($r) ?>" <?= $role === $r ? 'selected' : '' ?>><?= escapeHtml($r) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <?php foreach (['Active', 'Inactive'] as $s): ?>
                    <option value="<?= escapeHtml($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= escapeHtml($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Search</button>
        </div>
    </form>
    <?php if (!$users): ?>
        <p>No matching accounts.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= escapeHtml($u['full_name']) ?></td>
                        <td><?= escapeHtml($u['email']) ?></td>
                        <td><?= escapeHtml($u['role']) ?></td>
                        <td><?= escapeHtml($u['status']) ?></td>
                        <td><?= escapeHtml($u['created_at']) ?></td>
                        <td><a href="admin-manage-user.php?id=<?= (int)$u['user_id'] ?>" class="btn btn-sm btn-outline-primary">Manage</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
