<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Dashboard</h1>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshStats()">
                        <i class="fas fa-sync-alt"></i> Обновить
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
                            <h6 class="card-title">
                                CPU
                                <i class="fas fa-question-circle ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Загрузка процессора. Вычисляется на основе Load Average и количества ядер."></i>
                            </h6>
                            <h3 class="mb-0"><?= $stats['cpu']['usage'] ?>%</h3>
                            <small><?= $stats['cpu']['cores'] ?> ядер</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-microchip fa-2x"></i>
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
                            <h6 class="card-title">
                                RAM
                                <i class="fas fa-question-circle ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Использование оперативной памяти. Показывает процент занятой памяти от общего объема."></i>
                            </h6>
                            <h3 class="mb-0"><?= $stats['memory']['usage_percent'] ?>%</h3>
                            <small><?= $stats['memory']['used'] ?> / <?= $stats['memory']['total'] ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-memory fa-2x"></i>
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
                            <h6 class="card-title">Диск</h6>
                            <h3 class="mb-0"><?= $stats['disk']['usage_percent'] ?>%</h3>
                            <small><?= $stats['disk']['used'] ?> / <?= $stats['disk']['total'] ?></small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-hdd fa-2x"></i>
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
                            <h6 class="card-title">Сеть</h6>
                            <h3 class="mb-0"><?= $stats['network']['status'] ?></h3>
                            <small><?= $stats['network']['active_count'] ?>/<?= $stats['network']['total_count'] ?> активных</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-network-wired fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Быстрые действия -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Быстрые действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-primary w-100" onclick="location.href='/system'">
                                <i class="fas fa-server"></i> Системная информация
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-success w-100" onclick="location.href='/processes'">
                                <i class="fas fa-tasks"></i> Управление процессами
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-warning w-100" onclick="location.href='/services'">
                                <i class="fas fa-cogs"></i> Управление сервисами
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info w-100" onclick="location.href='/network/ssh'">
                                <i class="fas fa-network-wired"></i> Сеть
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-warning w-100" onclick="location.href='/packages'">
                                <i class="fas fa-box"></i> Пакеты
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Активные процессы -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Активные процессы
                        <i class="fas fa-question-circle text-muted ms-1" 
                           data-bs-toggle="tooltip" 
                           data-bs-placement="top" 
                           title="Топ-5 процессов по загрузке CPU. Полный список доступен в разделе 'Процессы'."></i>
                    </h5>
                    <a href="/processes" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> Все процессы
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>PID</th>
                                    <th>Имя</th>
                                    <th>CPU</th>
                                    <th>RAM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['processes'])): ?>
                                    <?php foreach ($stats['processes'] as $process): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($process['pid']) ?></td>
                                        <td>
                                            <small><?= htmlspecialchars(substr($process['command'], 0, 30)) ?><?= strlen($process['command']) > 30 ? '...' : '' ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($process['cpu']) ?>%</td>
                                        <td><?= htmlspecialchars($process['mem']) ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Нет данных</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Системная информация -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Системная информация
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p><strong>ОС:</strong> <?= htmlspecialchars($stats['system']['os']) ?></p>
                            <p><strong>Ядро:</strong> <?= htmlspecialchars($stats['system']['kernel']) ?></p>
                            <p><strong>Время работы:</strong> <?= htmlspecialchars($stats['system']['uptime']) ?></p>
                            <p><strong>Хост:</strong> <?= htmlspecialchars($stats['system']['hostname']) ?></p>
                        </div>
                        <div class="col-6">
                            <p>
                                <strong>Загрузка:</strong> 
                                <?= htmlspecialchars($stats['system']['load']) ?>
                                <i class="fas fa-question-circle text-muted ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Load Average: средняя нагрузка на систему за 1, 5 и 15 минут. Значения показывают количество процессов в очереди на выполнение. Норма: меньше количества ядер CPU."></i>
                            </p>
                            <p><strong>Пользователи:</strong> <?= htmlspecialchars($stats['system']['users']) ?></p>
                            <p><strong>Дата:</strong> <?= htmlspecialchars($stats['system']['date']) ?></p>
                            <p><strong>CPU:</strong> <?= htmlspecialchars($stats['cpu']['model']) ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($stats['network']['interfaces'])): ?>
                    <hr>
                    <h6><i class="fas fa-network-wired"></i> Сетевые интерфейсы:</h6>
                    <div class="row">
                        <?php foreach ($stats['network']['interfaces'] as $interface): ?>
                        <div class="col-12 mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge <?= $interface['status'] === 'up' ? 'bg-success' : 'bg-secondary' ?> me-2">
                                    <?= htmlspecialchars($interface['name']) ?>
                                </span>
                                <?php if ($interface['status'] === 'up'): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-circle text-success me-1"></i>
                                        <?php if (!empty($interface['ips'])): ?>
                                            <?= htmlspecialchars(implode(', ', $interface['ips'])) ?>
                                        <?php else: ?>
                                            Без IP
                                        <?php endif; ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">
                                        <i class="fas fa-circle text-secondary me-1"></i>
                                        Неактивен
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Инициализация tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Автообновление каждые 30 секунд
    setInterval(refreshStats, 30000);
});

function refreshStats() {
    // Перезагружаем страницу для получения свежих данных
    location.reload();
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

