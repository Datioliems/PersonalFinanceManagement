<?php
// ============================================================
// BASE REPOSITORY — app/Repositories/BaseRepository.php
// ============================================================
// Abstract class cha cho tất cả Repository.
// Cung cấp $this->db (PDO) và các method CRUD cơ bản.
//
// TODO (TV3 — Ngày 1): Implement findById(), findAll(), delete()
// ============================================================

namespace App\Repositories;

use App\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Tên bảng DB — mỗi Repository con phải khai báo.
     */
    abstract protected function getTable(): string;

    /**
     * Tìm 1 record theo ID.
     * TODO: viết prepared statement, trả về array|null
     */
    public function findById(int $id): ?array
    {
        // TODO
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . $this->getTable() . ' WHERE id = ?'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Lấy tất cả record.
     * TODO: thêm ORDER BY nếu cần
     */
    public function findAll(): array
    {
        // TODO
        $stmt = $this->db->query('SELECT * FROM ' . $this->getTable());
        return $stmt->fetchAll();
    }

    /**
     * Xoá record theo ID.
     * TODO: trả về bool (true nếu xoá được)
     */
    public function delete(int $id): bool
    {
        // TODO
        $stmt = $this->db->prepare(
            'DELETE FROM ' . $this->getTable() . ' WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
