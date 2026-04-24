<?php
// ============================================================
// CATEGORY REPOSITORY — app/Repositories/CategoryRepository.php
// ============================================================
// TODO (TV2 — Ngày 2)
// ============================================================

namespace App\Repositories;

class CategoryRepository extends BaseRepository
{
    protected function getTable(): string { return 'categories'; }

    /** Lấy tất cả danh mục của 1 user */
    public function findByUser(int $userId): array
    {
        // TODO
        throw new \RuntimeException('Chưa implement — TV2 Ngày 2');
    }

    /** Lưu danh mục mới, trả về ID */
    public function save(array $data): int
    {
        // TODO: INSERT INTO categories (user_id, name, type, icon) VALUES (?,?,?,?)
        throw new \RuntimeException('Chưa implement — TV2 Ngày 2');
    }

    /** Xoá — chỉ xoá được nếu không có giao dịch nào dùng danh mục này */
    public function deleteByIdAndUser(int $id, int $userId): bool
    {
        // TODO: kiểm tra FK constraint transactions.category_id trước khi xoá
        throw new \RuntimeException('Chưa implement — TV2 Ngày 2');
    }
}
