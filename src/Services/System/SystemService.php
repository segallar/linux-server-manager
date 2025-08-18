<?php

namespace App\Services\System;

use App\Abstracts\BaseService;
use App\Interfaces\SystemServiceInterface;

class SystemService extends BaseService implements SystemServiceInterface
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
        $output = $this->executeCommand('df -h /');
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
            'hostname' => gethostname(),
            'ip' => $this->getLocalIp()
        ];
    }

    /**
     * Получить информацию о системе
     */
    public function getSystemInfo(): array
    {
        return [
            'os' => $this->getOsInfo(),
            'kernel' => $this->getKernelVersion(),
            'uptime' => $this->getUptime(),
            'boot_time' => $this->getBootTime(),
            'timezone' => date_default_timezone_get()
        ];
    }

    /**
     * Получить статистику
     */
    public function getStats(): array
    {
        try {
            $cpu = $this->getCpuInfo();
        } catch (\Exception $e) {
            $cpu = ['usage' => 0, 'load' => [0, 0, 0], 'cores' => 0, 'model' => 'Unknown'];
        }
        
        try {
            $memory = $this->getMemoryInfo();
        } catch (\Exception $e) {
            $memory = ['total' => '0', 'used' => '0', 'free' => '0', 'usage_percent' => 0];
        }
        
        try {
            $disk = $this->getDiskInfo();
        } catch (\Exception $e) {
            $disk = ['total' => '0', 'used' => '0', 'free' => '0', 'usage_percent' => 0];
        }
        
        try {
            $network = $this->getNetworkInfo();
        } catch (\Exception $e) {
            $network = ['status' => 'offline', 'active_count' => 0, 'total_count' => 0, 'interfaces' => []];
        }
        
        try {
            $system = $this->getSystemInfo();
        } catch (\Exception $e) {
            $system = [
                'os' => 'Unknown',
                'kernel' => 'Unknown',
                'uptime' => 'Unknown',
                'boot_time' => 'Unknown',
                'timezone' => 'UTC',
                'hostname' => 'Unknown',
                'users' => '0',
                'date' => date('Y-m-d H:i:s'),
                'load' => '0.00, 0.00, 0.00',
                'architecture' => 'Unknown'
            ];
        }
        
        return [
            'cpu' => $cpu,
            'memory' => $memory,
            'disk' => $disk,
            'network' => $network,
            'system' => $system,
            'timestamp' => time()
        ];
    }

    /**
     * Получить модель CPU
     */
    protected function getCpuModel(): string
    {
        $cpuInfo = file_get_contents('/proc/cpuinfo');
        preg_match('/model name\s+:\s+(.+)/', $cpuInfo, $matches);
        return $matches[1] ?? 'Unknown';
    }

    /**
     * Форматировать байты
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Проверить подключение к интернету
     */
    protected function isOnline(): bool
    {
        $result = $this->safeExecute('ping -c 1 8.8.8.8');
        return $result['success'];
    }

    /**
     * Получить активные интерфейсы
     */
    protected function getActiveInterfaces(): array
    {
        $output = $this->executeCommand('ip addr show');
        $lines = explode("\n", trim($output));
        
        $interfaces = [];
        $currentInterface = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (preg_match('/^\d+:\s+(\w+):/', $line, $matches)) {
                $name = $matches[1];
                if ($name !== 'lo') {
                    $currentInterface = [
                        'name' => $name,
                        'status' => 'down',
                        'ip' => ''
                    ];
                    $interfaces[] = $currentInterface;
                }
            }
            
            if ($currentInterface && strpos($line, 'state UP') !== false) {
                $currentInterface['status'] = 'up';
            }
            
            if ($currentInterface && strpos($line, 'inet ') !== false) {
                if (preg_match('/inet\s+([0-9.]+)/', $line, $matches)) {
                    $currentInterface['ip'] = $matches[1];
                }
            }
        }
        
        return $interfaces;
    }

    /**
     * Получить локальный IP
     */
    protected function getLocalIp(): string
    {
        $output = $this->executeCommand("hostname -I");
        $ips = explode(' ', trim($output));
        return $ips[0] ?? '127.0.0.1';
    }

    /**
     * Получить информацию об ОС
     */
    protected function getOsInfo(): string
    {
        $osRelease = file_get_contents('/etc/os-release');
        if ($osRelease) {
            preg_match('/PRETTY_NAME="(.+)"/', $osRelease, $matches);
            return $matches[1] ?? 'Linux';
        }
        return 'Linux';
    }

    /**
     * Получить версию ядра
     */
    protected function getKernelVersion(): string
    {
        return trim(file_get_contents('/proc/version') ?: 'Unknown');
    }

    /**
     * Получить время работы системы
     */
    protected function getUptime(): string
    {
        $uptime = file_get_contents('/proc/uptime');
        $seconds = (int)explode(' ', $uptime)[0];
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($days > 0) {
            return "{$days}д {$hours}ч {$minutes}м";
        } elseif ($hours > 0) {
            return "{$hours}ч {$minutes}м";
        } else {
            return "{$minutes}м";
        }
    }

    /**
     * Получить время загрузки
     */
    protected function getBootTime(): string
    {
        $uptime = file_get_contents('/proc/uptime');
        $seconds = (int)explode(' ', $uptime)[0];
        $bootTime = time() - $seconds;
        return date('Y-m-d H:i:s', $bootTime);
    }
}
