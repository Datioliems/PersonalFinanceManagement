<?php
// ============================================================
// WALLET MODEL — app/Models/Wallet.php
// Thêm vào Ngày 6
// ============================================================

namespace App\Models;

class Wallet
{
    public const TYPE_CASH    = 'cash';
    public const TYPE_BANK    = 'bank';
    public const TYPE_EWALLET = 'e_wallet';
    public const TYPE_CREDIT  = 'credit';

    public function __construct(
        private int     $id,
        private int     $userId,
        private string  $name,
        private string  $type      = self::TYPE_CASH,
        private float   $balance   = 0.0,
        private string  $currency  = 'VND',
        private ?string $color     = null,
        private ?string $icon      = null,
        private bool    $isDefault = false,
        private bool    $isActive  = true,
    ) {}

    /**
     * Ví credit cho phép âm (đang nợ).
     * Ví cash/bank/e_wallet không được âm.
     */
    public function canDeduct(float $amount): bool
    {
        if ($this->type === self::TYPE_CREDIT) return true;
        return ($this->balance - $amount) >= 0;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_CASH    => 'Tiền mặt',
            self::TYPE_BANK    => 'Ngân hàng',
            self::TYPE_EWALLET => 'Ví điện tử',
            self::TYPE_CREDIT  => 'Thẻ tín dụng',
            default            => $this->type,
        };
    }

    public static function fromArray(array $r): self
    {
        return new self(
            id:        (int)   $r['id'],
            userId:    (int)   $r['user_id'],
            name:             $r['name'],
            type:             $r['type'],
            balance:   (float) $r['balance'],
            currency:         $r['currency'] ?? 'VND',
            color:            $r['color']    ?? null,
            icon:             $r['icon']     ?? null,
            isDefault: (bool)  $r['is_default'],
            isActive:  (bool)  $r['is_active'],
        );
    }

    public function getId(): int       { return $this->id; }
    public function getUserId(): int   { return $this->userId; }
    public function getName(): string  { return $this->name; }
    public function getType(): string  { return $this->type; }
    public function getBalance(): float { return $this->balance; }
    public function getCurrency(): string { return $this->currency; }
    public function getColor(): ?string  { return $this->color; }
    public function getIcon(): ?string   { return $this->icon; }
    public function isDefault(): bool    { return $this->isDefault; }
    public function isActive(): bool     { return $this->isActive; }
}
