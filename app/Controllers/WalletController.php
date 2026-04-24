<?php
// ============================================================
// WALLET CONTROLLER — app/Controllers/WalletController.php
// Thêm vào Ngày 6
// ============================================================

namespace App\Controllers;

use App\Services\WalletService;
use App\Repositories\WalletRepository;
use App\Helpers\{CsrfTokenManager, FlashMessage};

class WalletController extends BaseController
{
    private WalletService $walletService;

    public function __construct()
    {
        $this->walletService = new WalletService(new WalletRepository());
    }

    /** GET /wallets — Danh sách ví + tổng tài sản */
    public function index(): void
    {
        $overview = $this->walletService->getOverview($this->currentUserId());
        $csrf     = CsrfTokenManager::generate();
        $this->render('wallets/index', compact('overview', 'csrf'));
    }

    /** GET /wallets/create */
    public function create(): void
    {
        $csrf = CsrfTokenManager::generate();
        $this->render('wallets/create', compact('csrf'));
    }

    /** POST /wallets */
    public function store(): void
    {
        // TODO: validate CSRF → walletService->create() → redirect /wallets
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** POST /wallets/{id}/default */
    public function setDefault(string $id): void
    {
        // TODO: CSRF → walletRepo->setDefault() → redirect
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }

    /** POST /wallets/{id}/deactivate */
    public function deactivate(string $id): void
    {
        // TODO: Kiểm tra không phải ví default → deactivate → redirect
        throw new \RuntimeException('Chưa implement — Ngày 6');
    }
}
