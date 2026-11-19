<?php
/** Open the same SQLite application database used by Lab 6. */
function openDatabase(?string $path = null): PDO
{
    $path = $path ?? (getenv('TUTORING_DB_PATH') ?: dirname(__DIR__) . '/database/tutoring.db');
    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    return $pdo;
}
