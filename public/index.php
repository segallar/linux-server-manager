<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Controllers\DashboardController;
use App\Controllers\SystemController;
use App\Controllers\ProcessController;
use App\Controllers\ServiceController;
use App\Controllers\NetworkController;

// Загружаем переменные окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Создаем экземпляр приложения
$app = new Application(__DIR__ . '/..');

// Регистрируем маршруты
$app->router->get('/', [DashboardController::class, 'index']);
$app->router->get('/system', [SystemController::class, 'index']);
$app->router->get('/processes', [ProcessController::class, 'index']);
$app->router->get('/services', [ServiceController::class, 'index']);

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

// Запускаем приложение
$app->run();
