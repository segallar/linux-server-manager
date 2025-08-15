<?php
$currentPage = 'dashboard';
$title = 'Главная - Linux Server Manager';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-tachometer-alt me-2"></i>
            Панель управления
        </h1>
    </div>
</div>

<!-- Статистика системы -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-microchip me-2"></i>CPU</h3>
            <p class="value" id="cpu-usage">45%</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-memory me-2"></i>Память</h3>
            <p class="value" id="memory-usage">67%</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-hdd me-2"></i>Диск</h3>
            <p class="value" id="disk-usage">23%</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-network-wired me-2"></i>Сеть</h3>
            <p class="value" id="network-usage">12%</p>
        </div>
    </div>
</div>

<!-- Быстрые действия -->
<div class="row mb-4">
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
                        <button class="btn btn-primary btn-custom w-100" data-bs-toggle="tooltip" title="Перезагрузить систему">
                            <i class="fas fa-redo me-2"></i>Перезагрузка
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-warning btn-custom w-100" data-bs-toggle="tooltip" title="Остановить систему">
                            <i class="fas fa-power-off me-2"></i>Выключение
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-info btn-custom w-100" data-bs-toggle="tooltip" title="Обновить данные">
                            <i class="fas fa-sync me-2"></i>Обновить
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-success btn-custom w-100" data-bs-toggle="tooltip" title="Создать резервную копию">
                            <i class="fas fa-download me-2"></i>Резервная копия
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Активные процессы -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tasks me-2"></i>
                    Активные процессы
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Процесс</th>
                                <th>CPU</th>
                                <th>Память</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>nginx</td>
                                <td>2.3%</td>
                                <td>45 MB</td>
                                <td><span class="status-indicator status-online"></span>Работает</td>
                            </tr>
                            <tr>
                                <td>mysql</td>
                                <td>5.1%</td>
                                <td>128 MB</td>
                                <td><span class="status-indicator status-online"></span>Работает</td>
                            </tr>
                            <tr>
                                <td>php-fpm</td>
                                <td>1.8%</td>
                                <td>67 MB</td>
                                <td><span class="status-indicator status-online"></span>Работает</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Последние события
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Система перезагружена</h6>
                            <small class="text-muted">Система была успешно перезагружена</small>
                        </div>
                        <span class="badge bg-success rounded-pill">2 мин назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Обновление безопасности</h6>
                            <small class="text-muted">Установлены обновления безопасности</small>
                        </div>
                        <span class="badge bg-info rounded-pill">15 мин назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Резервная копия создана</h6>
                            <small class="text-muted">Автоматическая резервная копия выполнена</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">1 час назад</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Системная информация -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Системная информация
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ОС:</strong></td>
                                <td>Ubuntu 22.04 LTS</td>
                            </tr>
                            <tr>
                                <td><strong>Ядро:</strong></td>
                                <td>5.15.0-88-generic</td>
                            </tr>
                            <tr>
                                <td><strong>Время работы:</strong></td>
                                <td>15 дней, 7 часов</td>
                            </tr>
                            <tr>
                                <td><strong>Загрузка:</strong></td>
                                <td>0.45, 0.32, 0.28</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Процессор:</strong></td>
                                <td>Intel Core i7-8700K</td>
                            </tr>
                            <tr>
                                <td><strong>Память:</strong></td>
                                <td>16 GB DDR4</td>
                            </tr>
                            <tr>
                                <td><strong>Диск:</strong></td>
                                <td>500 GB SSD</td>
                            </tr>
                            <tr>
                                <td><strong>Сеть:</strong></td>
                                <td>1 Gbps Ethernet</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
