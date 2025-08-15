<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-shield-alt me-2"></i>
            IPSec
        </h1>
    </div>
</div>

<!-- Статистика IPSec -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-link me-2"></i>Активные туннели</h3>
            <p class="value" id="active-tunnels">3</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-exchange-alt me-2"></i>Соединения</h3>
            <p class="value" id="connections">6</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-tachometer-alt me-2"></i>Трафик</h3>
            <p class="value" id="ipsec-traffic">3.2 GB</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Шифрование</h3>
            <p class="value" id="encryption">AES-256</p>
        </div>
    </div>
</div>

<!-- Управление IPSec туннелями -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    IPSec туннели
                </h5>
                <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#newIpsecModal">
                    <i class="fas fa-plus me-2"></i>Новый туннель
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Локальный IP</th>
                                <th>Удаленный IP</th>
                                <th>Статус</th>
                                <th>Время работы</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Office-Branch</td>
                                <td>192.168.1.1</td>
                                <td>203.0.113.10</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
                                <td>5д 12ч</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Настройки">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>DataCenter</td>
                                <td>192.168.1.1</td>
                                <td>198.51.100.50</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
                                <td>2д 8ч</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Настройки">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Backup-Site</td>
                                <td>192.168.1.1</td>
                                <td>10.0.0.100</td>
                                <td><span class="status-indicator status-offline"></span>Остановлен</td>
                                <td>-</td>
                                <td>
                                    <button class="btn btn-sm btn-success me-1" title="Запустить">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Настройки">
                                        <i class="fas fa-cog"></i>
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

<!-- Мониторинг соединений -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Мониторинг соединений
                </h5>
            </div>
            <div class="card-body">
                <canvas id="ipsecChart" width="600" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
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
                            <h6 class="mb-1">Туннель переподключен</h6>
                            <small class="text-muted">Office-Branch восстановлен</small>
                        </div>
                        <span class="badge bg-success rounded-pill">5 мин назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Новый туннель создан</h6>
                            <small class="text-muted">DataCenter подключен</small>
                        </div>
                        <span class="badge bg-info rounded-pill">2 часа назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Туннель отключен</h6>
                            <small class="text-muted">Backup-Site потерял связь</small>
                        </div>
                        <span class="badge bg-warning rounded-pill">1 день назад</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Конфигурация безопасности -->
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
                        <h6>Шифрование</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>AES-256</li>
                            <li><i class="fas fa-check text-success me-2"></i>SHA-256</li>
                            <li><i class="fas fa-check text-success me-2"></i>DH Group 14</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Протоколы</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>IKEv2</li>
                            <li><i class="fas fa-check text-success me-2"></i>ESP</li>
                            <li><i class="fas fa-check text-success me-2"></i>AH</li>
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

<!-- Модальное окно для создания нового IPSec туннеля -->
<div class="modal fade" id="newIpsecModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Новый IPSec туннель
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newIpsecForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tunnelName" class="form-label">Название туннеля</label>
                                <input type="text" class="form-control" id="tunnelName" required>
                            </div>
                            <div class="mb-3">
                                <label for="localIP" class="form-label">Локальный IP</label>
                                <input type="text" class="form-control" id="localIP" placeholder="192.168.1.1" required>
                            </div>
                            <div class="mb-3">
                                <label for="remoteIP" class="form-label">Удаленный IP</label>
                                <input type="text" class="form-control" id="remoteIP" placeholder="203.0.113.10" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encryptionAlgo" class="form-label">Алгоритм шифрования</label>
                                <select class="form-select" id="encryptionAlgo" required>
                                    <option value="aes-256">AES-256</option>
                                    <option value="aes-192">AES-192</option>
                                    <option value="aes-128">AES-128</option>
                                    <option value="3des">3DES</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="hashAlgo" class="form-label">Хеш-алгоритм</label>
                                <select class="form-select" id="hashAlgo" required>
                                    <option value="sha-256">SHA-256</option>
                                    <option value="sha-1">SHA-1</option>
                                    <option value="md5">MD5</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="dhGroup" class="form-label">DH группа</label>
                                <select class="form-select" id="dhGroup" required>
                                    <option value="14">DH Group 14 (2048-bit)</option>
                                    <option value="5">DH Group 5 (1536-bit)</option>
                                    <option value="2">DH Group 2 (1024-bit)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
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
                <button type="button" class="btn btn-primary" id="createIpsecBtn">Создать туннель</button>
            </div>
        </div>
    </div>
</div>
