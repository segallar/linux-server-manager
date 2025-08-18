<?php

namespace App\Controllers\Network;

use App\Core\Controller;
use App\Services\NetworkService;

class NetworkController extends Controller
{
    /**
     * Главная страница сети
     */
    public function index()
    {
        return $this->render('network/index', [
            'title' => 'Сеть',
            'currentPage' => 'network'
        ]);
    }

    /**
     * Страница SSH туннелей
     */
    public function ssh()
    {
        $networkService = new NetworkService();
        
        // Получаем реальные данные о SSH туннелях
        $tunnels = $networkService->getSSHTunnels();
        
        // Получаем информацию о подключениях
        $connections = $networkService->getSSHTunnelConnections();
        
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
    public function portForwarding()
    {
        $networkService = new NetworkService();
        
        // Получаем реальные данные о правилах проброса портов
        $rules = $networkService->getPortForwardingRules();
        
        // Получаем предупреждения безопасности
        $securityWarnings = $networkService->getPortForwardingSecurityWarnings();
        
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
     * Страница маршрутизации
     */
    public function routing()
    {
        $networkService = new NetworkService();
        
        $routes = $networkService->getRoutes();
        $routingStats = $networkService->getRoutingStats();
        $interfaces = $networkService->getInterfaces();
        $dnsInfo = $networkService->getDnsInfo();
        
        return $this->render('network/routing', [
            'title' => 'Маршрутизация',
            'currentPage' => 'network',
            'routes' => $routes,
            'routingStats' => $routingStats,
            'interfaces' => $interfaces,
            'dnsInfo' => $dnsInfo
        ]);
    }
}
