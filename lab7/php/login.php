<?php
// php/login.php — UM-09 Log In
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';

$pdo = openDatabase();
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $user = authenticate($pdo, $email, $password);
    if ($user) {
        loginUser($user);
        $redirect = $_GET['redirect'] ?? 'requests.php';
        header('Location: ' . $redirect);
        exit;
    }
    $error = 'Invalid email, password, or inactive account.';
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4">
    <h1>Log In</h1>
    <?php if (isset($_GET['logged_out'])): ?>
        <div class="alert alert-info">You have been logged out.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= escapeHtml($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= escapeHtml($email) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Log In</button>
    </form>
    <p class="mt-3">No account? <a href="register.php">Create one</a>.</p>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
