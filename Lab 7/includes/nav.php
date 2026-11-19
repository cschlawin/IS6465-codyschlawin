<?php
$navLoggedIn = currentUserId() !== null;
$navRole = $_SESSION['role'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary"><div class="container">
    <a class="navbar-brand" href="../index.php">Peer Tutoring Hub</a>
    <div class="navbar-nav">
        <?php if ($navLoggedIn): ?>
            <?php if (in_array($navRole, ['Student','Administrator'], true)): ?>
                <a class="nav-link" href="requests.php">Requests</a>
            <?php endif; ?>
            <?php if ($navRole === 'Student'): ?><a class="nav-link" href="request-form.php">Request a Session</a><?php endif; ?>
            <a class="nav-link" href="profile.php">My Profile</a>
            <?php if ($navRole === 'Administrator'): ?><a class="nav-link" href="admin-users.php">Manage Users</a><?php endif; ?>
            <form class="d-flex" method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml(csrfToken()) ?>">
                <button class="nav-link btn btn-link" type="submit">Log Out</button>
            </form>
        <?php else: ?>
            <a class="nav-link" href="login.php">Log In</a>
            <a class="nav-link" href="register.php">Register</a>
        <?php endif; ?>
    </div>
</div></nav>
