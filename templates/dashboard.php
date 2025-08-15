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
                            <h6 class="card-title">CPU</h6>
                            <h3 class="mb-0" id="cpu-usage">0%</h3>
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
                            <h6 class="card-title">RAM</h6>
                            <h3 class="mb-0" id="ram-usage">0%</h3>
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
                            <h3 class="mb-0" id="disk-usage">0%</h3>
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
                            <h3 class="mb-0" id="network-status">Онлайн</h3>
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
                            <button class="btn btn-outline-info w-100" onclick="location.href='/tunnels/ssh'">
                                <i class="fas fa-network-wired"></i> Туннели
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
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Активные процессы
                    </h5>
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
                            <tbody id="processes-table">
                                <tr>
                                    <td colspan="4" class="text-center">Загрузка...</td>
                                </tr>
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
                            <p><strong>ОС:</strong> <span id="os-info">Загрузка...</span></p>
                            <p><strong>Ядро:</strong> <span id="kernel-info">Загрузка...</span></p>
                            <p><strong>Время работы:</strong> <span id="uptime-info">Загрузка...</span></p>
                        </div>
                        <div class="col-6">
                            <p><strong>Загрузка:</strong> <span id="load-info">Загрузка...</span></p>
                            <p><strong>Пользователи:</strong> <span id="users-info">Загрузка...</span></p>
                            <p><strong>Дата:</strong> <span id="date-info">Загрузка...</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    updateSystemInfo();
    setInterval(updateSystemInfo, 5000);
});

function updateSystemInfo() {
    // Здесь будет AJAX запрос для получения системной информации
    // Пока используем заглушки
    $('#cpu-usage').text('25%');
    $('#ram-usage').text('45%');
    $('#disk-usage').text('30%');
    $('#network-status').text('Онлайн');
    
    $('#os-info').text('Linux Ubuntu 20.04');
    $('#kernel-info').text('5.4.0-42-generic');
    $('#uptime-info').text('2 дня, 15 часов');
    $('#load-info').text('0.5, 0.3, 0.2');
    $('#users-info').text('3 подключенных');
    $('#date-info').text(new Date().toLocaleString());
    
    // Обновляем таблицу процессов
    $('#processes-table').html(`
        <tr>
            <td>1</td>
            <td>systemd</td>
            <td>0.1%</td>
            <td>0.2%</td>
        </tr>
        <tr>
            <td>1234</td>
            <td>nginx</td>
            <td>2.3%</td>
            <td>1.5%</td>
        </tr>
        <tr>
            <td>5678</td>
            <td>php-fpm</td>
            <td>1.8%</td>
            <td>2.1%</td>
        </tr>
    `);
}

function refreshStats() {
    updateSystemInfo();
    showAlert('Статистика обновлена', 'success');
}
</script>
