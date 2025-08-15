<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Services\WireGuardService;

// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>WireGuard Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>WireGuard Test Page</h1>";

try {
    $service = new WireGuardService();
    
    echo "<h2>1. Проверка установки</h2>";
    $isInstalled = $service->isInstalled();
    echo "<p class='" . ($isInstalled ? 'success' : 'error') . "'>";
    echo "WireGuard установлен: " . ($isInstalled ? 'ДА' : 'НЕТ');
    echo "</p>";
    
    if (!$isInstalled) {
        echo "<p class='warning'>Для установки WireGuard выполните:</p>";
        echo "<pre>sudo apt update && sudo apt install wireguard</pre>";
        exit;
    }
    
    echo "<h2>2. Проверка интерфейсов</h2>";
    $interfaces = $service->getInterfaces();
    echo "<p>Найдено интерфейсов: " . count($interfaces) . "</p>";
    
    if (empty($interfaces)) {
        echo "<p class='warning'>Интерфейсы WireGuard не найдены</p>";
        echo "<p>Возможные причины:</p>";
        echo "<ul>";
        echo "<li>WireGuard не настроен</li>";
        echo "<li>Нет активных интерфейсов</li>";
        echo "<li>Проблемы с правами доступа</li>";
        echo "</ul>";
    } else {
        echo "<h3>Детали интерфейсов:</h3>";
        foreach ($interfaces as $interface) {
            echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 10px;'>";
            echo "<h4>Интерфейс: " . htmlspecialchars($interface['name']) . "</h4>";
            echo "<p><strong>Статус:</strong> " . htmlspecialchars($interface['status']) . "</p>";
            echo "<p><strong>Адрес:</strong> " . htmlspecialchars($interface['address']) . "</p>";
            echo "<p><strong>Порт:</strong> " . htmlspecialchars($interface['port']) . "</p>";
            echo "<p><strong>Пиров:</strong> " . count($interface['peers']) . "</p>";
            
            if (!empty($interface['peers'])) {
                echo "<h5>Пиры:</h5>";
                foreach ($interface['peers'] as $peer) {
                    echo "<div style='margin-left: 20px;'>";
                    echo "<p><strong>Ключ:</strong> " . substr($peer['public_key'], 0, 20) . "...</p>";
                    echo "<p><strong>Статус:</strong> " . $peer['status'] . "</p>";
                    echo "<p><strong>IP:</strong> " . implode(', ', $peer['allowed_ips']) . "</p>";
                    echo "</div>";
                }
            }
            echo "</div>";
        }
    }
    
    echo "<h2>3. Статистика</h2>";
    $stats = $service->getStats();
    echo "<pre>" . print_r($stats, true) . "</pre>";
    
    echo "<h2>4. Тест команд</h2>";
    echo "<h3>wg show interfaces:</h3>";
    $output = shell_exec('/usr/bin/wg show interfaces 2>&1');
    echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";
    
    echo "<h3>ip link show | grep wg:</h3>";
    $output = shell_exec('ip link show | grep wg 2>&1');
    echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";
    
    echo "<h3>ls /etc/wireguard/:</h3>";
    $output = shell_exec('ls -la /etc/wireguard/ 2>&1');
    echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";
    
} catch (Exception $e) {
    echo "<p class='error'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
