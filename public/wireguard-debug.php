<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>WireGuard Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 WireGuard Debug Information</h1>";

// Проверка 1: Существование файлов WireGuard
echo "<div class='section'>
    <h2>1. Проверка установки WireGuard</h2>";

$wgPath = '/usr/bin/wg';
$wgQuickPath = '/usr/bin/wg-quick';

echo "<p><strong>wg:</strong> <span class='" . (file_exists($wgPath) ? 'success' : 'error') . "'>";
echo file_exists($wgPath) ? 'НАЙДЕН' : 'НЕ НАЙДЕН';
echo "</span></p>";

echo "<p><strong>wg-quick:</strong> <span class='" . (file_exists($wgQuickPath) ? 'success' : 'error') . "'>";
echo file_exists($wgQuickPath) ? 'НАЙДЕН' : 'НЕ НАЙДЕН';
echo "</span></p>";

if (!file_exists($wgPath) || !file_exists($wgQuickPath)) {
    echo "<p class='warning'>WireGuard не установлен. Установите командой:</p>";
    echo "<pre>sudo apt update && sudo apt install wireguard</pre>";
}

// Проверка 2: Выполнение команд
echo "</div><div class='section'>
    <h2>2. Проверка команд WireGuard</h2>";

echo "<h3>wg show interfaces:</h3>";
$output = shell_exec('/usr/bin/wg show interfaces 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";

echo "<h3>ip link show | grep wg:</h3>";
$output = shell_exec('ip link show | grep wg 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";

echo "<h3>wg show:</h3>";
$output = shell_exec('/usr/bin/wg show 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";

echo "<h3>wg show wg0:</h3>";
$output = shell_exec('/usr/bin/wg show wg0 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";

// Проверка 3: Конфигурационные файлы
echo "</div><div class='section'>
    <h2>3. Конфигурационные файлы</h2>";

$configDir = '/etc/wireguard/';
if (is_dir($configDir)) {
    $files = @scandir($configDir);
    echo "<p><strong>Папка:</strong> $configDir</p>";
    echo "<p><strong>Файлы:</strong></p>";
    
    if ($files === false) {
        echo "<p class='warning'>Нет доступа к папке (требуются права root)</p>";
        echo "<p>Проверим через команду:</p>";
        $output = shell_exec('sudo ls -la /etc/wireguard/ 2>&1');
        echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";
    } elseif (count($files) <= 2) {
        echo "<p class='warning'>Папка пустая</p>";
    } else {
        echo "<ul>";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = $configDir . $file;
                $size = @filesize($fullPath);
                echo "<li>$file ($size байт)</li>";
            }
        }
        echo "</ul>";
    }
} else {
    echo "<p class='error'>Папка $configDir не существует</p>";
}

// Проверка 4: Права доступа
echo "</div><div class='section'>
    <h2>4. Права доступа</h2>";

echo "<p><strong>Текущий пользователь:</strong> " . shell_exec('whoami') . "</p>";
echo "<p><strong>wg исполняемый:</strong> " . (is_executable($wgPath) ? 'ДА' : 'НЕТ') . "</p>";
echo "<p><strong>wg-quick исполняемый:</strong> " . (is_executable($wgQuickPath) ? 'ДА' : 'НЕТ') . "</p>";

// Проверка 5: Статус сервисов
echo "</div><div class='section'>
    <h2>5. Статус сервисов</h2>";

echo "<h3>systemctl status wg-quick@wg0:</h3>";
$output = shell_exec('systemctl status wg-quick@wg0 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";

// Проверка 6: Тест нашего сервиса
echo "</div><div class='section'>
    <h2>6. Тест PHP сервиса</h2>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Проверяем, что класс существует
    if (class_exists('App\Services\WireGuardService')) {
        echo "<p class='success'>Класс WireGuardService найден</p>";
        
        $service = new App\Services\WireGuardService();
        
        echo "<p><strong>isInstalled():</strong> " . ($service->isInstalled() ? 'ДА' : 'НЕТ') . "</p>";
        
        $interfaces = $service->getInterfaces();
        echo "<p><strong>getInterfaces():</strong> " . count($interfaces) . " интерфейсов</p>";
        
        if (!empty($interfaces)) {
            echo "<h3>Детали интерфейсов:</h3>";
            foreach ($interfaces as $interface) {
                echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 10px;'>";
                echo "<h4>" . htmlspecialchars($interface['name']) . "</h4>";
                echo "<p><strong>Статус:</strong> " . htmlspecialchars($interface['status']) . "</p>";
                echo "<p><strong>Адрес:</strong> " . htmlspecialchars($interface['address']) . "</p>";
                echo "<p><strong>Порт:</strong> " . htmlspecialchars($interface['port']) . "</p>";
                echo "<p><strong>Пиров:</strong> " . count($interface['peers']) . "</p>";
                echo "</div>";
            }
        }
        
        $stats = $service->getStats();
        echo "<h3>Статистика:</h3>";
        echo "<pre>" . print_r($stats, true) . "</pre>";
        
    } else {
        echo "<p class='error'>Класс WireGuardService не найден</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Проверка 7: Логи PHP
echo "</div><div class='section'>
    <h2>7. Последние логи PHP</h2>";

$logFiles = [
    '/var/log/php8.1-fpm.log',
    '/var/log/php8.0-fpm.log',
    '/var/log/php-fpm.log'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "<h3>$logFile:</h3>";
        $output = shell_exec("tail -10 $logFile 2>&1");
        echo "<pre>" . htmlspecialchars($output ?: 'Нет вывода') . "</pre>";
        break;
    }
}

echo "</div>
    <div class='section'>
        <h2>8. Рекомендации</h2>";
        
if (!file_exists($wgPath)) {
    echo "<p class='warning'>1. Установите WireGuard:</p>";
    echo "<pre>sudo apt update && sudo apt install wireguard</pre>";
}

if (is_dir($configDir) && count(scandir($configDir)) <= 2) {
    echo "<p class='warning'>2. Создайте тестовый интерфейс:</p>";
    echo "<pre>sudo wg genkey | sudo tee /etc/wireguard/wg0.key
sudo wg pubkey < /etc/wireguard/wg0.key | sudo tee /etc/wireguard/wg0.pub
sudo tee /etc/wireguard/wg0.conf > /dev/null <<EOF
[Interface]
PrivateKey = \$(cat /etc/wireguard/wg0.key)
Address = 10.9.0.1/24
ListenPort = 51820
EOF
sudo wg-quick up wg0</pre>";
}

echo "<p>3. После установки и настройки обновите эту страницу</p>";
echo "</div>";

echo "</body></html>";
?>
