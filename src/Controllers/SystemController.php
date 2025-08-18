<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\System\SystemService;

class SystemController extends Controller
{
    public function index()
    {
        $systemService = new SystemService();
        $info = $systemService->getDetailedSystemInfo();

        return $this->render('system', [
            'title' => 'Системная информация',
            'currentPage' => 'system',
            'info' => $info
        ]);
    }

    /**
     * API: Получить системную информацию
     */
    public function getSystemInfo()
    {
        try {
            $systemService = new SystemService();
            $info = $systemService->getDetailedSystemInfo();
            
            return $this->json([
                'success' => true,
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения системной информации: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Получить статистику системы в реальном времени
     */
    public function getSystemStats()
    {
        try {
            $systemService = new SystemService();
            $stats = $systemService->getSystemStats();
            
            return $this->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения статистики: ' . $e->getMessage()
            ]);
        }
    }
}
