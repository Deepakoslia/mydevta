<?php
/**
 * Admin authentication helpers
 */

require_once __DIR__ . '/config.php';

/**
 * Attempt admin login with prepared statements + password_verify
 */
function attemptLogin(string $username, string $password): bool
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return false;
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['admin_id']       = (int) $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['login_time']     = time();

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Destroy admin session
 */
function logoutAdmin(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
