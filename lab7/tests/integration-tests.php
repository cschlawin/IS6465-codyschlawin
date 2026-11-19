<?php
require_once dirname(__DIR__) . '/services/requestService.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec(file_get_contents(dirname(__DIR__) . '/database/setup-database.sql'));

$results = [];

function check(bool $ok, string $label): void
{
    global $results;
    $results[] = ['name' => $label, 'passed' => $ok];
}

$sample = [
    'student_name' => 'Jordan Lee',
    'student_email' => 'jordan@example.edu',
    'course' => 'IS 6465',
    'reason' => 'Please help with PHP form validation.',
    'topics' => ['Exam review', 'Study plan'],
    'preferreddate' => (new DateTimeImmutable('+10 days'))->format('Y-m-d'),
    'preferredtime' => 'Morning',
];

$id = submitRequest($pdo, $sample);
$r = findRequest($pdo, $id);
check($r !== false && $r['status'] === 'Open', 'UC01: Create request and status is Open');
check(decodeTopic($r['topic'])['topics'] === ['Exam review', 'Study plan'], 'Lab 4: Checkbox topics preserved');

$id2 = submitRequest($pdo, $sample);
check((int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn() === 1, 'UC01: Duplicate email reuses student');
check(count(listRequests($pdo, 'IS 6465', 'Open')) === 2, 'UC02: Course and status filter');

$bad = $sample;
$bad['reason'] = 'short';
try {
    submitRequest($pdo, $bad);
    check(false, 'UC01: Invalid reason rejected');
} catch (InvalidArgumentException $e) {
    check(true, 'UC01: Invalid reason rejected');
}
check((int)$pdo->query('SELECT COUNT(*) FROM tutoring_requests')->fetchColumn() === 2, 'UC01: Invalid submission writes nothing');

$edit = $sample;
$edit['reason'] = 'Updated request reason for the exam.';
check(reviseRequest($pdo, $id, $edit), 'UC01: Edit Open request');
check(decodeTopic(findRequest($pdo, $id)['topic'])['reason'] === $edit['reason'], 'UC01: Updated reason persisted');

check(cancelOpenRequest($pdo, $id), 'UC01: Cancel Open request');
check(findRequest($pdo, $id)['status'] === 'Cancelled', 'UC01: Cancelled row retained');
check(!cancelOpenRequest($pdo, $id), 'UC01: Second cancellation rejected');
check(findRequest($pdo, $id2)['status'] === 'Open', 'UC01: Unrelated request unchanged');

try {
    $pdo->exec("INSERT INTO tutoring_requests(student_id,course,topic,preferred_date,preferred_time) VALUES(999,'IS 6465','x','2030-01-01','Morning')");
    check(false, 'Database: Foreign key enforced');
} catch (PDOException $e) {
    check(true, 'Database: Foreign key enforced');
}
check(count(listRequests($pdo, 'IS 6570', '')) === 0, 'UC02: Empty filter result');

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
  <title>Integration Test Results</title>
</head>
<body class="bg-light">
  <main class="container py-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3">Integration Test Results</h1>

        <?php if ($allPassed): ?>
          <div class="alert alert-success">All <?= (int)$total ?> integration tests passed.</div>
        <?php else: ?>
          <div class="alert alert-danger"><?= (int)$failed ?> of <?= (int)$total ?> integration tests failed.</div>
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
                <th>Integration test</th>
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
