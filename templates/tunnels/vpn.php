<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-shield-virus me-2"></i>
            VPN туннели
        </h1>
    </div>
</div>

<!-- Статистика VPN -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-link me-2"></i>Активные VPN</h3>
            <p class="value" id="active-vpn">2</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-users me-2"></i>Подключенные клиенты</h3>
            <p class="value" id="vpn-clients">8</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-tachometer-alt me-2"></i>Трафик</h3>
            <p class="value" id="vpn-traffic">1.2 GB</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Шифрование</h3>
            <p class="value" id="encryption">AES-256</p>
        </div>
    </div>
</div>

<!-- Управление VPN туннелями -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Управление VPN
                </h5>
                <div>
                    <button class="btn btn-success btn-custom me-2" data-bs-toggle="modal" data-bs-target="#newVpnModal">
                        <i class="fas fa-plus me-2"></i>Новый VPN
                    </button>
                    <button class="btn btn-info btn-custom">
                        <i class="fas fa-download me-2"></i>Экспорт конфигурации
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Тип</th>
                                <th>IP адрес</th>
                                <th>Клиенты</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Office VPN</td>
                                <td>OpenVPN</td>
                                <td>10.8.0.1</td>
                                <td>5/10</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
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
                                <td>Remote Access</td>
                                <td>WireGuard</td>
                                <td>10.9.0.1</td>
                                <td>3/5</td>
                                <td><span class="status-indicator status-online"></span>Активен</td>
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
                                <td>Test VPN</td>
                                <td>OpenVPN</td>
                                <td>10.10.0.1</td>
                                <td>0/3</td>
                                <td><span class="status-indicator status-offline"></span>Остановлен</td>
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

<!-- Подключенные клиенты -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    Подключенные клиенты
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Клиент</th>
                                <th>VPN</th>
                                <th>IP адрес</th>
                                <th>Подключен</th>
                                <th>Трафик</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>user1@company.com</td>
                                <td>Office VPN</td>
                                <td>10.8.0.10</td>
                                <td>2 часа назад</td>
                                <td>256 MB</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" title="Отключить">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>admin@company.com</td>
                                <td>Office VPN</td>
                                <td>10.8.0.11</td>
                                <td>15 мин назад</td>
                                <td>128 MB</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" title="Отключить">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>remote1@company.com</td>
                                <td>Remote Access</td>
                                <td>10.9.0.10</td>
                                <td>1 час назад</td>
                                <td>512 MB</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" title="Отключить">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Статистика трафика
                </h5>
            </div>
            <div class="card-body">
                <canvas id="trafficChart" width="300" height="200"></canvas>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Office VPN</span>
                        <span>45%</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" style="width: 45%"></div>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Remote Access</span>
                        <span>55%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: 55%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового VPN -->
<div class="modal fade" id="newVpnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Новый VPN туннель
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newVpnForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vpnName" class="form-label">Название VPN</label>
                                <input type="text" class="form-control" id="vpnName" required>
                            </div>
                            <div class="mb-3">
                                <label for="vpnType" class="form-label">Тип VPN</label>
                                <select class="form-select" id="vpnType" required>
                                    <option value="">Выберите тип</option>
                                    <option value="openvpn">OpenVPN</option>
                                    <option value="wireguard">WireGuard</option>
                                    <option value="ipsec">IPSec</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="vpnNetwork" class="form-label">Сеть</label>
                                <input type="text" class="form-control" id="vpnNetwork" placeholder="10.8.0.0/24" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maxClients" class="form-label">Максимум клиентов</label>
                                <input type="number" class="form-control" id="maxClients" min="1" max="100" value="10" required>
                            </div>
                            <div class="mb-3">
                                <label for="encryptionType" class="form-label">Шифрование</label>
                                <select class="form-select" id="encryptionType" required>
                                    <option value="aes-256">AES-256</option>
                                    <option value="aes-128">AES-128</option>
                                    <option value="chacha20">ChaCha20</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="port" class="form-label">Порт</label>
                                <input type="number" class="form-control" id="port" min="1024" max="65535" value="1194" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createVpnBtn">Создать VPN</button>
            </div>
        </div>
    </div>
</div>
