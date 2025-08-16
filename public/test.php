<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Тест контроллеров и шаблонов</h1>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';

    use App\Core\Application;
    use App\Controllers\DashboardController;

    // Создаем приложение
    $app = new Application(__DIR__ . '/..');
    global $app;

    echo "<h2>1. Тест создания DashboardController</h2>";
    $controller = new DashboardController();
    echo "✅ DashboardController создан успешно<br>";

    echo "<h2>2. Тест метода index()</h2>";
    $result = $controller->index();
    echo "✅ Метод index() выполнен<br>";
    echo "Тип результата: " . gettype($result) . "<br>";
    echo "Длина результата: " . strlen($result) . " символов<br>";

    if (strlen($result) > 0) {
        echo "<h3>Первые 500 символов результата:</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
        echo htmlspecialchars(substr($result, 0, 500));
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Результат пустой!</p>";
    }

    echo "<h2>3. Тест шаблона dashboard.php</h2>";
    $templatePath = __DIR__ . '/../templates/dashboard.php';
    if (file_exists($templatePath)) {
        echo "✅ Шаблон dashboard.php найден<br>";
        
        // Тестируем включение шаблона
        ob_start();
        $title = 'Тест';
        $currentPage = 'dashboard';
        $stats = [
            'cpu_usage' => 25,
            'memory_usage' => 50,
            'disk_usage' => 75,
            'uptime' => '2 дня'
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

    echo "<h2>4. Тест шаблона main.php</h2>";
    $layoutPath = __DIR__ . '/../templates/layouts/main.php';
    if (file_exists($layoutPath)) {
        echo "✅ Шаблон main.php найден<br>";
        
        // Тестируем включение layout
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
