<?php

namespace App\Services;

class ServiceService
{
    /**
     * Получить список всех сервисов
     */
    public function getAllServices(): array
    {
        $output = shell_exec('systemctl list-units --type=service --all --no-pager 2>/dev/null');
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
        $status = shell_exec("systemctl is-active $serviceName 2>/dev/null");
        $enabled = shell_exec("systemctl is-enabled $serviceName 2>/dev/null");
        $uptime = shell_exec("systemctl show $serviceName --property=ActiveEnterTimestamp 2>/dev/null");
        $description = shell_exec("systemctl show $serviceName --property=Description 2>/dev/null");

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
     * Запустить сервис
     */
    public function startService(string $serviceName): array
    {
        $output = shell_exec("sudo systemctl start $serviceName 2>&1");
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Сервис $serviceName запущен" : "Ошибка запуска: $output"
        ];
    }

    /**
     * Остановить сервис
     */
    public function stopService(string $serviceName): array
    {
        $output = shell_exec("sudo systemctl stop $serviceName 2>&1");
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Сервис $serviceName остановлен" : "Ошибка остановки: $output"
        ];
    }

    /**
     * Перезапустить сервис
     */
    public function restartService(string $serviceName): array
    {
        $output = shell_exec("sudo systemctl restart $serviceName 2>&1");
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Сервис $serviceName перезапущен" : "Ошибка перезапуска: $output"
        ];
    }

    /**
     * Включить автозапуск сервиса
     */
    public function enableService(string $serviceName): array
    {
        $output = shell_exec("sudo systemctl enable $serviceName 2>&1");
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Автозапуск для $serviceName включен" : "Ошибка включения: $output"
        ];
    }

    /**
     * Отключить автозапуск сервиса
     */
    public function disableService(string $serviceName): array
    {
        $output = shell_exec("sudo systemctl disable $serviceName 2>&1");
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Автозапуск для $serviceName отключен" : "Ошибка отключения: $output"
        ];
    }

    /**
     * Парсить строку сервиса из systemctl
     */
    private function parseServiceLine(string $line): ?array
    {
        // Формат: UNIT LOAD ACTIVE SUB DESCRIPTION
        $parts = preg_split('/\s+/', $line, 5);
        if (count($parts) < 5) {
            return null;
        }

        $unit = $parts[0];
        $load = $parts[1];
        $active = $parts[2];
        $sub = $parts[3];
        $description = $parts[4] ?? '';

        // Пропускаем не-сервисы
        if (!str_ends_with($unit, '.service')) {
            return null;
        }

        $serviceName = str_replace('.service', '', $unit);

        return [
            'name' => $serviceName,
            'status' => $this->normalizeStatus($active),
            'enabled' => $load === 'loaded',
            'uptime' => $this->getServiceUptime($serviceName),
            'description' => $description
        ];
    }

    /**
     * Нормализовать статус сервиса
     */
    private function normalizeStatus(string $status): string
    {
        switch ($status) {
            case 'active':
                return 'active';
            case 'inactive':
                return 'inactive';
            case 'failed':
                return 'failed';
            default:
                return 'unknown';
        }
    }

    /**
     * Получить время работы сервиса
     */
    private function getServiceUptime(string $serviceName): string
    {
        $uptime = shell_exec("systemctl show $serviceName --property=ActiveEnterTimestamp 2>/dev/null");
        return $this->parseUptime($uptime);
    }

    /**
     * Парсить время работы сервиса
     */
    private function parseUptime(string $uptime): string
    {
        if (empty($uptime)) {
            return '-';
        }

        // Извлекаем timestamp из вывода systemctl
        if (preg_match('/ActiveEnterTimestamp=([^\\n]+)/', $uptime, $matches)) {
            $timestamp = $matches[1];
            $startTime = strtotime($timestamp);
            
            if ($startTime === false) {
                return '-';
            }

            $now = time();
            $diff = $now - $startTime;

            if ($diff < 60) {
                return 'Только что';
            } elseif ($diff < 3600) {
                $minutes = floor($diff / 60);
                return $minutes . ' мин';
            } elseif ($diff < 86400) {
                $hours = floor($diff / 3600);
                return $hours . ' ч';
            } else {
                $days = floor($diff / 86400);
                return $days . ' дн';
            }
        }

        return '-';
    }

    /**
     * Парсить описание сервиса
     */
    private function parseDescription(string $description): string
    {
        if (empty($description)) {
            return '';
        }

        if (preg_match('/Description=([^\\n]+)/', $description, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Получить популярные сервисы для быстрого доступа
     */
    public function getPopularServices(): array
    {
        $popularServices = [
            'nginx', 'apache2', 'mysql', 'postgresql', 'redis-server',
            'php-fpm', 'ssh', 'cron', 'rsyslog', 'ufw', 'fail2ban',
            'docker', 'kubelet', 'elasticsearch', 'mongod', 'rabbitmq-server'
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
}
