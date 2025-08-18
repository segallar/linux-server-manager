<?php

namespace App\Services\Cloudflare;

use App\Abstracts\BaseService;
use App\Interfaces\CloudflareServiceInterface;

class CloudflareService extends BaseService implements CloudflareServiceInterface
{
    protected string $cloudflaredPath = '/usr/local/bin/cloudflared';

    /**
     * Проверить, установлен ли cloudflared
     */
    public function isInstalled(): bool
    {
        $cloudflaredExists = file_exists($this->cloudflaredPath);
        $whichOutput = $this->executeCommand('which cloudflared');
        
        return $cloudflaredExists || !empty($whichOutput);
    }

    /**
     * Проверить, авторизован ли cloudflared
     */
    public function isAuthenticated(): bool
    {
        // Проверяем наличие сертификата в разных местах
        $certPaths = [
            '/root/.cloudflared/cert.pem',
            '/home/www-data/.cloudflared/cert.pem',
            '/var/www/.cloudflared/cert.pem',
            '/etc/cloudflared/cert.pem'
        ];
        
        $certExists = false;
        foreach ($certPaths as $path) {
            if (file_exists($path)) {
                $certExists = true;
                break;
            }
        }
        
        if (!$certExists) {
            return false;
        }
        
        $cloudflaredPath = $this->getCloudflaredPath();
        $output = $this->executeCommand("$cloudflaredPath tunnel list");
        
        // Если нет ошибки с сертификатом, значит авторизован
        $isAuth = strpos($output, 'originCertPath=') === false && strpos($output, 'Cannot determine default origin certificate path') === false;
        
        return $isAuth;
    }

    /**
     * Получить путь к cloudflared
     */
    protected function getCloudflaredPath(): string
    {
        $whichOutput = $this->executeCommand('which cloudflared');
        if (!empty($whichOutput)) {
            return trim($whichOutput);
        }
        
        if (file_exists($this->cloudflaredPath)) {
            return $this->cloudflaredPath;
        }
        
        return 'cloudflared';
    }

    /**
     * Получить список всех туннелей
     */
    public function getTunnels(): array
    {
        $tunnels = [];

        if (!$this->isInstalled()) {
            return $tunnels;
        }

        if (!$this->isAuthenticated()) {
            return $tunnels;
        }

        // Получаем список туннелей
        $cloudflaredPath = $this->getCloudflaredPath();
        
        $output = $this->executeCommand("$cloudflaredPath tunnel list");
        if (!$output) {
            return $tunnels;
        }

        $lines = explode("\n", trim($output));
        
        // Пропускаем заголовок
        $headerFound = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Пропускаем заголовок таблицы
            if (strpos($line, 'ID') !== false && strpos($line, 'NAME') !== false) {
                $headerFound = true;
                continue;
            }

            if (!$headerFound) {
                continue;
            }

            $tunnel = $this->parseTunnelLine($line);
            if ($tunnel) {
                $tunnels[] = $tunnel;
            }
        }

        return $tunnels;
    }

    /**
     * Получить статистику
     */
    public function getStats(): array
    {
        $tunnels = $this->getTunnels();
        
        $stats = [
            'total_tunnels' => count($tunnels),
            'active_tunnels' => 0,
            'inactive_tunnels' => 0,
            'total_connections' => 0,
            'installed' => $this->isInstalled(),
            'authenticated' => $this->isAuthenticated()
        ];

        foreach ($tunnels as $tunnel) {
            if ($tunnel['status'] === 'active') {
                $stats['active_tunnels']++;
                // Для активных туннелей добавляем примерное количество соединений
                $stats['total_connections'] += 1;
            } else {
                $stats['inactive_tunnels']++;
            }
        }

        return $stats;
    }

    /**
     * Парсить строку туннеля
     */
    protected function parseTunnelLine(string $line): ?array
    {
        // Пример строки: "12345678-1234-1234-1234-123456789012 my-tunnel active 2023-01-01T00:00:00Z"
        $parts = preg_split('/\s+/', $line);
        
        if (count($parts) < 4) {
            return null;
        }

        $id = $parts[0];
        $name = $parts[1];
        $status = $parts[2];
        $created = $parts[3];

        return [
            'id' => $id,
            'name' => $name,
            'status' => $status,
            'created' => $created,
            'created_formatted' => $this->formatCreatedTime($created)
        ];
    }

    /**
     * Форматировать время создания
     */
    protected function formatCreatedTime(string $createdTime): string
    {
        try {
            $timestamp = strtotime($createdTime);
            if ($timestamp === false) {
                return $createdTime;
            }

            $now = time();
            $diff = $now - $timestamp;

            if ($diff < 60) {
                return 'только что';
            } elseif ($diff < 3600) {
                $minutes = floor($diff / 60);
                return "{$minutes} мин. назад";
            } elseif ($diff < 86400) {
                $hours = floor($diff / 3600);
                return "{$hours} ч. назад";
            } elseif ($diff < 2592000) {
                $days = floor($diff / 86400);
                return "{$days} дн. назад";
            } else {
                return date('d.m.Y H:i', $timestamp);
            }
        } catch (\Exception $e) {
            return $createdTime;
        }
    }
}
