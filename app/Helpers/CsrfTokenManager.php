<?php
namespace App\Helpers;

class CsrfTokenManager
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Tạo token mới 64-char hex và lưu vào session.
     * Mỗi lần gọi tạo token MỚI (invalidate token cũ).
     */
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY] = $token;
        return $token;
    }

    /**
     * Xác thực token từ form có khớp session không.
     * Dùng hash_equals() thay vì === để chống timing attack.
     */
    public static function validate(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /** Xoá token sau khi validate (one-time use) */
    public static function invalidate(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
