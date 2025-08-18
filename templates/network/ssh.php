<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-terminal me-2"></i>
            SSH туннели
        </h1>
    </div>
</div>

<!-- Статистика SSH туннелей -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-link me-2"></i>Активные туннели</h3>
            <p class="value" id="active-tunnels"><?= $stats['active_tunnels'] ?? 0 ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-users me-2"></i>Всего туннелей</h3>
            <p class="value" id="total-tunnels"><?= $stats['total_tunnels'] ?? 0 ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-clock me-2"></i>Время работы</h3>
            <p class="value" id="uptime"><?= $stats['uptime'] ?? '0д 0ч' ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Безопасность</h3>
            <p class="value" id="security">100%</p>
        </div>
    </div>
</div>

<!-- Управление SSH туннелями -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Управление туннелями
                </h5>
                <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#newTunnelModal">
                    <i class="fas fa-plus me-2"></i>Новый туннель
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($tunnels)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-terminal fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">SSH туннели не найдены</h5>
                        <p class="text-muted">Создайте первый SSH туннель для начала работы</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTunnelModal">
                            <i class="fas fa-plus me-2"></i>Создать туннель
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Локальный порт</th>
                                    <th>Удаленный хост</th>
                                    <th>Удаленный порт</th>
                                    <th>Статус</th>
                                    <th>Создан</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tunnels as $tunnel): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($tunnel['name']) ?></td>
                                        <td><?= htmlspecialchars($tunnel['local_port']) ?></td>
                                        <td><?= htmlspecialchars($tunnel['host']) ?></td>
                                        <td><?= htmlspecialchars($tunnel['remote_port']) ?></td>
                                        <td>
                                            <?php if ($tunnel['status'] === 'running'): ?>
                                                <span class="status-indicator status-online"></span>Активен
                                            <?php else: ?>
                                                <span class="status-indicator status-offline"></span>Остановлен
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($tunnel['created']) ?></td>
                                        <td>
                                            <?php if ($tunnel['status'] === 'running'): ?>
                                                <button class="btn btn-sm btn-warning me-1" title="Остановить" onclick="stopSSHTunnel('<?= $tunnel['id'] ?>')">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success me-1" title="Запустить" onclick="startSSHTunnel('<?= $tunnel['id'] ?>')">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger me-1" title="Удалить" onclick="deleteSSHTunnel('<?= $tunnel['id'] ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" title="Логи" onclick="viewSSHTunnelLogs('<?= $tunnel['id'] ?>')">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Мониторинг подключений -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Активность подключений
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($connections)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Нет данных для отображения</h6>
                        <p class="text-muted small">Активность подключений появится после создания SSH туннелей</p>
                    </div>
                <?php else: ?>
                    <canvas id="connectionsChart" width="400" height="200"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Последние подключения
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($connections)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-list fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Нет активных подключений</h6>
                        <p class="text-muted small">Подключения появятся после запуска SSH туннелей</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php 
                        $displayedConnections = 0;
                        foreach ($connections as $connection): 
                            if ($displayedConnections >= 5) break; // Показываем только первые 5
                            
                            $tunnelName = htmlspecialchars($connection['tunnel_name']);
                            $localPort = $connection['local_port'];
                            $remoteHost = htmlspecialchars($connection['remote_host']);
                            $remotePort = $connection['remote_port'];
                            $lastUsed = $connection['last_used'];
                            $status = $connection['status'];
                            
                            // Вычисляем время с последнего использования
                            $timeAgo = '';
                            if (!empty($lastUsed)) {
                                $lastUsedTime = strtotime($lastUsed);
                                $now = time();
                                $diff = $now - $lastUsedTime;
                                
                                if ($diff < 60) {
                                    $timeAgo = 'только что';
                                    $badgeClass = 'bg-success';
                                } elseif ($diff < 3600) {
                                    $minutes = floor($diff / 60);
                                    $timeAgo = $minutes . ' мин назад';
                                    $badgeClass = 'bg-info';
                                } elseif ($diff < 86400) {
                                    $hours = floor($diff / 3600);
                                    $timeAgo = $hours . ' ч назад';
                                    $badgeClass = 'bg-warning';
                                } else {
                                    $days = floor($diff / 86400);
                                    $timeAgo = $days . ' д назад';
                                    $badgeClass = 'bg-secondary';
                                }
                            }
                            
                            // Определяем цвет статуса
                            $statusClass = $status === 'active' ? 'status-online' : 'status-offline';
                            $statusText = $status === 'active' ? 'Активен' : 'Неактивен';
                        ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?= $remoteHost ?>:<?= $remotePort ?></h6>
                                    <small class="text-muted"><?= $tunnelName ?> (порт <?= $localPort ?>)</small>
                                    <br>
                                    <small class="text-muted">
                                        <span class="status-indicator <?= $statusClass ?>"></span><?= $statusText ?>
                                    </small>
                                </div>
                                <?php if (!empty($timeAgo)): ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill"><?= $timeAgo ?></span>
                                <?php endif; ?>
                            </div>
                        <?php 
                            $displayedConnections++;
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового туннеля -->
<div class="modal fade" id="newTunnelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Новый SSH туннель
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newTunnelForm">
                    <div class="mb-3">
                        <label for="tunnelName" class="form-label">Название туннеля</label>
                        <input type="text" class="form-control" id="tunnelName" required>
                    </div>
                    <div class="mb-3">
                        <label for="localPort" class="form-label">Локальный порт</label>
                        <input type="number" class="form-control" id="localPort" min="1024" max="65535" required>
                    </div>
                    <div class="mb-3">
                        <label for="remoteHost" class="form-label">Удаленный хост</label>
                        <input type="text" class="form-control" id="remoteHost" required>
                    </div>
                    <div class="mb-3">
                        <label for="remotePort" class="form-label">Удаленный порт</label>
                        <input type="number" class="form-control" id="remotePort" min="1" max="65535" required>
                    </div>
                    <div class="mb-3">
                        <label for="sshUser" class="form-label">SSH пользователь</label>
                        <input type="text" class="form-control" id="sshUser" required>
                    </div>
                    <div class="mb-3">
                        <label for="sshKey" class="form-label">SSH ключ</label>
                        <select class="form-select" id="sshKey" required>
                            <option value="">Выберите ключ</option>
                            <option value="id_rsa">id_rsa</option>
                            <option value="id_ed25519">id_ed25519</option>
                            <option value="custom">Пользовательский</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createTunnelBtn">Создать туннель</button>
            </div>
        </div>
    </div>
</div>

<script>
// Функции для работы с SSH туннелями

function startSSHTunnel(tunnelId) {
    if (confirm('Запустить SSH туннель?')) {
        makeAjaxRequest(`/api/ssh/tunnel/${tunnelId}/start`, 'POST').done(function(data) {
            if (data.success) {
                showAlert('SSH туннель запущен', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка запуска SSH туннеля: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка подключения к серверу', 'danger');
        });
    }
}

function stopSSHTunnel(tunnelId) {
    if (confirm('Остановить SSH туннель?')) {
        makeAjaxRequest(`/api/ssh/tunnel/${tunnelId}/stop`, 'POST').done(function(data) {
            if (data.success) {
                showAlert('SSH туннель остановлен', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка остановки SSH туннеля: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка подключения к серверу', 'danger');
        });
    }
}

function deleteSSHTunnel(tunnelId) {
    if (confirm('Удалить SSH туннель? Это действие нельзя отменить.')) {
        makeAjaxRequest(`/api/ssh/tunnel/${tunnelId}`, 'DELETE').done(function(data) {
            if (data.success) {
                showAlert('SSH туннель удален', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка удаления SSH туннеля: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка подключения к серверу', 'danger');
        });
    }
}

function viewSSHTunnelLogs(tunnelId) {
    showAlert('Функция просмотра логов будет доступна в следующей версии', 'info');
}

// Обработчик создания нового туннеля
$(document).ready(function() {
    $('#createTunnelBtn').click(function() {
        const formData = {
            name: $('#tunnelName').val(),
            local_port: $('#localPort').val(),
            remote_host: $('#remoteHost').val(),
            remote_port: $('#remotePort').val(),
            username: $('#sshUser').val(),
            ssh_key: $('#sshKey').val()
        };
        
        // Проверяем обязательные поля
        if (!formData.name || !formData.local_port || !formData.remote_host || 
            !formData.remote_port || !formData.username) {
            showAlert('Заполните все обязательные поля', 'warning');
            return;
        }
        
        makeAjaxRequest('/api/ssh/tunnel/create', 'POST', formData).done(function(data) {
            if (data.success) {
                showAlert('SSH туннель создан успешно', 'success');
                $('#newTunnelModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка создания SSH туннеля: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка подключения к серверу', 'danger');
        });
    });
});
</script>
