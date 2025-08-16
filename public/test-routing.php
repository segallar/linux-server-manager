<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Тест маршрутизации</h1>";

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

    // Симулируем HTTP запрос к главной странице
    echo "<h2>Симуляция HTTP запроса к главной странице</h2>";
    
    // Устанавливаем переменные $_SERVER для симуляции запроса
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['PATH_INFO'] = '/';
    $_SERVER['QUERY_STRING'] = '';
    
    echo "✅ Переменные запроса установлены<br>";
    echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "<br>";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "PATH_INFO: " . $_SERVER['PATH_INFO'] . "<br>";

    // Создаем Request и Response
    $request = new App\Core\Request();
    $response = new App\Core\Response();
    echo "✅ Request и Response созданы<br>";

    // Регистрируем маршрут
    $app->router->get('/', [App\Controllers\DashboardController::class, 'index']);
    echo "✅ Маршрут зарегистрирован<br>";

    // Получаем путь из запроса
    $path = $request->getPath();
    $method = strtolower($request->method());
    echo "✅ Путь из запроса: '$path'<br>";
    echo "✅ Метод запроса: '$method'<br>";

    // Проверяем, есть ли маршрут
    $callback = $app->router->routes[$method][$path] ?? false;
    if ($callback) {
        echo "✅ Маршрут найден<br>";
    } else {
        echo "❌ Маршрут НЕ найден<br>";
        echo "Доступные маршруты GET:<br>";
        foreach ($app->router->routes['get'] ?? [] as $route => $handler) {
            echo "- $route<br>";
        }
    }

    // Тестируем resolve()
    echo "<h2>Тест resolve()</h2>";
    ob_start();
    $result = $app->router->resolve();
    $output = ob_get_clean();
    
    if ($output) {
        echo "⚠️ Есть вывод в буфер: " . strlen($output) . " символов<br>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
        echo htmlspecialchars(substr($output, 0, 300));
        echo "</div>";
    } else {
        echo "✅ Буфер пустой<br>";
    }
    
    echo "Тип результата resolve(): " . gettype($result) . "<br>";
    echo "Длина результата resolve(): " . strlen($result) . " символов<br>";
    
    if (strlen($result) > 0) {
        echo "<h3>Первые 500 символов результата resolve():</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
        echo htmlspecialchars(substr($result, 0, 500));
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Результат resolve() пустой!</p>";
    }

    // Тестируем run()
    echo "<h2>Тест run()</h2>";
    ob_start();
    $app->run();
    $runOutput = ob_get_clean();
    
    echo "Длина вывода run(): " . strlen($runOutput) . " символов<br>";
    
    if (strlen($runOutput) > 0) {
        echo "<h3>Первые 500 символов вывода run():</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
        echo htmlspecialchars(substr($runOutput, 0, 500));
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Вывод run() пустой!</p>";
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
