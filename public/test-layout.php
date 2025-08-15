<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем автозагрузчик
require_once __DIR__ . '/../vendor/autoload.php';

// Симулируем переменные для layout
$title = 'Test Layout';
$currentPage = 'tunnels';
$content = '<h1>Test Content</h1><p>This is a test of the layout system.</p>';

// Включаем layout
include_once __DIR__ . '/../templates/layouts/main.php';
?>
