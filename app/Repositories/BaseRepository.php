<?php
// ============================================================
// REPOSITORY — app/Repositories/BaseRepository.php
// ============================================================
// Abstract class cha — TV3 viết ngày 1, TV2 kế thừa từ đây.
// File này TV2 copy từ TV3 để module độc lập khi test riêng.
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

    abstract protected function getTable(): string;

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ' . $this->getTable() . ' WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(): array
    {
        return $this->db->query('SELECT * FROM ' . $this->getTable())->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM ' . $this->getTable() . ' WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
