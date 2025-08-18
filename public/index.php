<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Загружаем helpers для работы с Git версиями
require_once __DIR__ . '/../src/helpers.php';

use App\Core\Application;
use App\Controllers\DashboardController;
use App\Controllers\SystemController;
use App\Controllers\ProcessController;
use App\Controllers\ServiceController;
use App\Controllers\Network\NetworkViewController;
use App\Controllers\Network\SSHTunnelApiController;
use App\Controllers\Network\PortForwardingApiController;
use App\Controllers\Network\WireGuardController;
use App\Controllers\Network\CloudflareController;
use App\Controllers\PackageController;
use App\Controllers\FirewallController;

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
    $app->router->get('/dashboard', [DashboardController::class, 'index']);
    $app->router->get('/system', [SystemController::class, 'index']);
    $app->router->get('/processes', [ProcessController::class, 'index']);
    $app->router->get('/services', [ServiceController::class, 'index']);
    $app->router->get('/packages', [PackageController::class, 'index']);

    // Маршруты для сети
    $app->router->get('/network', [NetworkViewController::class, 'index']);
    $app->router->get('/network/ssh', [NetworkViewController::class, 'ssh']);
    $app->router->get('/network/port-forwarding', [NetworkViewController::class, 'portForwarding']);
    $app->router->get('/network/wireguard', [NetworkViewController::class, 'wireguard']);
    $app->router->get('/network/cloudflare', [NetworkViewController::class, 'cloudflare']);
    $app->router->get('/network/routing', [NetworkViewController::class, 'routing']);
    $app->router->get('/firewall', [FirewallController::class, 'index']);

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

    // API маршруты для системной информации
    $app->router->get('/api/system/info', [SystemController::class, 'getSystemInfo']);
    $app->router->get('/api/system/stats', [SystemController::class, 'getSystemStats']);
    $app->router->get('/api/system/processes', [ProcessController::class, 'getProcesses']);
    $app->router->post('/api/system/processes/{id}/kill', [ProcessController::class, 'killProcess']);

    // API маршруты для WireGuard
    $app->router->get('/api/wireguard/interfaces', [WireGuardController::class, 'getWireGuardInterfaces']);
    $app->router->get('/api/wireguard/interface/{name}', [WireGuardController::class, 'getWireGuardInterface']);
    $app->router->post('/api/wireguard/interface/{name}/up', [WireGuardController::class, 'upWireGuardInterface']);
    $app->router->post('/api/wireguard/interface/{name}/down', [WireGuardController::class, 'downWireGuardInterface']);
    $app->router->post('/api/wireguard/interface/{name}/restart', [WireGuardController::class, 'restartWireGuardInterface']);
    $app->router->get('/api/wireguard/interface/{name}/config', [WireGuardController::class, 'getWireGuardConfig']);
    $app->router->post('/api/wireguard/interface/{name}/config', [WireGuardController::class, 'updateWireGuardConfig']);

    // API маршруты для SSH туннелей
    $app->router->get('/api/ssh/tunnels', [SSHTunnelApiController::class, 'getSSHTunnels']);
    $app->router->post('/api/ssh/tunnel/create', [SSHTunnelApiController::class, 'createSSHTunnel']);
    $app->router->post('/api/ssh/tunnel/{id}/start', [SSHTunnelApiController::class, 'startSSHTunnel']);
    $app->router->post('/api/ssh/tunnel/{id}/stop', [SSHTunnelApiController::class, 'stopSSHTunnel']);
    $app->router->delete('/api/ssh/tunnel/{id}', [SSHTunnelApiController::class, 'deleteSSHTunnel']);

    // API маршруты для Cloudflare туннелей
    $app->router->get('/api/cloudflare/tunnels', [CloudflareController::class, 'getCloudflareTunnels']);
    $app->router->post('/api/cloudflare/tunnel/create', [CloudflareController::class, 'createCloudflareTunnel']);
    $app->router->post('/api/cloudflare/tunnel/{id}/start', [CloudflareController::class, 'startCloudflareTunnel']);
    $app->router->post('/api/cloudflare/tunnel/{id}/stop', [CloudflareController::class, 'stopCloudflareTunnel']);
    $app->router->delete('/api/cloudflare/tunnel/{id}', [CloudflareController::class, 'deleteCloudflareTunnel']);

    // API маршруты для проброса портов
    $app->router->get('/api/port-forwarding/rules', [PortForwardingApiController::class, 'getPortForwardingRules']);
    $app->router->post('/api/port-forwarding/rule/add', [PortForwardingApiController::class, 'addPortForwardingRule']);
    $app->router->delete('/api/port-forwarding/rule/{id}', [PortForwardingApiController::class, 'deletePortForwardingRule']);

    // API маршруты для файрвола
    $app->router->get('/api/firewall/info', [FirewallController::class, 'getFirewallInfo']);
    $app->router->get('/api/firewall/info/detailed', [FirewallController::class, 'getDetailedFirewallInfo']);
    $app->router->get('/api/firewall/stats', [FirewallController::class, 'getFirewallStats']);
    $app->router->get('/api/firewall/rules', [FirewallController::class, 'getFirewallRules']);
    $app->router->post('/api/firewall/rule/add', [FirewallController::class, 'addFirewallRule']);
    $app->router->delete('/api/firewall/rule/{id}', [FirewallController::class, 'deleteFirewallRule']);
    $app->router->post('/api/firewall/enable', [FirewallController::class, 'enableFirewall']);
    $app->router->post('/api/firewall/disable', [FirewallController::class, 'disableFirewall']);
    $app->router->get('/api/firewall/logs', [FirewallController::class, 'getFirewallLogs']);

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
