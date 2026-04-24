<?php
// ============================================================
// TRANSFER CONTROLLER — app/Controllers/TransferController.php
// Thêm vào Ngày 6-7
// ============================================================

namespace App\Controllers;

use App\Services\TransferService;
use App\Repositories\{TransferRepository, WalletRepository};
use App\Helpers\{CsrfTokenManager, FlashMessage, Paginator};

class TransferController extends BaseController
{
    private TransferService  $transferService;
    private WalletRepository $walletRepo;

    public function __construct()
    {
        $this->walletRepo      = new WalletRepository();
        $this->transferService = new TransferService(
            new TransferRepository(),
            $this->walletRepo
        );
    }

    /** GET /transfers — Lịch sử chuyển tiền */
    public function index(): void
    {
        // TODO: findByUser + Paginator
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** GET /transfers/create — Form chuyển tiền */
    public function create(): void
    {
        $wallets = $this->walletRepo->findActiveByUser($this->currentUserId());
        $csrf    = CsrfTokenManager::generate();
        $this->render('transfers/create', compact('wallets', 'csrf'));
    }

    /**
     * POST /transfers
     * TODO:
     *   1. validate CSRF
     *   2. validate from_wallet_id != to_wallet_id
     *   3. transferService->transfer(...)
     *   4. Bắt InvalidArgumentException → flash danger → redirect /transfers/create
     *   5. Bắt RuntimeException → flash danger "Lỗi hệ thống" → redirect
     *   6. Thành công → flash success → redirect /transfers
     */
    public function store(): void
    {
        // TODO
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }
}
