<?php
/**
 * DEVTA database bootstrap
 * Hostinger credentials go in config.local.php (created by /install.php)
 */

// Load Hostinger / local overrides first
$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    require_once $localConfig;
}

// Defaults (used only if config.local.php is missing)
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', 'auto');
}
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'mydevta');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}
if (!defined('DB_SQLITE_PATH')) {
    define(
        'DB_SQLITE_PATH',
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'mydevta.sqlite'
    );
}

// Callback / contact alerts go here
if (!defined('NOTIFY_EMAIL')) {
    define('NOTIFY_EMAIL', 'devtaknowledge@gmail.com');
}

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Create required tables (MySQL + SQLite)
 */
function ensureSchema(PDO $pdo): void
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $pdo->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS service_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT NOT NULL,
            service TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS service_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            service VARCHAR(150) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_service (service),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        // admin / Admin@123
        $hash = '$2y$12$GQW1KvW.AarWMvCpfyyovecpAqRvS3i4YT.7dtsEkz9tgvJwtfQCC';
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:u, :p)');
        $stmt->execute([':u' => 'admin', ':p' => $hash]);
    }
}

/**
 * PDO connection (singleton)
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $driver = DB_DRIVER;
    $mysqlError = null;
    $isHostinger = is_file(__DIR__ . '/config.local.php') || $driver === 'mysql';

    if ($driver === 'mysql' || $driver === 'auto') {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            ensureSchema($pdo);
            return $pdo;
        } catch (PDOException $e) {
            try {
                $dsnNoDb = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
                $tmp = new PDO($dsnNoDb, DB_USER, DB_PASS, $options);
                $safeName = str_replace('`', '``', DB_NAME);
                $tmp->exec('CREATE DATABASE IF NOT EXISTS `' . $safeName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                ensureSchema($pdo);
                return $pdo;
            } catch (PDOException $e2) {
                $mysqlError = $e2->getMessage();
                if ($isHostinger || $driver === 'mysql') {
                    throw new PDOException(
                        'Hostinger MySQL connect fail. Check config.local.php credentials. Detail: ' . $mysqlError
                    );
                }
            }
        }
    }

    // Local SQLite fallback only (not for Hostinger production)
    try {
        $dir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
        ensureSchema($pdo);
        return $pdo;
    } catch (PDOException $e) {
        $msg = 'Database connection failed.';
        if ($mysqlError) {
            $msg .= ' MySQL: ' . $mysqlError;
        }
        throw new PDOException($msg);
    }
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}
