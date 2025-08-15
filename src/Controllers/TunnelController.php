<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\WireGuardService;

class TunnelController extends Controller
{
    public function ssh()
    {
        return $this->render('tunnels/ssh', [
            'title' => 'SSH туннели',
            'currentPage' => 'tunnels'
        ]);
    }
    
    public function portForwarding()
    {
        return $this->render('tunnels/port-forwarding', [
            'title' => 'Проброс портов',
            'currentPage' => 'tunnels'
        ]);
    }
    
    public function wireguard()
    {
        $wireguardService = new WireGuardService();
        
        $interfaces = $wireguardService->getInterfaces();
        $stats = $wireguardService->getStats();
        $isInstalled = $wireguardService->isInstalled();
        
        return $this->render('tunnels/wireguard', [
            'title' => 'WireGuard',
            'currentPage' => 'tunnels',
            'interfaces' => $interfaces,
            'stats' => $stats,
            'isInstalled' => $isInstalled
        ]);
    }
    
    public function cloudflare()
    {
        $cloudflareService = new \App\Services\CloudflareService();

        $tunnels = $cloudflareService->getTunnels();
        $stats = $cloudflareService->getStats();
        $isInstalled = $cloudflareService->isInstalled();

        return $this->render('tunnels/cloudflare', [
            'title' => 'Cloudflare',
            'currentPage' => 'tunnels',
            'tunnels' => $tunnels,
            'stats' => $stats,
            'isInstalled' => $isInstalled
        ]);
    }
}
