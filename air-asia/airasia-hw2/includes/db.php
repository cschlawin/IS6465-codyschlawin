<?php
// FR-07: one shared PDO connection, with no automatic schema/seed writes.
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/../repositories/cardPDO.php';

$dbError = null;
$pdo = null;
$databasePath = __DIR__ . '/../database/rewards.db';
try {
  if (!is_file($databasePath)) {
    throw new RuntimeException('Database setup is required.');
  }
  $pdo = new PDO('sqlite:' . $databasePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  $pdo->exec('PRAGMA busy_timeout = 5000');
  $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException | RuntimeException $exception) {
  error_log('Air Asia database connection: ' . $exception->getMessage());
  $dbError = 'The database is unavailable. Check the README setup steps.';
}

