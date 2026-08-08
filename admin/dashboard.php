<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$messages = [];
$requests = [];
$totalMessages = $totalRequests = 0;
$dbDriver = '';
$dbError = '';

try {
    $pdo = getDB();
    $dbDriver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    // Ensure tables exist
    ensureSchema($pdo);

    $totalMessages = (int) $pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
    $totalRequests = (int) $pdo->query('SELECT COUNT(*) FROM service_requests')->fetchColumn();

    $messages = $pdo->query(
        'SELECT id, name, email, message, created_at FROM contacts ORDER BY created_at DESC'
    )->fetchAll();

    $requests = $pdo->query(
        'SELECT id, name, email, phone, service, created_at FROM service_requests ORDER BY created_at DESC'
    )->fetchAll();
} catch (Throwable $e) {
    $dbError = $e->getMessage();
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
          <i data-lucide="layout-dashboard" style="width:18px;height:18px"></i> Dashboard
        </a>
        <a href="quotes.php">
          <i data-lucide="phone-call" style="width:18px;height:18px"></i> Callbacks
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
          <h1>Admin Dashboard</h1>
          <p>
            Welcome, <?= e($_SESSION['admin_username'] ?? 'Admin') ?>
            <?php if ($dbDriver): ?>
              · DB: <span style="color:#00ff88"><?= e($dbDriver) ?></span>
            <?php endif; ?>
          </p>
        </div>
      </div>

      <?php if ($flash): ?>
        <div class="flash flash-success"><?= e($flash) ?></div>
      <?php endif; ?>

      <?php if ($dbError): ?>
        <div class="flash" style="background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.35);color:#f87171">
          Database error: <?= e($dbError) ?><br />
          Hostinger pe <a href="../install.php" style="color:#00ff88">install.php</a> chalao ya <code>backend/config.local.php</code> check karo.
        </div>
      <?php endif; ?>

      <div class="stats-row">
        <div class="stat-box">
          <span>Request Callbacks</span>
          <strong><?= $totalRequests ?></strong>
        </div>
        <div class="stat-box">
          <span>Contact Messages</span>
          <strong><?= $totalMessages ?></strong>
        </div>
        <div class="stat-box">
          <span>Total Leads</span>
          <strong><?= $totalRequests + $totalMessages ?></strong>
        </div>
      </div>

      <h2 style="font-size:1.15rem;margin:0 0 0.85rem">Request Callbacks (Services)</h2>
      <div class="table-wrap" style="margin-bottom:2rem">
        <?php if (empty($requests)): ?>
          <div class="empty-state">
            <p>Abhi koi callback nahi. Website par <b>Request Callback</b> submit karo — yahan dikhega.</p>
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
                  <td><?= e($row['phone']) ?></td>
                  <td><?= e(date('M j, Y g:i A', strtotime($row['created_at']))) ?></td>
                  <td>
                    <form method="POST" action="delete.php" onsubmit="return confirm('Delete this callback?');">
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

      <h2 style="font-size:1.15rem;margin:0 0 0.85rem">Contact Messages</h2>
      <div class="table-wrap">
        <?php if (empty($messages)): ?>
          <div class="empty-state">
            <p>Abhi koi contact message nahi. Contact page se form bhejo — yahan dikhega.</p>
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
                      <input type="hidden" name="type" value="contact" />
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
