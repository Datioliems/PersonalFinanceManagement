
</main>

<!-- ── Footer ───────────────────────────────────────────── -->
<footer class="bg-white border-top py-3 mt-auto">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            <i class="bi bi-wallet2 me-1"></i>
            FinanceApp — Đề 13 OOP MVC &copy; <?= date('Y') ?>
        </span>
        <span class="text-muted small">
            TV1: Auth &nbsp;|&nbsp; TV2: Budget &nbsp;|&nbsp;
            TV3: Chi tiêu &nbsp;|&nbsp; TV4: Thu nhập &nbsp;|&nbsp; TV5: Báo cáo
        </span>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="/js/app.js"></script>

<?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>

</body>
</html>
