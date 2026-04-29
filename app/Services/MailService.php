<?php
// ============================================================
// SERVICE — app/Services/MailService.php
// ============================================================
// Gửi email qua SMTP (Gmail hoặc Mailtrap) dùng PHP thuần.
// Không cần cài thêm thư viện.
//
// Cấu hình trong .env:
//   MAIL_HOST=smtp.gmail.com
//   MAIL_PORT=587
//   MAIL_USER=your@gmail.com
//   MAIL_PASS=app_password_16_chars
//   MAIL_FROM=your@gmail.com
//   MAIL_FROM_NAME=FinanceApp
//
// Nếu không cấu hình SMTP → tự động ghi vào storage/logs/mail.log
// ============================================================

namespace App\Services;

class MailService
{
    private string  $host;
    private int     $port;
    private string  $user;
    private string  $pass;
    private string  $from;
    private string  $fromName;
    private bool    $hasSmtp;

    public function __construct()
    {
        $this->host     = $_ENV['MAIL_HOST']      ?? '';
        $this->port     = (int)($_ENV['MAIL_PORT'] ?? 587);
        $this->user     = $_ENV['MAIL_USER']      ?? '';
        $this->pass     = $_ENV['MAIL_PASS']      ?? '';
        $this->from     = $_ENV['MAIL_FROM']      ?? 'noreply@de13finance.local';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'FinanceApp';
        $this->hasSmtp  = !empty($this->host) && !empty($this->user) && !empty($this->pass);
    }

    public function sendVerification(string $toEmail, string $toName, string $verifyUrl): bool
    {
        $subject = '[FinanceApp] Xác nhận địa chỉ email';
        $body    = <<<HTML
<html lang="vi"><body style="font-family:sans-serif;max-width:520px;margin:auto;padding:24px">
<h2 style="color:#1d1d1b">Xác nhận email đăng ký</h2>
<p>Xin chào <strong>{$toName}</strong>, bạn đã đăng ký tài khoản FinanceApp.</p>
<p>Bấm nút bên dưới để xác nhận:</p>
<p style="margin:28px 0;text-align:center">
  <a href="{$verifyUrl}" style="background:#1d1d1b;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:500;display:inline-block">
    ✅ Xác nhận email
  </a>
</p>
<p style="color:#888;font-size:13px">Link có hiệu lực trong <strong>24 giờ</strong>.<br>
Nếu bạn không đăng ký tài khoản này, hãy bỏ qua email này.</p>
<hr style="border:none;border-top:1px solid #eee;margin:20px 0">
<p style="color:#aaa;font-size:12px">FinanceApp — Đề 13 OOP MVC</p>
</body></html>
HTML;
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl): bool
    {
        $subject = '[FinanceApp] Đặt lại mật khẩu';
        $body    = <<<HTML
<html lang="vi"><body style="font-family:sans-serif;max-width:520px;margin:auto;padding:24px">
<h2 style="color:#dc2626">Đặt lại mật khẩu</h2>
<p>Xin chào <strong>{$toName}</strong>, chúng tôi nhận được yêu cầu đặt lại mật khẩu.</p>
<p style="margin:28px 0;text-align:center">
  <a href="{$resetUrl}" style="background:#dc2626;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:500;display:inline-block">
    🔑 Đặt lại mật khẩu
  </a>
</p>
<p style="color:#888;font-size:13px">Link có hiệu lực trong <strong>30 phút</strong>.<br>
Nếu không phải bạn yêu cầu, hãy bỏ qua. Mật khẩu sẽ không thay đổi.</p>
<hr style="border:none;border-top:1px solid #eee;margin:20px 0">
<p style="color:#aaa;font-size:12px">FinanceApp — Đề 13 OOP MVC</p>
</body></html>
HTML;
        return $this->send($toEmail, $toName, $subject, $body);
    }

    // ── Private ───────────────────────────────────────────────

    private function send(string $to, string $name, string $subject, string $html): bool
    {
        // Luôn ghi log để debug
        $this->writeLog($to, $subject, $html);

        if ($this->hasSmtp) {
            return $this->sendViaSmtp($to, $name, $subject, $html);
        }

        // Fallback: php mail() (thường không hoạt động trên localhost)
        $headers  = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->from}>\r\n";
        return @mail("{$name} <{$to}>", $subject, $html, $headers);
    }

    /**
     * Gửi qua SMTP dùng socket PHP thuần (không cần extension).
     * Hỗ trợ STARTTLS (port 587) — chuẩn cho Gmail và Mailtrap.
     */
    private function sendViaSmtp(string $to, string $name, string $subject, string $html): bool
    {
        try {
            // Kết nối socket TCP
            $socket = fsockopen("tcp://{$this->host}", $this->port, $errno, $errstr, 10);
            if (!$socket) {
                error_log("[MailService] Không thể kết nối SMTP: {$errstr} ({$errno})");
                return false;
            }
            stream_set_timeout($socket, 10);

            $read = fn() => fgets($socket, 515);
            $write = fn(string $cmd) => fwrite($socket, $cmd . "\r\n");

            // Hàm đọc và kiểm tra response code
            $expect = function (int $code) use ($read): string {
                $res = '';
                do {
                    $line = fgets(/** @var resource */$GLOBALS['_sock'] ?? STDIN, 515);
                    $res  = $line;
                } while (isset($line[3]) && $line[3] === '-');
                return $res;
            };

            // Dùng cách đơn hơn — đọc response sau mỗi lệnh
            $smtp = function (string $cmd, $s): string {
                fwrite($s, $cmd . "\r\n");
                $res = '';
                do {
                    $line = fgets($s, 515);
                    if ($line === false) break;
                    $res = $line;
                } while (strlen($line) > 3 && $line[3] === '-');
                return $res;
            };

            fgets($socket, 515); // 220 greeting

            $smtp("EHLO localhost", $socket);
            $smtp("STARTTLS", $socket);

            // Upgrade sang TLS
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            $smtp("EHLO localhost", $socket);
            $smtp("AUTH LOGIN", $socket);
            $smtp(base64_encode($this->user), $socket);
            $smtp(base64_encode($this->pass), $socket);
            $smtp("MAIL FROM:<{$this->from}>", $socket);
            $smtp("RCPT TO:<{$to}>", $socket);
            $smtp("DATA", $socket);

            // Tạo message headers + body
            $boundary = md5(uniqid('', true));
            $date     = date('r');
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $message  = "Date: {$date}\r\n";
            $message .= "From: {$this->fromName} <{$this->from}>\r\n";
            $message .= "To: {$name} <{$to}>\r\n";
            $message .= "Subject: {$encodedSubject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "\r\n";
            $message .= chunk_split(base64_encode($html));
            $message .= "\r\n.\r\n";

            fwrite($socket, $message);
            $smtp("QUIT", $socket);
            fclose($socket);

            return true;

        } catch (\Throwable $e) {
            error_log("[MailService] SMTP error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ghi nội dung email vào log — luôn chạy để dễ debug.
     * Mở storage/logs/mail.log để xem link verify/reset khi test.
     */
    private function writeLog(string $to, string $subject, string $body): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $entry = sprintf(
            "[%s] To: %s | Subject: %s\n%s\n%s\n",
            date('Y-m-d H:i:s'), $to, $subject,
            strip_tags(str_replace(['<br>', '<p>', '</p>'], "\n", $body)),
            str_repeat('-', 60)
        );
        file_put_contents($dir . '/mail.log', $entry, FILE_APPEND | LOCK_EX);
    }
}

