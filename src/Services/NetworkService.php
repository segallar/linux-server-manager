<?php

namespace App\Services;

class NetworkService
{
    /**
     * Получить таблицу маршрутов
     */
    public function getRoutes(): array
    {
        $output = shell_exec('ip route show 2>/dev/null');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $routes = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $route = $this->parseRouteLine($line);
            if ($route) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /**
     * Получить статистику маршрутизации
     */
    public function getRoutingStats(): array
    {
        $routes = $this->getRoutes();
        
        $stats = [
            'total_routes' => count($routes),
            'default_routes' => 0,
            'local_routes' => 0,
            'network_routes' => 0,
            'gateway_routes' => 0
        ];

        foreach ($routes as $route) {
            switch ($route['type']) {
                case 'default':
                    $stats['default_routes']++;
                    break;
                case 'local':
                    $stats['local_routes']++;
                    break;
                case 'network':
                    $stats['network_routes']++;
                    break;
                case 'gateway':
                    $stats['gateway_routes']++;
                    break;
            }
        }

        return $stats;
    }

    /**
     * Получить информацию о сетевых интерфейсах
     */
    public function getInterfaces(): array
    {
        $output = shell_exec('ip addr show 2>/dev/null');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $interfaces = [];
        $currentInterface = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Ищем строку с интерфейсом
            if (preg_match('/^\d+:\s+(\w+):/', $line, $matches)) {
                $name = $matches[1];
                if ($name !== 'lo') { // Исключаем loopback
                    $currentInterface = [
                        'name' => $name,
                        'status' => 'down',
                        'ips' => [],
                        'mac' => '',
                        'mtu' => ''
                    ];
                    $interfaces[] = $currentInterface;
                }
            }
            
            // Статус интерфейса
            elseif ($currentInterface && strpos($line, 'state UP') !== false) {
                $currentInterface['status'] = 'up';
            }
            
            // MAC адрес
            elseif ($currentInterface && preg_match('/link\/\w+\s+([a-fA-F0-9:]+)/', $line, $matches)) {
                $currentInterface['mac'] = $matches[1];
            }
            
            // MTU
            elseif ($currentInterface && preg_match('/mtu\s+(\d+)/', $line, $matches)) {
                $currentInterface['mtu'] = $matches[1];
            }
            
            // IP адреса
            elseif ($currentInterface && preg_match('/inet\s+([0-9.]+)/', $line, $matches)) {
                $currentInterface['ips'][] = $matches[1];
            }
            elseif ($currentInterface && preg_match('/inet6\s+([a-fA-F0-9:]+)/', $line, $matches)) {
                $currentInterface['ips'][] = $matches[1];
            }
        }

        return $interfaces;
    }

    /**
     * Получить информацию о DNS
     */
    public function getDnsInfo(): array
    {
        $dns = [];
        
        // Читаем resolv.conf
        if (file_exists('/etc/resolv.conf')) {
            $resolv = file_get_contents('/etc/resolv.conf');
            preg_match_all('/nameserver\s+([0-9.]+)/', $resolv, $matches);
            $dns['nameservers'] = $matches[1] ?? [];
        }

        // Читаем hosts
        if (file_exists('/etc/hosts')) {
            $hosts = file_get_contents('/etc/hosts');
            $dns['hosts_count'] = substr_count($hosts, "\n") - 2; // Исключаем заголовки
        }

        return $dns;
    }

    /**
     * Получить информацию о сетевых соединениях
     */
    public function getConnections(): array
    {
        $output = shell_exec('ss -tuln 2>/dev/null');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $connections = [];

        // Пропускаем заголовок
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $connection = $this->parseConnectionLine($line);
            if ($connection) {
                $connections[] = $connection;
            }
        }

        return $connections;
    }

    /**
     * Парсить строку маршрута
     */
    private function parseRouteLine(string $line): ?array
    {
        // Примеры строк:
        // default via 192.168.1.1 dev eth0
        // 192.168.1.0/24 dev eth0 proto kernel scope link src 192.168.1.100
        // 10.0.0.0/8 via 192.168.1.1 dev eth0

        $route = [
            'destination' => '',
            'gateway' => '',
            'interface' => '',
            'type' => 'unknown',
            'scope' => '',
            'source' => ''
        ];

        // Определяем тип маршрута
        if (strpos($line, 'default') === 0) {
            $route['type'] = 'default';
            $route['destination'] = 'default';
        } elseif (strpos($line, 'link') !== false) {
            $route['type'] = 'local';
        } elseif (strpos($line, 'via') !== false) {
            $route['type'] = 'gateway';
        } else {
            $route['type'] = 'network';
        }

        // Парсим destination
        if (preg_match('/^([0-9.]+(?:\/[0-9]+)?)/', $line, $matches)) {
            $route['destination'] = $matches[1];
        }

        // Парсим gateway
        if (preg_match('/via\s+([0-9.]+)/', $line, $matches)) {
            $route['gateway'] = $matches[1];
        }

        // Парсим interface
        if (preg_match('/dev\s+(\w+)/', $line, $matches)) {
            $route['interface'] = $matches[1];
        }

        // Парсим scope
        if (preg_match('/scope\s+(\w+)/', $line, $matches)) {
            $route['scope'] = $matches[1];
        }

        // Парсим source
        if (preg_match('/src\s+([0-9.]+)/', $line, $matches)) {
            $route['source'] = $matches[1];
        }

        return $route;
    }

    /**
     * Парсить строку соединения
     */
    private function parseConnectionLine(string $line): ?array
    {
        $parts = preg_split('/\s+/', $line);
        if (count($parts) < 5) {
            return null;
        }

        return [
            'protocol' => $parts[0],
            'state' => $parts[1],
            'local_address' => $parts[3],
            'remote_address' => $parts[4]
        ];
    }

    /**
     * Добавить маршрут
     */
    public function addRoute(string $destination, string $gateway, string $interface): array
    {
        $command = "sudo ip route add $destination via $gateway dev $interface 2>&1";
        $output = shell_exec($command);
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Маршрут добавлен" : "Ошибка: $output"
        ];
    }

    /**
     * Удалить маршрут
     */
    public function deleteRoute(string $destination, string $gateway = '', string $interface = ''): array
    {
        $command = "sudo ip route del $destination";
        if ($gateway) {
            $command .= " via $gateway";
        }
        if ($interface) {
            $command .= " dev $interface";
        }
        $command .= " 2>&1";
        
        $output = shell_exec($command);
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Маршрут удален" : "Ошибка: $output"
        ];
    }

    /**
     * Получить статистику сетевого трафика
     */
    public function getTrafficStats(): array
    {
        $output = shell_exec('cat /proc/net/dev 2>/dev/null');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $stats = [];

        // Пропускаем заголовки
        for ($i = 2; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $interface = $this->parseTrafficLine($line);
            if ($interface) {
                $stats[] = $interface;
            }
        }

        return $stats;
    }

    /**
     * Парсить строку статистики трафика
     */
    private function parseTrafficLine(string $line): ?array
    {
        // Формат: interface | rx_bytes rx_packets rx_errors rx_dropped | tx_bytes tx_packets tx_errors tx_dropped
        $parts = preg_split('/\s*:\s*/', $line);
        if (count($parts) < 2) {
            return null;
        }

        $interface = trim($parts[0]);
        $data = preg_split('/\s+/', trim($parts[1]));

        if (count($data) < 8) {
            return null;
        }

        return [
            'interface' => $interface,
            'rx_bytes' => (int)$data[0],
            'rx_packets' => (int)$data[1],
            'rx_errors' => (int)$data[2],
            'rx_dropped' => (int)$data[3],
            'tx_bytes' => (int)$data[4],
            'tx_packets' => (int)$data[5],
            'tx_errors' => (int)$data[6],
            'tx_dropped' => (int)$data[7]
        ];
    }
}
