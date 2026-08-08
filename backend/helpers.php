<?php
/**
 * Shared helpers
 */

require_once __DIR__ . '/smtp.php';

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
 * Short alert: "kisi ne detail fill ki"
 */
function notify_admin(string $subject, string $bodyText, ?string $replyTo = null): void
{
    $to = defined('NOTIFY_EMAIL') ? NOTIFY_EMAIL : 'devtaknowledge@gmail.com';

    if (defined('SMTP_HOST') && SMTP_HOST !== '' && defined('SMTP_USER') && SMTP_USER !== '') {
        smtp_send($to, $subject, $bodyText, $replyTo);
        return;
    }

    // Fallback (may not work on some hosts)
    $host = preg_replace('/^www\./', '', strtolower($_SERVER['HTTP_HOST'] ?? 'mydevta.com'));
    $headers = "From: DEVTA <noreply@{$host}>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $bodyText, $headers);
}
