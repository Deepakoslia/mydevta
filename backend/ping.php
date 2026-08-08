<?php
/**
 * Quick hosting check — open in browser:
 * https://yourdomain.com/backend/ping.php
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$info = [
    'ok' => true,
    'php' => PHP_VERSION,
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'pdo_sqlite' => extension_loaded('pdo_sqlite'),
];

try {
    require_once __DIR__ . '/config.php';
    $pdo = getDB();
    $info['db_driver'] = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $info['service_requests_table'] = true;
    $pdo->query('SELECT 1 FROM service_requests LIMIT 1');
} catch (Throwable $e) {
    $info['ok'] = false;
    $info['error'] = $e->getMessage();
}

echo json_encode($info, JSON_PRETTY_PRINT);
