<?php

namespace App\Services\Service;

use App\Abstracts\BaseService;
use App\Interfaces\ServiceServiceInterface;

class ServiceService extends BaseService implements ServiceServiceInterface
{
    /**
     * Получить список всех сервисов
     */
    public function getAllServices(): array
    {
        $output = $this->executeCommand('systemctl list-units --type=service --all --no-pager');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $services = [];

        // Пропускаем заголовки
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'UNIT') !== false) {
                continue;
            }

            $service = $this->parseServiceLine($line);
            if ($service) {
                $services[] = $service;
            }
        }

        return $services;
    }

    /**
     * Получить статистику сервисов
     */
    public function getStats(): array
    {
        $services = $this->getAllServices();
        
        $stats = [
            'total' => count($services),
            'active' => 0,
            'inactive' => 0,
            'failed' => 0,
            'enabled' => 0,
            'disabled' => 0
        ];

        foreach ($services as $service) {
            switch ($service['status']) {
                case 'active':
                    $stats['active']++;
                    break;
                case 'inactive':
                    $stats['inactive']++;
                    break;
                case 'failed':
                    $stats['failed']++;
                    break;
            }

            if ($service['enabled']) {
                $stats['enabled']++;
            } else {
                $stats['disabled']++;
            }
        }

        return $stats;
    }

    /**
     * Получить информацию о конкретном сервисе
     */
    public function getServiceInfo(string $serviceName): ?array
    {
        $status = $this->executeCommand("systemctl is-active $serviceName");
        $enabled = $this->executeCommand("systemctl is-enabled $serviceName");
        $uptime = $this->executeCommand("systemctl show $serviceName --property=ActiveEnterTimestamp");
        $description = $this->executeCommand("systemctl show $serviceName --property=Description");

        if ($status === null) {
            return null;
        }

        return [
            'name' => $serviceName,
            'status' => trim($status),
            'enabled' => trim($enabled) === 'enabled',
            'uptime' => $this->parseUptime($uptime),
            'description' => $this->parseDescription($description)
        ];
    }

    /**
     * Получить популярные сервисы
     */
    public function getPopularServices(): array
    {
        $popularServices = [
            'ssh', 'apache2', 'nginx', 'mysql', 'postgresql', 'redis', 'memcached',
            'docker', 'kubelet', 'elasticsearch', 'mongodb', 'rabbitmq', 'zookeeper'
        ];

        $services = [];
        foreach ($popularServices as $serviceName) {
            $info = $this->getServiceInfo($serviceName);
            if ($info) {
                $services[] = $info;
            }
        }

        return $services;
    }

    /**
     * Получить сервисы
     */
    public function getServices(): array
    {
        $services = $this->getAllServices();
        $stats = $this->getStats();

        return [
            'services' => $services,
            'stats' => $stats
        ];
    }

    /**
     * Парсить строку сервиса
     */
    protected function parseServiceLine(string $line): ?array
    {
        // Пример строки: "apache2.service loaded active running The Apache HTTP Server"
        $parts = preg_split('/\s+/', $line, 5);
        
        if (count($parts) < 4) {
            return null;
        }

        $name = rtrim($parts[0], '.service');
        $load = $parts[1];
        $status = $parts[2];
        $sub = $parts[3];
        $description = $parts[4] ?? '';

        return [
            'name' => $name,
            'load' => $load,
            'status' => $status,
            'sub' => $sub,
            'description' => $description,
            'enabled' => $this->isServiceEnabled($name)
        ];
    }

    /**
     * Проверить, включен ли сервис
     */
    protected function isServiceEnabled(string $serviceName): bool
    {
        $output = $this->executeCommand("systemctl is-enabled $serviceName");
        return trim($output) === 'enabled';
    }

    /**
     * Парсить время работы
     */
    protected function parseUptime(string $uptime): string
    {
        if (preg_match('/ActiveEnterTimestamp=([^\\n]+)/', $uptime, $matches)) {
            $timestamp = $matches[1];
            if ($timestamp === 'n/a') {
                return 'Не запущен';
            }
            
            $time = strtotime($timestamp);
            if ($time) {
                $diff = time() - $time;
                $days = floor($diff / 86400);
                $hours = floor(($diff % 86400) / 3600);
                $minutes = floor(($diff % 3600) / 60);
                
                if ($days > 0) {
                    return "{$days}д {$hours}ч {$minutes}м";
                } elseif ($hours > 0) {
                    return "{$hours}ч {$minutes}м";
                } else {
                    return "{$minutes}м";
                }
            }
        }
        
        return 'Неизвестно';
    }

    /**
     * Парсить описание
     */
    protected function parseDescription(string $description): string
    {
        if (preg_match('/Description=([^\\n]+)/', $description, $matches)) {
            return $matches[1];
        }
        
        return '';
    }
}
