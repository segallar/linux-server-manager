<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Тест главной страницы</h1>";

try {
    // Загружаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoload загружен<br>";

    // Загружаем переменные окружения
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "✅ .env загружен<br>";

    // Создаем приложение
    $app = new App\Core\Application(__DIR__ . '/..');
    global $app;
    echo "✅ Приложение создано<br>";

    // Создаем контроллер
    $controller = new App\Controllers\DashboardController();
    echo "✅ DashboardController создан<br>";

    // Вызываем метод index
    echo "<h2>Вызов метода index()</h2>";
    $result = $controller->index();
    echo "✅ Метод index() выполнен<br>";
    echo "Тип результата: " . gettype($result) . "<br>";
    echo "Длина результата: " . strlen($result) . " символов<br>";

    if (strlen($result) > 0) {
        echo "<h3>Первые 500 символов результата:</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
        echo htmlspecialchars(substr($result, 0, 500));
        echo "</div>";
        
        echo "<h3>Последние 200 символов результата:</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
        echo htmlspecialchars(substr($result, -200));
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Результат пустой!</p>";
    }

    // Тестируем шаблон dashboard напрямую
    echo "<h2>Тест шаблона dashboard.php</h2>";
    $templatePath = __DIR__ . '/../templates/dashboard.php';
    if (file_exists($templatePath)) {
        echo "✅ Шаблон dashboard.php найден<br>";
        
        ob_start();
        $title = 'Тест';
        $currentPage = 'dashboard';
        $stats = [
            'cpu' => ['usage' => 25, 'model' => 'Test CPU'],
            'memory' => ['usage_percent' => 50],
            'disk' => ['usage_percent' => 75],
            'system' => ['uptime' => '2 дня'],
            'network' => ['status' => 'Online', 'active_count' => 2, 'total_count' => 2]
        ];
        $processes = [];
        $services = [];
        include $templatePath;
        $templateOutput = ob_get_clean();
        
        echo "✅ Шаблон dashboard.php выполнен<br>";
        echo "Длина вывода шаблона: " . strlen($templateOutput) . " символов<br>";
        
        if (strlen($templateOutput) > 0) {
            echo "<h3>Первые 300 символов вывода шаблона:</h3>";
            echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
            echo htmlspecialchars(substr($templateOutput, 0, 300));
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Шаблон не выводит контент!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Шаблон dashboard.php не найден</p>";
    }

    // Тестируем layout main напрямую
    echo "<h2>Тест layout main.php</h2>";
    $layoutPath = __DIR__ . '/../templates/layouts/main.php';
    if (file_exists($layoutPath)) {
        echo "✅ Layout main.php найден<br>";
        
        ob_start();
        $content = "<h1>Тестовый контент</h1><p>Это тестовый контент для проверки layout.</p>";
        include $layoutPath;
        $layoutOutput = ob_get_clean();
        
        echo "✅ Layout main.php выполнен<br>";
        echo "Длина вывода layout: " . strlen($layoutOutput) . " символов<br>";
        
        if (strlen($layoutOutput) > 0) {
            echo "<h3>Первые 300 символов вывода layout:</h3>";
            echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
            echo htmlspecialchars(substr($layoutOutput, 0, 300));
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Layout не выводит контент!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Layout main.php не найден</p>";
    }

} catch (Throwable $e) {
    echo "<h2>❌ Ошибка при тестировании</h2>";
    echo "<p><strong>Ошибка:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Файл:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<h3>Стек вызовов:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><em>Тест завершен в " . date('Y-m-d H:i:s') . "</em></p>";
?>
