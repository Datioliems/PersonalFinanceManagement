<?php
// ============================================================
// SERVICE — app/Services/MailService.php  (PHPMailer edition)
// ============================================================
// Dùng PHPMailer API thay vì socket thủ công.
// Không cần Composer — wrapper tương thích nằm ở vendor/phpmailer/
//
// Nếu muốn PHPMailer thật: composer require phpmailer/phpmailer
// rồi xoá vendor/phpmailer/ (folder tự viết).
// ============================================================

namespace App\Services;

// Ưu tiên Composer; fallback về wrapper tự viết
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
} else {
    require_once BASE_PATH . '/vendor/phpmailer/PHPMailer.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class MailService
{
    private bool   $hasSmtp;

    public function __construct() {}

    // ── Public API ────────────────────────────────────────────

    public function sendVerification(string $toEmail, string $toName, string $verifyUrl): bool
    {
        return $this->send(
            $toEmail, $toName,
            '[FinanceApp] Xác nhận địa chỉ email',
            $this->tplVerification($toName, $verifyUrl)
        );
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl): bool
    {
        return $this->send(
            $toEmail, $toName,
            '[FinanceApp] Đặt lại mật khẩu',
            $this->tplReset($toName, $resetUrl)
        );
    }

    // ── Private ───────────────────────────────────────────────

    private function send(string $to, string $name, string $subject, string $html): bool
    {
        // Luôn ghi log để dễ debug khi test localhost
        $this->writeLog($to, $subject, $html);

        $host = $_ENV['MAIL_HOST'] ?? '';
        $user = $_ENV['MAIL_USER'] ?? '';
        $pass = $_ENV['MAIL_PASS'] ?? '';

        if (empty($host) || empty($user) || empty($pass)) {
            // Không config SMTP → fallback php mail()
            $from    = $_ENV['MAIL_FROM']      ?? 'noreply@de13finance.local';
            $fromName= $_ENV['MAIL_FROM_NAME'] ?? 'FinanceApp';
            $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n"
                     . "From: {$fromName} <{$from}>\r\n";
            return @mail("{$name} <{$to}>", $subject, $html, $headers);
        }

        return $this->sendViaPHPMailer($to, $name, $subject, $html);
    }

    private function sendViaPHPMailer(string $to, string $name, string $subject, string $html): bool
    {
        $mail = new PHPMailer(true); // true = throw Exception khi lỗi

        try {
            // Server
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USER'];
            $mail->Password   = $_ENV['MAIL_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
            $mail->SMTPDebug  = (int)($_ENV['MAIL_DEBUG'] ?? 0);
            $mail->CharSet    = 'UTF-8';

            // Sender
            $mail->setFrom(
                $_ENV['MAIL_FROM']      ?? 'noreply@de13finance.local',
                $_ENV['MAIL_FROM_NAME'] ?? 'FinanceApp'
            );

            // Recipient
            $mail->addAddress($to, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<p>', '</p>'], "\n", $html));

            $mail->send();
            return true;

        } catch (MailException $e) {
            error_log('[MailService] PHPMailer error: ' . $e->getMessage());
            return false;
        }
    }

    private function writeLog(string $to, string $subject, string $body): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents(
            $dir . '/mail.log',
            sprintf("[%s] To: %s | Subject: %s\n%s\n%s\n",
                date('Y-m-d H:i:s'), $to, $subject,
                strip_tags(str_replace(['<br>', '<p>', '</p>'], "\n", $body)),
                str_repeat('-', 60)
            ),
            FILE_APPEND | LOCK_EX
        );
    }

    // ── Email Templates ───────────────────────────────────────

    private function tplVerification(string $name, string $url): string
    {
        return <<<HTML
<!DOCTYPE html><html lang="vi"><body style="font-family:sans-serif;max-width:520px;margin:auto;padding:24px">
<h2 style="color:#1d1d1b">Xác nhận email đăng ký</h2>
<p>Xin chào <strong>{$name}</strong>, bạn đã đăng ký tài khoản FinanceApp.</p>
<p style="margin:28px 0;text-align:center">
  <a href="{$url}" style="background:#1d1d1b;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:500;display:inline-block">✅ Xác nhận email</a>
</p>
<p style="color:#888;font-size:13px">Link có hiệu lực trong <strong>24 giờ</strong>.<br>Nếu bạn không đăng ký, hãy bỏ qua email này.</p>
<hr style="border:none;border-top:1px solid #eee;margin:20px 0">
<p style="color:#aaa;font-size:12px">FinanceApp — Đề 13 OOP MVC</p>
</body></html>
HTML;
    }

    private function tplReset(string $name, string $url): string
    {
        return <<<HTML
<!DOCTYPE html><html lang="vi"><body style="font-family:sans-serif;max-width:520px;margin:auto;padding:24px">
<h2 style="color:#dc2626">Đặt lại mật khẩu</h2>
<p>Xin chào <strong>{$name}</strong>, chúng tôi nhận được yêu cầu đặt lại mật khẩu.</p>
<p style="margin:28px 0;text-align:center">
  <a href="{$url}" style="background:#dc2626;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:500;display:inline-block">🔑 Đặt lại mật khẩu</a>
</p>
<p style="color:#888;font-size:13px">Link có hiệu lực trong <strong>30 phút</strong>.<br>Nếu không phải bạn yêu cầu, hãy bỏ qua.</p>
<hr style="border:none;border-top:1px solid #eee;margin:20px 0">
<p style="color:#aaa;font-size:12px">FinanceApp — Đề 13 OOP MVC</p>
</body></html>
HTML;
    }
}