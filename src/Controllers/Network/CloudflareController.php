<?php

namespace App\Controllers\Network;

use App\Core\Controller;
use App\Services\Cloudflare\CloudflareService;

class CloudflareController extends Controller
{
    private CloudflareService $cloudflareService;

    public function __construct()
    {
        $this->cloudflareService = new CloudflareService();
    }

    /**
     * Страница Cloudflare туннелей
     */
    public function index()
    {
        $tunnels = $this->cloudflareService->getTunnels();
        $stats = $this->cloudflareService->getStats();
        
        return $this->render('network/cloudflare', [
            'title' => 'Cloudflare туннели',
            'currentPage' => 'network',
            'tunnels' => $tunnels,
            'stats' => $stats
        ]);
    }

    /**
     * API: Получить Cloudflare туннели
     */
    public function getCloudflareTunnels()
    {
        try {
            $tunnels = $this->cloudflareService->getTunnels();
            
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
            $protocol = $this->request->post('protocol', 'http');
            $hostname = $this->request->post('hostname');
            $service = $this->request->post('service', 'http://localhost:8080');
            
            if (!$name || !$hostname) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не все обязательные параметры указаны'
                ]);
            }

            $result = $this->cloudflareService->createTunnel($name, $protocol, $hostname, $service);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['tunnel'] ?? null
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
            $tunnelId = $this->request->post('tunnel_id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $result = $this->cloudflareService->startTunnel($tunnelId);
            
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
            $tunnelId = $this->request->post('tunnel_id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $result = $this->cloudflareService->stopTunnel($tunnelId);
            
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
            $tunnelId = $this->request->post('tunnel_id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $result = $this->cloudflareService->deleteTunnel($tunnelId);
            
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
}
