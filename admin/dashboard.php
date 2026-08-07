<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

try {
    $pdo = getDB();

    $total = (int) $pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
    $today = (int) $pdo->query('SELECT COUNT(*) FROM contacts WHERE DATE(created_at) = CURDATE()')->fetchColumn();
    $week  = (int) $pdo->query('SELECT COUNT(*) FROM contacts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();

    $stmt = $pdo->query('SELECT id, name, email, message, created_at FROM contacts ORDER BY created_at DESC');
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $total = $today = $week = 0;
    $messages = [];
    $flash = $flash ?: 'Could not load messages. Check database connection.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | DEVTA Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/logo.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
  <div class="admin-shell">
    <aside class="admin-sidebar">
      <div class="brand">
        <a href="../frontend/index.html" class="brand-logo brand-logo--nav">
          <span class="brand-logo__row">
            <span class="brand-logo__icon">D</span>
            <span class="brand-logo__name">DEVTA</span>
          </span>
        </a>
      </div>
      <nav class="admin-nav">
        <a href="dashboard.php" class="active">
          <i data-lucide="inbox" style="width:18px;height:18px"></i> Messages
        </a>
        <a href="../frontend/index.html">
          <i data-lucide="globe" style="width:18px;height:18px"></i> Website
        </a>
      </nav>
      <a href="logout.php" class="logout" style="display:flex;align-items:center;gap:0.65rem;padding:0.75rem 0.9rem;border-radius:10px;color:#f87171;font-weight:600;font-size:0.92rem">
        <i data-lucide="log-out" style="width:18px;height:18px"></i> Logout
      </a>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <div>
          <h1>Contact Messages</h1>
          <p>Welcome back, <?= e($_SESSION['admin_username'] ?? 'Admin') ?></p>
        </div>
      </div>

      <?php if ($flash): ?>
        <div class="flash flash-success"><?= e($flash) ?></div>
      <?php endif; ?>

      <div class="stats-row">
        <div class="stat-box">
          <span>Total Messages</span>
          <strong><?= $total ?></strong>
        </div>
        <div class="stat-box">
          <span>Today</span>
          <strong><?= $today ?></strong>
        </div>
        <div class="stat-box">
          <span>Last 7 Days</span>
          <strong><?= $week ?></strong>
        </div>
      </div>

      <div class="table-wrap">
        <?php if (empty($messages)): ?>
          <div class="empty-state">
            <p>No messages yet. Submissions from the contact form will appear here.</p>
          </div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($messages as $row): ?>
                <tr>
                  <td>#<?= (int) $row['id'] ?></td>
                  <td><?= e($row['name']) ?></td>
                  <td><a href="mailto:<?= e($row['email']) ?>" style="color:#0bc965"><?= e($row['email']) ?></a></td>
                  <td class="message-cell"><?= e($row['message']) ?></td>
                  <td><?= e(date('M j, Y g:i A', strtotime($row['created_at']))) ?></td>
                  <td>
                    <form method="POST" action="delete.php" onsubmit="return confirm('Delete this message?');">
                      <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf_token'] ?? '') ?>" />
                      <button type="submit" class="btn-delete">
                        <i data-lucide="trash-2" style="width:14px;height:14px"></i> Delete
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <script>lucide.createIcons();</script>
</body>
</html>
