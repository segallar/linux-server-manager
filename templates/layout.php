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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- jQuery (через CDN) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <!-- Верхнее меню -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <button class="menu-toggle d-lg-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="/">
                <i class="fas fa-server me-2"></i>
                Linux Server Manager
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Настройки</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i>Выйти</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <!-- Боковое меню -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-tachometer-alt me-2"></i>Панель управления</h3>
            </div>
            
            <nav class="sidebar-menu">
                <div class="nav-item">
                    <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="/">
                        <i class="fas fa-home"></i>
                        <span>Главная</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link <?= $currentPage === 'system' ? 'active' : '' ?>" href="/system">
                        <i class="fas fa-microchip"></i>
                        <span>Система</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link <?= $currentPage === 'processes' ? 'active' : '' ?>" href="/processes">
                        <i class="fas fa-tasks"></i>
                        <span>Процессы</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link <?= $currentPage === 'services' ? 'active' : '' ?>" href="/services">
                        <i class="fas fa-cogs"></i>
                        <span>Сервисы</span>
                    </a>
                </div>
                
                <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                
                <div class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#monitoringSubmenu">
                        <i class="fas fa-chart-line"></i>
                        <span>Мониторинг</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse submenu" id="monitoringSubmenu">
                        <a class="nav-link" href="#"><i class="fas fa-chart-bar"></i>Графики</a>
                        <a class="nav-link" href="#"><i class="fas fa-exclamation-triangle"></i>Алерты</a>
                        <a class="nav-link" href="#"><i class="fas fa-history"></i>История</a>
                    </div>
                </div>
                
                <div class="nav-item">
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
                </div>
                
                <div class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#adminSubmenu">
                        <i class="fas fa-tools"></i>
                        <span>Администрирование</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse submenu" id="adminSubmenu">
                        <a class="nav-link" href="#"><i class="fas fa-users"></i>Пользователи</a>
                        <a class="nav-link" href="#"><i class="fas fa-shield-alt"></i>Безопасность</a>
                        <a class="nav-link" href="#"><i class="fas fa-database"></i>Резервное копирование</a>
                    </div>
                </div>
                
                <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                
                <div class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-question-circle"></i>
                        <span>Помощь</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Основной контент -->
        <div class="flex-grow-1">
            <div class="container-fluid">
                <div class="main-content fade-in">
                    <?= $content ?? '' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2024 Linux Server Manager. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>
