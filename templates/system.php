<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h1 class="h3 mb-0 mb-2 mb-md-0">Системная информация</h1>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> 
                        <span class="d-none d-sm-inline">Обновить</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Основная информация -->
    <div class="row">
        <!-- Система -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-server"></i> Система
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p><strong>ОС:</strong> <?= htmlspecialchars($info['system']['os']) ?></p>
                            <p><strong>Ядро:</strong> <?= htmlspecialchars($info['system']['kernel']) ?></p>
                            <p><strong>Архитектура:</strong> <?= htmlspecialchars($info['system']['architecture']) ?></p>
                            <p><strong>Хост:</strong> <?= htmlspecialchars($info['system']['hostname']) ?></p>
                        </div>
                        <div class="col-6">
                            <p><strong>Время работы:</strong> <?= htmlspecialchars($info['system']['uptime']) ?></p>
                            <p><strong>Дата:</strong> <?= htmlspecialchars($info['system']['date']) ?></p>
                            <p><strong>Часовой пояс:</strong> <?= htmlspecialchars($info['system']['timezone']) ?></p>
                            <p><strong>Загрузка:</strong> <?= htmlspecialchars($info['system']['load']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ресурсы -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-microchip"></i> Ресурсы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p><strong>CPU:</strong> <?= htmlspecialchars($info['cpu']['model']) ?></p>
                            <p><strong>Ядра:</strong> <?= $info['cpu']['cores'] ?></p>
                            <p><strong>Частота:</strong> <?= htmlspecialchars($info['cpu']['frequency']) ?> MHz</p>
                            <p><strong>Кэш:</strong> <?= htmlspecialchars($info['cpu']['cache']) ?></p>
                        </div>
                        <div class="col-6">
                            <p><strong>RAM:</strong> <?= $info['memory']['used'] ?> / <?= $info['memory']['total'] ?> (<?= $info['memory']['usage_percent'] ?>%)</p>
                            <p><strong>Процессы:</strong> <?= $info['process_count'] ?> активных</p>
                            <p><strong>Пользователи:</strong> <?= htmlspecialchars($info['system']['users']) ?></p>
                            <p><strong>Сеть:</strong> <?= $info['network']['active_count'] ?>/<?= $info['network']['total_count'] ?> активных</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Диски -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-hdd"></i> Диски
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Файловая система</th>
                                    <th>Размер</th>
                                    <th>Использовано</th>
                                    <th>Доступно</th>
                                    <th>Использование</th>
                                    <th>Точка монтирования</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($info['disk'] as $disk): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($disk['filesystem']) ?></code></td>
                                    <td><?= htmlspecialchars($disk['size']) ?></td>
                                    <td><?= htmlspecialchars($disk['used']) ?></td>
                                    <td><?= htmlspecialchars($disk['available']) ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?= $disk['usage_percent'] > 80 ? 'bg-danger' : ($disk['usage_percent'] > 60 ? 'bg-warning' : 'bg-success') ?>" 
                                                 style="width: <?= $disk['usage_percent'] ?>%">
                                                <?= $disk['usage_percent'] ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td><code><?= htmlspecialchars($disk['mounted_on']) ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Сетевые интерфейсы -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-network-wired"></i> Сетевые интерфейсы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($info['network']['interfaces'] as $interface): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-<?= $interface['status'] === 'up' ? 'success' : 'secondary' ?>">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-circle text-<?= $interface['status'] === 'up' ? 'success' : 'secondary' ?>"></i>
                                        <?= htmlspecialchars($interface['name']) ?>
                                    </h6>
                                    <p class="card-text">
                                        <strong>Статус:</strong> 
                                        <span class="badge bg-<?= $interface['status'] === 'up' ? 'success' : 'secondary' ?>">
                                            <?= $interface['status'] === 'up' ? 'Активен' : 'Неактивен' ?>
                                        </span>
                                    </p>
                                    <?php if ($interface['status'] === 'up' && !empty($interface['ips'])): ?>
                                    <p class="card-text">
                                        <strong>IP адреса:</strong><br>
                                        <?php foreach ($interface['ips'] as $ip): ?>
                                        <code class="text-primary"><?= htmlspecialchars($ip) ?></code><br>
                                        <?php endforeach; ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Дополнительная информация -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Дополнительная информация
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Домен:</strong> <?= htmlspecialchars($info['system']['domain']) ?></p>
                    <p><strong>Время загрузки:</strong> <?= htmlspecialchars($info['system']['boot_time']) ?></p>
                    <p><strong>Статус сети:</strong> 
                        <span class="badge bg-<?= $info['network']['online'] ? 'success' : 'danger' ?>">
                            <?= $info['network']['online'] ? 'Онлайн' : 'Офлайн' ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Статистика памяти
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-primary"><?= $info['memory']['usage_percent'] ?>%</h4>
                                <small class="text-muted">Использовано</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-success"><?= $info['memory']['available'] ?></h4>
                                <small class="text-muted">Доступно</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar <?= $info['memory']['usage_percent'] > 80 ? 'bg-danger' : ($info['memory']['usage_percent'] > 60 ? 'bg-warning' : 'bg-success') ?>" 
                             style="width: <?= $info['memory']['usage_percent'] ?>%">
                            <?= $info['memory']['usage_percent'] ?>%
                        </div>
                    </div>
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
});
</script>
