<?php
// ============================================================
// MODEL — app/Models/Category.php
// ============================================================
// Đại diện cho 1 danh mục thu/chi.
// TV2 viết — Ngày 1
// ============================================================

namespace App\Models;

class Category
{
    public function __construct(
        private int     $id,
        private int     $userId,
        private string  $name,
        private string  $type      = 'both',   // 'income' | 'expense' | 'both'
        private ?string $icon      = null,
        private ?string $color     = null,
        private string  $createdAt = '',
    ) {}

    // ── Getters ───────────────────────────────────────────────
    public function getId(): int       { return $this->id; }
    public function getUserId(): int   { return $this->userId; }
    public function getName(): string  { return $this->name; }
    public function getType(): string  { return $this->type; }
    public function getIcon(): ?string { return $this->icon; }
    public function getColor(): ?string{ return $this->color; }

    /** Label hiển thị loại danh mục */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'income'  => 'Thu nhập',
            'expense' => 'Chi tiêu',
            default   => 'Cả hai',
        };
    }

    /** Tạo từ DB row array */
    public static function fromArray(array $r): self
    {
        return new self(
            id:        (int)  $r['id'],
            userId:    (int)  $r['user_id'],
            name:             $r['name'],
            type:             $r['type']      ?? 'both',
            icon:             $r['icon']      ?? null,
            color:            $r['color']     ?? null,
            createdAt:        $r['created_at'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name'    => $this->name,
            'type'    => $this->type,
            'icon'    => $this->icon,
            'color'   => $this->color,
        ];
    }
}
