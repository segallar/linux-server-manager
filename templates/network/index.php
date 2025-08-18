<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-network-wired"></i> Управление сетью
                </h1>
            </div>
        </div>
    </div>

    <!-- Карточки разделов сети -->
    <div class="row">
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-terminal fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">SSH туннели</h5>
                    <p class="card-text text-muted">Управление SSH туннелями и подключениями</p>
                    <a href="/network/ssh" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Перейти
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-exchange-alt fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Проброс портов</h5>
                    <p class="card-text text-muted">Настройка правил проброса портов</p>
                    <a href="/network/port-forwarding" class="btn btn-success">
                        <i class="fas fa-arrow-right"></i> Перейти
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-lock fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title">WireGuard</h5>
                    <p class="card-text text-muted">Управление WireGuard VPN интерфейсами</p>
                    <a href="/network/wireguard" class="btn btn-warning">
                        <i class="fas fa-arrow-right"></i> Перейти
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-cloud fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title">Cloudflare</h5>
                    <p class="card-text text-muted">Управление Cloudflare туннелями</p>
                    <a href="/network/cloudflare" class="btn btn-info">
                        <i class="fas fa-arrow-right"></i> Перейти
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-route fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title">Маршрутизация</h5>
                    <p class="card-text text-muted">Управление таблицами маршрутов</p>
                    <a href="/network/routing" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> Перейти
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Информационная карточка -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-primary"></i> О разделе "Сеть"
                    </h5>
                    <p class="card-text">
                        В этом разделе вы можете управлять всеми сетевыми настройками сервера:
                    </p>
                    <ul class="mb-0">
                        <li><strong>SSH туннели</strong> - создание и управление SSH соединениями</li>
                        <li><strong>Проброс портов</strong> - настройка правил iptables для перенаправления трафика</li>
                        <li><strong>WireGuard</strong> - управление VPN интерфейсами и конфигурациями</li>
                        <li><strong>Cloudflare</strong> - настройка Cloudflare туннелей для безопасного доступа</li>
                        <li><strong>Маршрутизация</strong> - просмотр и управление таблицами маршрутов</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
