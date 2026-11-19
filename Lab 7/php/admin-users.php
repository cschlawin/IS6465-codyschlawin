<?php
// UM-03: Administrator-only list and filter. No password hashes returned by listUsers().
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
$pdo = openDatabase();
$admin = requireLogin($pdo);
requireRole($admin, ['Administrator']);
$search = trim((string)($_GET['search'] ?? ''));
$role = (string)($_GET['role'] ?? '');
$status = (string)($_GET['status'] ?? '');
if (($role !== '' && !in_array($role, ['Student','Tutor','Administrator'], true)) ||
    ($status !== '' && !in_array($status, ['Active','Inactive'], true))) {
    http_response_code(400); exit('Invalid user filter.');
}
$users = listUsers($pdo, $search, $role, $status);
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4"><h1>Manage Users <small class="text-muted fs-5">UM-03</small></h1>
    <?php if (isset($_GET['deleted'])): ?><p class="alert alert-success">Account was deleted.</p><?php endif; ?>
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-5"><label class="form-label" for="search">Name or email</label>
            <input class="form-control" id="search" name="search" value="<?= escapeHtml($search) ?>" maxlength="100"></div>
        <div class="col-md-3"><label class="form-label" for="role">Role</label>
            <select class="form-select" id="role" name="role"><option value="">All roles</option>
                <?php foreach (['Student','Tutor','Administrator'] as $r): ?><option value="<?= $r ?>" <?= $r === $role ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-3"><label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status"><option value="">All statuses</option>
                <?php foreach (['Active','Inactive'] as $s): ?><option value="<?= $s ?>" <?= $s === $status ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary">Search</button></div>
    </form>
    <?php if (!$users): ?><p>No matching accounts.</p><?php else: ?>
        <div class="table-responsive"><table class="table table-striped">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th></th></tr></thead>
            <tbody><?php foreach ($users as $item): ?><tr>
                <td><?= escapeHtml($item['full_name']) ?></td><td><?= escapeHtml($item['email']) ?></td>
                <td><?= escapeHtml($item['role']) ?></td><td><?= escapeHtml($item['status']) ?></td>
                <td><?= escapeHtml($item['created_at']) ?></td>
                <td><a class="btn btn-sm btn-outline-primary" href="admin-manage-user.php?id=<?= (int)$item['user_id'] ?>">Manage</a></td>
            </tr><?php endforeach; ?></tbody>
        </table></div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
