<?php
$env = parse_ini_file(__DIR__ . '/.env');

$mysqli = new mysqli(
    $env['DB_HOST'] ?? 'localhost',
    $env['DB_USER'] ?? '',
    $env['DB_PASS'] ?? '',
    $env['DB_NAME'] ?? '',
    $env['DB_PORT'] ?? 3306
);

if ($mysqli->connect_error) {
    http_response_code(500);
    exit;
}
?>
