<?php
/**
 * Contact form handler
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!empty($_POST['website'])) {
    json_response(['success' => true, 'message' => 'Thank you! We will contact you soon.']);
}

$name    = clean_text($_POST['name'] ?? '');
$email   = clean_text($_POST['email'] ?? '');
$message = clean_text($_POST['message'] ?? '');

$errors = [];

if ($name === '' || str_len($name) < 2 || str_len($name) > 100) {
    $errors[] = 'Please enter a valid name (2–100 characters).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || str_len($email) > 150) {
    $errors[] = 'Please enter a valid email address.';
}

if ($message === '' || str_len($message) < 10 || str_len($message) > 2000) {
    $errors[] = 'Please enter a message (10–2000 characters).';
}

if (!empty($errors)) {
    json_response(['success' => false, 'message' => implode(' ', $errors)], 422);
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

    json_response([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully.',
    ]);
} catch (Throwable $e) {
    json_response([
        'success' => false,
        'message' => 'Server error. Please check database settings in backend/config.php.',
    ], 500);
}
