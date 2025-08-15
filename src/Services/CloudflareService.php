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
        $output = shell_exec("$cloudflaredPath tunnel list 2>&1");
        
        // Если нет ошибки с сертификатом, значит авторизован
        $isAuth = strpos($output, 'originCertPath=') === false && strpos($output, 'Cannot determine default origin certificate path') === false;
        
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
            return $tunnels;
        }

        // Получаем список туннелей
        $cloudflaredPath = $this->getCloudflaredPath();
        
        $output = shell_exec("$cloudflaredPath tunnel list 2>&1");
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
        // Пример строки: "a12b3b7a-c9f3-49df-b9d7-22d4f8daa46e cftun 2025-07-15T15:47:59Z 1xams01, 1xams07, 1xams08, 1xams20"
        // Формат: ID NAME CREATED CONNECTIONS
        $parts = preg_split('/\s+/', $line);
        
        if (count($parts) < 3) {
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
            
            // Пропускаем сообщения об отсутствии маршрутов
            if (strpos($line, 'No routes were found') !== false) continue;
            if (strpos($line, 'You can use') !== false) continue;

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
    public function runTunnel(string $tunnelId, ?string $configPath = null): array
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
