<?php

namespace App\Controllers\Network;

use App\Core\Controller;
use App\Services\Network\SSHTunnelService;

class SSHTunnelApiController extends Controller
{
    private SSHTunnelService $sshTunnelService;

    public function __construct()
    {
        $this->sshTunnelService = new SSHTunnelService();
    }

    /**
     * API: Получить список SSH туннелей
     */
    public function getSSHTunnels()
    {
        try {
            $tunnels = $this->sshTunnelService->getSSHTunnels();
            
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
            $name = $this->request->post('name');
            $host = $this->request->post('host');
            $port = $this->request->post('port');
            $username = $this->request->post('username');
            $localPort = $this->request->post('local_port');
            $remotePort = $this->request->post('remote_port');
            
            if (!$name || !$host || !$port || !$username || !$localPort || !$remotePort) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не все обязательные параметры указаны'
                ]);
            }

            $result = $this->sshTunnelService->createSSHTunnel(
                $name, 
                $host, 
                (int)$port, 
                $username, 
                (int)$localPort, 
                (int)$remotePort
            );
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['tunnel'] ?? null
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
            $tunnelId = $this->request->post('tunnel_id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $result = $this->sshTunnelService->startSSHTunnel($tunnelId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['tunnel'] ?? null
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
            $tunnelId = $this->request->post('tunnel_id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $result = $this->sshTunnelService->stopSSHTunnel($tunnelId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['tunnel'] ?? null
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
            $tunnelId = $this->request->post('tunnel_id');
            if (!$tunnelId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID туннеля'
                ]);
            }

            $result = $this->sshTunnelService->deleteSSHTunnel($tunnelId);
            
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

    /**
     * API: Получить соединения SSH туннелей
     */
    public function getSSHTunnelConnections()
    {
        try {
            $connections = $this->sshTunnelService->getSSHTunnelConnections();
            
            return $this->json([
                'success' => true,
                'data' => $connections
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения соединений SSH туннелей: ' . $e->getMessage()
            ]);
        }
    }
}
