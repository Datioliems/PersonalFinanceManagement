<?php
// ============================================================
// USER REPOSITORY — app/Repositories/UserRepository.php
// ============================================================
// TODO (TV1 — Ngày 2): Implement findByUsername(), save()
// ============================================================

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    protected function getTable(): string
    {
        return 'users';
    }

    /**
     * Tìm user theo username.
     * Dùng bởi: AuthService::login()
     *
     * TODO: prepared statement, trả array|null
     */
    public function findByUsername(string $username): ?array
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV1 Ngày 2');
    }

    /**
     * Tìm user theo email.
     * Dùng bởi: AuthController::register() để check trùng email
     */
    public function findByEmail(string $email): ?array
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV1 Ngày 2');
    }

    /**
     * Lưu user mới. Trả về ID vừa tạo.
     * Input: ['username'=>..., 'email'=>..., 'password_hash'=>...]
     */
    public function save(array $data): int
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV1 Ngày 2');
    }

    /**
     * Cập nhật remember_token (Ngày 6 — nâng cao).
     */
    public function updateRememberToken(int $userId, ?string $token): void
    {
        // TODO (TV1 Ngày 6)
    }
}
