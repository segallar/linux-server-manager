<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Controllers\DashboardController;
use App\Controllers\SystemController;
use App\Controllers\ProcessController;
use App\Controllers\ServiceController;

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

// Запускаем приложение
$app->run();
