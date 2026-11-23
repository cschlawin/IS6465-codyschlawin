<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
if ($pdo !== null && currentUser($pdo) !== null) {
  header('Location: card-list.php', true, 303);
  exit;
}
$error = $_SESSION['loginError'] ?? '';
$userName = $_SESSION['loginName'] ?? '';
unset($_SESSION['loginError'], $_SESSION['loginName']);
$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
      <div class="login-layout">
        <section class="welcome">
          <span class="eyebrow">AIR ASIA REWARDS</span>
          <h1>Your points.<br>Your next reward.</h1>
          <p>Explore gift cards from brands you love, all in one place.</p>
          <div class="welcome-art" aria-hidden="true">
            <img src="images/gift-card.png" alt="">
          </div>
          <span class="welcome-caption">
            A little reward. A world of possibilities.
          </span>
        </section>
        <section class="panel login-panel">
          <span class="eyebrow">LET’S GET STARTED</span>
          <h2>Welcome back</h2>
          <p class="muted">Sign in to explore your rewards.</p>
          <?php if ($error !== ''): ?>
            <p class="error-summary" role="alert"><?= e($error) ?></p>
          <?php endif; ?>
          <form action="authenticate.php" method="post">
            <?= csrfField() ?>
            <div class="field">
              <label for="username">Username</label>
              <input id="username" name="userName" type="text"
                maxlength="100" autocomplete="username"
                value="<?= e($userName) ?>" required>
            </div>
            <div class="field">
              <label for="password">Password</label>
              <input id="password" name="password" type="password"
                maxlength="72" autocomplete="current-password" required>
            </div>
            <button class="button full" type="submit">Login →</button>
          </form>
        </section>
      </div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
