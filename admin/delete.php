<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();

$type = $_POST['type'] ?? 'contact';
$redirect = 'dashboard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}

// CSRF check
if (
    empty($_POST['csrf']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf'])
) {
    $_SESSION['flash'] = 'Invalid security token. Please try again.';
    header('Location: ' . $redirect);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['flash'] = 'Invalid entry ID.';
    header('Location: ' . $redirect);
    exit;
}

try {
    $pdo = getDB();

    if ($type === 'service_request') {
        $stmt = $pdo->prepare('DELETE FROM service_requests WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $_SESSION['flash'] = $stmt->rowCount()
            ? 'Quote request deleted successfully.'
            : 'Quote request not found.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM contacts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $_SESSION['flash'] = $stmt->rowCount()
            ? 'Message deleted successfully.'
            : 'Message not found.';
    }
} catch (PDOException $e) {
    $_SESSION['flash'] = 'Failed to delete entry.';
}

header('Location: ' . $redirect);
exit;
