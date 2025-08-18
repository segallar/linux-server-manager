<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Файрвол</h1>
                <div>
                    <button class="btn btn-success me-2" onclick="enableFirewall()">
                        <i class="fas fa-shield-alt"></i> Включить
                    </button>
                    <button class="btn btn-danger me-2" onclick="disableFirewall()">
                        <i class="fas fa-times"></i> Выключить
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRuleModal">
                        <i class="fas fa-plus"></i> Новое правило
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Тип файрвола</h6>
                            <h3 class="mb-0"><?= strtoupper($stats['type'] ?? 'Unknown') ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card <?= ($stats['status'] ?? '') === 'active' ? 'bg-success' : 'bg-secondary' ?> text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Статус</h6>
                            <h3 class="mb-0"><?= ($stats['status'] ?? 'unknown') === 'active' ? 'Активен' : 'Неактивен' ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-<?= ($stats['status'] ?? '') === 'active' ? 'check-circle' : 'times-circle' ?> fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Всего правил</h6>
                            <h3 class="mb-0"><?= $stats['total_rules'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Активные соединения</h6>
                            <h3 class="mb-0"><?= $stats['active_connections'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-network-wired fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Заблокировано</h6>
                            <h3 class="mb-0"><?= $stats['blocked_attempts'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-ban fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Последняя активность</h6>
                            <h6 class="mb-0"><?= $stats['last_activity'] ?? 'Неизвестно' ?></h6>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Политика по умолчанию -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog"></i> Политика по умолчанию
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Входящие:</strong></span>
                                <span class="badge bg-<?= ($stats['default_policy']['input'] ?? '') === 'ACCEPT' ? 'success' : 'danger' ?>">
                                    <?= $stats['default_policy']['input'] ?? 'UNKNOWN' ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Исходящие:</strong></span>
                                <span class="badge bg-<?= ($stats['default_policy']['output'] ?? '') === 'ACCEPT' ? 'success' : 'danger' ?>">
                                    <?= $stats['default_policy']['output'] ?? 'UNKNOWN' ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Пересылка:</strong></span>
                                <span class="badge bg-<?= ($stats['default_policy']['forward'] ?? '') === 'ACCEPT' ? 'success' : 'danger' ?>">
                                    <?= $stats['default_policy']['forward'] ?? 'UNKNOWN' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица правил -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Правила файрвола
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($rules)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Правила файрвола не найдены</h5>
                    <p class="text-muted">Добавьте первое правило для настройки файрвола</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRuleModal">
                        <i class="fas fa-plus me-2"></i>Добавить правило
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Действие</th>
                                <th>Протокол</th>
                                <th>Порт</th>
                                <th>Источник</th>
                                <th>Описание</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rule['id']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $rule['action'] === 'ALLOW' ? 'success' : ($rule['action'] === 'DENY' ? 'danger' : 'warning') ?>">
                                            <?= $rule['action'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= strtoupper($rule['protocol']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($rule['port'] !== 'any'): ?>
                                            <span class="badge bg-secondary"><?= $rule['port'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Любой</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rule['source'] !== 'any'): ?>
                                            <span class="badge bg-dark"><?= htmlspecialchars($rule['source']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Любой</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $icon = 'fa-shield-alt';
                                        $description = $rule['description'] ?? '';
                                        
                                        // Определяем иконку на основе описания
                                        if (strpos($description, 'SSH') !== false) {
                                            $icon = 'fa-terminal';
                                        } elseif (strpos($description, 'HTTP') !== false) {
                                            $icon = 'fa-globe';
                                        } elseif (strpos($description, 'HTTPS') !== false) {
                                            $icon = 'fa-lock';
                                        } elseif (strpos($description, 'WireGuard') !== false) {
                                            $icon = 'fa-shield-virus';
                                        } elseif (strpos($description, 'DNS') !== false) {
                                            $icon = 'fa-search';
                                        } elseif (strpos($description, 'SMTP') !== false) {
                                            $icon = 'fa-envelope';
                                        } elseif (strpos($description, 'FTP') !== false) {
                                            $icon = 'fa-folder-open';
                                        } elseif (strpos($description, 'MySQL') !== false || strpos($description, 'PostgreSQL') !== false) {
                                            $icon = 'fa-database';
                                        } elseif (strpos($description, 'Redis') !== false) {
                                            $icon = 'fa-memory';
                                        } elseif (strpos($description, 'NTP') !== false) {
                                            $icon = 'fa-clock';
                                        } elseif (strpos($description, 'SNMP') !== false) {
                                            $icon = 'fa-chart-line';
                                        } elseif (strpos($description, 'VPN') !== false) {
                                            $icon = 'fa-user-shield';
                                        } elseif (strpos($description, 'AMQP') !== false || strpos($description, 'RabbitMQ') !== false) {
                                            $icon = 'fa-rabbit';
                                        } elseif (strpos($description, 'RIP') !== false) {
                                            $icon = 'fa-route';
                                        } elseif (strpos($description, 'Custom Service') !== false) {
                                            $icon = 'fa-cog';
                                        }
                                        ?>
                                        <i class="fas <?= $icon ?> me-2"></i>
                                        <?= htmlspecialchars($description) ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="deleteFirewallRule('<?= htmlspecialchars($rule['id']) ?>')" title="Удалить правило">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Логи файрвола -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-alt"></i> Логи файрвола
            </h5>
        </div>
        <div class="card-body">
            <div id="firewallLogs">
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin"></i> Загрузка логов...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для нового правила -->
<div class="modal fade" id="newRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить правило файрвола</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newRuleForm">
                    <div class="mb-3">
                        <label for="ruleAction" class="form-label">Действие</label>
                        <select class="form-select" id="ruleAction" required>
                            <option value="ALLOW">Разрешить</option>
                            <option value="DENY">Запретить</option>
                            <option value="REJECT">Отклонить</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="ruleProtocol" class="form-label">Протокол</label>
                        <select class="form-select" id="ruleProtocol">
                            <option value="any">Любой</option>
                            <option value="tcp">TCP</option>
                            <option value="udp">UDP</option>
                            <option value="icmp">ICMP</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rulePort" class="form-label">Порт</label>
                        <input type="text" class="form-control" id="rulePort" placeholder="например: 80, 443 или диапазон 1000-2000">
                        <div class="form-text">Оставьте пустым для всех портов</div>
                    </div>
                    <div class="mb-3">
                        <label for="ruleSource" class="form-label">Источник</label>
                        <input type="text" class="form-control" id="ruleSource" placeholder="например: 192.168.1.0/24 или any">
                        <div class="form-text">IP адрес или сеть. Оставьте пустым для всех</div>
                    </div>
                    <div class="mb-3">
                        <label for="ruleDescription" class="form-label">Описание</label>
                        <input type="text" class="form-control" id="ruleDescription" placeholder="Краткое описание правила">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="addFirewallRule()">Добавить</button>
            </div>
        </div>
    </div>
</div>

<script>
// Загрузка логов при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadFirewallLogs();
});

function loadFirewallLogs() {
    makeAjaxRequest('/api/firewall/logs').done(function(data) {
        if (data.success) {
            displayFirewallLogs(data.data);
        } else {
            $('#firewallLogs').html('<div class="text-center text-muted">Ошибка загрузки логов</div>');
        }
    }).fail(function() {
        $('#firewallLogs').html('<div class="text-center text-muted">Не удалось загрузить логи</div>');
    });
}

function displayFirewallLogs(logs) {
    if (logs.length === 0) {
        $('#firewallLogs').html('<div class="text-center text-muted">Логи не найдены</div>');
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm">';
    html += '<thead><tr><th>Время</th><th>Тип</th><th>Сообщение</th></tr></thead><tbody>';
    
    logs.forEach(function(log) {
        const typeClass = log.type === 'blocked' ? 'text-danger' : 
                         log.type === 'allowed' ? 'text-success' : 
                         log.type === 'rejected' ? 'text-warning' : 'text-muted';
        
        html += `<tr>
            <td><small>${log.timestamp}</small></td>
            <td><span class="badge bg-${log.type === 'blocked' ? 'danger' : log.type === 'allowed' ? 'success' : 'warning'}">${log.type}</span></td>
            <td class="${typeClass}"><small>${log.message}</small></td>
        </tr>`;
    });
    
    html += '</tbody></table></div>';
    $('#firewallLogs').html(html);
}

function enableFirewall() {
    if (confirm('Включить файрвол? Это может заблокировать некоторые соединения.')) {
        makeAjaxRequest('/api/firewall/enable', 'POST').done(function(data) {
            if (data.success) {
                showAlert('Файрвол успешно включен', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка включения файрвола: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка включения файрвола', 'danger');
        });
    }
}

function disableFirewall() {
    if (confirm('Выключить файрвол? Это может сделать сервер уязвимым.')) {
        makeAjaxRequest('/api/firewall/disable', 'POST').done(function(data) {
            if (data.success) {
                showAlert('Файрвол успешно выключен', 'warning');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка выключения файрвола: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка выключения файрвола', 'danger');
        });
    }
}

function addFirewallRule() {
    const action = $('#ruleAction').val();
    const protocol = $('#ruleProtocol').val();
    const port = $('#rulePort').val() || 'any';
    const source = $('#ruleSource').val() || 'any';
    const description = $('#ruleDescription').val();
    
    const rule = {
        action: action,
        protocol: protocol,
        port: port,
        source: source,
        description: description
    };
    
    makeAjaxRequest('/api/firewall/rule/add', 'POST', JSON.stringify(rule)).done(function(data) {
        if (data.success) {
            showAlert('Правило успешно добавлено', 'success');
            $('#newRuleModal').modal('hide');
            $('#newRuleForm')[0].reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('Ошибка добавления правила: ' + data.message, 'danger');
        }
    }).fail(function() {
        showAlert('Ошибка добавления правила', 'danger');
    });
}

function deleteFirewallRule(id) {
    if (confirm('Удалить правило файрвола?')) {
        makeAjaxRequest(`/api/firewall/rule/${id}`, 'DELETE').done(function(data) {
            if (data.success) {
                showAlert('Правило успешно удалено', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('Ошибка удаления правила: ' + data.message, 'danger');
            }
        }).fail(function() {
            showAlert('Ошибка удаления правила', 'danger');
        });
    }
}

// Обновление данных каждые 30 секунд
setInterval(function() {
    loadFirewallLogs();
}, 30000);
</script>
