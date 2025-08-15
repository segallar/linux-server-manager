<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">WireGuard</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newInterfaceModal">
                    <i class="fas fa-plus"></i> Новый интерфейс
                </button>
            </div>
        </div>
    </div>

    <?php if (!$isInstalled): ?>
    <!-- Предупреждение об отсутствии WireGuard -->
    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> WireGuard не установлен</h4>
        <p>WireGuard не найден в системе. Для использования этой функции установите WireGuard:</p>
        <hr>
        <p class="mb-0">
            <code>sudo apt update && sudo apt install wireguard</code>
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
                            <h6 class="card-title">Всего интерфейсов</h6>
                            <h3 class="mb-0"><?= $stats['total_interfaces'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-network-wired fa-2x"></i>
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
                            <h3 class="mb-0"><?= $stats['active_interfaces'] ?></h3>
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
                            <h6 class="card-title">Всего пиров</h6>
                            <h3 class="mb-0"><?= $stats['total_peers'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
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
                            <h6 class="card-title">Активные пиры</h6>
                            <h3 class="mb-0"><?= $stats['active_peers'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица интерфейсов -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Интерфейсы WireGuard
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($interfaces)): ?>
            <div class="text-center py-4">
                <i class="fas fa-network-wired fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Интерфейсы WireGuard не найдены</h5>
                <p class="text-muted">Создайте первый интерфейс для начала работы</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newInterfaceModal">
                    <i class="fas fa-plus"></i> Создать интерфейс
                </button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Интерфейс</th>
                            <th>Статус</th>
                            <th>Адрес</th>
                            <th>Порт</th>
                            <th>Пиры</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($interfaces as $interface): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($interface['name']) ?></strong>
                                <?php if (!empty($interface['public_key'])): ?>
                                <br><small class="text-muted"><?= substr($interface['public_key'], 0, 20) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($interface['status'] === 'up'): ?>
                                <span class="badge bg-success">Активен</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Остановлен</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($interface['address']) ?: '-' ?></td>
                            <td><?= htmlspecialchars($interface['port']) ?: '-' ?></td>
                            <td>
                                <span class="badge bg-info"><?= count($interface['peers']) ?></span>
                                <?php 
                                $activePeers = 0;
                                foreach ($interface['peers'] as $peer) {
                                    if ($peer['status'] === 'active') $activePeers++;
                                }
                                ?>
                                <small class="text-muted">(<?= $activePeers ?> активных)</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-info" onclick="viewInterface('<?= $interface['name'] ?>')" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($interface['status'] === 'up'): ?>
                                    <button class="btn btn-outline-warning" onclick="restartInterface('<?= $interface['name'] ?>')" title="Перезапустить">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="downInterface('<?= $interface['name'] ?>')" title="Остановить">
                                        <i class="fas fa-stop"></i>
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-outline-success" onclick="upInterface('<?= $interface['name'] ?>')" title="Запустить">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-secondary" onclick="editInterface('<?= $interface['name'] ?>')" title="Редактировать">
                                        <i class="fas fa-edit"></i>
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

    <!-- Детальная информация об интерфейсе -->
    <div class="modal fade" id="interfaceDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Детали интерфейса: <span id="interface-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="interface-details">
                    <!-- Детали будут загружены через AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для создания нового интерфейса -->
    <div class="modal fade" id="newInterfaceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Новый WireGuard интерфейс</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newInterfaceForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="interfaceName" class="form-label">Имя интерфейса</label>
                                    <input type="text" class="form-control" id="interfaceName" placeholder="wg0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="wgIP" class="form-label">IP адрес</label>
                                    <input type="text" class="form-control" id="wgIP" placeholder="10.9.0.1/24" required>
                                </div>
                                <div class="mb-3">
                                    <label for="wgPort" class="form-label">Порт</label>
                                    <input type="number" class="form-control" id="wgPort" min="1024" max="65535" value="51820" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="privateKey" class="form-label">Приватный ключ</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="privateKey" readonly>
                                        <button class="btn btn-outline-secondary" type="button" id="generatePrivateKey">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="publicKey" class="form-label">Публичный ключ</label>
                                    <input type="text" class="form-control" id="publicKey" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="wgDescription" class="form-label">Описание</label>
                                    <textarea class="form-control" id="wgDescription" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="createInterface()">Создать интерфейс</button>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Генерация ключей
    $('#generatePrivateKey').click(function() {
        // Здесь будет генерация ключей через AJAX
        $('#privateKey').val('generated_private_key_here');
        $('#publicKey').val('generated_public_key_here');
    });
});

function viewInterface(interfaceName) {
    $('#interface-name').text(interfaceName);
    $('#interface-details').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Загрузка...</div>');
    $('#interfaceDetailModal').modal('show');
    
    // Здесь будет AJAX запрос для получения деталей интерфейса
    setTimeout(() => {
        $('#interface-details').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Информация об интерфейсе</h6>
                    <p><strong>Имя:</strong> ${interfaceName}</p>
                    <p><strong>Статус:</strong> <span class="badge bg-success">Активен</span></p>
                    <p><strong>IP адрес:</strong> 10.9.0.1/24</p>
                    <p><strong>Порт:</strong> 51820</p>
                </div>
                <div class="col-md-6">
                    <h6>Статистика</h6>
                    <p><strong>Получено:</strong> 1.2 GB</p>
                    <p><strong>Отправлено:</strong> 856 MB</p>
                    <p><strong>Пиров:</strong> 5 (3 активных)</p>
                </div>
            </div>
            <hr>
            <h6>Пиры</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Публичный ключ</th>
                            <th>IP адрес</th>
                            <th>Последний хендшейк</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>abc123...</code></td>
                            <td>10.9.0.10</td>
                            <td>2 мин назад</td>
                            <td><span class="badge bg-success">Активен</span></td>
                        </tr>
                        <tr>
                            <td><code>def456...</code></td>
                            <td>10.9.0.11</td>
                            <td>5 мин назад</td>
                            <td><span class="badge bg-success">Активен</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `);
    }, 1000);
}

function upInterface(interfaceName) {
    if (confirm(`Запустить интерфейс ${interfaceName}?`)) {
        // Здесь будет AJAX запрос для запуска интерфейса
        showAlert(`Интерфейс ${interfaceName} запущен`, 'success');
        setTimeout(() => location.reload(), 1000);
    }
}

function downInterface(interfaceName) {
    if (confirm(`Остановить интерфейс ${interfaceName}?`)) {
        // Здесь будет AJAX запрос для остановки интерфейса
        showAlert(`Интерфейс ${interfaceName} остановлен`, 'warning');
        setTimeout(() => location.reload(), 1000);
    }
}

function restartInterface(interfaceName) {
    if (confirm(`Перезапустить интерфейс ${interfaceName}?`)) {
        // Здесь будет AJAX запрос для перезапуска интерфейса
        showAlert(`Интерфейс ${interfaceName} перезапущен`, 'info');
        setTimeout(() => location.reload(), 1000);
    }
}

function editInterface(interfaceName) {
    // Здесь будет открытие модального окна для редактирования
    showAlert(`Редактирование интерфейса ${interfaceName}`, 'info');
}

function createInterface() {
    const name = $('#interfaceName').val();
    const ip = $('#wgIP').val();
    const port = $('#wgPort').val();
    const privateKey = $('#privateKey').val();
    const description = $('#wgDescription').val();
    
    if (!name || !ip || !port || !privateKey) {
        showAlert('Заполните обязательные поля', 'danger');
        return;
    }
    
    // Здесь будет AJAX запрос для создания интерфейса
    showAlert(`Интерфейс ${name} создан`, 'success');
    $('#newInterfaceModal').modal('hide');
    $('#newInterfaceForm')[0].reset();
    setTimeout(() => location.reload(), 1000);
}
</script>
