<?php

namespace App\Services\Package;

use App\Abstracts\BaseService;
use App\Interfaces\PackageServiceInterface;

class PackageService extends BaseService implements PackageServiceInterface
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
        
        // Сначала проверяем, не заблокирован ли apt
        if ($this->isAptLocked()) {
            return [];
        }
        
        // Добавляем таймаут для избежания зависания
        $output = $this->executeCommand('timeout 10 apt list --upgradable');
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

        // Используем безопасные методы
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
     * Обновить список пакетов
     */
    public function updatePackageList(): array
    {
        try {
            if (!file_exists('/usr/bin/apt')) {
                return ['success' => false, 'message' => 'APT не установлен'];
            }

            if ($this->isAptLocked()) {
                return ['success' => false, 'message' => 'APT заблокирован'];
            }

            $result = $this->safeExecute('apt update');
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка обновления списка пакетов: ' . $result['error']];
            }

            // Очищаем кэш
            $this->clearCache();

            return ['success' => true, 'message' => 'Список пакетов обновлен'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Обновить все пакеты
     */
    public function upgradeAllPackages(): array
    {
        try {
            if (!file_exists('/usr/bin/apt')) {
                return ['success' => false, 'message' => 'APT не установлен'];
            }

            if ($this->isAptLocked()) {
                return ['success' => false, 'message' => 'APT заблокирован'];
            }

            $result = $this->safeExecute('apt upgrade -y');
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка обновления пакетов: ' . $result['error']];
            }

            // Очищаем кэш
            $this->clearCache();

            return ['success' => true, 'message' => 'Все пакеты обновлены'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Обновить конкретный пакет
     */
    public function upgradePackage(string $packageName): array
    {
        try {
            if (!file_exists('/usr/bin/apt')) {
                return ['success' => false, 'message' => 'APT не установлен'];
            }

            if ($this->isAptLocked()) {
                return ['success' => false, 'message' => 'APT заблокирован'];
            }

            if (empty($packageName)) {
                return ['success' => false, 'message' => 'Имя пакета не указано'];
            }

            $result = $this->safeExecute("apt install -y $packageName");
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка обновления пакета: ' . $result['error']];
            }

            // Очищаем кэш
            $this->clearCache();

            return ['success' => true, 'message' => "Пакет '$packageName' обновлен"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Получить количество установленных пакетов
     */
    protected function getInstalledPackagesCount(): int
    {
        // Проверяем, что команда dpkg доступна
        if (!file_exists('/usr/bin/dpkg')) {
            return 0;
        }
        
        // Добавляем таймаут для избежания зависания
        $output = $this->executeCommand('timeout 5 dpkg -l | grep "^ii" | wc -l');
        return (int)trim($output ?: '0');
    }

    /**
     * Получить количество обновлений безопасности
     */
    protected function getSecurityUpdatesCount(): int
    {
        if (!file_exists('/usr/bin/apt')) {
            return 0;
        }

        $output = $this->executeCommand('apt list --upgradable 2>/dev/null | grep -i security | wc -l');
        return (int)trim($output ?: '0');
    }

    /**
     * Получить время последнего обновления
     */
    protected function getLastUpdateTime(): string
    {
        $updateFile = '/var/lib/apt/periodic/update-success-stamp';
        if (file_exists($updateFile)) {
            $timestamp = filemtime($updateFile);
            return date('Y-m-d H:i:s', $timestamp);
        }
        return 'Неизвестно';
    }

    /**
     * Проверить, заблокирован ли apt
     */
    protected function isAptLocked(): bool
    {
        $lockFiles = [
            '/var/lib/apt/lists/lock',
            '/var/lib/dpkg/lock',
            '/var/cache/apt/archives/lock'
        ];

        foreach ($lockFiles as $lockFile) {
            if (file_exists($lockFile)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Парсить строку пакета
     */
    protected function parsePackageLine(string $line): ?array
    {
        // Пример строки: "package/stable 1.0.0-1 amd64 [upgradable from: 0.9.0-1]"
        if (preg_match('/^([^\/]+)\/([^\s]+)\s+([^\s]+)\s+([^\s]+)/', $line, $matches)) {
            return [
                'name' => $matches[1],
                'repository' => $matches[2],
                'version' => $matches[3],
                'architecture' => $matches[4]
            ];
        }

        return null;
    }

    /**
     * Получить данные из кэша
     */
    protected function getCachedData(string $key): ?array
    {
        if (!file_exists(self::CACHE_FILE)) {
            return null;
        }

        $cache = json_decode(file_get_contents(self::CACHE_FILE), true);
        if (!$cache || !isset($cache[$key])) {
            return null;
        }

        if (time() - $cache[$key]['timestamp'] > self::CACHE_DURATION) {
            return null;
        }

        return $cache[$key]['data'];
    }

    /**
     * Сохранить данные в кэш
     */
    protected function setCachedData(string $key, array $data): void
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

    /**
     * Очистить кэш
     */
    protected function clearCache(): void
    {
        if (file_exists(self::CACHE_FILE)) {
            unlink(self::CACHE_FILE);
        }
    }
}
