<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
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
    <!-- Навигационная панель Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <!-- Бренд -->
            <a class="navbar-brand" href="/">
                <i class="fas fa-server"></i> Server Manager
            </a>
            
            <!-- Кнопка мобильного меню -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Навигационные элементы -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="/">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'system' ? 'active' : '' ?>" href="/system">
                            <i class="fas fa-server"></i> Системная информация
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'processes' ? 'active' : '' ?>" href="/processes">
                            <i class="fas fa-tasks"></i> Управление процессами
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'services' ? 'active' : '' ?>" href="/services">
                            <i class="fas fa-cogs"></i> Управление сервисами
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'packages' ? 'active' : '' ?>" href="/packages">
                            <i class="fas fa-box"></i> Управление пакетами
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'firewall' ? 'active' : '' ?>" href="/firewall">
                            <i class="fas fa-shield-alt"></i> Файрвол
                        </a>
                    </li>
                    
                    <!-- Выпадающее меню для сети -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-network-wired"></i> Сеть
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/network/ssh"><i class="fas fa-terminal"></i> SSH туннели</a></li>
                            <li><a class="dropdown-item" href="/network/port-forwarding"><i class="fas fa-exchange-alt"></i> Проброс портов</a></li>
                            <li><a class="dropdown-item" href="/network/wireguard"><i class="fas fa-lock"></i> WireGuard</a></li>
                            <li><a class="dropdown-item" href="/network/cloudflare"><i class="fas fa-cloud"></i> Cloudflare</a></li>
                            <li><a class="dropdown-item" href="/network/routing"><i class="fas fa-route"></i> Маршрутизация</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <main class="main-content">
        <div class="container-fluid">
            <?= $content ?? '' ?>
            
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
        </div>
    </main>

    <!-- Кастомные скрипты -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/smart-loading.js"></script>
</body>
</html>
