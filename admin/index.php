<?php
require_once __DIR__ . '/../backend/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Simple rate-limit via session
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_last'] = time();
    }

    if ($_SESSION['login_attempts'] >= 8 && (time() - ($_SESSION['login_last'] ?? 0)) < 300) {
        $error = 'Too many attempts. Please wait a few minutes.';
    } elseif ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } elseif (attemptLogin($username, $password)) {
        $_SESSION['login_attempts'] = 0;
        header('Location: dashboard.php');
        exit;
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['login_last'] = time();
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login | DEVTA</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/logo.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body>
  <div class="login-page">
    <div class="login-card">
      <div class="brand">
        <a href="../frontend/index.html" class="brand-logo brand-logo--nav">
          <span class="brand-logo__row">
            <span class="brand-logo__icon">D</span>
            <span class="brand-logo__name">DEVTA</span>
          </span>
        </a>
      </div>
      <h1>Admin Login</h1>
      <p class="sub">Secure access to contact messages dashboard</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="" autocomplete="off">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required maxlength="50" autofocus />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <button type="submit" class="btn-login">Sign In</button>
      </form>
      <a class="back-link" href="../frontend/index.html">&larr; Back to website</a>
    </div>
  </div>
</body>
</html>
