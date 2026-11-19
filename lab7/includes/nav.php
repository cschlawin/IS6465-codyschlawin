<?php
// includes/nav.php — updated for Lab 7 authentication
$loggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="../index.php">Peer Tutoring Hub</a>
    <div class="navbar-nav">
      <?php if ($loggedIn): ?>
        <a class="nav-link" href="requests.php">Requests</a>
        <?php if ($role === 'Student'): ?>
          <a class="nav-link" href="request-form.php">Request a Session</a>
        <?php endif; ?>
        <a class="nav-link" href="profile.php">Profile</a>
        <?php if ($role === 'Administrator'): ?>
          <a class="nav-link" href="admin-users.php">Manage Users</a>
        <?php endif; ?>
        <form method="post" action="logout.php" class="d-inline">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <button type="submit" class="nav-link btn btn-link" style="border:none;background:none;color:rgba(255,255,255,.55);">Logout</button>
        </form>
      <?php else: ?>
        <a class="nav-link" href="login.php">Log In</a>
        <a class="nav-link" href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
