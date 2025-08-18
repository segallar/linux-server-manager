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

    // ==================== WIREGUARD API METHODS ====================

    /**
     * API: Получить список WireGuard интерфейсов
     */
    public function getWireGuardInterfaces()
    {
        try {
            $wireguardService = new WireGuardService();
            $interfaces = $wireguardService->getInterfaces();
            
            return $this->json([
                'success' => true,
                'data' => $interfaces
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения интерфейсов WireGuard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Получить информацию о конкретном WireGuard интерфейсе
     */
    public function getWireGuardInterface()
    {
        try {
            $interfaceName = $this->request->get('name');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $wireguardService = new WireGuardService();
            $interface = $wireguardService->getInterfaceInfo($interfaceName);
            
            if (!$interface) {
                return $this->json([
                    'success' => false,
                    'message' => 'Интерфейс не найден'
                ]);
            }
            
            return $this->json([
                'success' => true,
                'data' => $interface
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения информации об интерфейсе: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Запустить WireGuard интерфейс
     */
    public function upWireGuardInterface()
    {
        try {
            $interfaceName = $this->request->get('name');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $wireguardService = new WireGuardService();
            $result = $wireguardService->upInterface($interfaceName);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка запуска интерфейса: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Остановить WireGuard интерфейс
     */
    public function downWireGuardInterface()
    {
        try {
            $interfaceName = $this->request->get('name');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $wireguardService = new WireGuardService();
            $result = $wireguardService->downInterface($interfaceName);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка остановки интерфейса: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Перезапустить WireGuard интерфейс
     */
    public function restartWireGuardInterface()
    {
        try {
            $interfaceName = $this->request->get('name');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $wireguardService = new WireGuardService();
            $result = $wireguardService->restartInterface($interfaceName);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка перезапуска интерфейса: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Получить конфигурацию WireGuard интерфейса
     */
    public function getWireGuardConfig()
    {
        try {
            $interfaceName = $this->request->get('name');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $wireguardService = new WireGuardService();
            $config = $wireguardService->getInterfaceConfig($interfaceName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Конфигурация не найдена'
                ]);
            }
            
            return $this->json([
                'success' => true,
                'data' => [
                    'interface' => $interfaceName,
                    'config' => $config
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения конфигурации: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Обновить конфигурацию WireGuard интерфейса
     */
    public function updateWireGuardConfig()
    {
        try {
            $interfaceName = $this->request->get('name');
            $config = $this->request->post('config');
            
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указана конфигурация'
                ]);
            }

            $wireguardService = new WireGuardService();
            $result = $wireguardService->updateInterfaceConfig($interfaceName, $config);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка обновления конфигурации: ' . $e->getMessage()
            ]);
        }
    }
}
