<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Управление процессами</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProcessModal">
                    <i class="fas fa-plus"></i> Новый процесс
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
                            <h6 class="card-title">Всего процессов</h6>
                            <h3 class="mb-0"><?= $stats['total'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tasks fa-2x"></i>
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
                            <h3 class="mb-0"><?= $stats['active'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-play fa-2x"></i>
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
                            <h6 class="card-title">Спящие</h6>
                            <h3 class="mb-0"><?= $stats['sleeping'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pause fa-2x"></i>
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
                            <h6 class="card-title">Остановленные</h6>
                            <h3 class="mb-0"><?= $stats['stopped'] ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-stop fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица процессов -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Список процессов
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>PID</th>
                            <th>Имя</th>
                            <th>Пользователь</th>
                            <th>CPU %</th>
                            <th>RAM %</th>
                            <th>Статус</th>
                            <th>Время</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($processes)): ?>
                            <?php foreach ($processes as $process): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($process['pid']) ?></strong></td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars(substr($process['command'], 0, 20)) ?><?= strlen($process['command']) > 20 ? '...' : '' ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($process['vsz']) ?> / <?= htmlspecialchars($process['rss']) ?></small>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($process['user']) ?></td>
                                <td>
                                    <span class="badge <?= (float)$process['cpu'] > 10 ? 'bg-danger' : ((float)$process['cpu'] > 5 ? 'bg-warning' : 'bg-success') ?>">
                                        <?= htmlspecialchars($process['cpu']) ?>%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= (float)$process['mem'] > 10 ? 'bg-danger' : ((float)$process['mem'] > 5 ? 'bg-warning' : 'bg-success') ?>">
                                        <?= htmlspecialchars($process['mem']) ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    $statusText = 'Неизвестно';
                                    
                                    switch ($process['status']) {
                                        case 'active':
                                            $statusClass = 'bg-success';
                                            $statusText = 'Активен';
                                            break;
                                        case 'sleeping':
                                            $statusClass = 'bg-warning';
                                            $statusText = 'Спящий';
                                            break;
                                        case 'stopped':
                                            $statusClass = 'bg-danger';
                                            $statusText = 'Остановлен';
                                            break;
                                        case 'zombie':
                                            $statusClass = 'bg-dark';
                                            $statusText = 'Зомби';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td><?= htmlspecialchars($process['time']) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-info" onclick="showProcessInfo(<?= $process['pid'] ?>)" title="Информация">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        <?php if ($process['status'] === 'active'): ?>
                                        <button class="btn btn-outline-warning" onclick="pauseProcess(<?= $process['pid'] ?>)" title="Приостановить">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-danger" onclick="stopProcess(<?= $process['pid'] ?>)" title="Остановить">
                                            <i class="fas fa-stop"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Нет данных о процессах</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для нового процесса -->
<div class="modal fade" id="newProcessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Запустить новый процесс</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newProcessForm">
                    <div class="mb-3">
                        <label for="processCommand" class="form-label">Команда</label>
                        <input type="text" class="form-control" id="processCommand" placeholder="например: nginx" required>
                    </div>
                    <div class="mb-3">
                        <label for="processUser" class="form-label">Пользователь</label>
                        <select class="form-select" id="processUser">
                            <option value="root">root</option>
                            <option value="www-data">www-data</option>
                            <option value="systemd">systemd</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="processPriority" class="form-label">Приоритет</label>
                        <select class="form-select" id="processPriority">
                            <option value="0">Высокий</option>
                            <option value="5" selected>Обычный</option>
                            <option value="10">Низкий</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="startProcess()">Запустить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для информации о процессе -->
<div class="modal fade" id="processInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Информация о процессе</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="processInfoContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Автообновление каждые 30 секунд
    setInterval(function() {
        location.reload();
    }, 30000);
});

function showProcessInfo(pid) {
    $('#processInfoModal').modal('show');
    $('#processInfoContent').html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
        </div>
    `);
    
    // Здесь будет AJAX запрос для получения информации о процессе
    // Пока показываем заглушку
    setTimeout(function() {
        $('#processInfoContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Основная информация</h6>
                    <p><strong>PID:</strong> ${pid}</p>
                    <p><strong>Команда:</strong> Процесс ${pid}</p>
                    <p><strong>Пользователь:</strong> root</p>
                    <p><strong>Статус:</strong> <span class="badge bg-success">Активен</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Ресурсы</h6>
                    <p><strong>CPU:</strong> 0.5%</p>
                    <p><strong>RAM:</strong> 1.2%</p>
                    <p><strong>Время работы:</strong> 2:15:30</p>
                    <p><strong>Виртуальная память:</strong> 128 MB</p>
                </div>
            </div>
        `);
    }, 1000);
}

function startProcess() {
    const command = $('#processCommand').val();
    const user = $('#processUser').val();
    const priority = $('#processPriority').val();
    
    if (!command) {
        showAlert('Введите команду для запуска', 'danger');
        return;
    }
    
    // Здесь будет AJAX запрос для запуска процесса
    showAlert(`Процесс "${command}" запущен`, 'success');
    $('#newProcessModal').modal('hide');
    $('#newProcessForm')[0].reset();
    location.reload();
}

function pauseProcess(pid) {
    if (confirm(`Приостановить процесс ${pid}?`)) {
        // Здесь будет AJAX запрос для приостановки процесса
        showAlert(`Процесс ${pid} приостановлен`, 'warning');
        location.reload();
    }
}

function stopProcess(pid) {
    if (confirm(`Вы уверены, что хотите остановить процесс ${pid}?`)) {
        // Здесь будет AJAX запрос для остановки процесса
        showAlert(`Процесс ${pid} остановлен`, 'danger');
        location.reload();
    }
}

function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Добавляем уведомление в начало страницы
    $('.container-fluid').prepend(alertHtml);
    
    // Автоматически скрываем через 3 секунды
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 3000);
}
</script>
