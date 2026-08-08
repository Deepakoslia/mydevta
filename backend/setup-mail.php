<?php
/**
 * One-time SMTP setup for notifications
 * https://yourdomain.com/backend/setup-mail.php
 * DELETE after success.
 */

require_once __DIR__ . '/config.php';

$msg = '';
$err = '';

function h($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function smtp_test_send($host, $port, $user, $pass, $secure, $from, $to, $subject, $body): array
{
    $secure = strtolower($secure);
    $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host;
    $errno = 0; $errstr = '';
    $fp = @stream_socket_client(
        $remote . ':' . $port, $errno, $errstr, 25, STREAM_CLIENT_CONNECT,
        stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
    );
    if (!$fp) return ['ok' => false, 'error' => "Connect failed: $errstr"];
    stream_set_timeout($fp, 20);
    $read = function () use ($fp) {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) break;
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = function ($command, $expect) use ($fp, $read) {
        fwrite($fp, $command . "\r\n");
        $resp = $read();
        $code = (int) substr($resp, 0, 3);
        if (!in_array($code, $expect, true)) throw new RuntimeException(trim($resp));
    };
    try {
        $read();
        $cmd('EHLO localhost', [250]);
        if ($secure === 'tls') {
            $cmd('STARTTLS', [220]);
            stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $cmd('EHLO localhost', [250]);
        }
        $cmd('AUTH LOGIN', [334]);
        $cmd(base64_encode($user), [334]);
        $cmd(base64_encode($pass), [235]);
        $cmd('MAIL FROM:<' . $from . '>', [250]);
        $cmd('RCPT TO:<' . $to . '>', [250, 251]);
        $cmd('DATA', [354]);
        $sub = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $cmd("From: DEVTA <$from>\r\nTo: <$to>\r\nSubject: $sub\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n$body\r\n.", [250]);
        $cmd('QUIT', [221, 250]);
        fclose($fp);
        return ['ok' => true, 'error' => ''];
    } catch (Throwable $e) {
        fclose($fp);
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notify = trim($_POST['notify_email'] ?? 'devtaknowledge@gmail.com');
    $host = trim($_POST['smtp_host'] ?? 'smtp.gmail.com');
    $port = (int) ($_POST['smtp_port'] ?? 587);
    $user = trim($_POST['smtp_user'] ?? '');
    $pass = (string) ($_POST['smtp_pass'] ?? '');
    $secure = trim($_POST['smtp_secure'] ?? 'tls');
    $from = trim($_POST['smtp_from'] ?? $user);

    if ($user === '' || $pass === '') {
        $err = 'Gmail + App Password required.';
    } else {
        $file = __DIR__ . '/config.mail.php';
        $php = "<?php\n"
            . "define('NOTIFY_EMAIL', " . var_export($notify, true) . ");\n"
            . "define('SMTP_HOST', " . var_export($host, true) . ");\n"
            . "define('SMTP_PORT', " . var_export($port, true) . ");\n"
            . "define('SMTP_USER', " . var_export($user, true) . ");\n"
            . "define('SMTP_PASS', " . var_export($pass, true) . ");\n"
            . "define('SMTP_SECURE', " . var_export($secure, true) . ");\n"
            . "define('SMTP_FROM', " . var_export($from, true) . ");\n";
        if (file_put_contents($file, $php) === false) {
            $err = 'config.mail.php write fail.';
        } else {
            $test = smtp_test_send($host, $port, $user, $pass, $secure, $from, $notify,
                'DEVTA: Mail OK',
                "Notification mail setup successful.\nYou will receive alerts when someone submits a form.\n"
            );
            if ($test['ok']) {
                $msg = "OK — test mail sent to $notify. Check Inbox/Spam, then DELETE setup-mail.php.";
            } else {
                $err = 'Saved, but test failed: ' . $test['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DEVTA Mail Alert Setup</title>
  <style>
    body{margin:0;font-family:system-ui,sans-serif;background:#0b0f19;color:#fff;min-height:100vh;display:grid;place-items:center;padding:1rem}
    .card{width:min(520px,100%);background:#111827;border:1px solid rgba(0,255,136,.3);border-radius:16px;padding:1.4rem}
    h1{font-size:1.25rem;margin:0 0 .5rem}
    p,li{color:#9ca3af;font-size:.9rem;line-height:1.45}
    label{display:block;margin:.75rem 0 .3rem;font-size:.8rem;color:#9ca3af;font-weight:700}
    input,select{width:100%;box-sizing:border-box;padding:.8rem;border-radius:10px;border:1px solid #333;background:#0b0f19;color:#fff}
    button{margin-top:1rem;width:100%;padding:.9rem;border:0;border-radius:10px;background:#0bc965;color:#04140c;font-weight:800;cursor:pointer}
    .ok{margin:.8rem 0;padding:.8rem;border-radius:10px;background:rgba(11,201,101,.12);border:1px solid #0bc965;color:#00ff88}
    .bad{margin:.8rem 0;padding:.8rem;border-radius:10px;background:rgba(248,113,113,.12);border:1px solid #f87171;color:#f87171}
  </style>
</head>
<body>
  <div class="card">
    <h1>Simple Mail Notification</h1>
    <p>Jab koi form fill kare, short alert <b>devtaknowledge@gmail.com</b> pe jayega.</p>
    <ol>
      <li>Google → Security → 2-Step Verification ON</li>
      <li>App passwords → 16 digit password copy</li>
      <li>Neeche paste karo → Save</li>
    </ol>

    <?php if (!empty($msg)): ?><div class="ok"><?= h($msg) ?></div><?php endif; ?>
    <?php if (!empty($err)): ?><div class="bad"><?= h($err) ?></div><?php endif; ?>

    <form method="POST">
      <label>Notify Email</label>
      <input name="notify_email" value="<?= h($_POST['notify_email'] ?? 'devtaknowledge@gmail.com') ?>" required />

      <label>SMTP Host</label>
      <input name="smtp_host" value="<?= h($_POST['smtp_host'] ?? 'smtp.gmail.com') ?>" required />

      <label>Port</label>
      <input name="smtp_port" value="<?= h($_POST['smtp_port'] ?? '587') ?>" required />

      <label>Secure</label>
      <select name="smtp_secure"><option value="tls" selected>tls</option><option value="ssl">ssl</option></select>

      <label>Gmail</label>
      <input name="smtp_user" value="<?= h($_POST['smtp_user'] ?? 'devtaknowledge@gmail.com') ?>" required />

      <label>Gmail App Password</label>
      <input name="smtp_pass" type="password" required placeholder="16 digit app password" />

      <label>From Email</label>
      <input name="smtp_from" value="<?= h($_POST['smtp_from'] ?? 'devtaknowledge@gmail.com') ?>" required />

      <button type="submit">Save &amp; Send Test Alert</button>
    </form>
  </div>
</body>
</html>
