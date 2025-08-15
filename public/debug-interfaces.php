<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\SystemService;

echo "<h1>🔧 Отладка сетевых интерфейсов</h1>";

try {
    echo "<h2>1. Проверка команды ip addr show</h2>";
    $output = shell_exec('ip addr show 2>&1');
    echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";
    
    echo "<h2>2. Проверка WireGuard интерфейса wg0</h2>";
    $wgOutput = shell_exec('ip link show wg0 2>&1');
    echo "<pre>" . htmlspecialchars($wgOutput ?: 'Нет вывода') . "</pre>";
    
    echo "<h2>3. Проверка IP адресов wg0</h2>";
    $wgIpOutput = shell_exec('ip addr show wg0 2>&1');
    echo "<pre>" . htmlspecialchars($wgIpOutput ?: 'Нет вывода') . "</pre>";
    
    echo "<h2>4. Тест SystemService</h2>";
    $service = new SystemService();
    $networkInfo = $service->getNetworkInfo();
    
    echo "<p><strong>Результат getNetworkInfo():</strong></p>";
    echo "<pre>" . print_r($networkInfo, true) . "</pre>";
    
    echo "<h2>5. Проверка интерфейса wg0 в результатах</h2>";
    if (isset($networkInfo['interfaces']['wg0'])) {
        echo "<p><strong>wg0 найден:</strong></p>";
        echo "<pre>" . print_r($networkInfo['interfaces']['wg0'], true) . "</pre>";
    } else {
        echo "<p><strong>wg0 НЕ найден в результатах!</strong></p>";
        echo "<p>Доступные интерфейсы:</p>";
        echo "<pre>" . print_r(array_keys($networkInfo['interfaces']), true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
