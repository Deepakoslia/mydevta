<?php
/**
 * One-time: set admin to Devtaknowledge / Nico@871
 * Open: https://yourdomain.com/admin/update-password.php
 * DELETE this file after success.
 */
require_once __DIR__ . '/../backend/config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = getDB();
    ensureSchema($pdo);

    $hash = password_hash('Nico@871', PASSWORD_DEFAULT);

    $pdo->prepare('DELETE FROM users WHERE username = :u')->execute([':u' => 'admin']);

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password) VALUES (:u, :p)
         ON DUPLICATE KEY UPDATE password = VALUES(password)'
    );
    $stmt->execute([':u' => 'Devtaknowledge', ':p' => $hash]);

    echo "OK\nUsername: Devtaknowledge\nPassword: Nico@871\n\nAb is file (update-password.php) ko DELETE kar do.";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
