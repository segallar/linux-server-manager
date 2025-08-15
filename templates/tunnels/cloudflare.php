<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-cloud me-2"></i>
            Cloudflare
        </h1>
    </div>
</div>

<!-- Статистика Cloudflare -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-link me-2"></i>Активные туннели</h3>
            <p class="value" id="active-tunnels">2</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-globe me-2"></i>Домены</h3>
            <p class="value" id="domains">5</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-tachometer-alt me-2"></i>Трафик</h3>
            <p class="value" id="cf-traffic">1.8 GB</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Защита</h3>
            <p class="value" id="protection">100%</p>
        </div>
    </div>
</div>

<!-- Управление Cloudflare туннелями -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Cloudflare туннели
                </h5>
                <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#newCloudflareModal">
                    <i class="fas fa-plus me-2"></i>Новый туннель
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Домен</th>
                                <th>Локальный сервис</th>
                                <th>Статус</th>
                                <th>Создан</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>web-server</td>
                                <td>web.example.com</td>
                                <td>localhost:8080</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
                                <td>2 дня назад</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Настройки">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success me-1" title="Логи">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>api-server</td>
                                <td>api.example.com</td>
                                <td>localhost:3000</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
                                <td>1 неделя назад</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Настройки">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success me-1" title="Логи">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>admin-panel</td>
                                <td>admin.example.com</td>
                                <td>localhost:9000</td>
                                <td><span class="status-indicator status-offline"></span>Остановлен</td>
                                <td>3 дня назад</td>
                                <td>
                                    <button class="btn btn-sm btn-success me-1" title="Запустить">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Настройки">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success me-1" title="Логи">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Мониторинг и аналитика -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Трафик туннелей
                </h5>
            </div>
            <div class="card-body">
                <canvas id="cloudflareChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Последние события
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Туннель перезапущен</h6>
                            <small class="text-muted">web-server восстановлен</small>
                        </div>
                        <span class="badge bg-success rounded-pill">10 мин назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Новый домен добавлен</h6>
                            <small class="text-muted">api.example.com подключен</small>
                        </div>
                        <span class="badge bg-info rounded-pill">1 час назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">DDoS атака заблокирована</h6>
                            <small class="text-muted">web.example.com защищен</small>
                        </div>
                        <span class="badge bg-warning rounded-pill">2 часа назад</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Настройки безопасности и производительности -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    Настройки безопасности
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Защита</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>DDoS Protection</li>
                            <li><i class="fas fa-check text-success me-2"></i>WAF</li>
                            <li><i class="fas fa-check text-success me-2"></i>Bot Management</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>SSL/TLS</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Full (strict)</li>
                            <li><i class="fas fa-check text-success me-2"></i>HSTS</li>
                            <li><i class="fas fa-check text-success me-2"></i>OCSP Stapling</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Быстрые действия
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-success btn-custom w-100" title="Запустить все туннели">
                            <i class="fas fa-play me-2"></i>Запустить все
                        </button>
                    </div>
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-warning btn-custom w-100" title="Остановить все туннели">
                            <i class="fas fa-pause me-2"></i>Остановить все
                        </button>
                    </div>
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-info btn-custom w-100" title="Проверить статус">
                            <i class="fas fa-search me-2"></i>Проверить статус
                        </button>
                    </div>
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-secondary btn-custom w-100" title="Экспорт конфигурации">
                            <i class="fas fa-download me-2"></i>Экспорт
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового Cloudflare туннеля -->
<div class="modal fade" id="newCloudflareModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Новый Cloudflare туннель
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newCloudflareForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tunnelName" class="form-label">Название туннеля</label>
                                <input type="text" class="form-control" id="tunnelName" required>
                            </div>
                            <div class="mb-3">
                                <label for="domain" class="form-label">Домен</label>
                                <input type="text" class="form-control" id="domain" placeholder="example.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="localService" class="form-label">Локальный сервис</label>
                                <input type="text" class="form-control" id="localService" placeholder="localhost:8080" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="protocol" class="form-label">Протокол</label>
                                <select class="form-select" id="protocol" required>
                                    <option value="http">HTTP</option>
                                    <option value="https">HTTPS</option>
                                    <option value="tcp">TCP</option>
                                    <option value="ssh">SSH</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="region" class="form-label">Регион</label>
                                <select class="form-select" id="region" required>
                                    <option value="auto">Автоматически</option>
                                    <option value="us">США</option>
                                    <option value="eu">Европа</option>
                                    <option value="ap">Азия</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="maxConnections" class="form-label">Максимум соединений</label>
                                <input type="number" class="form-control" id="maxConnections" min="1" max="1000" value="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enableWaf" checked>
                            <label class="form-check-label" for="enableWaf">
                                Включить WAF
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enableDdos" checked>
                            <label class="form-check-label" for="enableDdos">
                                Включить DDoS защиту
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enableTunnel" checked>
                            <label class="form-check-label" for="enableTunnel">
                                Включить туннель сразу
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createCloudflareBtn">Создать туннель</button>
            </div>
        </div>
    </div>
</div>
