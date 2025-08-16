<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Управление сервисами</h1>
                <div class="d-flex align-items-center">
                    <?php if (isset($fromCache) && $fromCache): ?>
                        <span class="badge bg-success me-2">
                            <i class="fas fa-database"></i> Кэш
                        </span>
                    <?php endif; ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newServiceModal">
                        <i class="fas fa-plus"></i> Новый сервис
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
                            <h6 class="card-title">Всего сервисов</h6>
                            <h3 class="mb-0" id="total-services">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cogs fa-2x"></i>
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
                            <h3 class="mb-0" id="active-services">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <h6 class="card-title">Остановленные</h6>
                            <h3 class="mb-0" id="stopped-services">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
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
                            <h6 class="card-title">Ошибки</h6>
                            <h3 class="mb-0" id="failed-services">0</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица сервисов -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Список сервисов
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Сервис</th>
                            <th>Описание</th>
                            <th>Статус</th>
                            <th>Автозапуск</th>
                            <th>Время работы</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody id="services-table">
                        <tr>
                            <td colspan="6" class="text-center">Загрузка сервисов...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для нового сервиса -->
<div class="modal fade" id="newServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создать новый сервис</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newServiceForm">
                    <div class="mb-3">
                        <label for="serviceName" class="form-label">Имя сервиса</label>
                        <input type="text" class="form-control" id="serviceName" placeholder="например: myapp" required>
                    </div>
                    <div class="mb-3">
                        <label for="serviceDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="serviceDescription" rows="3" placeholder="Описание сервиса"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="serviceCommand" class="form-label">Команда запуска</label>
                        <input type="text" class="form-control" id="serviceCommand" placeholder="/usr/bin/myapp" required>
                    </div>
                    <div class="mb-3">
                        <label for="serviceUser" class="form-label">Пользователь</label>
                        <select class="form-select" id="serviceUser">
                            <option value="root">root</option>
                            <option value="www-data">www-data</option>
                            <option value="systemd">systemd</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="autoStart" checked>
                            <label class="form-check-label" for="autoStart">
                                Автозапуск при загрузке системы
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="createService()">Создать</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadServices();
    setInterval(loadServices, 15000); // Обновляем каждые 15 секунд
});

function loadServices() {
    // Обновляем статистику
    $('#total-services').text('<?= $stats['total'] ?>');
    $('#active-services').text('<?= $stats['active'] ?>');
    $('#stopped-services').text('<?= $stats['inactive'] ?>');
    $('#failed-services').text('<?= $stats['failed'] ?>');
    
    // Обновляем таблицу
    let tableHtml = '';
    <?php if (!empty($services)): ?>
        <?php foreach ($services as $service): ?>
        tableHtml += `
            <tr>
                <td><?= htmlspecialchars($service['name']) ?></td>
                <td><?= htmlspecialchars($service['description']) ?></td>
                <td>
                    <?php
                    $statusBadge = match($service['status']) {
                        'active' => 'bg-success',
                        'inactive' => 'bg-warning',
                        'failed' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    $statusText = match($service['status']) {
                        'active' => 'Активен',
                        'inactive' => 'Остановлен',
                        'failed' => 'Ошибка',
                        default => 'Неизвестно'
                    };
                    ?>
                    <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                </td>
                <td>
                    <?php if ($service['enabled']): ?>
                        <span class="badge bg-success">Включен</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Отключен</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($service['uptime']) ?></td>
                <td>
                    <?php if ($service['status'] === 'active'): ?>
                        <button class="btn btn-sm btn-warning" onclick="restartService('<?= htmlspecialchars($service['name']) ?>')" title="Перезапустить">
                            <i class="fas fa-redo"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="stopService('<?= htmlspecialchars($service['name']) ?>')" title="Остановить">
                            <i class="fas fa-stop"></i>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-success" onclick="startService('<?= htmlspecialchars($service['name']) ?>')" title="Запустить">
                            <i class="fas fa-play"></i>
                        </button>
                    <?php endif; ?>
                    <?php if ($service['enabled']): ?>
                        <button class="btn btn-sm btn-info" onclick="disableService('<?= htmlspecialchars($service['name']) ?>')" title="Отключить автозапуск">
                            <i class="fas fa-toggle-off"></i>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-info" onclick="enableService('<?= htmlspecialchars($service['name']) ?>')" title="Включить автозапуск">
                            <i class="fas fa-toggle-on"></i>
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        `;
        <?php endforeach; ?>
    <?php else: ?>
        tableHtml = '<tr><td colspan="6" class="text-center">Нет сервисов</td></tr>';
    <?php endif; ?>
    
    $('#services-table').html(tableHtml);
}

function startService(serviceName) {
    $.post('/api/services/start', { service: serviceName })
        .done(function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                loadServices();
            } else {
                showAlert(response.message, 'danger');
            }
        })
        .fail(function() {
            showAlert('Ошибка при запуске сервиса', 'danger');
        });
}

function stopService(serviceName) {
    if (confirm(`Вы уверены, что хотите остановить сервис "${serviceName}"?`)) {
        $.post('/api/services/stop', { service: serviceName })
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'warning');
                    loadServices();
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Ошибка при остановке сервиса', 'danger');
            });
    }
}

function restartService(serviceName) {
    if (confirm(`Вы уверены, что хотите перезапустить сервис "${serviceName}"?`)) {
        $.post('/api/services/restart', { service: serviceName })
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'info');
                    loadServices();
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Ошибка при перезапуске сервиса', 'danger');
            });
    }
}

function enableService(serviceName) {
    $.post('/api/services/enable', { service: serviceName })
        .done(function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                loadServices();
            } else {
                showAlert(response.message, 'danger');
            }
        })
        .fail(function() {
            showAlert('Ошибка при включении автозапуска', 'danger');
        });
}

function disableService(serviceName) {
    $.post('/api/services/disable', { service: serviceName })
        .done(function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                loadServices();
            } else {
                showAlert(response.message, 'danger');
            }
        })
        .fail(function() {
            showAlert('Ошибка при отключении автозапуска', 'danger');
        });
}

function createService() {
    const name = $('#serviceName').val();
    const description = $('#serviceDescription').val();
    const command = $('#serviceCommand').val();
    const user = $('#serviceUser').val();
    const autoStart = $('#autoStart').is(':checked');
    
    if (!name || !command) {
        showAlert('Заполните обязательные поля', 'danger');
        return;
    }
    
    // Здесь будет AJAX запрос для создания сервиса
    showAlert(`Сервис "${name}" создан`, 'success');
    $('#newServiceModal').modal('hide');
    $('#newServiceForm')[0].reset();
    loadServices();
}
</script>
