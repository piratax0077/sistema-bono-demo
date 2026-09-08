<?php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'sdi_vouchers';
$username = getenv('DB_ADMIN_USERNAME') ?: 'root';
$password = getenv('DB_ADMIN_PASSWORD') ?: '';

$pdo = new PDO(
    "mysql:host={$host};port={$port}",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
);

$safeDatabase = str_replace('`', '``', $database);

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo "Database {$database} ready.".PHP_EOL;

$appUsername = getenv('DB_APP_USERNAME') ?: null;
$appPassword = getenv('DB_APP_PASSWORD') ?: null;

if ($appUsername && $appPassword !== null) {
    $safeAppUsername = str_replace(["'", "`"], ["''", ''], $appUsername);
    $safeAppPassword = str_replace("'", "''", $appPassword);

    foreach (['localhost', '127.0.0.1'] as $hostName) {
        $safeHost = str_replace("'", "''", $hostName);

        $pdo->exec("CREATE USER IF NOT EXISTS '{$safeAppUsername}'@'{$safeHost}' IDENTIFIED BY '{$safeAppPassword}'");
        $pdo->exec("ALTER USER '{$safeAppUsername}'@'{$safeHost}' IDENTIFIED BY '{$safeAppPassword}'");
        $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX, REFERENCES, CREATE TEMPORARY TABLES, LOCK TABLES ON `{$safeDatabase}`.* TO '{$safeAppUsername}'@'{$safeHost}'");
    }

    $pdo->exec('FLUSH PRIVILEGES');

    echo "Database user {$appUsername} ready.".PHP_EOL;
}
