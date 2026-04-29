<?php
namespace App\Repositories;

class UserRepository extends BaseRepository
{
    protected function getTable(): string { return 'users'; }

    // ── READ ──────────────────────────────────────────────────
    public function findByUsername(string $username): ?array
    {
        $s = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $s->execute([$username]); return $s->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $s = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $s->execute([$email]); return $s->fetch() ?: null;
    }

    public function findByRememberToken(string $hashedToken): ?array
    {
        $s = $this->db->prepare(
            'SELECT * FROM users WHERE remember_token = ? AND token_expires_at > NOW() AND is_active = 1 LIMIT 1'
        );
        $s->execute([$hashedToken]); return $s->fetch() ?: null;
    }

    public function findByEmailVerifyToken(string $token): ?array
    {
        $s = $this->db->prepare(
            'SELECT * FROM users WHERE email_verify_token = ? AND is_active = 1 LIMIT 1'
        );
        $s->execute([$token]); return $s->fetch() ?: null;
    }

    // ── WRITE ─────────────────────────────────────────────────
    public function save(array $data): int
    {
        $s = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash, email_verify_token)
             VALUES (:username, :email, :password_hash, :token)'
        );
        $s->execute([
            ':username'      => $data['username'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':token'         => $data['email_verify_token'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function markEmailVerified(int $userId): void
    {
        $this->db->prepare('UPDATE users SET email_verified = 1, email_verify_token = NULL WHERE id = ?')
                 ->execute([$userId]);
    }

    /** Xoá user chưa verify để cho phép đăng ký lại */
    public function deleteById(int $userId): void
    {
        $this->db->prepare('DELETE FROM users WHERE id = ? AND email_verified = 0')
                 ->execute([$userId]);
    }

    public function updatePassword(int $userId, string $hash): void
    {
        $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                 ->execute([$hash, $userId]);
    }

    public function updateRememberToken(int $userId, ?string $hashedToken, ?\DateTime $expiresAt): void
    {
        $this->db->prepare('UPDATE users SET remember_token = ?, token_expires_at = ? WHERE id = ?')
                 ->execute([$hashedToken, $expiresAt?->format('Y-m-d H:i:s'), $userId]);
    }

    // ── BRUTE-FORCE ───────────────────────────────────────────
    public function incrementFailedAttempts(int $userId): void
    {
        $this->db->prepare(
            'UPDATE users SET login_attempts = login_attempts + 1,
             locked_until = CASE WHEN login_attempts + 1 >= 5
                 THEN DATE_ADD(NOW(), INTERVAL 60 SECOND) ELSE locked_until END
             WHERE id = ?'
        )->execute([$userId]);
    }

    public function resetFailedAttempts(int $userId): void
    {
        $this->db->prepare('UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?')
                 ->execute([$userId]);
    }

    public function getLockedSeconds(int $userId): int
    {
        $s = $this->db->prepare(
            'SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until)) AS secs FROM users WHERE id = ?'
        );
        $s->execute([$userId]);
        return (int)($s->fetch()['secs'] ?? 0);
    }

    // ── PASSWORD RESET ────────────────────────────────────────
    public function savePasswordResetToken(int $userId, string $tokenHash): void
    {
        $this->db->prepare('UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0')
                 ->execute([$userId]);
        $this->db->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))'
        )->execute([$userId, $tokenHash]);
    }

    public function findValidResetToken(string $tokenHash): ?array
    {
        $s = $this->db->prepare(
            'SELECT pr.*, u.email, u.username FROM password_resets pr JOIN users u ON pr.user_id = u.id
             WHERE pr.token_hash = ? AND pr.used = 0 AND pr.expires_at > NOW() LIMIT 1'
        );
        $s->execute([$tokenHash]); return $s->fetch() ?: null;
    }

    public function markResetTokenUsed(string $tokenHash): void
    {
        $this->db->prepare('UPDATE password_resets SET used = 1 WHERE token_hash = ?')
                 ->execute([$tokenHash]);
    }

    // ── LOGIN LOGS ────────────────────────────────────────────
    public function logLogin(?int $userId, string $username, string $ip, string $status): void
    {
        $this->db->prepare(
            'INSERT INTO login_logs (user_id, username, ip_address, status) VALUES (?,?,?,?)'
        )->execute([$userId, $username, $ip, $status]);
    }

    public function countRecentFailsByIp(string $ip, int $minutes = 15): int
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM login_logs WHERE ip_address = ? AND status = 'failed'
             AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
        );
        $s->execute([$ip, $minutes]);
        return (int) $s->fetchColumn();
    }
}
