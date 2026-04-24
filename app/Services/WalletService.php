<?php
// ============================================================
// WALLET SERVICE — app/Services/WalletService.php
// Thêm vào Ngày 6
// ============================================================

namespace App\Services;

use App\Repositories\WalletRepository;

class WalletService
{
    public function __construct(
        private WalletRepository $walletRepo
    ) {}

    /**
     * Tạo ví mới.
     * Nếu là ví đầu tiên của user → tự set is_default = 1.
     *
     * TODO:
     *   1. $existing = walletRepo->findActiveByUser($userId)
     *   2. $data['is_default'] = empty($existing) ? 1 : 0
     *   3. return walletRepo->save($data)
     */
    public function create(int $userId, array $data): int
    {
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /**
     * Tổng quan tài sản của user:
     * ['wallets'=>[...], 'net_worth'=>float, 'by_type'=>[...]]
     *
     * TODO: dùng walletRepo->findActiveByUser() + getNetWorth()
     */
    public function getOverview(int $userId): array
    {
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }
}
