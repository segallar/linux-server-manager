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
            'isInstalled' => $isInstalled
        ]);
    }

    /**
     * Страница Cloudflare
     */
    public function cloudflare(): string
    {
        $cloudflareService = new CloudflareService();
        
        $tunnels = $cloudflareService->getTunnels();
        $stats = $cloudflareService->getStats();
        $isInstalled = $cloudflareService->isInstalled();
        
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
        return $this->render('network/routing', [
            'title' => 'Маршрутизация',
            'currentPage' => 'network'
        ]);
    }
}
