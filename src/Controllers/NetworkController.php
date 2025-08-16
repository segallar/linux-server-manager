<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\WireGuardService;
use App\Services\CloudflareService;
use App\Services\NetworkService;

class NetworkController extends Controller
{
    public function ssh()
    {
        return $this->render('network/ssh', [
            'title' => 'SSH туннели',
            'currentPage' => 'network'
        ]);
    }
    
    public function portForwarding()
    {
        return $this->render('network/port-forwarding', [
            'title' => 'Проброс портов',
            'currentPage' => 'network'
        ]);
    }
    
    public function wireguard()
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
    
    public function cloudflare()
    {
        global $app;
        $cache = $app->cache;
        
        // Пытаемся получить данные из кэша
        $cacheKey = 'cloudflare_data';
        $cachedData = $cache->get($cacheKey);
        
        if ($cachedData !== null) {
            // Используем кэшированные данные
            $tunnels = $cachedData['tunnels'];
            $stats = $cachedData['stats'];
            $isInstalled = $cachedData['isInstalled'];
            $fromCache = true;
        } else {
            // Получаем свежие данные
            $cloudflareService = new CloudflareService();
            
            $tunnels = $cloudflareService->getTunnels();
            $stats = $cloudflareService->getStats();
            $isInstalled = $cloudflareService->isInstalled();
            
            // Сохраняем в кэш на 5 минут
            $cache->set($cacheKey, [
                'tunnels' => $tunnels,
                'stats' => $stats,
                'isInstalled' => $isInstalled
            ], 300);
            
            $fromCache = false;
        }

        return $this->render('network/cloudflare', [
            'title' => 'Cloudflare',
            'currentPage' => 'network',
            'tunnels' => $tunnels,
            'stats' => $stats,
            'isInstalled' => $isInstalled,
            'fromCache' => $fromCache
        ]);
    }

    public function routing()
    {
        $networkService = new NetworkService();
        
        $routes = $networkService->getRoutes();
        $stats = $networkService->getRoutingStats();
        
        return $this->render('network/routing', [
            'title' => 'Маршрутизация',
            'currentPage' => 'network',
            'routes' => $routes,
            'stats' => $stats
        ]);
    }
}
