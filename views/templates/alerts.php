<?php if (isset($_SESSION['error'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div class="toast align-items-center text-white bg-danger border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <?= $_SESSION['error'] ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>