<?php
// CLI-only, repeatable setup/migration. Never runs when opening a web page.
if (PHP_SAPI !== 'cli') {
  http_response_code(404);
  exit('Not found');
}
$pdo = new PDO('sqlite:' . __DIR__ . '/rewards.db', null, null, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA busy_timeout = 5000');
$pdo->exec('PRAGMA foreign_keys = OFF');
$schema = file_get_contents(__DIR__ . '/schema.sql');
$pdo->beginTransaction();
try {
  $pdo->exec($schema);
  $pdo->exec(file_get_contents(__DIR__ . '/seed.sql'));
  // SQLite cannot add a foreign key to the old HW3 ACCOUNT in place.
  // Rebuild just that table, preserving every row and its generated IDs.
  if ($pdo->query('PRAGMA foreign_key_list(ACCOUNT)')->fetchAll() === []) {
    $sequence = (int) $pdo->query(
      "SELECT seq FROM sqlite_sequence WHERE name = 'ACCOUNT'"
    )->fetchColumn();
    preg_match('/CREATE TABLE IF NOT EXISTS ACCOUNT \([\s\S]*?\n\);/',
      $schema, $match);
    if (!isset($match[0])) {
      throw new RuntimeException('ACCOUNT schema was not found.');
    }
    $pdo->exec(str_replace('IF NOT EXISTS ACCOUNT', 'ACCOUNT_hw4', $match[0]));
    $pdo->exec('INSERT INTO ACCOUNT_hw4 SELECT * FROM ACCOUNT');
    $pdo->exec('DROP TABLE ACCOUNT');
    $pdo->exec('ALTER TABLE ACCOUNT_hw4 RENAME TO ACCOUNT');
    $stmt = $pdo->prepare(
      "UPDATE sqlite_sequence SET seq = MAX(seq, ?) WHERE name = 'ACCOUNT'"
    );
    $stmt->execute([$sequence]);
    $pdo->exec('CREATE INDEX IF NOT EXISTS account_user ON ACCOUNT(userId)');
  }
  if ($pdo->query('PRAGMA foreign_key_check')->fetchAll() !== []) {
    throw new RuntimeException(
      'Unlinked records found; migration rolled back.'
    );
  }
  $pdo->commit();
  $pdo->exec('PRAGMA foreign_keys = ON');
  echo 'Setup complete; existing records preserved.' . PHP_EOL;
  foreach (['USER', 'ACCOUNT', 'GIFTCARD', 'REDEMPTION'] as $table) {
    echo $table . ': ' . $pdo->query('SELECT COUNT(*) FROM ' . $table)
      ->fetchColumn() . PHP_EOL;
  }
} catch (Throwable $exception) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  fwrite(STDERR, 'Setup failed: ' . $exception->getMessage() . PHP_EOL);
  exit(1);
}
