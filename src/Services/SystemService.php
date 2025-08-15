<?php

namespace App\Services;

class SystemService
{
    /**
     * Получить информацию о CPU
     */
    public function getCpuInfo(): array
    {
        // Получаем загрузку CPU
        $load = sys_getloadavg();
        
        // Получаем количество ядер
        $cpuInfo = file_get_contents('/proc/cpuinfo');
        $cores = substr_count($cpuInfo, 'processor');
        
        // Вычисляем процент загрузки (1 минута)
        $cpuUsage = min(100, round(($load[0] / $cores) * 100));
        
        return [
            'usage' => $cpuUsage,
            'load' => $load,
            'cores' => $cores,
            'model' => $this->getCpuModel()
        ];
    }

    /**
     * Получить информацию о RAM
     */
    public function getMemoryInfo(): array
    {
        $memInfo = file_get_contents('/proc/meminfo');
        
        preg_match('/MemTotal:\s+(\d+)/', $memInfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $memInfo, $available);
        preg_match('/MemFree:\s+(\d+)/', $memInfo, $free);
        
        $totalKb = (int)$total[1];
        $availableKb = (int)$available[1];
        $freeKb = (int)$free[1];
        
        $usedKb = $totalKb - $availableKb;
        $usagePercent = round(($usedKb / $totalKb) * 100);
        
        return [
            'total' => $this->formatBytes($totalKb * 1024),
            'used' => $this->formatBytes($usedKb * 1024),
            'free' => $this->formatBytes($freeKb * 1024),
            'available' => $this->formatBytes($availableKb * 1024),
            'usage_percent' => $usagePercent,
            'total_kb' => $totalKb,
            'used_kb' => $usedKb,
            'free_kb' => $freeKb
        ];
    }

    /**
     * Получить информацию о дисках
     */
    public function getDiskInfo(): array
    {
        $output = shell_exec('df -h / 2>/dev/null');
        $lines = explode("\n", trim($output));
        
        if (count($lines) < 2) {
            return ['usage_percent' => 0, 'total' => '0', 'used' => '0', 'free' => '0'];
        }
        
        $parts = preg_split('/\s+/', trim($lines[1]));
        
        if (count($parts) < 5) {
            return ['usage_percent' => 0, 'total' => '0', 'used' => '0', 'free' => '0'];
        }
        
        $total = $parts[1];
        $used = $parts[2];
        $free = $parts[3];
        $usagePercent = (int)rtrim($parts[4], '%');
        
        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'usage_percent' => $usagePercent
        ];
    }

    /**
     * Получить информацию о сети
     */
    public function getNetworkInfo(): array
    {
        // Проверяем подключение к интернету
        $isOnline = $this->isOnline();
        
        // Получаем активные интерфейсы
        $interfaces = $this->getActiveInterfaces();
        
        // Подсчитываем активные интерфейсы
        $activeCount = 0;
        foreach ($interfaces as $interface) {
            if ($interface['status'] === 'up') {
                $activeCount++;
            }
        }
        
        return [
            'online' => $isOnline,
            'interfaces' => $interfaces,
            'active_count' => $activeCount,
            'total_count' => count($interfaces),
            'status' => $isOnline ? 'Онлайн' : 'Офлайн'
        ];
    }

    /**
     * Получить системную информацию
     */
    public function getSystemInfo(): array
    {
        return [
            'os' => $this->getOsInfo(),
            'kernel' => $this->getKernelInfo(),
            'architecture' => $this->getArchitecture(),
            'uptime' => $this->getUptime(),
            'load' => $this->getLoadAverage(),
            'users' => $this->getConnectedUsers(),
            'date' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'hostname' => gethostname(),
            'domain' => $this->getDomain(),
            'boot_time' => $this->getBootTime()
        ];
    }

    /**
     * Получить активные процессы
     */
    public function getActiveProcesses(int $limit = 10): array
    {
        $output = shell_exec("ps aux --sort=-%cpu | head -" . ($limit + 1) . " 2>/dev/null");
        $lines = explode("\n", trim($output));
        
        $processes = [];
        
        // Пропускаем заголовок
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;
            
            $parts = preg_split('/\s+/', $line, 11);
            if (count($parts) < 11) continue;
            
            $processes[] = [
                'pid' => $parts[1],
                'user' => $parts[0],
                'cpu' => $parts[2],
                'mem' => $parts[3],
                'vsz' => $parts[4],
                'rss' => $parts[5],
                'tty' => $parts[6],
                'stat' => $parts[7],
                'start' => $parts[8],
                'time' => $parts[9],
                'command' => $parts[10]
            ];
        }
        
        return $processes;
    }

    /**
     * Получить общую статистику
     */
    public function getStats(): array
    {
        $cpu = $this->getCpuInfo();
        $memory = $this->getMemoryInfo();
        $disk = $this->getDiskInfo();
        $network = $this->getNetworkInfo();
        $system = $this->getSystemInfo();
        $processes = $this->getActiveProcesses(5);
        
        return [
            'cpu' => $cpu,
            'memory' => $memory,
            'disk' => $disk,
            'network' => $network,
            'system' => $system,
            'processes' => $processes,
            'timestamp' => time()
        ];
    }

    // Вспомогательные методы

    private function getCpuModel(): string
    {
        $cpuInfo = file_get_contents('/proc/cpuinfo');
        preg_match('/model name\s+:\s+(.+)/', $cpuInfo, $matches);
        return $matches[1] ?? 'Unknown';
    }

    private function getOsInfo(): string
    {
        if (file_exists('/etc/os-release')) {
            $osInfo = file_get_contents('/etc/os-release');
            preg_match('/PRETTY_NAME="(.+)"/', $osInfo, $matches);
            return $matches[1] ?? 'Linux';
        }
        return 'Linux';
    }

    private function getKernelInfo(): string
    {
        return trim(shell_exec('uname -r 2>/dev/null') ?: 'Unknown');
    }

    private function getUptime(): string
    {
        $uptime = file_get_contents('/proc/uptime');
        $seconds = (int)explode(' ', $uptime)[0];
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($days > 0) {
            return sprintf('%d дн, %d ч, %d мин', $days, $hours, $minutes);
        } elseif ($hours > 0) {
            return sprintf('%d ч, %d мин', $hours, $minutes);
        } else {
            return sprintf('%d мин', $minutes);
        }
    }

    private function getLoadAverage(): string
    {
        $load = sys_getloadavg();
        return sprintf('%.2f, %.2f, %.2f', $load[0], $load[1], $load[2]);
    }

    private function getConnectedUsers(): string
    {
        $output = shell_exec('who | wc -l 2>/dev/null');
        $count = (int)trim($output ?: '0');
        return $count . ' подключенных';
    }

    private function isOnline(): bool
    {
        // Проверяем подключение к Google DNS
        $connection = @fsockopen('8.8.8.8', 53, $errno, $errstr, 2);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }

    private function getActiveInterfaces(): array
    {
        $output = shell_exec('ip addr show 2>/dev/null');
        $lines = explode("\n", $output);
        
        $interfaces = [];
        $currentInterface = null;
        
        foreach ($lines as $line) {
            // Ищем строку с интерфейсом
            if (preg_match('/^\d+:\s+(\w+):/', $line, $matches)) {
                $name = $matches[1];
                if ($name !== 'lo') { // Исключаем loopback
                    $currentInterface = $name;
                    $interfaces[$name] = [
                        'name' => $name,
                        'ips' => [],
                        'status' => 'down'
                    ];
                }
            }
            
            // Ищем IP адреса для текущего интерфейса
            if ($currentInterface && preg_match('/inet\s+([0-9.]+)/', $line, $matches)) {
                $interfaces[$currentInterface]['ips'][] = $matches[1];
            }
            
            // Определяем статус интерфейса
            if ($currentInterface && strpos($line, 'state UP') !== false) {
                $interfaces[$currentInterface]['status'] = 'up';
            }
        }
        
        // Дополнительная проверка для WireGuard интерфейсов
        foreach ($interfaces as $name => &$interface) {
            if (strpos($name, 'wg') === 0) { // WireGuard интерфейсы начинаются с wg
                // Проверяем, есть ли IP адреса - если есть, значит интерфейс активен
                if (!empty($interface['ips'])) {
                    $interface['status'] = 'up';
                }
                
                // Дополнительная проверка через ip link
                $linkOutput = shell_exec("ip link show $name 2>/dev/null");
                if (strpos($linkOutput, 'UP') !== false) {
                    $interface['status'] = 'up';
                }
            }
        }
        
        return $interfaces;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getArchitecture(): string
    {
        return trim(shell_exec('uname -m 2>/dev/null') ?: 'Unknown');
    }

    private function getDomain(): string
    {
        $domain = shell_exec('hostname -d 2>/dev/null');
        return trim($domain ?: 'local');
    }

    private function getBootTime(): string
    {
        $uptime = file_get_contents('/proc/uptime');
        $seconds = (int)explode(' ', $uptime)[0];
        $bootTime = time() - $seconds;
        return date('Y-m-d H:i:s', $bootTime);
    }

    /**
     * Получить детальную информацию о CPU
     */
    public function getDetailedCpuInfo(): array
    {
        $cpuInfo = file_get_contents('/proc/cpuinfo');
        
        // Получаем модель CPU
        preg_match('/model name\s+:\s+(.+)/', $cpuInfo, $modelMatches);
        $model = $modelMatches[1] ?? 'Unknown';
        
        // Получаем количество ядер
        $cores = substr_count($cpuInfo, 'processor');
        
        // Получаем частоту
        preg_match('/cpu MHz\s+:\s+([0-9.]+)/', $cpuInfo, $freqMatches);
        $frequency = $freqMatches[1] ?? 'Unknown';
        
        // Получаем кэш
        preg_match('/cache size\s+:\s+([0-9]+)/', $cpuInfo, $cacheMatches);
        $cache = $cacheMatches[1] ?? 'Unknown';
        
        return [
            'model' => $model,
            'cores' => $cores,
            'frequency' => $frequency,
            'cache' => $cache . ' KB',
            'load' => sys_getloadavg()
        ];
    }

    /**
     * Получить детальную информацию о дисках
     */
    public function getDetailedDiskInfo(): array
    {
        $output = shell_exec('df -h 2>/dev/null');
        $lines = explode("\n", trim($output));
        
        $disks = [];
        
        // Пропускаем заголовок
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;
            
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 6) continue;
            
            $disks[] = [
                'filesystem' => $parts[0],
                'size' => $parts[1],
                'used' => $parts[2],
                'available' => $parts[3],
                'usage_percent' => (int)rtrim($parts[4], '%'),
                'mounted_on' => $parts[5]
            ];
        }
        
        return $disks;
    }

    /**
     * Получить количество процессов
     */
    public function getProcessCount(): int
    {
        $output = shell_exec('ps aux | wc -l 2>/dev/null');
        return (int)trim($output ?: '0') - 1; // Вычитаем заголовок
    }

    /**
     * Получить детальную информацию о системе
     */
    public function getDetailedSystemInfo(): array
    {
        $cpu = $this->getDetailedCpuInfo();
        $memory = $this->getMemoryInfo();
        $disk = $this->getDetailedDiskInfo();
        $network = $this->getNetworkInfo();
        $system = $this->getSystemInfo();
        $processCount = $this->getProcessCount();
        
        return [
            'cpu' => $cpu,
            'memory' => $memory,
            'disk' => $disk,
            'network' => $network,
            'system' => $system,
            'process_count' => $processCount,
            'timestamp' => time()
        ];
    }
}
