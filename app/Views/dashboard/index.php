<?php
// ============================================================
// VIEW — app/Views/dashboard/index.php
// ============================================================
// Biến nhận từ DashboardController::index():
//   $summary   — ['income', 'expense', 'balance']
//   $chartJson — JSON string cho Chart.js (donut + bar)
//   $month, $year
//   $needChartJs = true → footer.php load Chart.js CDN
// ============================================================
$pageTitle = 'Dashboard';
require BASE_PATH . '/app/Views/partials/layout.php';
?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card border-0 h-100" style="background:#f0fdf4">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-arrow-down-circle me-1 text-success"></i>Tổng thu
                </div>
                <div class="h3 fw-semibold text-success mb-0">
                    +<?= number_format($summary['income'], 0, ',', '.') ?>đ
                </div>
                <div class="small text-muted mt-1">Tháng <?= $month ?>/<?= $year ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border-0 h-100" style="background:#fef2f2">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-arrow-up-circle me-1 text-danger"></i>Tổng chi
                </div>
                <div class="h3 fw-semibold text-danger mb-0">
                    -<?= number_format($summary['expense'], 0, ',', '.') ?>đ
                </div>
                <div class="small text-muted mt-1">Tháng <?= $month ?>/<?= $year ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <?php $bal = $summary['balance']; $balPositive = $bal >= 0; ?>
        <div class="card border-0 h-100"
             style="background:<?= $balPositive ? '#eff6ff' : '#fef2f2' ?>">
            <div class="card-body">
                <div class="small text-muted mb-1">
                    <i class="bi bi-wallet2 me-1"></i>Số dư
                </div>
                <div class="h3 fw-semibold mb-0
                             <?= $balPositive ? 'text-primary' : 'text-danger' ?>">
                    <?= ($balPositive ? '+' : '') . number_format($bal, 0, ',', '.') ?>đ
                </div>
                <div class="small text-muted mt-1">Thu - Chi</div>
            </div>
        </div>
    </div>
</div>

<!-- Biểu đồ -->
<div class="row g-4">
    <!-- Biểu đồ cột: thu/chi theo tuần (4 tuần) -->
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-medium">Thu/Chi theo tuần</span>
                <span class="small text-muted">4 tuần gần nhất</span>
            </div>
            <div class="card-body">
                <canvas id="barChart" style="max-height:260px"></canvas>
            </div>
        </div>
    </div>

    <!-- Biểu đồ tròn: chi theo danh mục -->
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-medium">Chi tiêu theo danh mục</span>
                <span class="small text-muted">Tháng <?= $month ?>/<?= $year ?></span>
            </div>
            <div class="card-body d-flex flex-column align-items-center">
                <canvas id="donutChart" style="max-height:240px;max-width:240px"></canvas>
                <div id="donutLegend" class="mt-3 w-100"></div>
            </div>
        </div>
    </div>
</div>

<!-- Nút export + link báo cáo chi tiết -->
<div class="d-flex gap-2 mt-4">
    <a href="<?= BASE_URL ?>/report?month=<?= $month ?>&year=<?= $year ?>"
       class="btn btn-outline-dark btn-sm">
        <i class="bi bi-bar-chart-line me-1"></i>Xem báo cáo chi tiết
    </a>
    <a href="<?= BASE_URL ?>/report/export?month=<?= $month ?>&year=<?= $year ?>"
       class="btn btn-outline-success btn-sm">
        <i class="bi bi-download me-1"></i>Xuất CSV tháng này
    </a>
</div>

<!-- Chart.js init -->
<script>
(function () {
    // Đọc data từ PHP — json_encode đã encode đúng UTF-8
    const chartData = <?= $chartJson ?>;

    // ── Biểu đồ cột ──────────────────────────────────────────
    const barCtx = document.getElementById('barChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: chartData.bar,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': '
                                + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val =>
                                new Intl.NumberFormat('vi-VN',
                                    {notation:'compact'}).format(val) + 'đ'
                        }
                    }
                }
            }
        });
    }

    // ── Biểu đồ tròn ─────────────────────────────────────────
    const donutCtx = document.getElementById('donutChart');
    if (donutCtx) {
        const donutData = chartData.donut;

        if (!donutData.labels || donutData.labels.length === 0) {
            donutCtx.closest('.card-body').innerHTML =
                '<p class="text-muted text-center my-4">Chưa có dữ liệu chi tiêu tháng này.</p>';
        } else {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: donutData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.label + ': '
                                    + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                            }
                        }
                    }
                }
            });

            // Legend thủ công — hiện tên + số tiền
            const legend = document.getElementById('donutLegend');
            if (legend && donutData.labels) {
                legend.innerHTML = donutData.labels.map((lbl, i) => {
                    const color = donutData.datasets[0].backgroundColor[i];
                    const val   = new Intl.NumberFormat('vi-VN')
                                      .format(donutData.datasets[0].data[i]);
                    return `<div class="d-flex align-items-center gap-2 mb-1">
                        <span style="width:12px;height:12px;border-radius:50%;
                              background:${color};flex-shrink:0;display:inline-block"></span>
                        <span class="small">${lbl}: <strong>${val}đ</strong></span>
                    </div>`;
                }).join('');
            }
        }
    }
})();
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
