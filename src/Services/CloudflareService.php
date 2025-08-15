<?php

namespace App\Services;

class CloudflareService
{
    private string $cloudflaredPath = '/usr/local/bin/cloudflared';

    /**
     * Проверить, установлен ли cloudflared
     */
    public function isInstalled(): bool
    {
        $cloudflaredExists = file_exists($this->cloudflaredPath);
        $whichOutput = shell_exec('which cloudflared 2>/dev/null');
        
        error_log("Cloudflare: cloudflared exists: " . ($cloudflaredExists ? 'YES' : 'NO'));
        error_log("Cloudflare: which cloudflared: " . ($whichOutput ?: 'NOT_FOUND'));
        
        return $cloudflaredExists || !empty($whichOutput);
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

        // Получаем список туннелей
        $output = shell_exec('cloudflared tunnel list 2>/dev/null');
        if (!$output) {
            return $tunnels;
        }

        $lines = explode("\n", trim($output));
        
        // Пропускаем заголовок
        $headerFound = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Ищем строку с заголовками
            if (strpos($line, 'ID') !== false && strpos($line, 'NAME') !== false) {
                $headerFound = true;
                continue;
            }

            if ($headerFound) {
                $tunnel = $this->parseTunnelLine($line);
                if ($tunnel) {
                    $tunnels[] = $tunnel;
                }
            }
        }

        return $tunnels;
    }

    /**
     * Парсить строку туннеля
     */
    private function parseTunnelLine(string $line): ?array
    {
        // Пример строки: "12345678-1234-1234-1234-123456789012 test-tunnel active 2024-01-15T10:30:45Z"
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
            'connections' => $this->getTunnelConnections($id),
            'routes' => $this->getTunnelRoutes($id)
        ];
    }

    /**
     * Получить активные соединения туннеля
     */
    private function getTunnelConnections(string $tunnelId): array
    {
        $connections = [];

        // Получаем информацию о соединениях
        $output = shell_exec("cloudflared tunnel info $tunnelId 2>/dev/null");
        if (!$output) {
            return $connections;
        }

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Ищем строки с информацией о соединениях
            if (preg_match('/Connection ID:\s+(.+)/', $line, $matches)) {
                $connections[] = [
                    'id' => $matches[1],
                    'status' => 'active'
                ];
            }
        }

        return $connections;
    }

    /**
     * Получить маршруты туннеля
     */
    private function getTunnelRoutes(string $tunnelId): array
    {
        $routes = [];

        // Получаем маршруты туннеля
        $output = shell_exec("cloudflared tunnel route ip list --tunnel-id $tunnelId 2>/dev/null");
        if (!$output) {
            return $routes;
        }

        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'IP') !== false) continue;

            $routes[] = $line;
        }

        return $routes;
    }

    /**
     * Создать новый туннель
     */
    public function createTunnel(string $name): array
    {
        if (!$this->isInstalled()) {
            return ['success' => false, 'error' => 'cloudflared не установлен'];
        }

        $output = shell_exec("cloudflared tunnel create $name 2>&1");
        
        if (strpos($output, 'Created tunnel') !== false) {
            return ['success' => true, 'message' => "Туннель $name создан"];
        }

        return ['success' => false, 'error' => $output];
    }

    /**
     * Удалить туннель
     */
    public function deleteTunnel(string $tunnelId): array
    {
        if (!$this->isInstalled()) {
            return ['success' => false, 'error' => 'cloudflared не установлен'];
        }

        $output = shell_exec("cloudflared tunnel delete $tunnelId 2>&1");
        
        if (strpos($output, 'Deleted tunnel') !== false) {
            return ['success' => true, 'message' => "Туннель удален"];
        }

        return ['success' => false, 'error' => $output];
    }

    /**
     * Запустить туннель
     */
    public function runTunnel(string $tunnelId, string $configPath = null): array
    {
        if (!$this->isInstalled()) {
            return ['success' => false, 'error' => 'cloudflared не установлен'];
        }

        $command = "cloudflared tunnel run $tunnelId";
        if ($configPath) {
            $command .= " --config $configPath";
        }

        $output = shell_exec("$command 2>&1");
        
        if (strpos($output, 'error') === false) {
            return ['success' => true, 'message' => "Туннель запущен"];
        }

        return ['success' => false, 'error' => $output];
    }

    /**
     * Получить конфигурацию туннеля
     */
    public function getTunnelConfig(string $tunnelId): string
    {
        if (!$this->isInstalled()) {
            return '';
        }

        $output = shell_exec("cloudflared tunnel config show $tunnelId 2>/dev/null");
        return $output ?: '';
    }

    /**
     * Получить статистику
     */
    public function getStats(): array
    {
        $tunnels = $this->getTunnels();
        $totalConnections = 0;
        $activeTunnels = 0;

        foreach ($tunnels as $tunnel) {
            $totalConnections += count($tunnel['connections']);
            if ($tunnel['status'] === 'active') {
                $activeTunnels++;
            }
        }

        return [
            'total_tunnels' => count($tunnels),
            'active_tunnels' => $activeTunnels,
            'total_connections' => $totalConnections,
            'is_installed' => $this->isInstalled()
        ];
    }

    /**
     * Форматировать время создания
     */
    public function formatCreatedTime(string $createdTime): string
    {
        if (empty($createdTime)) {
            return 'Неизвестно';
        }

        $timestamp = strtotime($createdTime);
        if ($timestamp === false) {
            return 'Неизвестно';
        }

        return date('d.m.Y H:i', $timestamp);
    }
}
