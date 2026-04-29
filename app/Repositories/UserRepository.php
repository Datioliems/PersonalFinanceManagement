<?php
// ============================================================
// REPOSITORY — app/Repositories/UserRepository.php
// ============================================================
// Toàn bộ SQL liên quan đến bảng users nằm ở đây.
// Tầng Service gọi Repository — không viết SQL trong Service.
//
// TV1 phụ trách — Ngày 2
// ============================================================

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    protected function getTable(): string
    {
        return 'users';
    }

    // ── READ ──────────────────────────────────────────────────

    /**
     * Tìm user theo username (dùng khi đăng nhập).
     * Trả array gồm đủ các cột, hoặc null nếu không tìm thấy.
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Tìm user theo email (dùng khi đăng ký để check trùng).
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Tìm user theo remember_token (dùng cho Remember Me).
     * Token lưu trong DB đã được hash SHA-256.
     */
    public function findByRememberToken(string $hashedToken): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users
             WHERE remember_token = ?
               AND token_expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([$hashedToken]);
        return $stmt->fetch() ?: null;
    }

    // ── WRITE ─────────────────────────────────────────────────

    /**
     * Tạo user mới. Trả về ID vừa insert.
     *
     * @param array $data ['username', 'email', 'password_hash']
     */
    public function save(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash)
             VALUES (:username, :email, :password_hash)'
        );
        $stmt->execute([
            ':username'      => $data['username'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password_hash'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Cập nhật remember_token và thời hạn.
     * Truyền null để xoá token (khi logout).
     */
    public function updateRememberToken(int $userId, ?string $hashedToken, ?\DateTime $expiresAt): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET remember_token = ?, token_expires_at = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $hashedToken,
            $expiresAt?->format('Y-m-d H:i:s'),
            $userId,
        ]);
    }

    // ── LOGIN LOGS ────────────────────────────────────────────

    /**
     * Ghi log đăng nhập vào bảng login_logs.
     *
     * @param int|null $userId null nếu username không tồn tại
     * @param string   $username  username đã nhập
     * @param string   $ip        IP address
     * @param string   $status    'success' | 'failed'
     */
    public function logLogin(?int $userId, string $username, string $ip, string $status): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO login_logs (user_id, ip_address, status)
             VALUES (:user_id, :ip, :status)'
        );
        $stmt->execute([
            ':user_id'  => $userId,
            ':ip'       => $ip,
            ':status'   => $status,
        ]);
    }
}
