<?php
// Отладочный файл для диагностики проблем

echo "<h1>🔍 Отладка Linux Server Manager</h1>";

// Проверяем версию PHP
echo "<h2>📋 Информация о PHP</h2>";
echo "<p><strong>Версия PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Время выполнения:</strong> " . ini_get('max_execution_time') . " сек</p>";
echo "<p><strong>Лимит памяти:</strong> " . ini_get('memory_limit') . "</p>";

// Проверяем автозагрузку
echo "<h2>📦 Проверка автозагрузки</h2>";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p style='color: green;'>✅ Composer autoload загружен</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка загрузки autoload: " . $e->getMessage() . "</p>";
}

// Проверяем переменные окружения
echo "<h2>🌍 Переменные окружения</h2>";
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "<p style='color: green;'>✅ .env файл загружен</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠️ Ошибка загрузки .env: " . $e->getMessage() . "</p>";
}

// Проверяем основные классы
echo "<h2>🏗️ Проверка классов</h2>";
$classes = [
    'App\Core\Application',
    'App\Core\Router',
    'App\Core\Controller',
    'App\Core\Request',
    'App\Core\Response',
    'App\Controllers\DashboardController'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "<p style='color: green;'>✅ Класс $class найден</p>";
    } else {
        echo "<p style='color: red;'>❌ Класс $class НЕ найден</p>";
    }
}

// Проверяем файлы шаблонов
echo "<h2>📄 Проверка шаблонов</h2>";
$templates = [
    __DIR__ . '/../templates/layouts/main.php',
    __DIR__ . '/../templates/dashboard.php',
    __DIR__ . '/../templates/_error.php'
];

foreach ($templates as $template) {
    if (file_exists($template)) {
        echo "<p style='color: green;'>✅ Шаблон $template существует</p>";
    } else {
        echo "<p style='color: red;'>❌ Шаблон $template НЕ существует</p>";
    }
}

// Проверяем права доступа
echo "<h2>🔐 Проверка прав доступа</h2>";
$paths = [
    __DIR__ . '/../templates',
    __DIR__ . '/../src',
    __DIR__ . '/../vendor'
];

foreach ($paths as $path) {
    if (is_readable($path)) {
        echo "<p style='color: green;'>✅ $path доступен для чтения</p>";
    } else {
        echo "<p style='color: red;'>❌ $path НЕ доступен для чтения</p>";
    }
}

// Тестируем создание приложения
echo "<h2>🚀 Тест создания приложения</h2>";
try {
    $app = new App\Core\Application(__DIR__ . '/..');
    echo "<p style='color: green;'>✅ Приложение создано успешно</p>";
    
    // Тестируем роутер
    echo "<p style='color: green;'>✅ Роутер инициализирован</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка создания приложения: " . $e->getMessage() . "</p>";
    echo "<p><strong>Стек вызовов:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Проверяем ошибки PHP
echo "<h2>⚠️ Ошибки PHP</h2>";
$errors = error_get_last();
if ($errors) {
    echo "<p style='color: red;'>❌ Последняя ошибка PHP:</p>";
    echo "<pre>" . print_r($errors, true) . "</pre>";
} else {
    echo "<p style='color: green;'>✅ Ошибок PHP не обнаружено</p>";
}

// Информация о сервере
echo "<h2>🖥️ Информация о сервере</h2>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно') . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Неизвестно') . "</p>";
echo "<p><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Неизвестно') . "</p>";

echo "<hr>";
echo "<p><em>Отладка завершена в " . date('Y-m-d H:i:s') . "</em></p>";
?>
