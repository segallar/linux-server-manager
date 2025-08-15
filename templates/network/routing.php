<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Маршрутизация</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRouteModal">
                    <i class="fas fa-plus"></i> Новый маршрут
                </button>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Всего маршрутов</h6>
                            <h3 class="mb-0"><?= $stats['total_routes'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-route fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">По умолчанию</h6>
                            <h3 class="mb-0"><?= $stats['default_routes'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Локальные</h6>
                            <h3 class="mb-0"><?= $stats['local_routes'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-home fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Сетевые</h6>
                            <h3 class="mb-0"><?= $stats['network_routes'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-network-wired fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Шлюзы</h6>
                            <h3 class="mb-0"><?= $stats['gateway_routes'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-server fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица маршрутов -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Таблица маршрутов
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Тип</th>
                            <th>Назначение</th>
                            <th>Шлюз</th>
                            <th>Интерфейс</th>
                            <th>Область</th>
                            <th>Источник</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($routes)): ?>
                            <?php foreach ($routes as $route): ?>
                            <tr>
                                <td>
                                    <?php
                                    $badgeClass = match($route['type']) {
                                        'default' => 'bg-success',
                                        'local' => 'bg-info',
                                        'network' => 'bg-warning',
                                        'gateway' => 'bg-secondary',
                                        default => 'bg-dark'
                                    };
                                    $typeName = match($route['type']) {
                                        'default' => 'По умолчанию',
                                        'local' => 'Локальный',
                                        'network' => 'Сеть',
                                        'gateway' => 'Шлюз',
                                        default => 'Неизвестно'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $typeName ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($route['destination']) ?></code></td>
                                <td><?= htmlspecialchars($route['gateway'] ?: '-') ?></td>
                                <td>
                                    <?php if ($route['interface']): ?>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($route['interface']) ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($route['scope'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($route['source'] ?: '-') ?></td>
                                <td>
                                    <?php if ($route['type'] !== 'default'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteRoute('<?= htmlspecialchars($route['destination']) ?>', '<?= htmlspecialchars($route['gateway']) ?>', '<?= htmlspecialchars($route['interface']) ?>')" title="Удалить маршрут">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Системный</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Нет маршрутов</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для нового маршрута -->
<div class="modal fade" id="newRouteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить маршрут</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newRouteForm">
                    <div class="mb-3">
                        <label for="routeDestination" class="form-label">Назначение</label>
                        <input type="text" class="form-control" id="routeDestination" placeholder="например: 192.168.2.0/24" required>
                        <div class="form-text">IP адрес или сеть в формате CIDR</div>
                    </div>
                    <div class="mb-3">
                        <label for="routeGateway" class="form-label">Шлюз</label>
                        <input type="text" class="form-control" id="routeGateway" placeholder="например: 192.168.1.1" required>
                        <div class="form-text">IP адрес шлюза</div>
                    </div>
                    <div class="mb-3">
                        <label for="routeInterface" class="form-label">Интерфейс</label>
                        <select class="form-select" id="routeInterface" required>
                            <option value="">Выберите интерфейс</option>
                            <option value="eth0">eth0</option>
                            <option value="wlan0">wlan0</option>
                            <option value="wg0">wg0</option>
                            <option value="tun0">tun0</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="addRoute()">Добавить</button>
            </div>
        </div>
    </div>
</div>

<script>
function addRoute() {
    const destination = $('#routeDestination').val();
    const gateway = $('#routeGateway').val();
    const interface = $('#routeInterface').val();
    
    if (!destination || !gateway || !interface) {
        showAlert('Заполните все поля', 'danger');
        return;
    }
    
    // Здесь будет AJAX запрос для добавления маршрута
    showAlert(`Маршрут ${destination} добавлен`, 'success');
    $('#newRouteModal').modal('hide');
    $('#newRouteForm')[0].reset();
    location.reload();
}

function deleteRoute(destination, gateway, interface) {
    if (confirm(`Удалить маршрут ${destination}?`)) {
        // Здесь будет AJAX запрос для удаления маршрута
        showAlert(`Маршрут ${destination} удален`, 'warning');
        location.reload();
    }
}
</script>
