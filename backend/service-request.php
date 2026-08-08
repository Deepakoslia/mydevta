<?php
/**
 * Service callback request handler
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}

// Honeypot anti-spam
if (!empty($_POST['website'])) {
    json_response(['success' => true, 'message' => 'Thank you! We will contact you soon.']);
}

$name    = clean_text($_POST['name'] ?? '');
$email   = clean_text($_POST['email'] ?? '');
$phone   = clean_text($_POST['phone'] ?? '');
$service = clean_text($_POST['service'] ?? '');

$allowedServices = [
    'Marketing & Branding',
    'HR Consulting',
    'Growth Consulting',
    'Growth Consulting for MSMEs',
    'Accounting & Tax',
    'Startup & MCA Compliance',
    'Insurance & Lending',
    'Legal Advisory',
    'Tax Filing Assistance',
    'Government Services',
];

$errors = [];

if ($name === '' || str_len($name) < 2 || str_len($name) > 100) {
    $errors[] = 'Please enter a valid name (2–100 characters).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || str_len($email) > 150) {
    $errors[] = 'Please enter a valid email address.';
}

$phoneDigits = preg_replace('/[\s\-\(\)]+/', '', $phone) ?? '';
if ($phone === '' || !preg_match('/^\+?[0-9]{10,15}$/', $phoneDigits)) {
    $errors[] = 'Please enter a valid phone number (10–15 digits).';
}

if ($service === '' || !in_array($service, $allowedServices, true)) {
    $errors[] = 'Please select a valid service.';
}

if (!empty($errors)) {
    json_response(['success' => false, 'message' => implode(' ', $errors)], 422);
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        'INSERT INTO service_requests (name, email, phone, service)
         VALUES (:name, :email, :phone, :service)'
    );
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':phone'   => $phoneDigits,
        ':service' => $service,
    ]);

    // Email so you know WHICH service was requested
    notify_admin(
        'DEVTA Callback: ' . $service,
        "New Request Callback\n\n"
        . "Service : {$service}\n"
        . "Name    : {$name}\n"
        . "Email   : {$email}\n"
        . "Phone   : {$phoneDigits}\n"
        . "Time    : " . date('Y-m-d H:i:s') . "\n"
    );

    json_response([
        'success' => true,
        'message' => 'Thank you! Your callback request has been submitted. We will contact you soon.',
    ]);
} catch (Throwable $e) {
    json_response([
        'success' => false,
        'message' => 'Database not connected. Open /install.php on Hostinger and set MySQL details. Error: ' . $e->getMessage(),
    ], 500);
}
