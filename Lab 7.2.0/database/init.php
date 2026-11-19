<?php
/** For a NEW empty database. This will not erase an existing database. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}
require_once __DIR__ . '/../config/db.php';
$pdo = openDatabase();
try {
    $pdo->exec(file_get_contents(__DIR__ . '/setup-database.sql'));
    echo "Schema ready; existing rows were not removed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Initialization failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
