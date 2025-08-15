<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-terminal me-2"></i>
            SSH туннели
        </h1>
    </div>
</div>

<!-- Статистика SSH туннелей -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-link me-2"></i>Активные туннели</h3>
            <p class="value" id="active-tunnels">3</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-users me-2"></i>Подключения</h3>
            <p class="value" id="connections">12</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-clock me-2"></i>Время работы</h3>
            <p class="value" id="uptime">2д 15ч</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Безопасность</h3>
            <p class="value" id="security">100%</p>
        </div>
    </div>
</div>

<!-- Управление SSH туннелями -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Управление туннелями
                </h5>
                <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#newTunnelModal">
                    <i class="fas fa-plus me-2"></i>Новый туннель
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Локальный порт</th>
                                <th>Удаленный хост</th>
                                <th>Удаленный порт</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Web Server Tunnel</td>
                                <td>8080</td>
                                <td>192.168.1.100</td>
                                <td>80</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger me-1" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" title="Логи">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Database Tunnel</td>
                                <td>3307</td>
                                <td>10.0.0.50</td>
                                <td>3306</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger me-1" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" title="Логи">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>SSH Tunnel</td>
                                <td>2222</td>
                                <td>172.16.0.10</td>
                                <td>22</td>
                                <td><span class="status-indicator status-offline"></span>Остановлен</td>
                                <td>
                                    <button class="btn btn-sm btn-success me-1" title="Запустить">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger me-1" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" title="Логи">
                                        <i class="fas fa-file-alt"></i>
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

<!-- Мониторинг подключений -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Активность подключений
                </h5>
            </div>
            <div class="card-body">
                <canvas id="connectionsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Последние подключения
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">192.168.1.100:8080</h6>
                            <small class="text-muted">Web Server Tunnel</small>
                        </div>
                        <span class="badge bg-success rounded-pill">2 мин назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">10.0.0.50:3307</h6>
                            <small class="text-muted">Database Tunnel</small>
                        </div>
                        <span class="badge bg-info rounded-pill">5 мин назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">172.16.0.10:2222</h6>
                            <small class="text-muted">SSH Tunnel</small>
                        </div>
                        <span class="badge bg-warning rounded-pill">15 мин назад</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового туннеля -->
<div class="modal fade" id="newTunnelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Новый SSH туннель
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newTunnelForm">
                    <div class="mb-3">
                        <label for="tunnelName" class="form-label">Название туннеля</label>
                        <input type="text" class="form-control" id="tunnelName" required>
                    </div>
                    <div class="mb-3">
                        <label for="localPort" class="form-label">Локальный порт</label>
                        <input type="number" class="form-control" id="localPort" min="1024" max="65535" required>
                    </div>
                    <div class="mb-3">
                        <label for="remoteHost" class="form-label">Удаленный хост</label>
                        <input type="text" class="form-control" id="remoteHost" required>
                    </div>
                    <div class="mb-3">
                        <label for="remotePort" class="form-label">Удаленный порт</label>
                        <input type="number" class="form-control" id="remotePort" min="1" max="65535" required>
                    </div>
                    <div class="mb-3">
                        <label for="sshUser" class="form-label">SSH пользователь</label>
                        <input type="text" class="form-control" id="sshUser" required>
                    </div>
                    <div class="mb-3">
                        <label for="sshKey" class="form-label">SSH ключ</label>
                        <select class="form-select" id="sshKey" required>
                            <option value="">Выберите ключ</option>
                            <option value="id_rsa">id_rsa</option>
                            <option value="id_ed25519">id_ed25519</option>
                            <option value="custom">Пользовательский</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createTunnelBtn">Создать туннель</button>
            </div>
        </div>
    </div>
</div>
