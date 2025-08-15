<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-lock me-2"></i>
            WireGuard
        </h1>
    </div>
</div>

<!-- Статистика WireGuard -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-link me-2"></i>Активные интерфейсы</h3>
            <p class="value" id="active-interfaces">2</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-users me-2"></i>Подключенные пиры</h3>
            <p class="value" id="connected-peers">8</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-tachometer-alt me-2"></i>Трафик</h3>
            <p class="value" id="wg-traffic">2.1 GB</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Шифрование</h3>
            <p class="value" id="wg-encryption">ChaCha20</p>
        </div>
    </div>
</div>

<!-- Управление WireGuard интерфейсами -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    WireGuard интерфейсы
                </h5>
                <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#newWireGuardModal">
                    <i class="fas fa-plus me-2"></i>Новый интерфейс
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Интерфейс</th>
                                <th>IP адрес</th>
                                <th>Порт</th>
                                <th>Пиры</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>wg0</td>
                                <td>10.9.0.1/24</td>
                                <td>51820</td>
                                <td>5/10</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Настройки">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success me-1" title="QR код">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>wg1</td>
                                <td>10.10.0.1/24</td>
                                <td>51821</td>
                                <td>3/5</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Настройки">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success me-1" title="QR код">
                                        <i class="fas fa-qrcode"></i>
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

<!-- Управление пирами -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    Управление пирами
                </h5>
                <button class="btn btn-success btn-custom" data-bs-toggle="modal" data-bs-target="#newPeerModal">
                    <i class="fas fa-user-plus me-2"></i>Добавить пир
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Публичный ключ</th>
                                <th>IP адрес</th>
                                <th>Интерфейс</th>
                                <th>Последний хендшейк</th>
                                <th>Переданный трафик</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>laptop-user1</td>
                                <td><code>abc123...</code></td>
                                <td>10.9.0.10</td>
                                <td>wg0</td>
                                <td>2 мин назад</td>
                                <td>256 MB</td>
                                <td>
                                    <button class="btn btn-sm btn-info me-1" title="Конфигурация">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning me-1" title="Отключить">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>mobile-user2</td>
                                <td><code>def456...</code></td>
                                <td>10.9.0.11</td>
                                <td>wg0</td>
                                <td>5 мин назад</td>
                                <td>128 MB</td>
                                <td>
                                    <button class="btn btn-sm btn-info me-1" title="Конфигурация">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning me-1" title="Отключить">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>server-backup</td>
                                <td><code>ghi789...</code></td>
                                <td>10.10.0.10</td>
                                <td>wg1</td>
                                <td>1 час назад</td>
                                <td>1.2 GB</td>
                                <td>
                                    <button class="btn btn-sm btn-info me-1" title="Конфигурация">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning me-1" title="Отключить">
                                        <i class="fas fa-times"></i>
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

<!-- Мониторинг и статистика -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Активность WireGuard
                </h5>
            </div>
            <div class="card-body">
                <canvas id="wireguardChart" width="400" height="200"></canvas>
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
                            <h6 class="mb-1">Новый пир подключен</h6>
                            <small class="text-muted">laptop-user1 подключился к wg0</small>
                        </div>
                        <span class="badge bg-success rounded-pill">2 мин назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Пир отключен</h6>
                            <small class="text-muted">mobile-user2 отключился от wg0</small>
                        </div>
                        <span class="badge bg-warning rounded-pill">15 мин назад</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">Интерфейс перезапущен</h6>
                            <small class="text-muted">wg1 был перезапущен</small>
                        </div>
                        <span class="badge bg-info rounded-pill">1 час назад</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового интерфейса -->
<div class="modal fade" id="newWireGuardModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Новый WireGuard интерфейс
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newWireGuardForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interfaceName" class="form-label">Имя интерфейса</label>
                                <input type="text" class="form-control" id="interfaceName" placeholder="wg0" required>
                            </div>
                            <div class="mb-3">
                                <label for="wgIP" class="form-label">IP адрес</label>
                                <input type="text" class="form-control" id="wgIP" placeholder="10.9.0.1/24" required>
                            </div>
                            <div class="mb-3">
                                <label for="wgPort" class="form-label">Порт</label>
                                <input type="number" class="form-control" id="wgPort" min="1024" max="65535" value="51820" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="privateKey" class="form-label">Приватный ключ</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="privateKey" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="generatePrivateKey">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="publicKey" class="form-label">Публичный ключ</label>
                                <input type="text" class="form-control" id="publicKey" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="maxPeers" class="form-label">Максимум пиров</label>
                                <input type="number" class="form-control" id="maxPeers" min="1" max="100" value="10" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="wgDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="wgDescription" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createWireGuardBtn">Создать интерфейс</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления пира -->
<div class="modal fade" id="newPeerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Добавить пир
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newPeerForm">
                    <div class="mb-3">
                        <label for="peerName" class="form-label">Имя пира</label>
                        <input type="text" class="form-control" id="peerName" required>
                    </div>
                    <div class="mb-3">
                        <label for="peerInterface" class="form-label">Интерфейс</label>
                        <select class="form-select" id="peerInterface" required>
                            <option value="">Выберите интерфейс</option>
                            <option value="wg0">wg0 (10.9.0.1/24)</option>
                            <option value="wg1">wg1 (10.10.0.1/24)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="peerIP" class="form-label">IP адрес пира</label>
                        <input type="text" class="form-control" id="peerIP" placeholder="10.9.0.10" required>
                    </div>
                    <div class="mb-3">
                        <label for="peerPublicKey" class="form-label">Публичный ключ пира</label>
                        <textarea class="form-control" id="peerPublicKey" rows="3" placeholder="Введите публичный ключ пира"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="peerDescription" class="form-label">Описание</label>
                        <textarea class="form-control" id="peerDescription" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createPeerBtn">Добавить пир</button>
            </div>
        </div>
    </div>
</div>
