<?php
// UM-09: log in using email, password_verify, Active status, and a fresh session ID.
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../services/userService.php';
$pdo = openDatabase();
$email = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $email = trim((string)($_POST['email'] ?? ''));
    $user = authenticate($pdo, $email, (string)($_POST['password'] ?? ''));
    if ($user) {
        loginUser($user);
        header('Location: ' . safeLocalRedirect($_GET['redirect'] ?? 'profile.php'), true, 303);
        exit;
    }
    $error = 'Invalid email or password.'; // Do not reveal if account exists or is inactive.
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4"><h1>Log In <small class="text-muted fs-5">UM-09</small></h1>
    <?php if (isset($_GET['logged_out'])): ?><p class="alert alert-info">Session ended. You have been logged out.</p><?php endif; ?>
    <?php if ($error): ?><p class="alert alert-danger"><?= escapeHtml($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
        <div class="mb-3"><label class="form-label" for="email">Email</label>
            <input class="form-control" id="email" name="email" type="email" required autocomplete="username" value="<?= escapeHtml($email) ?>"></div>
        <div class="mb-3"><label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password"></div>
        <button type="submit" class="btn btn-primary">Log In</button>
    </form>
    <p class="mt-3">New here? <a href="register.php">Create an account</a>.</p>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
