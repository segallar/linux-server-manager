<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\System\SystemService;
use App\Services\Service\ServiceService;
use App\Services\System\ProcessService;

class DashboardController extends Controller
{
    public function index()
    {
        $systemService = new SystemService();
        $serviceService = new ServiceService();
        $processService = new ProcessService();
        
        // Получаем реальные данные о системе
        $cpuInfo = $systemService->getCpuInfo();
        $memoryInfo = $systemService->getMemoryInfo();
        $diskInfo = $systemService->getDiskInfo();
        $networkInfo = $systemService->getNetworkInfo();
        $systemInfo = $systemService->getSystemInfo();
        
        // Формируем статистику
        $stats = [
            'cpu' => [
                'usage' => $cpuInfo['usage'],
                'model' => $cpuInfo['model'] ?? 'Unknown',
                'cores' => $cpuInfo['cores'],
                'load' => implode(', ', $cpuInfo['load'])
            ],
            'memory' => [
                'total' => $memoryInfo['total'],
                'used' => $memoryInfo['used'],
                'free' => $memoryInfo['free'],
                'usage_percent' => $memoryInfo['usage_percent']
            ],
            'disk' => [
                'total' => $diskInfo['total'],
                'used' => $diskInfo['used'],
                'free' => $diskInfo['free'],
                'usage_percent' => $diskInfo['usage_percent']
            ],
            'system' => [
                'os' => $systemInfo['os'],
                'kernel' => $systemInfo['kernel'],
                'uptime' => $systemInfo['uptime'],
                'hostname' => $systemInfo['hostname'],
                'load' => $systemInfo['load'],
                'users' => $systemInfo['users'],
                'date' => $systemInfo['date']
            ],
            'network' => [
                'status' => $networkInfo['status'],
                'active_count' => $networkInfo['active_count'],
                'total_count' => $networkInfo['total_count'],
                'interfaces' => array_values($networkInfo['interfaces'])
            ]
        ];

        // Получаем реальные процессы
        $processes = $processService->getActiveProcesses(10);

        // Получаем реальные сервисы
        $services = $serviceService->getServices();

        return $this->render('dashboard', [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'processes' => $processes,
            'services' => $services
        ]);
    }
}
