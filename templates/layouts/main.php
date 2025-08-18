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
                <div class="position-sticky">
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
                            <a class="nav-link <?= ($currentPage ?? '') === 'packages' ? 'active' : '' ?>" href="/packages">
                                <i class="fas fa-box"></i>
                                Управление пакетами
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#networkSubmenu">
                                <i class="fas fa-network-wired"></i>
                                <span>Сеть</span>
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse submenu" id="networkSubmenu">
                                <a class="nav-link" href="/network/ssh"><i class="fas fa-terminal"></i>SSH туннели</a>
                                <a class="nav-link" href="/network/port-forwarding"><i class="fas fa-exchange-alt"></i>Проброс портов</a>
                                <a class="nav-link" href="/network/wireguard"><i class="fas fa-lock"></i>WireGuard</a>
                                <a class="nav-link" href="/network/cloudflare"><i class="fas fa-cloud"></i>Cloudflare</a>
                                <a class="nav-link" href="/network/routing"><i class="fas fa-route"></i>Маршрутизация</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Основной контент -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="pb-2 mb-3">
                    <?= $content ?? '' ?>
                </div>
                
                <!-- Подвал с версией и временем выполнения -->
                <footer class="footer mt-auto py-3 bg-light">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 text-center">
                                <small class="text-muted">
                                    Linux Server Manager v<?= getGitVersion() ?>
                                    | &copy; <?= date('Y') ?> Roman Segalla
                                    | Время генерации: <?= getPageExecutionTime() ?>
                                    | Время сервера: <?= date('Y-m-d H:i:s') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- Кастомные скрипты -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/smart-loading.js"></script>
</body>
</html>
