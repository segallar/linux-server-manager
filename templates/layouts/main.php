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
    <!-- Кнопка мобильного меню -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Оверлей для мобильного меню -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Боковое меню -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-server"></i> Server Manager</h3>
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="/">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'system' ? 'active' : '' ?>" href="/system">
                        <i class="fas fa-server"></i>
                        <span>Системная информация</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'processes' ? 'active' : '' ?>" href="/processes">
                        <i class="fas fa-tasks"></i>
                        <span>Управление процессами</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'services' ? 'active' : '' ?>" href="/services">
                        <i class="fas fa-cogs"></i>
                        <span>Управление сервисами</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'packages' ? 'active' : '' ?>" href="/packages">
                        <i class="fas fa-box"></i>
                        <span>Управление пакетами</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage ?? '') === 'firewall' ? 'active' : '' ?>" href="/firewall">
                        <i class="fas fa-shield-alt"></i>
                        <span>Файрвол</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#networkSubmenu">
                        <i class="fas fa-network-wired"></i>
                        <span>Сеть</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse submenu" id="networkSubmenu">
                        <a class="nav-link" href="/network/ssh">
                            <i class="fas fa-terminal"></i>
                            <span>SSH туннели</span>
                        </a>
                        <a class="nav-link" href="/network/port-forwarding">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Проброс портов</span>
                        </a>
                        <a class="nav-link" href="/network/wireguard">
                            <i class="fas fa-lock"></i>
                            <span>WireGuard</span>
                        </a>
                        <a class="nav-link" href="/network/cloudflare">
                            <i class="fas fa-cloud"></i>
                            <span>Cloudflare</span>
                        </a>
                        <a class="nav-link" href="/network/routing">
                            <i class="fas fa-route"></i>
                            <span>Маршрутизация</span>
                        </a>
                    </div>
                </li>
            </ul>
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
    
    <!-- Скрипт для мобильного меню -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            // Открытие/закрытие мобильного меню
            mobileMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isOpen = sidebar.classList.contains('show');
                
                if (isOpen) {
                    // Закрываем меню
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    mobileMenuToggle.classList.remove('active');
                    setTimeout(() => {
                        sidebarOverlay.style.display = 'none';
                    }, 300);
                } else {
                    // Открываем меню
                    sidebarOverlay.style.display = 'block';
                    setTimeout(() => {
                        sidebar.classList.add('show');
                        sidebarOverlay.classList.add('show');
                        mobileMenuToggle.classList.add('active');
                    }, 10);
                }
            });
            
            // Закрытие меню при клике на оверлей
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                mobileMenuToggle.classList.remove('active');
                setTimeout(() => {
                    sidebarOverlay.style.display = 'none';
                }, 300);
            });
            
            // Закрытие меню при клике на ссылку (на мобильных)
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                        mobileMenuToggle.classList.remove('active');
                        setTimeout(() => {
                            sidebarOverlay.style.display = 'none';
                        }, 300);
                    }
                });
            });
            
            // Закрытие меню при изменении размера окна
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    mobileMenuToggle.classList.remove('active');
                    sidebarOverlay.style.display = 'none';
                }
            });
            
            // Предотвращение скролла body когда меню открыто
            function toggleBodyScroll(disable) {
                if (disable) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
            
            // Обновляем обработчик для управления скроллом
            mobileMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isOpen = sidebar.classList.contains('show');
                toggleBodyScroll(!isOpen);
            });
            
            sidebarOverlay.addEventListener('click', function() {
                toggleBodyScroll(false);
            });
        });
    </script>
</body>
</html>
