<?php

namespace App\Services\Network;

use App\Abstracts\BaseService;
use App\Interfaces\NetworkServiceInterface;
use App\Exceptions\ServiceException;

class NetworkService extends BaseService implements NetworkServiceInterface
{
    /**
     * Получить таблицу маршрутов
     */
    public function getRoutes(): array
    {
        $output = $this->executeCommand('ip route show');
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
        $output = $this->executeCommand('ip addr show');
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
            
            // Парсим статус интерфейса
            if ($currentInterface && strpos($line, 'state') !== false) {
                if (strpos($line, 'UP') !== false) {
                    $currentInterface['status'] = 'up';
                }
            }
            
            // Парсим MAC адрес
            if ($currentInterface && strpos($line, 'link/ether') !== false) {
                if (preg_match('/link\/ether\s+([a-fA-F0-9:]+)/', $line, $matches)) {
                    $currentInterface['mac'] = $matches[1];
                }
            }
            
            // Парсим MTU
            if ($currentInterface && strpos($line, 'mtu') !== false) {
                if (preg_match('/mtu\s+(\d+)/', $line, $matches)) {
                    $currentInterface['mtu'] = $matches[1];
                }
            }
            
            // Парсим IP адреса
            if ($currentInterface && strpos($line, 'inet ') !== false) {
                if (preg_match('/inet\s+([0-9.]+)/', $line, $matches)) {
                    $currentInterface['ips'][] = $matches[1];
                }
            }
        }

        return $interfaces;
    }

    /**
     * Получить информацию о DNS
     */
    public function getDnsInfo(): array
    {
        $dnsServers = [];
        
        // Читаем resolv.conf
        $resolvContent = file_get_contents('/etc/resolv.conf');
        if ($resolvContent) {
            $lines = explode("\n", $resolvContent);
            foreach ($lines as $line) {
                if (preg_match('/^nameserver\s+([0-9.]+)/', trim($line), $matches)) {
                    $dnsServers[] = $matches[1];
                }
            }
        }

        return [
            'servers' => $dnsServers,
            'count' => count($dnsServers)
        ];
    }

    /**
     * Получить активные соединения
     */
    public function getConnections(): array
    {
        $output = $this->executeCommand('ss -tuln');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $connections = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'Netid') !== false) continue;

            $connection = $this->parseConnectionLine($line);
            if ($connection) {
                $connections[] = $connection;
            }
        }

        return $connections;
    }

    /**
     * Получить статистику трафика
     */
    public function getTrafficStats(): array
    {
        $output = $this->executeCommand('cat /proc/net/dev');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $stats = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'Inter-|') !== false || strpos($line, 'face |') !== false) continue;

            $stat = $this->parseTrafficLine($line);
            if ($stat) {
                $stats[] = $stat;
            }
        }

        return $stats;
    }

    /**
     * Добавить маршрут
     */
    public function addRoute(string $destination, string $gateway, string $interface): array
    {
        if (!$this->validateIpAddress($destination)) {
            throw new ValidationException("Неверный IP адрес назначения: {$destination}");
        }

        if (!$this->validateIpAddress($gateway)) {
            throw new ValidationException("Неверный IP адрес шлюза: {$gateway}");
        }

        if (!$this->validateInterfaceName($interface)) {
            throw new ValidationException("Неверное имя интерфейса: {$interface}");
        }

        $command = "ip route add {$destination} via {$gateway} dev {$interface}";
        $result = $this->safeExecute($command);

        if (!$result['success']) {
            throw new ServiceException("Ошибка добавления маршрута: " . $result['error']);
        }

        return [
            'success' => true,
            'message' => 'Маршрут добавлен успешно',
            'route' => [
                'destination' => $destination,
                'gateway' => $gateway,
                'interface' => $interface
            ]
        ];
    }

    /**
     * Удалить маршрут
     */
    public function deleteRoute(string $destination, string $gateway = '', string $interface = ''): array
    {
        if (!$this->validateIpAddress($destination)) {
            throw new ValidationException("Неверный IP адрес назначения: {$destination}");
        }

        $command = "ip route del {$destination}";
        
        if ($gateway && $this->validateIpAddress($gateway)) {
            $command .= " via {$gateway}";
        }
        
        if ($interface && $this->validateInterfaceName($interface)) {
            $command .= " dev {$interface}";
        }

        $result = $this->safeExecute($command);

        if (!$result['success']) {
            throw new ServiceException("Ошибка удаления маршрута: " . $result['error']);
        }

        return [
            'success' => true,
            'message' => 'Маршрут удален успешно'
        ];
    }

    /**
     * Парсить строку маршрута
     */
    private function parseRouteLine(string $line): ?array
    {
        // Простой парсинг маршрута
        if (preg_match('/^default\s+via\s+([0-9.]+)/', $line, $matches)) {
            return [
                'type' => 'default',
                'gateway' => $matches[1],
                'line' => $line
            ];
        }

        if (preg_match('/^([0-9.]+)\s+via\s+([0-9.]+)/', $line, $matches)) {
            return [
                'type' => 'network',
                'destination' => $matches[1],
                'gateway' => $matches[2],
                'line' => $line
            ];
        }

        return null;
    }

    /**
     * Парсить строку соединения
     */
    private function parseConnectionLine(string $line): ?array
    {
        $parts = preg_split('/\s+/', $line);
        if (count($parts) < 4) {
            return null;
        }

        return [
            'protocol' => $parts[0],
            'state' => $parts[1],
            'local_address' => $parts[3],
            'peer_address' => $parts[4] ?? '',
            'line' => $line
        ];
    }

    /**
     * Парсить строку статистики трафика
     */
    private function parseTrafficLine(string $line): ?array
    {
        $parts = preg_split('/\s+/', $line);
        if (count($parts) < 17) {
            return null;
        }

        return [
            'interface' => rtrim($parts[0], ':'),
            'rx_bytes' => $parts[1],
            'rx_packets' => $parts[2],
            'rx_errors' => $parts[3],
            'rx_dropped' => $parts[4],
            'tx_bytes' => $parts[9],
            'tx_packets' => $parts[10],
            'tx_errors' => $parts[11],
            'tx_dropped' => $parts[12]
        ];
    }
}
