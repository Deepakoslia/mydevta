<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// CSRF check
if (
    empty($_POST['csrf']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf'])
) {
    $_SESSION['flash'] = 'Invalid security token. Please try again.';
    header('Location: dashboard.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['flash'] = 'Invalid message ID.';
    header('Location: dashboard.php');
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('DELETE FROM contacts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $_SESSION['flash'] = $stmt->rowCount()
        ? 'Message deleted successfully.'
        : 'Message not found.';
} catch (PDOException $e) {
    $_SESSION['flash'] = 'Failed to delete message.';
}

header('Location: dashboard.php');
exit;
