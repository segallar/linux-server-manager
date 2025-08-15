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
                            <h3 class="mb-0" id="total-processes">0</h3>
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
                            <h3 class="mb-0" id="active-processes">0</h3>
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
                            <h3 class="mb-0" id="sleeping-processes">0</h3>
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
                            <h3 class="mb-0" id="stopped-processes">0</h3>
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
                    <tbody id="processes-table">
                        <tr>
                            <td colspan="8" class="text-center">Загрузка процессов...</td>
                        </tr>
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

<script>
$(document).ready(function() {
    loadProcesses();
    setInterval(loadProcesses, 10000); // Обновляем каждые 10 секунд
});

function loadProcesses() {
    // Здесь будет AJAX запрос для получения списка процессов
    // Пока используем заглушки
    $('#total-processes').text('156');
    $('#active-processes').text('142');
    $('#sleeping-processes').text('12');
    $('#stopped-processes').text('2');
    
    // Обновляем таблицу
    $('#processes-table').html(`
        <tr>
            <td>1</td>
            <td>systemd</td>
            <td>root</td>
            <td>0.1%</td>
            <td>0.2%</td>
            <td><span class="badge bg-success">Активен</span></td>
            <td>2:15:30</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="pauseProcess(1)"><i class="fas fa-pause"></i></button>
                <button class="btn btn-sm btn-danger" onclick="stopProcess(1)"><i class="fas fa-stop"></i></button>
            </td>
        </tr>
        <tr>
            <td>1234</td>
            <td>nginx</td>
            <td>www-data</td>
            <td>2.3%</td>
            <td>1.5%</td>
            <td><span class="badge bg-success">Активен</span></td>
            <td>0:45:12</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="pauseProcess(1234)"><i class="fas fa-pause"></i></button>
                <button class="btn btn-sm btn-danger" onclick="stopProcess(1234)"><i class="fas fa-stop"></i></button>
            </td>
        </tr>
    `);
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
    loadProcesses();
}

function pauseProcess(pid) {
    // Здесь будет AJAX запрос для приостановки процесса
    showAlert(`Процесс ${pid} приостановлен`, 'warning');
    loadProcesses();
}

function stopProcess(pid) {
    if (confirm(`Вы уверены, что хотите остановить процесс ${pid}?`)) {
        // Здесь будет AJAX запрос для остановки процесса
        showAlert(`Процесс ${pid} остановлен`, 'danger');
        loadProcesses();
    }
}
</script>
