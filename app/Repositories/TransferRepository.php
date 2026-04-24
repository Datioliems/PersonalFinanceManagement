<?php
// ============================================================
// TRANSFER REPOSITORY — app/Repositories/TransferRepository.php
// Thêm vào Ngày 6-7
// ============================================================

namespace App\Repositories;

class TransferRepository extends BaseRepository
{
    protected function getTable(): string { return 'transfers'; }

    /** Lịch sử chuyển tiền, JOIN 2 lần vào wallets để lấy tên ví */
    public function findByUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        // TODO:
        // SELECT t.*, wf.name as from_name, wf.color as from_color,
        //              wt.name as to_name,   wt.color as to_color
        // FROM transfers t
        // JOIN wallets wf ON t.from_wallet_id = wf.id
        // JOIN wallets wt ON t.to_wallet_id   = wt.id
        // WHERE t.user_id = ? ORDER BY t.transfer_date DESC LIMIT ? OFFSET ?
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** Tổng số record (cho Paginator) */
    public function countByUser(int $userId): int
    {
        // TODO
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /**
     * Lưu transfer mới.
     * QUAN TRỌNG: Gọi từ bên trong PDO transaction của TransferService.
     */
    public function save(array $data): int
    {
        // TODO
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }
}
