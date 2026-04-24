<?php
// ============================================================
// TRANSFER SERVICE — app/Services/TransferService.php
// Thêm vào Ngày 6-7
// ============================================================
// Đây là file phức tạp nhất trong phần nâng cao.
// Bắt buộc dùng PDO::beginTransaction() để đảm bảo
// trừ ví A và cộng ví B là atomic.
// ============================================================

namespace App\Services;

use App\Core\Database;
use App\Models\Wallet;
use App\Repositories\{TransferRepository, WalletRepository};

class TransferService
{
    public function __construct(
        private TransferRepository $transferRepo,
        private WalletRepository   $walletRepo
    ) {}

    /**
     * Chuyển tiền từ ví A sang ví B.
     *
     * Luồng chi tiết:
     *   1. Validate: from != to, amount > 0
     *   2. Kiểm tra from_wallet thuộc về user (ownership)
     *   3. Kiểm tra to_wallet thuộc về user
     *   4. Kiểm tra ví nguồn có đủ tiền không (canDeduct)
     *   5. BEGIN PDO transaction
     *   6. INSERT vào bảng transfers
     *   7. UPDATE wallets balance - amount (từ ví nguồn)
     *   8. UPDATE wallets balance + amount (vào ví đích)
     *   9. COMMIT
     *   (Nếu bước nào lỗi → ROLLBACK tự động)
     *
     * @throws \InvalidArgumentException nếu validate sai
     * @throws \RuntimeException         nếu DB lỗi
     */
    public function transfer(
        int    $userId,
        int    $fromWalletId,
        int    $toWalletId,
        float  $amount,
        string $transferDate,
        string $note = ''
    ): void {
        // 1. Validate cơ bản
        if ($fromWalletId === $toWalletId) {
            throw new \InvalidArgumentException('Không thể chuyển tiền trong cùng 1 ví.');
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Số tiền chuyển phải lớn hơn 0.');
        }

        // 2-3. Kiểm tra ownership
        $fromRow = $this->walletRepo->findById($fromWalletId);
        if (!$fromRow || (int)$fromRow['user_id'] !== $userId) {
            throw new \InvalidArgumentException('Ví nguồn không tồn tại hoặc không thuộc về bạn.');
        }
        $toRow = $this->walletRepo->findById($toWalletId);
        if (!$toRow || (int)$toRow['user_id'] !== $userId) {
            throw new \InvalidArgumentException('Ví đích không tồn tại hoặc không thuộc về bạn.');
        }

        // 4. Kiểm tra số dư
        $fromWallet = Wallet::fromArray($fromRow);
        if (!$fromWallet->canDeduct($amount)) {
            throw new \InvalidArgumentException(
                "Ví \"{$fromWallet->getName()}\" không đủ số dư (hiện có: "
                . number_format($fromWallet->getBalance(), 0, ',', '.') . " VND)."
            );
        }

        // 5-9. Atomic DB transaction
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            // Bước 6: Ghi lịch sử chuyển tiền
            $this->transferRepo->save([
                'user_id'        => $userId,
                'from_wallet_id' => $fromWalletId,
                'to_wallet_id'   => $toWalletId,
                'amount'         => $amount,
                'note'           => $note,
                'transfer_date'  => $transferDate,
            ]);

            // Bước 7: Trừ ví nguồn
            $this->walletRepo->updateBalance($fromWalletId, $userId, -$amount);

            // Bước 8: Cộng ví đích
            $this->walletRepo->updateBalance($toWalletId, $userId, +$amount);

            $pdo->commit();

        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('TransferService error: ' . $e->getMessage());
            throw new \RuntimeException('Chuyển tiền thất bại. Vui lòng thử lại.');
        }
    }
}
