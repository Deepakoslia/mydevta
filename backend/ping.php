<?php
/**
 * Hostinger DB check:
 * https://yourdomain.com/backend/ping.php
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$info = [
    'ok' => false,
    'php' => PHP_VERSION,
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'config_local_exists' => is_file(__DIR__ . '/config.local.php'),
    'hint' => 'Agar ok=false ho to https://yourdomain.com/install.php chalao',
];

try {
    require_once __DIR__ . '/config.php';
    $pdo = getDB();
    ensureSchema($pdo);

    $info['ok'] = true;
    $info['db_driver'] = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $info['db_name'] = defined('DB_NAME') ? DB_NAME : null;
    $info['callbacks_count'] = (int) $pdo->query('SELECT COUNT(*) FROM service_requests')->fetchColumn();
    $info['messages_count'] = (int) $pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();

    // Last 5 callbacks — yahan SERVICE name dikhega
    $stmt = $pdo->query(
        'SELECT id, service, name, email, phone, created_at
         FROM service_requests
         ORDER BY created_at DESC
         LIMIT 5'
    );
    $info['latest_callbacks'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $info['ok'] = false;
    $info['error'] = $e->getMessage();
}

echo json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
