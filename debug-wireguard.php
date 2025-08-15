<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Services\WireGuardService;

echo "<h1>WireGuard Debug Information</h1>";

$service = new WireGuardService();

echo "<h2>1. Проверка установки WireGuard</h2>";
echo "wg path exists: " . (file_exists('/usr/bin/wg') ? 'YES' : 'NO') . "<br>";
echo "wg-quick path exists: " . (file_exists('/usr/bin/wg-quick') ? 'YES' : 'NO') . "<br>";
echo "isInstalled(): " . ($service->isInstalled() ? 'YES' : 'NO') . "<br>";

echo "<h2>2. Проверка команд</h2>";
echo "<h3>wg show interfaces:</h3>";
$output = shell_exec('/usr/bin/wg show interfaces 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'No output') . "</pre>";

echo "<h3>ip link show (wg interfaces):</h3>";
$output = shell_exec('ip link show | grep wg 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'No output') . "</pre>";

echo "<h2>3. Проверка конфигурационных файлов</h2>";
$configDir = '/etc/wireguard/';
if (is_dir($configDir)) {
    $files = scandir($configDir);
    echo "Files in $configDir:<br>";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "- $file<br>";
        }
    }
} else {
    echo "Directory $configDir does not exist<br>";
}

echo "<h2>4. Тест сервиса</h2>";
try {
    $interfaces = $service->getInterfaces();
    echo "getInterfaces() returned: " . count($interfaces) . " interfaces<br>";
    echo "<pre>" . print_r($interfaces, true) . "</pre>";
    
    $stats = $service->getStats();
    echo "getStats() returned:<br>";
    echo "<pre>" . print_r($stats, true) . "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Проверка прав доступа</h2>";
echo "Current user: " . shell_exec('whoami') . "<br>";
echo "Can execute wg: " . (is_executable('/usr/bin/wg') ? 'YES' : 'NO') . "<br>";
echo "Can execute wg-quick: " . (is_executable('/usr/bin/wg-quick') ? 'YES' : 'NO') . "<br>";

echo "<h2>6. Проверка systemctl</h2>";
$output = shell_exec('systemctl status wg-quick@wg0 2>&1');
echo "<pre>" . htmlspecialchars($output ?: 'No output') . "</pre>";
?>
