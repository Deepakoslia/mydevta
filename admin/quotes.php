<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

try {
    $pdo = getDB();

    $total = (int) $pdo->query('SELECT COUNT(*) FROM service_requests')->fetchColumn();
    $today = (int) $pdo->query('SELECT COUNT(*) FROM service_requests WHERE DATE(created_at) = CURDATE()')->fetchColumn();
    $week  = (int) $pdo->query('SELECT COUNT(*) FROM service_requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();

    $stmt = $pdo->query(
        'SELECT id, name, email, phone, service, created_at
         FROM service_requests
         ORDER BY created_at DESC'
    );
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    $total = $today = $week = 0;
    $requests = [];
    $flash = $flash ?: 'Could not load service requests. Import database/service_requests.sql if the table is missing.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Service Quotes | DEVTA Admin</title>
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
        <a href="dashboard.php">
          <i data-lucide="inbox" style="width:18px;height:18px"></i> Messages
        </a>
        <a href="quotes.php" class="active">
          <i data-lucide="file-text" style="width:18px;height:18px"></i> Quotes
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
          <h1>Service Quote Requests</h1>
          <p>Welcome back, <?= e($_SESSION['admin_username'] ?? 'Admin') ?></p>
        </div>
      </div>

      <?php if ($flash): ?>
        <div class="flash flash-success"><?= e($flash) ?></div>
      <?php endif; ?>

      <div class="stats-row">
        <div class="stat-box">
          <span>Total Quotes</span>
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
        <?php if (empty($requests)): ?>
          <div class="empty-state">
            <p>No quote requests yet. Submissions from the Get Quote modal will appear here.</p>
          </div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Service</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $row): ?>
                <tr>
                  <td>#<?= (int) $row['id'] ?></td>
                  <td><span style="color:#00ff88;font-weight:700"><?= e($row['service']) ?></span></td>
                  <td><?= e($row['name']) ?></td>
                  <td><a href="mailto:<?= e($row['email']) ?>" style="color:#0bc965"><?= e($row['email']) ?></a></td>
                  <td><a href="tel:<?= e($row['phone']) ?>" style="color:#9ca3af"><?= e($row['phone']) ?></a></td>
                  <td><?= e(date('M j, Y g:i A', strtotime($row['created_at']))) ?></td>
                  <td>
                    <form method="POST" action="delete.php" onsubmit="return confirm('Delete this quote request?');">
                      <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                      <input type="hidden" name="type" value="service_request" />
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
