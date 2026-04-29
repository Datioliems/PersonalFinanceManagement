<?php
// ============================================================
// SERVICE — app/Services/IncomeService.php
// ============================================================
// Business logic cho thu nhập.
// Pattern giống ExpenseService nhưng đơn giản hơn:
//   - Không check budget
//   - notify() chỉ ghi log
//
// TV4 viết — Ngày 3
// ============================================================

namespace App\Services;

use App\Models\IncomeTransaction;
use App\Repositories\TransactionRepository;

class IncomeService
{
    public function __construct(
        private TransactionRepository $txRepo
    ) {}

    /**
     * Thêm thu nhập mới.
     *
     * Luồng: new IncomeTransaction → process() → validate → save → notify
     *
     * @param  array $data   ['category_id', 'amount', 'trans_date', 'note']
     * @param  int   $userId
     * @throws \InvalidArgumentException nếu validate thất bại
     */
    public function add(array $data, int $userId): void
    {
        $tx = new IncomeTransaction(
            userId:     $userId,
            categoryId: (int)($data['category_id'] ?? 0),
            amount:     (float)($data['amount']      ?? 0),
            transDate:  $data['trans_date']           ?? '',
            note:       trim($data['note']            ?? ''),
        );

        // Template Method: validate → save → notify
        $tx->process();
        // Không có budget alert — income không cần check
    }

    /**
     * Cập nhật thu nhập.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function update(int $id, int $userId, array $data): void
    {
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Số tiền phải lớn hơn 0.');
        }

        $updatedData = [
            'category_id' => (int)($data['category_id'] ?? 0),
            'amount'      => $amount,
            'note'        => trim($data['note'] ?? ''),
            'trans_date'  => $data['trans_date'] ?? '',
        ];
        if (isset($data['type'])) {
            $updatedData['type'] = $data['type'];
        }
        
        $updated = $this->txRepo->update($id, $userId, $updatedData);

        if (!$updated) {
            throw new \RuntimeException('Không tìm thấy giao dịch hoặc bạn không có quyền sửa.');
        }
    }

    /**
     * Xoá thu nhập — kiểm tra ownership.
     *
     * @throws \RuntimeException
     */
    public function delete(int $id, int $userId): void
    {
        if (!$this->txRepo->deleteByIdAndUser($id, $userId)) {
            throw new \RuntimeException('Không tìm thấy giao dịch hoặc bạn không có quyền xoá.');
        }
    }
}
