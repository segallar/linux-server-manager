<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Ошибка
                </h4>
                <p><?= $message ?? 'Произошла неизвестная ошибка' ?></p>
                <?php if (isset($code)): ?>
                <hr>
                <p class="mb-0">Код ошибки: <?= htmlspecialchars($code) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
