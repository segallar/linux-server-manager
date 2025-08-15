<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Системная информация</h1>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Системная информация -->
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
                            <p><strong>ОС:</strong> <span id="os-info">Загрузка...</span></p>
                            <p><strong>Ядро:</strong> <span id="kernel-info">Загрузка...</span></p>
                            <p><strong>Архитектура:</strong> <span id="arch-info">Загрузка...</span></p>
                        </div>
                        <div class="col-6">
                            <p><strong>Время работы:</strong> <span id="uptime-info">Загрузка...</span></p>
                            <p><strong>Дата:</strong> <span id="date-info">Загрузка...</span></p>
                            <p><strong>Часовой пояс:</strong> <span id="timezone-info">Загрузка...</span></p>
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
                            <p><strong>CPU:</strong> <span id="cpu-info">Загрузка...</span></p>
                            <p><strong>RAM:</strong> <span id="ram-info">Загрузка...</span></p>
                            <p><strong>Диск:</strong> <span id="disk-info">Загрузка...</span></p>
                        </div>
                        <div class="col-6">
                            <p><strong>Сеть:</strong> <span id="network-info">Загрузка...</span></p>
                            <p><strong>Процессы:</strong> <span id="processes-info">Загрузка...</span></p>
                            <p><strong>Пользователи:</strong> <span id="users-info">Загрузка...</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Графики -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Загрузка CPU
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="cpuChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-memory"></i> Использование RAM
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="ramChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Обновляем системную информацию каждые 5 секунд
    updateSystemInfo();
    setInterval(updateSystemInfo, 5000);
});

function updateSystemInfo() {
    // Здесь будет AJAX запрос для получения системной информации
    // Пока используем заглушки
    $('#os-info').text('Linux Ubuntu 20.04');
    $('#kernel-info').text('5.4.0-42-generic');
    $('#arch-info').text('x86_64');
    $('#uptime-info').text('2 дня, 15 часов');
    $('#date-info').text(new Date().toLocaleString());
    $('#timezone-info').text('UTC+3');
    
    $('#cpu-info').text('Intel Core i7-8700K @ 3.70GHz');
    $('#ram-info').text('8GB / 16GB (50%)');
    $('#disk-info').text('120GB / 500GB (24%)');
    $('#network-info').text('eth0: 192.168.1.100');
    $('#processes-info').text('156 активных');
    $('#users-info').text('3 подключенных');
}
</script>
