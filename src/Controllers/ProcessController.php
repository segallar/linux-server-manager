<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\SystemService;
use App\Services\ProcessService;

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
}
