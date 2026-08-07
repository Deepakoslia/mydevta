<?php
/**
 * Contact form handler — stores validated submissions in MySQL
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Honeypot anti-spam
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you soon.']);
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors[] = 'Please enter a valid name (2–100 characters).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = 'Please enter a valid email address.';
}

if ($message === '' || mb_strlen($message) < 10 || mb_strlen($message) > 2000) {
    $errors[] = 'Please enter a message (10–2000 characters).';
}

// Basic sanitization (strip tags)
$name    = strip_tags($name);
$email   = filter_var($email, FILTER_SANITIZE_EMAIL);
$message = strip_tags($message);

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        'INSERT INTO contacts (name, email, message) VALUES (:name, :email, :message)'
    );
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':message' => $message,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong. Please try again later.',
    ]);
}
