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

    <!-- Фильтры и поиск -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label for="searchProcess" class="form-label">Поиск процесса</label>
                    <input type="text" class="form-control" id="searchProcess" placeholder="Введите имя процесса или PID...">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="filterUser" class="form-label">Пользователь</label>
                    <select class="form-select" id="filterUser">
                        <option value="">Все пользователи</option>
                        <?php
                        $users = array_unique(array_column($processes, 'user'));
                        sort($users);
                        foreach ($users as $user):
                        ?>
                        <option value="<?= htmlspecialchars($user) ?>"><?= htmlspecialchars($user) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="filterStatus" class="form-label">Статус</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Все статусы</option>
                        <option value="active">Активные</option>
                        <option value="sleeping">Спящие</option>
                        <option value="stopped">Остановленные</option>
                        <option value="zombie">Зомби</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="sortBy" class="form-label">Сортировка</label>
                    <select class="form-select" id="sortBy">
                        <option value="cpu">По CPU</option>
                        <option value="mem">По RAM</option>
                        <option value="pid">По PID</option>
                        <option value="time">По времени</option>
                        <option value="command">По имени</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="hideKernelThreads" checked>
                        <label class="form-check-label" for="hideKernelThreads">
                            Скрыть kernel threads
                            <i class="fas fa-question-circle text-muted ms-1" 
                               data-bs-toggle="tooltip" 
                               data-bs-placement="top" 
                               title="Kernel threads - это процессы ядра Linux, отображаемые в квадратных скобках [kthreadd], [ksoftirqd], [kworker] и т.д. Они управляют системными ресурсами и обычно не требуют внимания пользователя."></i>
                        </label>
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
                <small class="text-muted ms-2" id="processCount">(<?= count($processes) ?> процессов)</small>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="processesTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="pid">PID <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="command">Имя процесса <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="user">Пользователь <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="cpu">CPU % <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="mem">RAM % <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="rss">Физическая память <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="vsz">Виртуальная память <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="status">Статус <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="time">Время <i class="fas fa-sort"></i></th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($processes)): ?>
                            <?php foreach ($processes as $process): ?>
                            <tr class="process-row" 
                                data-pid="<?= htmlspecialchars($process['pid']) ?>"
                                data-command="<?= htmlspecialchars(strtolower($process['command'])) ?>"
                                data-user="<?= htmlspecialchars($process['user']) ?>"
                                data-status="<?= htmlspecialchars($process['status']) ?>"
                                data-cpu="<?= (float)$process['cpu'] ?>"
                                data-mem="<?= (float)$process['mem'] ?>"
                                data-rss="<?= htmlspecialchars($process['rss']) ?>"
                                data-vsz="<?= htmlspecialchars($process['vsz']) ?>"
                                data-time="<?= htmlspecialchars($process['time']) ?>">
                                <td><strong><?= htmlspecialchars($process['pid']) ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars(substr($process['command'], 0, 25)) ?><?= strlen($process['command']) > 25 ? '...' : '' ?></strong>
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
                                <td><?= htmlspecialchars($process['rss']) ?></td>
                                <td><?= htmlspecialchars($process['vsz']) ?></td>
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
                                <td colspan="10" class="text-center">Нет данных о процессах</td>
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

<style>
.sortable {
    cursor: pointer;
    user-select: none;
}

.sortable:hover {
    background-color: #f8f9fa;
}

.sortable i {
    margin-left: 5px;
    opacity: 0.5;
}

.sortable.asc i::before {
    content: "\f0de";
    opacity: 1;
}

.sortable.desc i::before {
    content: "\f0dd";
    opacity: 1;
}

.process-row.hidden {
    display: none;
}

#processCount {
    font-size: 0.875rem;
}
</style>

<script>
$(document).ready(function() {
    // Инициализация фильтров и сортировки
    initFilters();
    initSorting();
    
    // Автообновление каждые 30 секунд
    setInterval(function() {
        location.reload();
    }, 30000);
});

let currentSort = { column: 'cpu', direction: 'desc' };

function initFilters() {
    // Поиск по процессам
    $('#searchProcess').on('input', function() {
        filterProcesses();
    });
    
    // Фильтр по пользователю
    $('#filterUser').on('change', function() {
        filterProcesses();
    });
    
    // Фильтр по статусу
    $('#filterStatus').on('change', function() {
        filterProcesses();
    });
    
    // Фильтр kernel threads
    $('#hideKernelThreads').on('change', function() {
        filterProcesses();
    });
    
    // Сортировка
    $('#sortBy').on('change', function() {
        currentSort.column = $(this).val();
        sortProcesses();
    });
}

function initSorting() {
    $('.sortable').on('click', function() {
        const column = $(this).data('sort');
        
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'desc';
        }
        
        // Обновляем визуальные индикаторы
        $('.sortable').removeClass('asc desc');
        $(this).addClass(currentSort.direction);
        
        sortProcesses();
    });
}

function filterProcesses() {
    const searchTerm = $('#searchProcess').val().toLowerCase();
    const userFilter = $('#filterUser').val();
    const statusFilter = $('#filterStatus').val();
    const hideKernelThreads = $('#hideKernelThreads').is(':checked');
    
    let visibleCount = 0;
    
    $('.process-row').each(function() {
        const $row = $(this);
        const pid = $row.data('pid').toString();
        const command = $row.data('command');
        const user = $row.data('user');
        const status = $row.data('status');
        
        let show = true;
        
        // Фильтр kernel threads (процессы в квадратных скобках)
        if (hideKernelThreads && command.startsWith('[') && command.endsWith(']')) {
            show = false;
        }
        
        // Поиск по PID или имени процесса
        if (show && searchTerm && !pid.includes(searchTerm) && !command.includes(searchTerm)) {
            show = false;
        }
        
        // Фильтр по пользователю
        if (show && userFilter && user !== userFilter) {
            show = false;
        }
        
        // Фильтр по статусу
        if (show && statusFilter && status !== statusFilter) {
            show = false;
        }
        
        if (show) {
            $row.removeClass('hidden');
            visibleCount++;
        } else {
            $row.addClass('hidden');
        }
    });
    
    // Обновляем счетчик
    $('#processCount').text(`(${visibleCount} процессов)`);
}

function sortProcesses() {
    const $tbody = $('#processesTable tbody');
    const $rows = $tbody.find('.process-row:not(.hidden)').get();
    
    $rows.sort(function(a, b) {
        const $a = $(a);
        const $b = $(b);
        
        let aVal, bVal;
        
        switch (currentSort.column) {
            case 'pid':
                aVal = parseInt($a.data('pid'));
                bVal = parseInt($b.data('pid'));
                break;
            case 'cpu':
                aVal = parseFloat($a.data('cpu'));
                bVal = parseFloat($b.data('cpu'));
                break;
            case 'mem':
                aVal = parseFloat($a.data('mem'));
                bVal = parseFloat($b.data('mem'));
                break;
            case 'rss':
                aVal = parseMemorySize($a.data('rss'));
                bVal = parseMemorySize($b.data('rss'));
                break;
            case 'vsz':
                aVal = parseMemorySize($a.data('vsz'));
                bVal = parseMemorySize($b.data('vsz'));
                break;
            case 'command':
                aVal = $a.data('command');
                bVal = $b.data('command');
                break;
            case 'user':
                aVal = $a.data('user');
                bVal = $b.data('user');
                break;
            case 'status':
                aVal = $a.data('status');
                bVal = $b.data('status');
                break;
            case 'time':
                aVal = parseTime($a.data('time'));
                bVal = parseTime($b.data('time'));
                break;
            default:
                return 0;
        }
        
        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
    
    $tbody.append($rows);
}

function parseMemorySize(memoryString) {
    // Парсим строки вида "128.5 KB", "2.1 MB", "1.0 GB"
    const match = memoryString.match(/^([\d.]+)\s*([KMGT]?B)$/i);
    if (!match) return 0;
    
    const value = parseFloat(match[1]);
    const unit = match[2].toUpperCase();
    
    switch (unit) {
        case 'B': return value;
        case 'KB': return value * 1024;
        case 'MB': return value * 1024 * 1024;
        case 'GB': return value * 1024 * 1024 * 1024;
        case 'TB': return value * 1024 * 1024 * 1024 * 1024;
        default: return value;
    }
}

function parseTime(timeString) {
    // Парсим время в форматах:
    // "0:00" - минуты:секунды
    // "1:23:45" - часы:минуты:секунды
    // "2-03:45:12" - дни-часы:минуты:секунды
    
    const parts = timeString.split(/[-:]/);
    
    if (parts.length === 2) {
        // Формат "0:00" - минуты:секунды
        const minutes = parseInt(parts[0]) || 0;
        const seconds = parseInt(parts[1]) || 0;
        return minutes * 60 + seconds;
    } else if (parts.length === 3) {
        // Формат "1:23:45" - часы:минуты:секунды
        const hours = parseInt(parts[0]) || 0;
        const minutes = parseInt(parts[1]) || 0;
        const seconds = parseInt(parts[2]) || 0;
        return hours * 3600 + minutes * 60 + seconds;
    } else if (parts.length === 4) {
        // Формат "2-03:45:12" - дни-часы:минуты:секунды
        const days = parseInt(parts[0]) || 0;
        const hours = parseInt(parts[1]) || 0;
        const minutes = parseInt(parts[2]) || 0;
        const seconds = parseInt(parts[3]) || 0;
        return days * 86400 + hours * 3600 + minutes * 60 + seconds;
    }
    
    return 0;
}

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
