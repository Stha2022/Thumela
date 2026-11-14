<?php
declare(strict_types=1);

/**
 * Thumela PDO connection.
 * XAMPP defaults: host=127.0.0.1, database=thumela, user=root, password=''
 * Change credentials for any non-local deployment. Never commit production secrets.
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $host = getenv('THUMELA_DB_HOST') ?: '127.0.0.1';
    $name = getenv('THUMELA_DB_NAME') ?: 'thumela';
    $user = getenv('THUMELA_DB_USER') ?: 'root';
    $pass = getenv('THUMELA_DB_PASS') !== false ? getenv('THUMELA_DB_PASS') : '';

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    return $pdo;
}
