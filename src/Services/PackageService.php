<?php

namespace App\Services;

class PackageService
{
    /**
     * Получить список доступных обновлений
     */
    public function getUpgradablePackages(): array
    {
        // Добавляем таймаут для избежания зависания
        $output = shell_exec('timeout 10 apt list --upgradable 2>/dev/null');
        if (!$output) {
            // Логируем ошибку для диагностики
            error_log("PackageService: apt list --upgradable returned no output");
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

        return $packages;
    }

    /**
     * Получить статистику пакетов
     */
    public function getPackageStats(): array
    {
        $upgradable = $this->getUpgradablePackages();
        $installed = $this->getInstalledPackagesCount();
        $security = $this->getSecurityUpdatesCount();

        return [
            'total_installed' => $installed,
            'upgradable' => count($upgradable),
            'security_updates' => $security,
            'last_update' => $this->getLastUpdateTime()
        ];
    }

    /**
     * Получить количество установленных пакетов
     */
    private function getInstalledPackagesCount(): int
    {
        // Добавляем таймаут для избежания зависания
        $output = shell_exec('timeout 5 dpkg -l | grep "^ii" | wc -l 2>/dev/null');
        return (int)trim($output ?: '0');
    }

    /**
     * Получить количество обновлений безопасности
     */
    private function getSecurityUpdatesCount(): int
    {
        // Добавляем таймаут для избежания зависания
        $output = shell_exec('timeout 10 apt list --upgradable 2>/dev/null | grep -i security | wc -l 2>/dev/null');
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
        $output = shell_exec('timeout 30 sudo apt update 2>&1');
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
        $output = shell_exec('timeout 300 sudo apt upgrade -y 2>&1'); // 5 минут для обновления
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
        $output = shell_exec("timeout 60 sudo apt install $packageName -y 2>&1");
        $success = strpos($output, 'upgraded') !== false || strpos($output, 'already the newest version') !== false;
        
        return [
            'success' => $success,
            'message' => $success ? "Пакет $packageName обновлен" : 'Ошибка обновления: ' . $output
        ];
    }

    /**
     * Получить информацию о пакете
     */
    public function getPackageInfo(string $packageName): ?array
    {
        $output = shell_exec("apt show $packageName 2>/dev/null");
        if (!$output) {
            return null;
        }

        $info = [
            'name' => $packageName,
            'version' => '',
            'description' => '',
            'size' => '',
            'depends' => [],
            'maintainer' => '',
            'homepage' => ''
        ];

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^Version:\s+(.+)$/', $line, $matches)) {
                $info['version'] = $matches[1];
            } elseif (preg_match('/^Description:\s+(.+)$/', $line, $matches)) {
                $info['description'] = $matches[1];
            } elseif (preg_match('/^Installed-Size:\s+(.+)$/', $line, $matches)) {
                $info['size'] = $matches[1];
            } elseif (preg_match('/^Depends:\s+(.+)$/', $line, $matches)) {
                $info['depends'] = array_map('trim', explode(',', $matches[1]));
            } elseif (preg_match('/^Maintainer:\s+(.+)$/', $line, $matches)) {
                $info['maintainer'] = $matches[1];
            } elseif (preg_match('/^Homepage:\s+(.+)$/', $line, $matches)) {
                $info['homepage'] = $matches[1];
            }
        }

        return $info;
    }

    /**
     * Получить список неиспользуемых пакетов
     */
    public function getUnusedPackages(): array
    {
        $output = shell_exec('apt list --installed 2>/dev/null | grep -v "WARNING"');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $unused = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^([^\/]+)\//', $line, $matches)) {
                $packageName = $matches[1];
                
                // Проверяем, используется ли пакет
                $usage = shell_exec("apt-cache rdepends $packageName 2>/dev/null | wc -l");
                $usageCount = (int)trim($usage ?: '0');
                
                if ($usageCount <= 1) { // Только сам пакет
                    $unused[] = [
                        'name' => $packageName,
                        'usage_count' => $usageCount
                    ];
                }
            }
        }

        return array_slice($unused, 0, 50); // Возвращаем только первые 50
    }

    /**
     * Очистить кэш пакетов
     */
    public function cleanPackageCache(): array
    {
        $output = shell_exec('sudo apt clean 2>&1');
        $success = empty($output) || strpos($output, 'error') === false;
        
        return [
            'success' => $success,
            'message' => $success ? 'Кэш пакетов очищен' : 'Ошибка очистки: ' . $output
        ];
    }

    /**
     * Автоматическое удаление неиспользуемых пакетов
     */
    public function autoremovePackages(): array
    {
        $output = shell_exec('sudo apt autoremove -y 2>&1');
        $success = strpos($output, 'removed') !== false || strpos($output, '0 to remove') !== false;
        
        return [
            'success' => $success,
            'message' => $success ? 'Неиспользуемые пакеты удалены' : 'Ошибка удаления: ' . $output
        ];
    }
}
