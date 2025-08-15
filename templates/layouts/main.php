<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Linux Server Manager' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome для иконок -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery (через CDN) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
             integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
             crossorigin="anonymous"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Кастомные стили -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Основной контент -->
    <div class="container-fluid">
        <div class="row">
            <!-- Боковое меню -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="/">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'system' ? 'active' : '' ?>" href="/system">
                                <i class="fas fa-server"></i>
                                Системная информация
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'processes' ? 'active' : '' ?>" href="/processes">
                                <i class="fas fa-tasks"></i>
                                Управление процессами
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($currentPage ?? '') === 'services' ? 'active' : '' ?>" href="/services">
                                <i class="fas fa-cogs"></i>
                                Управление сервисами
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#tunnelsSubmenu">
                                <i class="fas fa-network-wired"></i>
                                <span>Туннели</span>
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse submenu" id="tunnelsSubmenu">
                                <a class="nav-link" href="/tunnels/ssh"><i class="fas fa-terminal"></i>SSH туннели</a>
                                <a class="nav-link" href="/tunnels/port-forwarding"><i class="fas fa-exchange-alt"></i>Проброс портов</a>
                                <a class="nav-link" href="/tunnels/wireguard"><i class="fas fa-lock"></i>WireGuard</a>
                                <a class="nav-link" href="/tunnels/cloudflare"><i class="fas fa-cloud"></i>Cloudflare</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Основной контент -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="pt-3 pb-2 mb-3">
                    <?= $content ?? '' ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Кастомные скрипты -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
