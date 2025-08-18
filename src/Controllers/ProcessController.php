<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\System\SystemService;
use App\Services\System\ProcessService;

class ProcessController extends Controller
{
    public function index()
    {
        $systemService = new SystemService();
        $processService = new ProcessService();
        
        $stats = $systemService->getProcessStats();
        $processes = $systemService->getAllProcesses();
        
        // Ограничиваем количество процессов для отображения
        $processes = array_slice($processes, 0, 100);

        return $this->render('processes', [
            'title' => 'Управление процессами',
            'currentPage' => 'processes',
            'stats' => $stats,
            'processes' => $processes
        ]);
    }

    /**
     * API: Получить список процессов
     */
    public function getProcesses()
    {
        try {
            $systemService = new SystemService();
            $processes = $systemService->getAllProcesses();
            
            // Ограничиваем количество для API
            $limit = (int)($this->request->get('limit', 50));
            $processes = array_slice($processes, 0, $limit);
            
            return $this->json([
                'success' => true,
                'data' => $processes
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения списка процессов: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Завершить процесс
     */
    public function killProcess()
    {
        try {
            $processId = $this->request->get('id');
            if (!$processId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID процесса'
                ]);
            }

            $processService = new ProcessService();
            $result = $processService->killProcess($processId);
            
            return $this->json([
                'success' => true,
                'message' => 'Процесс успешно завершен',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка завершения процесса: ' . $e->getMessage()
            ]);
        }
    }
}
