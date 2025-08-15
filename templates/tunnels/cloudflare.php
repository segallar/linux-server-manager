<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Cloudflare</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTunnelModal">
                    <i class="fas fa-plus"></i> Новый туннель
                </button>
            </div>
        </div>
    </div>

    <?php if (!$isInstalled): ?>
    <!-- Предупреждение об отсутствии cloudflared -->
    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Cloudflare Tunnel не установлен</h4>
        <p>cloudflared не найден в системе. Для использования этой функции установите Cloudflare Tunnel:</p>
        <hr>
        <p class="mb-0">
            <code>curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -o cloudflared</code><br>
            <code>chmod +x cloudflared</code><br>
            <code>sudo mv cloudflared /usr/local/bin/</code>
        </p>
    </div>
    <?php else: ?>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Всего туннелей</h6>
                            <h3 class="mb-0"><?= $stats['total_tunnels'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cloud fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Активные</h6>
                            <h3 class="mb-0"><?= $stats['active_tunnels'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <h6 class="card-title">Соединения</h6>
                            <h3 class="mb-0"><?= $stats['total_connections'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-link fa-2x"></i>
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
                            <h6 class="card-title">Статус</h6>
                            <h3 class="mb-0"><?= $isInstalled ? 'Установлен' : 'Не установлен' ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица туннелей -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Туннели Cloudflare
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($tunnels)): ?>
            <div class="text-center py-4">
                <i class="fas fa-cloud fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Туннели Cloudflare не найдены</h5>
                <p class="text-muted">Создайте первый туннель для начала работы</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTunnelModal">
                    <i class="fas fa-plus"></i> Создать туннель
                </button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>ID</th>
                            <th>Статус</th>
                            <th>Создан</th>
                            <th>Соединения</th>
                            <th>Маршруты</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tunnels as $tunnel): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($tunnel['name']) ?></strong>
                            </td>
                            <td>
                                <code class="small"><?= htmlspecialchars(substr($tunnel['id'], 0, 8)) ?>...</code>
                                <button class="btn btn-sm btn-outline-secondary ms-1" onclick="copyToClipboard('<?= htmlspecialchars($tunnel['id']) ?>')" title="Копировать ID">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </td>
                            <td>
                                <?php if ($tunnel['status'] === 'active'): ?>
                                <span class="badge bg-success">Активен</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Неактивен</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $cloudflareService = new \App\Services\CloudflareService();
                                $createdTime = $cloudflareService->formatCreatedTime($tunnel['created']);
                                ?>
                                <span class="text-muted"><?= $createdTime ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= count($tunnel['connections']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($tunnel['routes'])): ?>
                                    <?php foreach (array_slice($tunnel['routes'], 0, 2) as $route): ?>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($route) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($tunnel['routes']) > 2): ?>
                                        <span class="badge bg-secondary">+<?= count($tunnel['routes']) - 2 ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-info" onclick="viewTunnel('<?= $tunnel['id'] ?>')" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="runTunnel('<?= $tunnel['id'] ?>')" title="Запустить">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="showConfig('<?= $tunnel['id'] ?>')" title="Конфигурация">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteTunnel('<?= $tunnel['id'] ?>', '<?= htmlspecialchars($tunnel['name']) ?>')" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Модальное окно для создания нового туннеля -->
    <div class="modal fade" id="newTunnelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Новый Cloudflare туннель</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newTunnelForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tunnelName" class="form-label">Название туннеля</label>
                            <input type="text" class="form-control" id="tunnelName" name="name" required>
                            <div class="form-text">Уникальное название для туннеля</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Создать туннель</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для конфигурации -->
    <div class="modal fade" id="configModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Конфигурация туннеля: <span id="config-tunnel-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre id="tunnel-config" class="bg-light p-3 rounded"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('Скопировано в буфер обмена', 'success');
    }).catch(function(err) {
        console.error('Ошибка копирования: ', err);
        showAlert('Ошибка копирования', 'danger');
    });
}

function viewTunnel(tunnelId) {
    showAlert(`Просмотр туннеля ${tunnelId}`, 'info');
}

function runTunnel(tunnelId) {
    if (confirm(`Запустить туннель ${tunnelId}?`)) {
        showAlert(`Туннель ${tunnelId} запущен`, 'success');
        setTimeout(() => location.reload(), 1000);
    }
}

function showConfig(tunnelId) {
    // Здесь будет AJAX запрос для получения конфигурации
    $('#config-tunnel-name').text(tunnelId);
    $('#tunnel-config').text('Загрузка конфигурации...');
    $('#configModal').modal('show');
    
    // Имитация загрузки конфигурации
    setTimeout(() => {
        $('#tunnel-config').text(`# Конфигурация туннеля ${tunnelId}\n\n[ingress]\nhostname = example.com\ntarget = localhost:8080\n\n[ingress]\nhostname = api.example.com\ntarget = localhost:3000`);
    }, 1000);
}

function deleteTunnel(tunnelId, tunnelName) {
    if (confirm(`Удалить туннель "${tunnelName}" (${tunnelId})?`)) {
        showAlert(`Туннель ${tunnelName} удален`, 'warning');
        setTimeout(() => location.reload(), 1000);
    }
}

// Обработка создания нового туннеля
$('#newTunnelForm').on('submit', function(e) {
    e.preventDefault();
    
    const name = $('#tunnelName').val();
    if (!name) {
        showAlert('Введите название туннеля', 'danger');
        return;
    }
    
    // Здесь будет AJAX запрос для создания туннеля
    showAlert(`Туннель ${name} создан`, 'success');
    $('#newTunnelModal').modal('hide');
    $('#newTunnelForm')[0].reset();
    setTimeout(() => location.reload(), 1000);
});
</script>
