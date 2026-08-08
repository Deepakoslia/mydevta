<?php
/**
 * Minimal SMTP sender for Hostinger / Gmail
 */

function smtp_send(string $to, string $subject, string $bodyText, ?string $replyTo = null): array
{
    $host   = defined('SMTP_HOST') ? SMTP_HOST : '';
    $port   = defined('SMTP_PORT') ? (int) SMTP_PORT : 587;
    $user   = defined('SMTP_USER') ? SMTP_USER : '';
    $pass   = defined('SMTP_PASS') ? SMTP_PASS : '';
    $secure = defined('SMTP_SECURE') ? strtolower((string) SMTP_SECURE) : 'tls';
    $fromEmail = (defined('SMTP_FROM') && SMTP_FROM !== '') ? SMTP_FROM : $user;

    if ($host === '' || $user === '' || $pass === '') {
        return ['ok' => false, 'error' => 'SMTP not set. Open /backend/setup-mail.php'];
    }

    $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host;
    $errno = 0;
    $errstr = '';
    $fp = @stream_socket_client(
        $remote . ':' . $port,
        $errno,
        $errstr,
        25,
        STREAM_CLIENT_CONNECT,
        stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
    );

    if (!$fp) {
        return ['ok' => false, 'error' => "SMTP connect failed: {$errstr}"];
    }

    stream_set_timeout($fp, 20);

    $read = static function () use ($fp) {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) {
                break;
            }
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $cmd = static function (string $command, array $expect) use ($fp, $read) {
        fwrite($fp, $command . "\r\n");
        $resp = $read();
        $code = (int) substr($resp, 0, 3);
        if (!in_array($code, $expect, true)) {
            throw new RuntimeException(trim($resp));
        }
    };

    try {
        $read();
        $cmd('EHLO localhost', [250]);

        if ($secure === 'tls') {
            $cmd('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('STARTTLS failed');
            }
            $cmd('EHLO localhost', [250]);
        }

        $cmd('AUTH LOGIN', [334]);
        $cmd(base64_encode($user), [334]);
        $cmd(base64_encode($pass), [235]);
        $cmd('MAIL FROM:<' . $fromEmail . '>', [250]);
        $cmd('RCPT TO:<' . $to . '>', [250, 251]);
        $cmd('DATA', [354]);

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $payload = "From: DEVTA Website <{$fromEmail}>\r\n"
            . "To: <{$to}>\r\n"
            . "Reply-To: " . ($replyTo ?: $to) . "\r\n"
            . "Subject: {$encodedSubject}\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
            . $bodyText . "\r\n.";

        $cmd($payload, [250]);
        $cmd('QUIT', [221, 250]);
        fclose($fp);

        return ['ok' => true, 'error' => ''];
    } catch (Throwable $e) {
        fclose($fp);
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}
