<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Управление сервисами</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newServiceModal">
                    <i class="fas fa-plus"></i> Новый сервис
                </button>
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
    // Здесь будет AJAX запрос для получения списка сервисов
    // Пока используем заглушки
    $('#total-services').text('24');
    $('#active-services').text('18');
    $('#stopped-services').text('4');
    $('#failed-services').text('2');
    
    // Обновляем таблицу
    $('#services-table').html(`
        <tr>
            <td>nginx</td>
            <td>A high performance web server</td>
            <td><span class="badge bg-success">Активен</span></td>
            <td><span class="badge bg-success">Включен</span></td>
            <td>2 дня, 15 часов</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="restartService('nginx')"><i class="fas fa-redo"></i></button>
                <button class="btn btn-sm btn-danger" onclick="stopService('nginx')"><i class="fas fa-stop"></i></button>
            </td>
        </tr>
        <tr>
            <td>mysql</td>
            <td>MySQL database server</td>
            <td><span class="badge bg-success">Активен</span></td>
            <td><span class="badge bg-success">Включен</span></td>
            <td>1 день, 8 часов</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="restartService('mysql')"><i class="fas fa-redo"></i></button>
                <button class="btn btn-sm btn-danger" onclick="stopService('mysql')"><i class="fas fa-stop"></i></button>
            </td>
        </tr>
        <tr>
            <td>apache2</td>
            <td>Apache web server</td>
            <td><span class="badge bg-warning">Остановлен</span></td>
            <td><span class="badge bg-secondary">Отключен</span></td>
            <td>-</td>
            <td>
                <button class="btn btn-sm btn-success" onclick="startService('apache2')"><i class="fas fa-play"></i></button>
                <button class="btn btn-sm btn-info" onclick="enableService('apache2')"><i class="fas fa-toggle-on"></i></button>
            </td>
        </tr>
    `);
}

function startService(serviceName) {
    // Здесь будет AJAX запрос для запуска сервиса
    showAlert(`Сервис "${serviceName}" запущен`, 'success');
    loadServices();
}

function stopService(serviceName) {
    if (confirm(`Вы уверены, что хотите остановить сервис "${serviceName}"?`)) {
        // Здесь будет AJAX запрос для остановки сервиса
        showAlert(`Сервис "${serviceName}" остановлен`, 'warning');
        loadServices();
    }
}

function restartService(serviceName) {
    if (confirm(`Вы уверены, что хотите перезапустить сервис "${serviceName}"?`)) {
        // Здесь будет AJAX запрос для перезапуска сервиса
        showAlert(`Сервис "${serviceName}" перезапущен`, 'info');
        loadServices();
    }
}

function enableService(serviceName) {
    // Здесь будет AJAX запрос для включения автозапуска
    showAlert(`Автозапуск для сервиса "${serviceName}" включен`, 'success');
    loadServices();
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
