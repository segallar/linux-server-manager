<div class="container-fluid">
    <!-- Отображение ошибок -->
    <?php if (isset($error)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Ошибка:</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Управление пакетами</h1>
                <div class="d-flex align-items-center">
                    <?php if (isset($fromCache) && $fromCache): ?>
                        <span class="badge bg-success me-2">
                            <i class="fas fa-database"></i> Кэш
                        </span>
                    <?php endif; ?>
                    <button class="btn btn-success me-2" onclick="updatePackageList()">
                        <i class="fas fa-sync-alt"></i> Обновить список
                    </button>
                    <button class="btn btn-primary" onclick="upgradeAllPackages()">
                        <i class="fas fa-arrow-up"></i> Обновить все
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Установлено пакетов</h6>
                            <h3 class="mb-0"><?= $stats['total_installed'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-box fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Доступно обновлений</h6>
                            <h3 class="mb-0"><?= $stats['upgradable'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Обновления безопасности</h6>
                            <h3 class="mb-0"><?= $stats['security_updates'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Последнее обновление</h6>
                            <h6 class="mb-0"><?= $stats['last_update'] ?></h6>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Действия -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools"></i> Действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-primary w-100" onclick="updatePackageList()">
                                <i class="fas fa-sync-alt"></i> Обновить список пакетов
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-success w-100" onclick="upgradeAllPackages()">
                                <i class="fas fa-arrow-up"></i> Обновить все пакеты
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-warning w-100" onclick="cleanPackageCache()">
                                <i class="fas fa-broom"></i> Очистить кэш
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-danger w-100" onclick="autoremovePackages()">
                                <i class="fas fa-trash"></i> Удалить неиспользуемые
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Доступные обновления -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Доступные обновления
                        <span class="badge bg-warning ms-2"><?= count($upgradable) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Пакет</th>
                                    <th>Архитектура</th>
                                    <th>Текущая версия</th>
                                    <th>Новая версия</th>
                                    <th>Тип</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($upgradable)): ?>
                                    <?php foreach ($upgradable as $package): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($package['name']) ?></strong>
                                            <button class="btn btn-sm btn-outline-info ms-1" onclick="showPackageInfo('<?= htmlspecialchars($package['name']) ?>')" title="Информация о пакете">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        </td>
                                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($package['architecture']) ?></span></td>
                                        <td><code><?= htmlspecialchars($package['current_version']) ?></code></td>
                                        <td><code class="text-success"><?= htmlspecialchars($package['new_version']) ?></code></td>
                                        <td>
                                            <?php if ($package['is_security']): ?>
                                                <span class="badge bg-danger">Безопасность</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Обычное</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success" onclick="upgradePackage('<?= htmlspecialchars($package['name']) ?>')" title="Обновить пакет">
                                                <i class="fas fa-arrow-up"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i> Все пакеты обновлены!
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Неиспользуемые пакеты -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-trash"></i> Неиспользуемые пакеты
                        <span class="badge bg-secondary ms-2"><?= count($unused) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($unused)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($unused, 0, 10) as $package): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($package['name']) ?></strong>
                                    <br>
                                    <small class="text-muted">Используется: <?= $package['usage_count'] ?> раз</small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($unused) > 10): ?>
                            <div class="text-center mt-2">
                                <small class="text-muted">И еще <?= count($unused) - 10 ?> пакетов...</small>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-center text-muted">
                            <i class="fas fa-check-circle"></i> Неиспользуемых пакетов не найдено
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для информации о пакете -->
<div class="modal fade" id="packageInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Информация о пакете</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="packageInfoContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePackageList() {
    showAlert('Обновление списка пакетов...', 'info');
    
    $.post('/api/packages/update')
        .done(function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert(response.message, 'danger');
            }
        })
        .fail(function() {
            showAlert('Ошибка при обновлении списка пакетов', 'danger');
        });
}

function upgradeAllPackages() {
    if (confirm('Обновить все пакеты? Это может занять некоторое время.')) {
        showAlert('Обновление всех пакетов...', 'info');
        
        $.post('/api/packages/upgrade-all')
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(() => location.reload(), 3000);
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Ошибка при обновлении пакетов', 'danger');
            });
    }
}

function upgradePackage(packageName) {
    if (confirm(`Обновить пакет "${packageName}"?`)) {
        showAlert(`Обновление пакета ${packageName}...`, 'info');
        
        $.post('/api/packages/upgrade', { package: packageName })
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Ошибка при обновлении пакета', 'danger');
            });
    }
}

function cleanPackageCache() {
    if (confirm('Очистить кэш пакетов?')) {
        showAlert('Очистка кэша пакетов...', 'info');
        
        $.post('/api/packages/clean-cache')
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Ошибка при очистке кэша', 'danger');
            });
    }
}

function autoremovePackages() {
    if (confirm('Удалить неиспользуемые пакеты?')) {
        showAlert('Удаление неиспользуемых пакетов...', 'info');
        
        $.post('/api/packages/autoremove')
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Ошибка при удалении пакетов', 'danger');
            });
    }
}

function showPackageInfo(packageName) {
    $('#packageInfoModal').modal('show');
    $('#packageInfoContent').html('<div class="text-center"><div class="spinner-border"></div></div>');
    
    $.get('/api/packages/info', { package: packageName })
        .done(function(response) {
            if (response.success) {
                const data = response.data;
                $('#packageInfoContent').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Основная информация</h6>
                            <p><strong>Название:</strong> ${data.name}</p>
                            <p><strong>Версия:</strong> ${data.version}</p>
                            <p><strong>Размер:</strong> ${data.size}</p>
                            <p><strong>Поддерживается:</strong> ${data.maintainer}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Описание</h6>
                            <p>${data.description}</p>
                            ${data.homepage ? `<p><strong>Домашняя страница:</strong> <a href="${data.homepage}" target="_blank">${data.homepage}</a></p>` : ''}
                        </div>
                    </div>
                    ${data.depends.length > 0 ? `
                    <hr>
                    <h6>Зависимости</h6>
                    <ul>
                        ${data.depends.map(dep => `<li>${dep}</li>`).join('')}
                    </ul>
                    ` : ''}
                `);
            } else {
                $('#packageInfoContent').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        })
        .fail(function() {
            $('#packageInfoContent').html('<div class="alert alert-danger">Ошибка при получении информации о пакете</div>');
        });
}
</script>
