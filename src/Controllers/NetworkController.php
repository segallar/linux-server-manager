<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\WireGuardService;
use App\Services\CloudflareService;
use App\Services\NetworkService;
use App\Services\SystemService;

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
        $systemService = new SystemService();
        
        $routes = $networkService->getRoutes();
        $stats = $networkService->getRoutingStats();
        $interfaces = $systemService->getNetworkInfo()['interfaces'];
        
        return $this->render('network/routing', [
            'title' => 'Маршрутизация',
            'currentPage' => 'network',
            'routes' => $routes,
            'stats' => $stats,
            'interfaces' => $interfaces
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

    // ==================== SSH TUNNELS API METHODS ====================

    /**
     * API: Получить список SSH туннелей
     */
    public function getSSHTunnels()
    {
        try {
            $networkService = new NetworkService();
            $tunnels = $networkService->getSSHTunnels();
            
            return $this->json([
                'success' => true,
                'data' => $tunnels
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения SSH туннелей: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Создать SSH туннель
     */
    public function createSSHTunnel()
    {
        try {
            $host = $this->request->post('host');
            $port = $this->request->post('port', 22);
            $username = $this->request->post('username');
            $localPort = $this->request->post('local_port');
            $remotePort = $this->request->post('remote_port');
            $name = $this->request->post('name');
            
            if (!$host || !$username || !$localPort || !$remotePort || !$name) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не все обязательные параметры указаны'
                ]);
            }

            $networkService = new NetworkService();
            $result = $networkService->createSSHTunnel($name, $host, $port, $username, $localPort, $remotePort);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка создания SSH туннеля: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Запустить SSH туннель
     */
    public function startSSHTunnel()
    {
        try {
            $tunnelId = $this->request->get('id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $networkService = new NetworkService();
            $result = $networkService->startSSHTunnel($tunnelId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка запуска SSH туннеля: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Остановить SSH туннель
     */
    public function stopSSHTunnel()
    {
        try {
            $tunnelId = $this->request->get('id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $networkService = new NetworkService();
            $result = $networkService->stopSSHTunnel($tunnelId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка остановки SSH туннеля: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Удалить SSH туннель
     */
    public function deleteSSHTunnel()
    {
        try {
            $tunnelId = $this->request->get('id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $networkService = new NetworkService();
            $result = $networkService->deleteSSHTunnel($tunnelId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка удаления SSH туннеля: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== CLOUDFLARE TUNNELS API METHODS ====================

    /**
     * API: Получить список Cloudflare туннелей
     */
    public function getCloudflareTunnels()
    {
        try {
            $cloudflareService = new CloudflareService();
            $tunnels = $cloudflareService->getTunnels();
            
            return $this->json([
                'success' => true,
                'data' => $tunnels
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения Cloudflare туннелей: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Создать Cloudflare туннель
     */
    public function createCloudflareTunnel()
    {
        try {
            $name = $this->request->post('name');
            $url = $this->request->post('url');
            $protocol = $this->request->post('protocol', 'http');
            
            if (!$name || !$url) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не все обязательные параметры указаны'
                ]);
            }

            $cloudflareService = new CloudflareService();
            $result = $cloudflareService->createTunnel($name, $url, $protocol);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка создания Cloudflare туннеля: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Запустить Cloudflare туннель
     */
    public function startCloudflareTunnel()
    {
        try {
            $tunnelId = $this->request->get('id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $cloudflareService = new CloudflareService();
            $result = $cloudflareService->startTunnel($tunnelId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка запуска Cloudflare туннеля: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Остановить Cloudflare туннель
     */
    public function stopCloudflareTunnel()
    {
        try {
            $tunnelId = $this->request->get('id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $cloudflareService = new CloudflareService();
            $result = $cloudflareService->stopTunnel($tunnelId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка остановки Cloudflare туннеля: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Удалить Cloudflare туннель
     */
    public function deleteCloudflareTunnel()
    {
        try {
            $tunnelId = $this->request->get('id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $cloudflareService = new CloudflareService();
            $result = $cloudflareService->deleteTunnel($tunnelId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка удаления Cloudflare туннеля: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== PORT FORWARDING API METHODS ====================

    /**
     * API: Получить правила проброса портов
     */
    public function getPortForwardingRules()
    {
        try {
            $networkService = new NetworkService();
            $rules = $networkService->getPortForwardingRules();
            
            return $this->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения правил проброса портов: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Добавить правило проброса портов
     */
    public function addPortForwardingRule()
    {
        try {
            $name = $this->request->post('name');
            $externalPort = $this->request->post('external_port');
            $internalPort = $this->request->post('internal_port');
            $protocol = $this->request->post('protocol', 'tcp');
            $targetIp = $this->request->post('target_ip', '127.0.0.1');
            
            if (!$name || !$externalPort || !$internalPort) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не все обязательные параметры указаны'
                ]);
            }

            $networkService = new NetworkService();
            $result = $networkService->addPortForwardingRule($name, $externalPort, $internalPort, $protocol, $targetIp);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка добавления правила проброса портов: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Удалить правило проброса портов
     */
    public function deletePortForwardingRule()
    {
        try {
            $ruleId = $this->request->get('id');
            if (!$ruleId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID правила'
                ]);
            }

            $networkService = new NetworkService();
            $result = $networkService->deletePortForwardingRule($ruleId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка удаления правила проброса портов: ' . $e->getMessage()
            ]);
        }
    }
}
