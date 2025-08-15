<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Controllers\DashboardController;
use App\Controllers\SystemController;
use App\Controllers\ProcessController;
use App\Controllers\ServiceController;
use App\Controllers\TunnelController;

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

// Маршруты для туннелей
$app->router->get('/tunnels/ssh', [TunnelController::class, 'ssh']);
$app->router->get('/tunnels/port-forwarding', [TunnelController::class, 'portForwarding']);
$app->router->get('/tunnels/wireguard', [TunnelController::class, 'wireguard']);
$app->router->get('/tunnels/cloudflare', [TunnelController::class, 'cloudflare']);

// Запускаем приложение
$app->run();
