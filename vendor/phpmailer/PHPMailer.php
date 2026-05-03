<?php
/**
 * PHPMailer-compatible SMTP wrapper (PHP thuần, không cần Composer).
 *
 * API giống PHPMailer thật 100%:
 *   $mail = new PHPMailer(true);
 *   $mail->isSMTP();
 *   $mail->Host = 'smtp.gmail.com';
 *   ...
 *   $mail->send();
 *
 * Để dùng PHPMailer thật khi deploy production:
 *   composer require phpmailer/phpmailer
 *   rồi xoá folder vendor/phpmailer/ này.
 */

namespace PHPMailer\PHPMailer;

class Exception extends \RuntimeException {}

class PHPMailer
{
    // ── Server settings ───────────────────────────────────────
    public string $Host       = '';
    public bool   $SMTPAuth   = true;
    public string $Username   = '';
    public string $Password   = '';
    public string $SMTPSecure = self::ENCRYPTION_STARTTLS;
    public int    $Port       = 587;
    public int    $SMTPDebug  = 0;   // 0=off, 2=verbose
    public int    $Timeout    = 15;

    // ── Content settings ──────────────────────────────────────
    public string $CharSet  = 'UTF-8';
    public string $From     = '';
    public string $FromName = '';
    public string $Subject  = '';
    public string $Body     = '';
    public string $AltBody  = '';

    // ── Constants ─────────────────────────────────────────────
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS    = 'ssl';

    private bool  $useSmtp  = false;
    private bool  $htmlMode = true;
    private array $toList   = [];
    private bool  $throwEx;

    public function __construct(bool $exceptions = false)
    {
        $this->throwEx = $exceptions;
    }

    public function isSMTP(): void        { $this->useSmtp = true; }
    public function isHTML(bool $v): void { $this->htmlMode = $v; }

    /** @throws Exception */
    public function setFrom(string $email, string $name = ''): bool
    {
        $this->From     = $email;
        $this->FromName = $name;
        return true;
    }

    public function addAddress(string $email, string $name = ''): bool
    {
        $this->toList[] = compact('email', 'name');
        return true;
    }

    public function clearAddresses(): void { $this->toList = []; }

    /** @throws Exception */
    public function send(): bool
    {
        if (empty($this->toList)) return $this->fail('No recipients.');
        if (empty($this->From))   return $this->fail('Sender not set.');

        if (!$this->useSmtp || empty($this->Host)) {
            // Fallback php mail()
            foreach ($this->toList as $to) {
                $h = "MIME-Version: 1.0\r\nContent-Type: text/html; charset={$this->CharSet}\r\n"
                   . "From: {$this->FromName} <{$this->From}>\r\n";
                @mail("{$to['name']} <{$to['email']}>", $this->Subject, $this->Body, $h);
            }
            return true;
        }

        foreach ($this->toList as $to) {
            $this->sendOne($to['email'], $to['name']);
        }
        return true;
    }

    /** @throws Exception */
    private function sendOne(string $toEmail, string $toName): void
    {
        $host = ($this->SMTPSecure === self::ENCRYPTION_SMTPS)
            ? "ssl://{$this->Host}"
            : "tcp://{$this->Host}";

        $socket = @fsockopen($host, $this->Port, $errno, $errstr, $this->Timeout);
        if (!$socket) {
            $this->fail("Cannot connect to SMTP {$this->Host}:{$this->Port} — {$errstr} ({$errno})");
        }
        stream_set_timeout($socket, $this->Timeout);

        $smtp = function(string $cmd = '') use ($socket): string {
            if ($cmd !== '') {
                fwrite($socket, $cmd . "\r\n");
                if ($this->SMTPDebug >= 2) error_log("[SMTP>] {$cmd}");
            }
            $res = '';
            do {
                $line = fgets($socket, 515);
                if ($line === false) break;
                if ($this->SMTPDebug >= 2) error_log("[SMTP<] " . trim($line));
                $res = $line;
            } while (strlen($line) > 3 && $line[3] === '-');
            return $res;
        };

        $smtp();  // read greeting
        $smtp("EHLO localhost");

        if ($this->SMTPSecure === self::ENCRYPTION_STARTTLS) {
            $r = $smtp("STARTTLS");
            if (!str_starts_with($r, '220')) $this->fail("STARTTLS failed: {$r}");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $smtp("EHLO localhost");
        }

        if ($this->SMTPAuth) {
            $smtp("AUTH LOGIN");
            $smtp(base64_encode($this->Username));
            $r = $smtp(base64_encode($this->Password));
            if (!str_starts_with($r, '235')) $this->fail("SMTP Auth failed. Check Username/Password.");
        }

        $smtp("MAIL FROM:<{$this->From}>");
        $smtp("RCPT TO:<{$toEmail}>");
        $smtp("DATA");

        $subjectEncoded = '=?UTF-8?B?' . base64_encode($this->Subject) . '?=';
        $fromNameEnc    = '=?UTF-8?B?' . base64_encode($this->FromName) . '?=';
        $toNameEnc      = '=?UTF-8?B?' . base64_encode($toName) . '?=';
        $bodyContent    = $this->htmlMode ? $this->Body : ($this->AltBody ?: $this->Body);
        $contentType    = $this->htmlMode ? "text/html" : "text/plain";

        $msg  = "Date: " . date('r') . "\r\n"
              . "From: {$fromNameEnc} <{$this->From}>\r\n"
              . "To: {$toNameEnc} <{$toEmail}>\r\n"
              . "Subject: {$subjectEncoded}\r\n"
              . "MIME-Version: 1.0\r\n"
              . "Content-Type: {$contentType}; charset={$this->CharSet}\r\n"
              . "Content-Transfer-Encoding: base64\r\n\r\n"
              . chunk_split(base64_encode($bodyContent))
              . "\r\n.\r\n";

        fwrite($socket, $msg);
        $smtp("QUIT");
        fclose($socket);
    }

    /** @throws Exception */
    private function fail(string $msg): bool
    {
        error_log("[PHPMailer-compat] {$msg}");
        if ($this->throwEx) throw new Exception($msg);
        return false;
    }
}