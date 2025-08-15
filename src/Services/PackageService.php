<?php

namespace App\Services;

class PackageService
{
    private const CACHE_DURATION = 300; // 5 минут
    private const CACHE_FILE = '/tmp/package_cache.json';

    /**
     * Получить список доступных обновлений
     */
    public function getUpgradablePackages(): array
    {
        // Проверяем кэш
        $cached = $this->getCachedData('upgradable');
        if ($cached !== null) {
            return $cached;
        }

        // Проверяем, что команда apt доступна
        if (!file_exists('/usr/bin/apt')) {
            return [];
        }
        
        // Добавляем таймаут для избежания зависания
        $output = shell_exec('timeout 15 apt list --upgradable 2>/dev/null');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $packages = [];

        // Пропускаем заголовок
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $package = $this->parsePackageLine($line);
            if ($package) {
                $packages[] = $package;
            }
        }

        // Кэшируем результат
        $this->setCachedData('upgradable', $packages);
        return $packages;
    }

    /**
     * Получить статистику пакетов
     */
    public function getPackageStats(): array
    {
        // Проверяем кэш
        $cached = $this->getCachedData('stats');
        if ($cached !== null) {
            return $cached;
        }

        $upgradable = $this->getUpgradablePackages();
        $installed = $this->getInstalledPackagesCount();
        $security = $this->getSecurityUpdatesCount();

        $stats = [
            'total_installed' => $installed,
            'upgradable' => count($upgradable),
            'security_updates' => $security,
            'last_update' => $this->getLastUpdateTime()
        ];

        // Кэшируем результат
        $this->setCachedData('stats', $stats);
        return $stats;
    }

    /**
     * Получить количество установленных пакетов
     */
    private function getInstalledPackagesCount(): int
    {
        // Проверяем, что команда dpkg доступна
        if (!file_exists('/usr/bin/dpkg')) {
            return 0;
        }
        
        // Добавляем таймаут для избежания зависания
        $output = shell_exec('timeout 10 dpkg -l | grep "^ii" | wc -l 2>/dev/null');
        return (int)trim($output ?: '0');
    }

    /**
     * Получить количество обновлений безопасности
     */
    private function getSecurityUpdatesCount(): int
    {
        // Проверяем, что команда apt доступна
        if (!file_exists('/usr/bin/apt')) {
            return 0;
        }
        
        // Добавляем таймаут для избежания зависания
        $output = shell_exec('timeout 15 apt list --upgradable 2>/dev/null | grep -i security | wc -l 2>/dev/null');
        return (int)trim($output ?: '0');
    }

    /**
     * Получить время последнего обновления
     */
    private function getLastUpdateTime(): string
    {
        $logFile = '/var/log/apt/history.log';
        if (!file_exists($logFile)) {
            return 'Неизвестно';
        }

        $output = shell_exec("tail -1 $logFile 2>/dev/null");
        if (!$output) {
            return 'Неизвестно';
        }

        // Парсим время из лога
        if (preg_match('/Start-Date: (.+)/', $output, $matches)) {
            $timestamp = strtotime($matches[1]);
            if ($timestamp) {
                $now = time();
                $diff = $now - $timestamp;

                if ($diff < 3600) {
                    $minutes = floor($diff / 60);
                    return $minutes . ' мин назад';
                } elseif ($diff < 86400) {
                    $hours = floor($diff / 3600);
                    return $hours . ' ч назад';
                } else {
                    $days = floor($diff / 86400);
                    return $days . ' дн назад';
                }
            }
        }

        return 'Неизвестно';
    }

    /**
     * Парсить строку пакета из apt list
     */
    private function parsePackageLine(string $line): ?array
    {
        // Формат: package/arch [upgradable from: version] (version)
        if (preg_match('/^([^\/]+)\/([^\s]+)\s+\[upgradable from: ([^\]]+)\]\s+\(([^)]+)\)/', $line, $matches)) {
            return [
                'name' => $matches[1],
                'architecture' => $matches[2],
                'current_version' => $matches[3],
                'new_version' => $matches[4],
                'is_security' => stripos($line, 'security') !== false
            ];
        }

        return null;
    }

    /**
     * Обновить список пакетов
     */
    public function updatePackageList(): array
    {
        // Проверяем права sudo
        if (!file_exists('/usr/bin/sudo')) {
            return ['success' => false, 'message' => 'sudo не доступен'];
        }
        
        // Очищаем кэш перед обновлением
        $this->clearCache();
        
        $output = shell_exec('timeout 60 sudo apt update 2>&1');
        $success = strpos($output, 'Reading package lists') !== false;
        
        return [
            'success' => $success,
            'message' => $success ? 'Список пакетов обновлен' : 'Ошибка обновления: ' . $output
        ];
    }

    /**
     * Обновить все пакеты
     */
    public function upgradeAllPackages(): array
    {
        // Проверяем права sudo
        if (!file_exists('/usr/bin/sudo')) {
            return ['success' => false, 'message' => 'sudo не доступен'];
        }
        
        // Очищаем кэш перед обновлением
        $this->clearCache();
        
        $output = shell_exec('timeout 600 sudo apt upgrade -y 2>&1'); // 10 минут для обновления
        $success = strpos($output, 'upgraded') !== false || strpos($output, '0 upgraded') !== false;
        
        return [
            'success' => $success,
            'message' => $success ? 'Все пакеты обновлены' : 'Ошибка обновления: ' . $output
        ];
    }

    /**
     * Обновить конкретный пакет
     */
    public function upgradePackage(string $packageName): array
    {
        // Проверяем права sudo
        if (!file_exists('/usr/bin/sudo')) {
            return ['success' => false, 'message' => 'sudo не доступен'];
        }
        
        // Очищаем кэш перед обновлением
        $this->clearCache();
        
        $output = shell_exec("timeout 120 sudo apt install $packageName -y 2>&1");
        $success = strpos($output, 'upgraded') !== false || strpos($output, 'already the newest version') !== false;
        
        return [
            'success' => $success,
            'message' => $success ? "Пакет $packageName обновлен" : 'Ошибка обновления: ' . $output
        ];
    }

    /**
     * Очистить кэш пакетов
     */
    public function cleanPackageCache(): array
    {
        // Проверяем права sudo
        if (!file_exists('/usr/bin/sudo')) {
            return ['success' => false, 'message' => 'sudo не доступен'];
        }
        
        // Очищаем кэш приложения
        $this->clearCache();
        
        $output = shell_exec('timeout 60 sudo apt clean 2>&1');
        $success = strpos($output, 'Cleaning') !== false || empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? 'Кэш пакетов очищен' : 'Ошибка очистки: ' . $output
        ];
    }

    /**
     * Удалить неиспользуемые пакеты
     */
    public function autoremovePackages(): array
    {
        // Проверяем права sudo
        if (!file_exists('/usr/bin/sudo')) {
            return ['success' => false, 'message' => 'sudo не доступен'];
        }
        
        // Очищаем кэш приложения
        $this->clearCache();
        
        $output = shell_exec('timeout 120 sudo apt autoremove -y 2>&1');
        $success = strpos($output, 'removed') !== false || strpos($output, '0 upgraded') !== false;
        
        return [
            'success' => $success,
            'message' => $success ? 'Неиспользуемые пакеты удалены' : 'Ошибка удаления: ' . $output
        ];
    }

    /**
     * Получить информацию о пакете
     */
    public function getPackageInfo(string $packageName): ?array
    {
        if (!file_exists('/usr/bin/apt')) {
            return null;
        }
        
        $output = shell_exec("timeout 10 apt show $packageName 2>/dev/null");
        if (!$output) {
            return null;
        }

        $info = [];
        $lines = explode("\n", $output);
        
        foreach ($lines as $line) {
            if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2]);
                $info[$key] = $value;
            }
        }

        return $info;
    }

    /**
     * Получить неиспользуемые пакеты
     */
    public function getUnusedPackages(): array
    {
        // Проверяем кэш
        $cached = $this->getCachedData('unused');
        if ($cached !== null) {
            return $cached;
        }

        if (!file_exists('/usr/bin/apt')) {
            return [];
        }
        
        $output = shell_exec('timeout 30 apt-mark showmanual | xargs apt-mark showauto 2>/dev/null');
        if (!$output) {
            return [];
        }

        $packages = [];
        $lines = explode("\n", trim($output));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $packages[] = $line;
            }
        }

        // Кэшируем результат
        $this->setCachedData('unused', $packages);
        return $packages;
    }

    /**
     * Кэширование данных
     */
    private function getCachedData(string $key): ?array
    {
        if (!file_exists(self::CACHE_FILE)) {
            return null;
        }

        $cache = json_decode(file_get_contents(self::CACHE_FILE), true);
        if (!$cache || !isset($cache[$key]) || !isset($cache[$key]['timestamp'])) {
            return null;
        }

        if (time() - $cache[$key]['timestamp'] > self::CACHE_DURATION) {
            return null;
        }

        return $cache[$key]['data'];
    }

    private function setCachedData(string $key, array $data): void
    {
        $cache = [];
        if (file_exists(self::CACHE_FILE)) {
            $cache = json_decode(file_get_contents(self::CACHE_FILE), true) ?: [];
        }

        $cache[$key] = [
            'data' => $data,
            'timestamp' => time()
        ];

        file_put_contents(self::CACHE_FILE, json_encode($cache));
    }

    private function clearCache(): void
    {
        if (file_exists(self::CACHE_FILE)) {
            unlink(self::CACHE_FILE);
        }
    }
}
