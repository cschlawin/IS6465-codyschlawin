        <nav aria-label="Main navigation">
          <?php if (isset($user)): ?>
            <a href="<?= e($basePath) ?>card-list.php">Gift Card List</a>
            <?php if ($user['role'] === 'Admin'): ?>
              <a href="<?= e($basePath) ?>card-add.php">Add Gift Card</a>
              <a href="<?= e($basePath) ?>cust-add.php">Add Customer</a>
            <?php else: ?>
              <a href="<?= e($basePath) ?>customer-profile.php">My Profile</a>
            <?php endif; ?>
            <span class="nav-label"><?= e($user['userName']) ?></span>
            <form method="post" action="<?= e($basePath) ?>logout.php">
              <?= csrfField() ?>
              <button class="nav-button" type="submit">Logout</button>
            </form>
          <?php else: ?>
            <span class="nav-label">Welcome to Rewards</span>
          <?php endif; ?>
        </nav>
      </div>
    </header>
    <main id="main">
