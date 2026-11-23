<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/authorization.php';
require_once __DIR__ . '/services/userService.php';
$user = requireLogin($pdo);
requireRole($user, ['Admin']);
$values = [
  'userName' => '', 'firstName' => '', 'lastName' => '',
  'accountType' => 'Rewards', 'points' => '0',
];
$errors = [];
$formError = '';
$created = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrfToken($_POST);
  $result = validateCustomer($_POST);
  $values = $result['values'];
  $errors = $result['errors'];
  if ($errors === []) {
    try {
      $created = createCustomer($pdo, $_POST);
      http_response_code(201);
    } catch (InvalidArgumentException $exception) {
      $errors['userName'] = $exception->getMessage();
      http_response_code(422);
    } catch (PDOException $exception) {
      error_log('Air Asia customer creation: ' . $exception->getMessage());
      $formError = $exception->getCode() === '23000'
        ? 'Customer could not be created. Check the username and try again.'
        : 'Unable to create this customer. Please try again later.';
      http_response_code(503);
    }
  } else {
    http_response_code(422);
  }
}
// Do not echo, redisplay, log, or store the submitted password in the session.
unset($_POST['password']);
$customerFields = [
  'userName' => 'Username', 'firstName' => 'First Name',
  'lastName' => 'Last Name', 'accountType' => 'Account Type',
];
$pageTitle = 'Add Customer';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
      <section class="panel form-panel">
        <h1>Add Customer</h1>
        <?php if ($created !== null): ?>
          <p class="success-message" role="status">
            Customer <?= e($values['userName']) ?> was created with a linked
            account and <?= e($values['points']) ?> starting points.
          </p>
          <dl class="specs">
            <div><dt>User ID</dt><dd><?= e($created) ?></dd></div>
            <div>
              <dt>First Name</dt>
              <dd><?= e($values['firstName']) ?></dd>
            </div>
            <div><dt>Last Name</dt><dd><?= e($values['lastName']) ?></dd></div>
            <div><dt>Role</dt><dd>Customer</dd></div>
          </dl>
          <a class="button" href="cust-add.php">Add Another Customer</a>
        <?php else: ?>
          <p class="muted">Create a Customer and its rewards account.</p>
          <?php if ($formError !== ''): ?>
            <p class="error-summary" role="alert"><?= e($formError) ?></p>
          <?php endif; ?>
          <form method="post" action="cust-add.php">
            <?= csrfField() ?>
            <?php foreach ($customerFields as $key => $label): ?>
              <div class="field">
                <label for="<?= e($key) ?>"><?= e($label) ?></label>
                <input id="<?= e($key) ?>" name="<?= e($key) ?>" type="text"
                  maxlength="100" value="<?= e($values[$key]) ?>"
                  <?= error_attributes($errors, $key) ?> required>
                <?php field_error($errors, $key); ?>
              </div>
            <?php endforeach; ?>
            <div class="field">
              <label for="new-password">Password (8–72 bytes)</label>
              <input id="new-password" name="password" type="password"
                minlength="8" maxlength="72" autocomplete="new-password"
                <?= error_attributes($errors, 'password') ?> required>
              <?php field_error($errors, 'password'); ?>
            </div>
            <div class="field">
              <label for="opening-points">Starting Points</label>
              <input id="opening-points" name="points" type="number"
                min="0" step="1" value="<?= e($values['points']) ?>"
                <?= error_attributes($errors, 'points') ?> required>
              <?php field_error($errors, 'points'); ?>
            </div>
            <div class="form-actions">
              <button class="button" type="submit">Create Customer</button>
              <a class="button secondary" href="card-list.php">Cancel</a>
            </div>
          </form>
        <?php endif; ?>
      </section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
