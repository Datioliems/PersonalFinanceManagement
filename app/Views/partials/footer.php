
</main><!-- /container -->

<footer class="bg-light border-top py-3 mt-5">
    <div class="container text-center text-muted small">
        FinanceApp — Đề 13 OOP MVC &copy; <?= date('Y') ?>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js (chỉ tải khi trang dashboard/report cần) -->
<?php if (isset($needChartJs) && $needChartJs): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php endif; ?>
<script src="/js/app.js"></script>
</body>
</html>
