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
        
        return [
            'online' => $isOnline,
            'interfaces' => $interfaces,
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
            'uptime' => $this->getUptime(),
            'load' => $this->getLoadAverage(),
            'users' => $this->getConnectedUsers(),
            'date' => date('Y-m-d H:i:s'),
            'hostname' => gethostname()
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
        $output = shell_exec('ip link show 2>/dev/null');
        $lines = explode("\n", $output);
        
        $interfaces = [];
        foreach ($lines as $line) {
            if (preg_match('/^\d+:\s+(\w+):/', $line, $matches)) {
                $name = $matches[1];
                if ($name !== 'lo') { // Исключаем loopback
                    $interfaces[] = $name;
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
}
