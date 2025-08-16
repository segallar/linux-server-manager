<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Загружаем helpers для работы с Git версиями
require_once __DIR__ . '/../src/helpers.php';

use App\Core\Application;
use App\Controllers\DashboardController;
use App\Controllers\SystemController;
use App\Controllers\ProcessController;
use App\Controllers\ServiceController;
use App\Controllers\NetworkController;
use App\Controllers\PackageController;

try {
    // Загружаем переменные окружения
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    // Создаем экземпляр приложения
    $app = new Application(__DIR__ . '/..');

    // Делаем приложение доступным глобально для контроллеров
    global $app;

    // Регистрируем маршруты
    $app->router->get('/', [DashboardController::class, 'index']);
    $app->router->get('/system', [SystemController::class, 'index']);
    $app->router->get('/processes', [ProcessController::class, 'index']);
    $app->router->get('/services', [ServiceController::class, 'index']);
    $app->router->get('/packages', [PackageController::class, 'index']);

    // Маршруты для сети
    $app->router->get('/network/ssh', [NetworkController::class, 'ssh']);
    $app->router->get('/network/port-forwarding', [NetworkController::class, 'portForwarding']);
    $app->router->get('/network/wireguard', [NetworkController::class, 'wireguard']);
    $app->router->get('/network/cloudflare', [NetworkController::class, 'cloudflare']);
    $app->router->get('/network/routing', [NetworkController::class, 'routing']);

    // API маршруты для управления сервисами
    $app->router->post('/api/services/start', [ServiceController::class, 'start']);
    $app->router->post('/api/services/stop', [ServiceController::class, 'stop']);
    $app->router->post('/api/services/restart', [ServiceController::class, 'restart']);
    $app->router->post('/api/services/enable', [ServiceController::class, 'enable']);
    $app->router->post('/api/services/disable', [ServiceController::class, 'disable']);

    // API маршруты для управления пакетами
    $app->router->post('/api/packages/update', [PackageController::class, 'update']);
    $app->router->post('/api/packages/upgrade-all', [PackageController::class, 'upgradeAll']);
    $app->router->post('/api/packages/upgrade', [PackageController::class, 'upgradePackage']);
    $app->router->post('/api/packages/clean-cache', [PackageController::class, 'cleanCache']);
    $app->router->post('/api/packages/autoremove', [PackageController::class, 'autoremove']);
    $app->router->get('/api/packages/info', [PackageController::class, 'getPackageInfo']);

    // Запускаем приложение
    $app->run();

} catch (Throwable $e) {
    // Логируем ошибку
    error_log("Critical error in index.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Показываем простую ошибку пользователю
    http_response_code(500);
    echo "<h1>Ошибка сервера</h1>";
    echo "<p>Произошла внутренняя ошибка сервера. Попробуйте обновить страницу.</p>";
}
?>
