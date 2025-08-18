<?php

namespace App\Controllers\Network;

use App\Core\Controller;
use App\Services\WireGuard\WireGuardService;

class WireGuardController extends Controller
{
    private WireGuardService $wireguardService;

    public function __construct()
    {
        $this->wireguardService = new WireGuardService();
    }

    /**
     * Страница WireGuard
     */
    public function index()
    {
        $interfaces = $this->wireguardService->getInterfaces();
        $stats = $this->wireguardService->getStats();
        
        return $this->render('network/wireguard', [
            'title' => 'WireGuard',
            'currentPage' => 'network',
            'interfaces' => $interfaces,
            'stats' => $stats
        ]);
    }

    /**
     * API: Получить интерфейсы WireGuard
     */
    public function getWireGuardInterfaces()
    {
        try {
            $interfaces = $this->wireguardService->getInterfaces();
            
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
     * API: Получить конкретный интерфейс WireGuard
     */
    public function getWireGuardInterface()
    {
        try {
            $interfaceName = $this->request->get('interface');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $interface = $this->wireguardService->getInterface($interfaceName);
            
            return $this->json([
                'success' => true,
                'data' => $interface
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения интерфейса WireGuard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Поднять интерфейс WireGuard
     */
    public function upWireGuardInterface()
    {
        try {
            $interfaceName = $this->request->post('interface');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $result = $this->wireguardService->upInterface($interfaceName);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка поднятия интерфейса WireGuard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Опустить интерфейс WireGuard
     */
    public function downWireGuardInterface()
    {
        try {
            $interfaceName = $this->request->post('interface');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $result = $this->wireguardService->downInterface($interfaceName);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка опускания интерфейса WireGuard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Перезапустить интерфейс WireGuard
     */
    public function restartWireGuardInterface()
    {
        try {
            $interfaceName = $this->request->post('interface');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $result = $this->wireguardService->restartInterface($interfaceName);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка перезапуска интерфейса WireGuard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Получить конфигурацию WireGuard
     */
    public function getWireGuardConfig()
    {
        try {
            $interfaceName = $this->request->get('interface');
            if (!$interfaceName) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса'
                ]);
            }

            $config = $this->wireguardService->getConfig($interfaceName);
            
            return $this->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения конфигурации WireGuard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Обновить конфигурацию WireGuard
     */
    public function updateWireGuardConfig()
    {
        try {
            $interfaceName = $this->request->post('interface');
            $config = $this->request->post('config');
            
            if (!$interfaceName || !$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указано имя интерфейса или конфигурация'
                ]);
            }

            $result = $this->wireguardService->updateConfig($interfaceName, $config);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка обновления конфигурации WireGuard: ' . $e->getMessage()
            ]);
        }
    }
}
