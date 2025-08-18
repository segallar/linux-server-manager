<?php

namespace App\Controllers\Network;

use App\Core\Controller;
use App\Interfaces\NetworkViewControllerInterface;
use App\Services\Network\SSHTunnelService;
use App\Services\Network\PortForwardingService;
use App\Services\WireGuard\WireGuardService;
use App\Services\Cloudflare\CloudflareService;

class NetworkViewController extends Controller implements NetworkViewControllerInterface
{
    /**
     * Главная страница сети
     */
    public function index(): string
    {
        return $this->render('network/index', [
            'title' => 'Сеть',
            'currentPage' => 'network'
        ]);
    }

    /**
     * Страница SSH туннелей
     */
    public function ssh(): string
    {
        $sshTunnelService = new SSHTunnelService();
        
        // Получаем реальные данные о SSH туннелях
        $tunnels = $sshTunnelService->getSSHTunnels();
        
        // Получаем информацию о подключениях
        $connections = $sshTunnelService->getSSHTunnelConnections();
        
        // Вычисляем статистику
        $stats = [
            'active_tunnels' => 0,
            'total_tunnels' => count($tunnels),
            'connections' => 0,
            'uptime' => '0д 0ч'
        ];
        
        foreach ($tunnels as $tunnel) {
            if ($tunnel['status'] === 'running') {
                $stats['active_tunnels']++;
            }
        }
        
        // Подсчитываем общее количество подключений
        foreach ($connections as $connection) {
            $stats['connections'] += count($connection['connections']);
        }
        
        return $this->render('network/ssh', [
            'title' => 'SSH туннели',
            'currentPage' => 'network',
            'tunnels' => $tunnels,
            'connections' => $connections,
            'stats' => $stats
        ]);
    }

    /**
     * Страница проброса портов
     */
    public function portForwarding(): string
    {
        $portForwardingService = new PortForwardingService();
        
        // Получаем реальные данные о правилах проброса портов
        $rules = $portForwardingService->getPortForwardingRules();
        
        // Получаем предупреждения безопасности
        $securityWarnings = $portForwardingService->getPortForwardingSecurityWarnings();
        
        // Вычисляем статистику
        $stats = [
            'active_rules' => 0,
            'total_rules' => count($rules),
            'total_connections' => 0,
            'bandwidth' => '0 MB/s'
        ];
        
        foreach ($rules as $rule) {
            if ($rule['status'] === 'active') {
                $stats['active_rules']++;
            }
        }
        
        return $this->render('network/port-forwarding', [
            'title' => 'Проброс портов',
            'currentPage' => 'network',
            'rules' => $rules,
            'securityWarnings' => $securityWarnings,
            'stats' => $stats
        ]);
    }

    /**
     * Страница WireGuard
     */
    public function wireguard(): string
    {
        $wireguardService = new WireGuardService();
        
        $interfaces = $wireguardService->getInterfaces();
        $stats = $wireguardService->getStats();
        $isInstalled = $wireguardService->isInstalled();
        
        return $this->render('network/wireguard', [
            'title' => 'WireGuard',
            'currentPage' => 'network',
            'interfaces' => $interfaces,
            'stats' => $stats,
            'isInstalled' => $isInstalled,
            'wireguardService' => $wireguardService
        ]);
    }

    /**
     * Страница Cloudflare
     */
    public function cloudflare(): string
    {
        try {
            $cloudflareService = new CloudflareService();
            
            $tunnels = $cloudflareService->getTunnels();
            $stats = $cloudflareService->getStats();
            $isInstalled = $cloudflareService->isInstalled();
            
        } catch (\Exception $e) {
            // Fallback значения в случае ошибки
            $tunnels = [];
            $stats = [
                'total_tunnels' => 0,
                'active_tunnels' => 0,
                'total_connections' => 0,
                'bandwidth' => '0 MB/s'
            ];
            $isInstalled = false;
        }
        
        return $this->render('network/cloudflare', [
            'title' => 'Cloudflare туннели',
            'currentPage' => 'network',
            'tunnels' => $tunnels,
            'stats' => $stats,
            'isInstalled' => $isInstalled
        ]);
    }

    /**
     * Страница маршрутизации
     */
    public function routing(): string
    {
        try {
            // Получаем информацию о маршрутах
            $routes = $this->getRoutingTable();
            
            // Получаем информацию о сетевых интерфейсах
            $interfaces = $this->getNetworkInterfaces();
            
            // Вычисляем статистику
            $stats = $this->calculateRoutingStats($routes);
            
        } catch (\Exception $e) {
            // Fallback значения в случае ошибки
            $routes = [];
            $interfaces = [];
            $stats = [
                'total_routes' => 0,
                'default_routes' => 0,
                'local_routes' => 0,
                'network_routes' => 0,
                'gateway_routes' => 0
            ];
        }
        
        return $this->render('network/routing', [
            'title' => 'Маршрутизация',
            'currentPage' => 'network',
            'routes' => $routes,
            'interfaces' => $interfaces,
            'stats' => $stats
        ]);
    }

    /**
     * Получает таблицу маршрутизации
     */
    private function getRoutingTable(): array
    {
        $routes = [];
        
        // Получаем маршруты через ip route
        $output = $this->executeCommand('ip route show');
        $lines = explode("\n", $output);
        
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
     * Получает сетевые интерфейсы
     */
    private function getNetworkInterfaces(): array
    {
        $interfaces = [];
        
        // Получаем интерфейсы через ip addr
        $output = $this->executeCommand('ip addr show');
        $lines = explode("\n", $output);
        
        $currentInterface = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Новая строка интерфейса
            if (preg_match('/^\d+:\s+(\w+):/', $line, $matches)) {
                if ($currentInterface) {
                    $interfaces[] = $currentInterface;
                }
                
                $currentInterface = [
                    'name' => $matches[1],
                    'status' => 'down',
                    'ips' => []
                ];
            }
            
            // Статус интерфейса
            if ($currentInterface && strpos($line, 'state UP') !== false) {
                $currentInterface['status'] = 'up';
            }
            
            // IP адрес
            if ($currentInterface && strpos($line, 'inet ') !== false) {
                if (preg_match('/inet\s+([0-9.]+)/', $line, $matches)) {
                    $currentInterface['ips'][] = $matches[1];
                }
            }
        }
        
        // Добавляем последний интерфейс
        if ($currentInterface) {
            $interfaces[] = $currentInterface;
        }
        
        return $interfaces;
    }

    /**
     * Вычисляет статистику маршрутизации
     */
    private function calculateRoutingStats(array $routes): array
    {
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
     * Парсит строку маршрута
     */
    private function parseRouteLine(string $line): ?array
    {
        // Парсим строку маршрута
        if (preg_match('/^default\s+via\s+([0-9.]+)\s+dev\s+(\w+)/', $line, $matches)) {
            return [
                'type' => 'default',
                'destination' => 'default',
                'gateway' => $matches[1],
                'interface' => $matches[2],
                'scope' => 'global',
                'source' => null
            ];
        }
        
        if (preg_match('/^([0-9.]+)\s+via\s+([0-9.]+)\s+dev\s+(\w+)/', $line, $matches)) {
            return [
                'type' => 'network',
                'destination' => $matches[1],
                'gateway' => $matches[2],
                'interface' => $matches[3],
                'scope' => 'global',
                'source' => null
            ];
        }
        
        if (preg_match('/^([0-9.]+)\s+dev\s+(\w+)\s+scope\s+(\w+)/', $line, $matches)) {
            return [
                'type' => 'local',
                'destination' => $matches[1],
                'gateway' => null,
                'interface' => $matches[2],
                'scope' => $matches[3],
                'source' => null
            ];
        }
        
        return null;
    }

    /**
     * Выполняет команду
     */
    private function executeCommand(string $command): string
    {
        $output = shell_exec($command . ' 2>/dev/null');
        return $output ?: '';
    }
}
