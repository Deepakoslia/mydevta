<?php
/**
 * Shared helpers for API endpoints (Hostinger-safe)
 */

function str_len(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function json_response(array $payload, int $status = 200): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
    echo json_encode($payload);
    exit;
}

function clean_text(string $value): string
{
    $value = trim($value);
    $value = strip_tags($value);
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value) ?? '';
}

/**
 * Notify admin by email (Hostinger mail() usually works)
 */
function notify_admin(string $subject, string $body): void
{
    $to = defined('NOTIFY_EMAIL') ? NOTIFY_EMAIL : 'devtaknowledge@gmail.com';
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        'From: DEVTA Website <noreply@' . ($_SERVER['HTTP_HOST'] ?? 'mydevta.com') . '>',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];
    @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}
