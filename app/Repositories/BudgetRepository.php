<?php
// ============================================================
// BUDGET REPOSITORY — app/Repositories/BudgetRepository.php
// ============================================================
// TODO (TV2 — Ngày 2)
// ============================================================

namespace App\Repositories;

interface BudgetRepositoryInterface
{
    public function findByUserAndMonth(int $userId, int $month, int $year): array;
    public function findByCategoryAndMonth(int $userId, int $categoryId, int $month, int $year): ?array;
    public function upsert(array $data): bool;
}

class BudgetRepository extends BaseRepository implements BudgetRepositoryInterface
{
    protected function getTable(): string { return 'budgets'; }

    /**
     * Lấy tất cả budget của user trong tháng.
     * Dùng bởi: BudgetController::index()
     * Nên JOIN categories để lấy tên danh mục.
     */
    public function findByUserAndMonth(int $userId, int $month, int $year): array
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV2 Ngày 2');
    }

    /**
     * Lấy budget của 1 danh mục cụ thể trong tháng.
     * Dùng bởi: BudgetService::checkAlert()
     */
    public function findByCategoryAndMonth(int $userId, int $categoryId,
                                            int $month, int $year): ?array
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV2 Ngày 2');
    }

    /**
     * Tạo mới hoặc cập nhật ngân sách (INSERT ... ON DUPLICATE KEY UPDATE).
     */
    public function upsert(array $data): bool
    {
        // TODO: INSERT INTO budgets (...) VALUES (...) ON DUPLICATE KEY UPDATE limit_amount=VALUES(limit_amount)
        throw new \RuntimeException('Chưa implement — TV2 Ngày 2');
    }
}
