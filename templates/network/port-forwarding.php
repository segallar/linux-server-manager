<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-exchange-alt me-2"></i>
            Проброс портов
        </h1>
    </div>
</div>

<!-- Статистика проброса портов -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-link me-2"></i>Активные правила</h3>
            <p class="value" id="active-rules">7</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-network-wired me-2"></i>Порты</h3>
            <p class="value" id="total-ports">12</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Защищенные</h3>
            <p class="value" id="protected-ports">8</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3><i class="fas fa-exclamation-triangle me-2"></i>Предупреждения</h3>
            <p class="value" id="warnings">2</p>
        </div>
    </div>
</div>

<!-- Управление пробросом портов -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Правила проброса портов
                </h5>
                <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#newPortForwardModal">
                    <i class="fas fa-plus me-2"></i>Новое правило
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Протокол</th>
                                <th>Внешний порт</th>
                                <th>Внутренний IP</th>
                                <th>Внутренний порт</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Web Server</td>
                                <td>TCP</td>
                                <td>80</td>
                                <td>192.168.1.100</td>
                                <td>80</td>
                                <td><span class="status-indicator status-online"></span>Активно</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Тест">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>HTTPS Server</td>
                                <td>TCP</td>
                                <td>443</td>
                                <td>192.168.1.100</td>
                                <td>443</td>
                                <td><span class="status-indicator status-online"></span>Активно</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Тест">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>SSH Access</td>
                                <td>TCP</td>
                                <td>2222</td>
                                <td>192.168.1.50</td>
                                <td>22</td>
                                <td><span class="status-indicator status-online"></span>Активно</td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" title="Остановить">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Тест">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Database</td>
                                <td>TCP</td>
                                <td>3306</td>
                                <td>192.168.1.200</td>
                                <td>3306</td>
                                <td><span class="status-indicator status-warning"></span>Ограничено</td>
                                <td>
                                    <button class="btn btn-sm btn-success me-1" title="Разрешить">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Тест">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>FTP Server</td>
                                <td>TCP</td>
                                <td>21</td>
                                <td>192.168.1.150</td>
                                <td>21</td>
                                <td><span class="status-indicator status-offline"></span>Отключено</td>
                                <td>
                                    <button class="btn btn-sm btn-success me-1" title="Включить">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info me-1" title="Тест">
                                        <i class="fas fa-play"></i>
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
                    Активность портов
                </h5>
            </div>
            <div class="card-body">
                <canvas id="portActivityChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Предупреждения безопасности
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 text-warning">Порт 3306 открыт</h6>
                            <small class="text-muted">База данных доступна извне</small>
                        </div>
                        <span class="badge bg-warning rounded-pill">Высокий риск</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 text-info">Порт 21 не используется</h6>
                            <small class="text-muted">FTP сервер отключен</small>
                        </div>
                        <span class="badge bg-info rounded-pill">Информация</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 text-success">Порт 443 защищен</h6>
                            <small class="text-muted">SSL сертификат активен</small>
                        </div>
                        <span class="badge bg-success rounded-pill">Безопасно</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Быстрые действия -->
<div class="row mt-4">
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
                        <button class="btn btn-success btn-custom w-100" title="Включить все правила">
                            <i class="fas fa-play me-2"></i>Включить все
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-warning btn-custom w-100" title="Остановить все правила">
                            <i class="fas fa-pause me-2"></i>Остановить все
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-info btn-custom w-100" title="Проверить все порты">
                            <i class="fas fa-search me-2"></i>Проверить порты
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-secondary btn-custom w-100" title="Экспорт конфигурации">
                            <i class="fas fa-download me-2"></i>Экспорт
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового правила -->
<div class="modal fade" id="newPortForwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Новое правило проброса портов
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newPortForwardForm">
                    <div class="mb-3">
                        <label for="ruleName" class="form-label">Название правила</label>
                        <input type="text" class="form-control" id="ruleName" required>
                    </div>
                    <div class="mb-3">
                        <label for="protocol" class="form-label">Протокол</label>
                        <select class="form-select" id="protocol" required>
                            <option value="tcp">TCP</option>
                            <option value="udp">UDP</option>
                            <option value="both">TCP/UDP</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="externalPort" class="form-label">Внешний порт</label>
                                <input type="number" class="form-control" id="externalPort" min="1" max="65535" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="internalPort" class="form-label">Внутренний порт</label>
                                <input type="number" class="form-control" id="internalPort" min="1" max="65535" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="internalIP" class="form-label">Внутренний IP адрес</label>
                        <input type="text" class="form-control" id="internalIP" placeholder="192.168.1.100" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enableRule" checked>
                            <label class="form-check-label" for="enableRule">
                                Включить правило сразу
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createPortForwardBtn">Создать правило</button>
            </div>
        </div>
    </div>
</div>
