<?php
// ============================================================
// SERVICE — app/Services/ExpenseService.php
// ============================================================
// Business logic cho chi tiêu.
// Không biết về HTTP hay HTML.
// TV3 viết — Ngày 3
//
// Luồng add():
//   1. Tạo ExpenseTransaction
//   2. Gán BudgetService để notify() có thể check budget
//   3. Gọi process() — Template Method: validate → save → notify
//   4. Đọc budgetAlert từ transaction
//   5. Trả về alert (Controller set flash message)
// ============================================================

namespace App\Services;

use App\Models\ExpenseTransaction;
use App\Services\BudgetService;
use App\Repositories\{TransactionRepository, BudgetRepository};

class ExpenseService
{
    private BudgetService $budgetService;

    public function __construct(
        private TransactionRepository $txRepo
    ) {
        // Khởi tạo BudgetService với Dependency Injection
        $this->budgetService = new BudgetService(
            new BudgetRepository(),
            $txRepo
        );
    }

    /**
     * Thêm chi tiêu mới.
     *
     * @param  array $data     ['category_id', 'amount', 'trans_date', 'note']
     * @param  int   $userId
     * @return string|null     Chuỗi cảnh báo ngân sách nếu vượt ngưỡng, null nếu không
     * @throws \InvalidArgumentException nếu validate thất bại
     */
    public function add(array $data, int $userId): ?string
    {
        $tx = new ExpenseTransaction(
            userId:     $userId,
            categoryId: (int)($data['category_id'] ?? 0),
            amount:     (float)($data['amount']      ?? 0),
            transDate:  $data['trans_date']           ?? '',
            note:       trim($data['note']            ?? ''),
        );

        // Truyền BudgetService để notify() có thể check ngân sách
        $tx->setBudgetService($this->budgetService);

        // Template Method: validate → save → notify
        $tx->process();

        // Trả về cảnh báo (nếu có) để Controller set flash message
        return $tx->getBudgetAlert();
    }

    /**
     * Cập nhật chi tiêu.
     *
     * @throws \InvalidArgumentException nếu validate thất bại
     * @throws \RuntimeException         nếu không tìm thấy hoặc không có quyền
     */
    public function update(int $id, int $userId, array $data): void
    {
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Số tiền phải lớn hơn 0.');
        }

        $updated = $this->txRepo->update($id, $userId, [
            'category_id' => (int)($data['category_id'] ?? 0),
            'amount'      => $amount,
            'note'        => trim($data['note'] ?? ''),
            'trans_date'  => $data['trans_date'] ?? '',
        ]);

        if (!$updated) {
            throw new \RuntimeException('Không tìm thấy giao dịch hoặc bạn không có quyền sửa.');
        }
    }

    /**
     * Xoá chi tiêu — kiểm tra ownership.
     *
     * @throws \RuntimeException nếu không tìm thấy hoặc không có quyền
     */
    public function delete(int $id, int $userId): void
    {
        if (!$this->txRepo->deleteByIdAndUser($id, $userId)) {
            throw new \RuntimeException('Không tìm thấy giao dịch hoặc bạn không có quyền xoá.');
        }
    }
}
