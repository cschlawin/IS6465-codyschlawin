<?php
require_once dirname(__DIR__) . '/includes/validation.php';

$results = [];

function record(array &$results, string $name, string $actual, string $expected): void
{
    $results[] = [
        'name' => $name,
        'passed' => $actual === $expected,
    ];
}

// Preferred time
record($results, 'Preferred time: Valid time', validatePreferredTime('Morning'), '');
record($results, 'Preferred time: Missing time', validatePreferredTime(''), 'Choose a valid preferred time.');
record($results, 'Preferred time: Altered value', validatePreferredTime('Midnight'), 'Choose a valid preferred time.');

// Topics
record($results, 'Topics: Multiple valid topics', validateTopics(['Exam review', 'Study plan']), '');
record($results, 'Topics: Missing topics', validateTopics([]), 'Select at least one valid topic.');
record($results, 'Topics: Altered topic', validateTopics(['Free tutoring']), 'Select at least one valid topic.');

// Reason
record($results, 'Reason: Minimum length: 10 characters', validateReason('1234567890'), '');
record($results, 'Reason: Maximum length: 500 characters', validateReason(str_repeat('a', 500)), '');
record($results, 'Reason: Too short: 9 characters', validateReason('123456789'), 'Describe your request using 10 to 500 characters.');
record($results, 'Reason: Too long: 501 characters', validateReason(str_repeat('a', 501)), 'Describe your request using 10 to 500 characters.');

$passed = 0;
foreach ($results as $row) {
    if ($row['passed']) {
        $passed++;
    }
}
$failed = count($results) - $passed;
$total = count($results);
$allPassed = $failed === 0;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Validation Test Results</title>
</head>
<body class="bg-light">
  <main class="container py-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3">Validation Test Results</h1>

        <?php if ($allPassed): ?>
          <div class="alert alert-success">All <?= (int)$total ?> validation tests passed.</div>
        <?php else: ?>
          <div class="alert alert-danger"><?= (int)$failed ?> of <?= (int)$total ?> validation tests failed.</div>
        <?php endif; ?>

        <p class="mb-3">
          <span class="badge bg-success">Passed: <?= (int)$passed ?></span>
          <span class="badge bg-danger">Failed: <?= (int)$failed ?></span>
          <span class="badge bg-dark">Total: <?= (int)$total ?></span>
        </p>

        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th style="width: 4rem;">#</th>
                <th>Validation test</th>
                <th style="width: 8rem;">Result</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $i => $row): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php if ($row['passed']): ?>
                      <span class="badge bg-success">PASS</span>
                    <?php else: ?>
                      <span class="badge bg-danger">FAIL</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
