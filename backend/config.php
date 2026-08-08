<?php
/**
 * Database configuration for DEVTA
 *
 * Local (this PC): uses SQLite automatically if MySQL login fails.
 * Hostinger: set DB_* below to your MySQL credentials (and DB_DRIVER to 'mysql').
 */

define('DB_DRIVER', 'auto'); // 'auto' | 'mysql' | 'sqlite'
define('DB_HOST', 'localhost');
define('DB_NAME', 'mydevta');
define('DB_USER', 'root');
define('DB_PASS', ''); // set your MySQL password if using MySQL
define('DB_CHARSET', 'utf8mb4');
define('DB_SQLITE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'mydevta.sqlite');

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token once per session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Create required tables (works for MySQL + SQLite)
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS service_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            service VARCHAR(150) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
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

    if ($driver === 'mysql' || $driver === 'auto') {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            ensureSchema($pdo);
            return $pdo;
        } catch (PDOException $e) {
            // Try creating the database if it doesn't exist
            try {
                $dsnNoDb = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
                $tmp = new PDO($dsnNoDb, DB_USER, DB_PASS, $options);
                $tmp->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', DB_NAME) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                ensureSchema($pdo);
                return $pdo;
            } catch (PDOException $e2) {
                $mysqlError = $e2->getMessage();
                if ($driver === 'mysql') {
                    http_response_code(500);
                    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
                }
            }
        }
    }

    // SQLite fallback (local development)
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
        http_response_code(500);
        $msg = 'Database connection failed.';
        if ($mysqlError) {
            $msg .= ' MySQL: ' . $mysqlError;
        }
        die(json_encode(['success' => false, 'message' => $msg]));
    }
}

/**
 * Escape output for HTML
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Check if admin is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require admin authentication
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}
