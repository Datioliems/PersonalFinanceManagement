<?php
// ============================================================
// WALLET REPOSITORY — app/Repositories/WalletRepository.php
// Thêm vào Ngày 6
// TODO: Implement tất cả method
// ============================================================

namespace App\Repositories;

class WalletRepository extends BaseRepository
{
    protected function getTable(): string { return 'wallets'; }

    /** Lấy tất cả ví active, ví default hiện trước */
    public function findActiveByUser(int $userId): array
    {
        // TODO: SELECT * FROM wallets WHERE user_id=? AND is_active=1
        //       ORDER BY is_default DESC, name ASC
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** Ví mặc định của user */
    public function findDefaultByUser(int $userId): ?array
    {
        // TODO: SELECT * FROM wallets WHERE user_id=? AND is_default=1 LIMIT 1
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** Tổng tài sản: SUM(balance) tất cả ví active */
    public function getNetWorth(int $userId): float
    {
        // TODO: SELECT COALESCE(SUM(balance),0) FROM wallets
        //       WHERE user_id=? AND is_active=1
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** Lưu ví mới, trả về ID */
    public function save(array $data): int
    {
        // TODO
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /**
     * Cập nhật balance sau transaction/transfer.
     * Luôn gọi từ bên trong PDO transaction của Service.
     * @param float $delta  Dương=cộng, Âm=trừ
     */
    public function updateBalance(int $walletId, int $userId, float $delta): bool
    {
        // TODO: UPDATE wallets SET balance = balance + ? WHERE id=? AND user_id=?
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** Set ví làm default, reset ví cũ trong cùng PDO transaction */
    public function setDefault(int $walletId, int $userId): bool
    {
        // TODO: 2 UPDATE trong transaction
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** Ẩn ví — soft delete */
    public function deactivate(int $walletId, int $userId): bool
    {
        // TODO: UPDATE wallets SET is_active=0 WHERE id=? AND user_id=?
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }
}
