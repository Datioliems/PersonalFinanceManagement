</main>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="<?= BASE_URL ?>/js/app.js?v=<?= filemtime(BASE_PATH . '/public/js/app.js') ?>"></script>

<!-- Generic Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-body text-center p-4">
        <i class="bi bi-exclamation-circle text-danger mb-3" style="font-size: 3rem;"></i>
        <h5 class="mb-2 fw-semibold">Xác nhận</h5>
        <p class="text-muted mb-4" id="confirmModalMessage">Bạn có chắc chắn muốn thực hiện hành động này?</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
          <button type="button" class="btn btn-danger px-4" id="confirmModalBtn">Đồng ý</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/app/Views/partials/icon_picker_modal.php'; ?>
<?php if (!empty($needChartJs)): ?>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>

</body>
</html>
