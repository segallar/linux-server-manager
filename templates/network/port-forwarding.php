<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-exchange-alt me-2"></i>
            Проброс портов
        </h1>
    </div>
</div>

<!-- Статистика проброса портов -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-link me-2"></i>Активные правила</h3>
            <p class="value" id="active-rules"><?= $stats['active_rules'] ?? 0 ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-network-wired me-2"></i>Всего правил</h3>
            <p class="value" id="total-rules"><?= $stats['total_rules'] ?? 0 ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Подключения</h3>
            <p class="value" id="connections"><?= $stats['total_connections'] ?? 0 ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-tachometer-alt me-2"></i>Пропускная способность</h3>
            <p class="value" id="bandwidth"><?= $stats['bandwidth'] ?? '0 MB/s' ?></p>
        </div>
    </div>
</div>

<!-- Управление пробросом портов -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Правила проброса портов
                </h5>
                <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#newPortForwardModal">
                    <i class="fas fa-plus me-2"></i>Новое правило
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($rules)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Правила проброса портов не найдены</h5>
                        <p class="text-muted">Создайте первое правило для начала работы</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPortForwardModal">
                            <i class="fas fa-plus me-2"></i>Создать правило
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Протокол</th>
                                    <th>Внешний порт</th>
                                    <th>Внутренний IP</th>
                                    <th>Внутренний порт</th>
                                    <th>Статус</th>
                                    <th>Создано</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rules as $rule): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($rule['name']) ?></td>
                                        <td><?= htmlspecialchars($rule['protocol']) ?></td>
                                        <td><?= htmlspecialchars($rule['external_port']) ?></td>
                                        <td><?= htmlspecialchars($rule['internal_ip']) ?></td>
                                        <td><?= htmlspecialchars($rule['internal_port']) ?></td>
                                        <td>
                                            <?php if ($rule['status'] === 'active'): ?>
                                                <span class="status-indicator status-online"></span>Активно
                                            <?php else: ?>
                                                <span class="status-indicator status-offline"></span>Отключено
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($rule['created']) ?></td>
                                        <td>
                                            <?php if ($rule['status'] === 'active'): ?>
                                                <button class="btn btn-sm btn-warning me-1" title="Остановить" onclick="disablePortForwardRule('<?= $rule['id'] ?>')">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success me-1" title="Включить" onclick="enablePortForwardRule('<?= $rule['id'] ?>')">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-info me-1" title="Тест" onclick="testPortForwardRule('<?= $rule['id'] ?>')">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" title="Удалить" onclick="deletePortForwardRule('<?= $rule['id'] ?>')">
                                                <i class="fas fa-trash"></i>
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

<!-- Мониторинг и статистика -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Активность портов
                </h5>
            </div>
            <div class="card-body">
                <canvas id="portActivityChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Предупреждения безопасности
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 text-warning">Порт 3306 открыт</h6>
                            <small class="text-muted">База данных доступна извне</small>
                        </div>
                        <span class="badge bg-warning rounded-pill">Высокий риск</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 text-info">Порт 21 не используется</h6>
                            <small class="text-muted">FTP сервер отключен</small>
                        </div>
                        <span class="badge bg-info rounded-pill">Информация</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 text-success">Порт 443 защищен</h6>
                            <small class="text-muted">SSL сертификат активен</small>
                        </div>
                        <span class="badge bg-success rounded-pill">Безопасно</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Быстрые действия -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Быстрые действия
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-success btn-custom w-100" title="Включить все правила">
                            <i class="fas fa-play me-2"></i>Включить все
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-warning btn-custom w-100" title="Остановить все правила">
                            <i class="fas fa-pause me-2"></i>Остановить все
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-info btn-custom w-100" title="Проверить все порты">
                            <i class="fas fa-search me-2"></i>Проверить порты
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-secondary btn-custom w-100" title="Экспорт конфигурации">
                            <i class="fas fa-download me-2"></i>Экспорт
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового правила -->
<div class="modal fade" id="newPortForwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Новое правило проброса портов
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newPortForwardForm">
                    <div class="mb-3">
                        <label for="ruleName" class="form-label">Название правила</label>
                        <input type="text" class="form-control" id="ruleName" required>
                    </div>
                    <div class="mb-3">
                        <label for="protocol" class="form-label">Протокол</label>
                        <select class="form-select" id="protocol" required>
                            <option value="tcp">TCP</option>
                            <option value="udp">UDP</option>
                            <option value="both">TCP/UDP</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="externalPort" class="form-label">Внешний порт</label>
                                <input type="number" class="form-control" id="externalPort" min="1" max="65535" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="internalPort" class="form-label">Внутренний порт</label>
                                <input type="number" class="form-control" id="internalPort" min="1" max="65535" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="internalIP" class="form-label">Внутренний IP адрес</label>
                        <input type="text" class="form-control" id="internalIP" placeholder="192.168.1.100" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enableRule" checked>
                            <label class="form-check-label" for="enableRule">
                                Включить правило сразу
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createPortForwardBtn">Создать правило</button>
            </div>
        </div>
    </div>
</div>

<script>
// Функции для работы с пробросом портов

function enablePortForwardRule(ruleId) {
    if (confirm('Включить правило проброса портов?')) {
        makeAjaxRequest(`/api/port-forwarding/rule/${ruleId}/enable`, 'POST').done(function(data) {
            if (data.success) {
                showAlert('Правило включено', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка включения правила: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка подключения к серверу', 'danger');
        });
    }
}

function disablePortForwardRule(ruleId) {
    if (confirm('Отключить правило проброса портов?')) {
        makeAjaxRequest(`/api/port-forwarding/rule/${ruleId}/disable`, 'POST').done(function(data) {
            if (data.success) {
                showAlert('Правило отключено', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка отключения правила: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка подключения к серверу', 'danger');
        });
    }
}

function deletePortForwardRule(ruleId) {
    if (confirm('Удалить правило проброса портов? Это действие нельзя отменить.')) {
        makeAjaxRequest(`/api/port-forwarding/rule/${ruleId}`, 'DELETE').done(function(data) {
            if (data.success) {
                showAlert('Правило удалено', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка удаления правила: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка подключения к серверу', 'danger');
        });
    }
}

function testPortForwardRule(ruleId) {
    showAlert('Тестирование правила...', 'info');
    makeAjaxRequest(`/api/port-forwarding/rule/${ruleId}/test`, 'POST').done(function(data) {
        if (data.success) {
            showAlert('Правило работает корректно', 'success');
        } else {
            showAlert('Ошибка тестирования: ' + data.message, 'warning');
        }
    }).fail(function() {
        showAlert('Ошибка подключения к серверу', 'danger');
    });
}

// Обработчик создания нового правила
$(document).ready(function() {
    $('#createPortForwardBtn').click(function() {
        const formData = {
            name: $('#ruleName').val(),
            protocol: $('#protocol').val(),
            external_port: $('#externalPort').val(),
            internal_port: $('#internalPort').val(),
            internal_ip: $('#internalIP').val(),
            description: $('#description').val(),
            enabled: $('#enableRule').is(':checked')
        };
        
        // Проверяем обязательные поля
        if (!formData.name || !formData.external_port || !formData.internal_port || !formData.internal_ip) {
            showAlert('Заполните все обязательные поля', 'warning');
            return;
        }
        
        makeAjaxRequest('/api/port-forwarding/rule/add', 'POST', formData).done(function(data) {
            if (data.success) {
                showAlert('Правило создано успешно', 'success');
                $('#newPortForwardModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка создания правила: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка подключения к серверу', 'danger');
        });
    });
});
</script>
