<?php
/**
 * PHPUnit bootstrap for Lab 7.
 * Loads application functions and prepares an isolated in-memory SQLite database.
 */

require_once dirname(__DIR__) . '/includes/validation.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/repositories/studentPDO.php';
require_once dirname(__DIR__) . '/repositories/requestPDO.php';
require_once dirname(__DIR__) . '/repositories/userPDO.php';
require_once dirname(__DIR__) . '/services/requestService.php';
require_once dirname(__DIR__) . '/services/userService.php';

/**
 * Open a fresh in-memory SQLite database with the Lab 7 schema.
 * Each test should call this so tests do not share rows.
 */
function openTestDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $schema = file_get_contents(dirname(__DIR__) . '/database/setup-database.sql');
    $pdo->exec($schema);
    return $pdo;
}
