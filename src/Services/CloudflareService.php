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
            error_log("Cloudflare: No certificate found in any location");
            return false;
        }
        
        $cloudflaredPath = $this->getCloudflaredPath();
        $output = shell_exec("$cloudflaredPath tunnel list 2>&1");
        
        // Если нет ошибки с сертификатом, значит авторизован
        $isAuth = strpos($output, 'originCertPath=') === false && strpos($output, 'Cannot determine default origin certificate path') === false;
        
        error_log("Cloudflare: Authentication check result: " . ($isAuth ? 'AUTHENTICATED' : 'NOT_AUTHENTICATED'));
        error_log("Cloudflare: Command output: " . $output);
        
        return $isAuth;
    }

    /**
     * Получить путь к cloudflared
     */
    private function getCloudflaredPath(): string
    {
        $whichOutput = shell_exec('which cloudflared 2>/dev/null');
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
            error_log("Cloudflare: Not authenticated - need to run cloudflared tunnel login");
            return $tunnels;
        }

        // Получаем список туннелей
        $cloudflaredPath = $this->getCloudflaredPath();
        error_log("Cloudflare: Using path: " . $cloudflaredPath);
        
        $output = shell_exec("$cloudflaredPath tunnel list 2>&1");
        if (!$output) {
            error_log("Cloudflare: No output from cloudflared tunnel list");
            return $tunnels;
        }

        error_log("Cloudflare: Raw output: " . $output);

        $lines = explode("\n", trim($output));
        
        // Пропускаем заголовок
        $headerFound = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Ищем строку с заголовками
            if (strpos($line, 'ID') !== false && strpos($line, 'NAME') !== false) {
                $headerFound = true;
                error_log("Cloudflare: Header found: " . $line);
                continue;
            }

            if ($headerFound) {
                error_log("Cloudflare: Processing line: " . $line);
                $tunnel = $this->parseTunnelLine($line);
                if ($tunnel) {
                    error_log("Cloudflare: Parsed tunnel: " . json_encode($tunnel));
                    $tunnels[] = $tunnel;
                }
            }
        }

        error_log("Cloudflare: Total tunnels found: " . count($tunnels));
        return $tunnels;
    }

    /**
     * Парсить строку туннеля
     */
    private function parseTunnelLine(string $line): ?array
    {
        // Пример строки: "a12b3b7a-c9f3-49df-b9d7-22d4f8daa46e cftun 2025-07-15T15:47:59Z 1xams01, 1xams07, 1xams08, 1xams20"
        // Формат: ID NAME CREATED CONNECTIONS
        $parts = preg_split('/\s+/', $line);
        
        if (count($parts) < 3) {
            error_log("Cloudflare: Invalid line format: " . $line);
            return null;
        }

        $id = $parts[0];
        $name = $parts[1];
        $created = $parts[2];
        
        // Определяем статус на основе наличия соединений
        $status = 'inactive';
        if (count($parts) > 3) {
            $connections = $parts[3];
            if (!empty($connections) && $connections !== '0') {
                $status = 'active';
            }
        }

        error_log("Cloudflare: Parsed - ID: $id, Name: $name, Created: $created, Status: $status");

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
        $cloudflaredPath = $this->getCloudflaredPath();
        $output = shell_exec("$cloudflaredPath tunnel info $tunnelId 2>/dev/null");
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

        // Если не нашли через tunnel info, попробуем из списка туннелей
        if (empty($connections)) {
            $cloudflaredPath = $this->getCloudflaredPath();
            $listOutput = shell_exec("$cloudflaredPath tunnel list 2>/dev/null");
            if ($listOutput) {
                $lines = explode("\n", $listOutput);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strpos($line, $tunnelId) !== false) {
                        // Ищем соединения в строке
                        if (preg_match('/\s+([a-z0-9,]+)$/', $line, $matches)) {
                            $connectionList = $matches[1];
                            $connectionIds = array_map('trim', explode(',', $connectionList));
                            foreach ($connectionIds as $connId) {
                                if (!empty($connId) && $connId !== '0') {
                                    $connections[] = [
                                        'id' => $connId,
                                        'status' => 'active'
                                    ];
                                }
                            }
                        }
                        break;
                    }
                }
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
        $cloudflaredPath = $this->getCloudflaredPath();
        $output = shell_exec("$cloudflaredPath tunnel route ip show 2>/dev/null");
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

        $cloudflaredPath = $this->getCloudflaredPath();
        $output = shell_exec("$cloudflaredPath tunnel create $name 2>&1");
        
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

        $cloudflaredPath = $this->getCloudflaredPath();
        $output = shell_exec("$cloudflaredPath tunnel delete $tunnelId 2>&1");
        
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

        $cloudflaredPath = $this->getCloudflaredPath();
        $command = "$cloudflaredPath tunnel run $tunnelId";
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

        $cloudflaredPath = $this->getCloudflaredPath();
        $output = shell_exec("$cloudflaredPath tunnel config show $tunnelId 2>/dev/null");
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
