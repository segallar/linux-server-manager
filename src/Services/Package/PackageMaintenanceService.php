<?php

namespace App\Services\Package;

use App\Abstracts\BaseService;
use App\Interfaces\PackageMaintenanceServiceInterface;

class PackageMaintenanceService extends BaseService implements PackageMaintenanceServiceInterface
{
    /**
     * Очистить кэш пакетов
     */
    public function cleanPackageCache(): array
    {
        try {
            if (!file_exists('/usr/bin/apt')) {
                return ['success' => false, 'message' => 'APT не установлен'];
            }

            if ($this->isAptLocked()) {
                return ['success' => false, 'message' => 'APT заблокирован'];
            }

            $result = $this->safeExecute('apt clean');
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка очистки кэша: ' . $result['error']];
            }

            return ['success' => true, 'message' => 'Кэш пакетов очищен'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Удалить неиспользуемые пакеты
     */
    public function autoremovePackages(): array
    {
        try {
            if (!file_exists('/usr/bin/apt')) {
                return ['success' => false, 'message' => 'APT не установлен'];
            }

            if ($this->isAptLocked()) {
                return ['success' => false, 'message' => 'APT заблокирован'];
            }

            $result = $this->safeExecute('apt autoremove -y');
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка удаления неиспользуемых пакетов: ' . $result['error']];
            }

            return ['success' => true, 'message' => 'Неиспользуемые пакеты удалены'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Получить информацию о пакете
     */
    public function getPackageInfo(string $packageName): ?array
    {
        try {
            if (!file_exists('/usr/bin/apt')) {
                return null;
            }

            if (empty($packageName)) {
                return null;
            }

            // Получаем информацию о пакете
            $output = $this->executeCommand("apt show $packageName");
            if (!$output) {
                return null;
            }

            $lines = explode("\n", trim($output));
            $info = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                    $key = trim($matches[1]);
                    $value = trim($matches[2]);
                    
                    switch ($key) {
                        case 'Package':
                            $info['name'] = $value;
                            break;
                        case 'Version':
                            $info['version'] = $value;
                            break;
                        case 'Architecture':
                            $info['architecture'] = $value;
                            break;
                        case 'Description':
                            $info['description'] = $value;
                            break;
                        case 'Depends':
                            $info['depends'] = $value;
                            break;
                        case 'Installed-Size':
                            $info['size'] = $value;
                            break;
                        case 'Maintainer':
                            $info['maintainer'] = $value;
                            break;
                        case 'Homepage':
                            $info['homepage'] = $value;
                            break;
                    }
                }
            }

            return empty($info) ? null : $info;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Получить неиспользуемые пакеты
     */
    public function getUnusedPackages(): array
    {
        try {
            if (!file_exists('/usr/bin/apt')) {
                return [];
            }

            if ($this->isAptLocked()) {
                return [];
            }

            $output = $this->executeCommand('apt list --installed 2>/dev/null | grep -v "WARNING"');
            if (!$output) {
                return [];
            }

            $lines = explode("\n", trim($output));
            $packages = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (preg_match('/^([^\/]+)\/([^\s]+)\s+([^\s]+)\s+([^\s]+)/', $line, $matches)) {
                    $packageName = $matches[1];
                    
                    // Проверяем, является ли пакет автоматически установленным
                    $autoInstalled = $this->isAutoInstalled($packageName);
                    
                    if ($autoInstalled) {
                        $packages[] = [
                            'name' => $packageName,
                            'repository' => $matches[2],
                            'version' => $matches[3],
                            'architecture' => $matches[4],
                            'auto_installed' => true
                        ];
                    }
                }
            }

            return $packages;
        } catch (\Exception $e) {
            return [];
        }
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
     * Проверить, является ли пакет автоматически установленным
     */
    protected function isAutoInstalled(string $packageName): bool
    {
        $output = $this->executeCommand("apt-mark showauto $packageName");
        return !empty(trim($output));
    }
}
