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
        
        try {
            // Получаем полную статистику системы
            $systemStats = $systemService->getStats();
            
            // Формируем статистику для dashboard
            $stats = [
                'cpu' => [
                    'usage' => $systemStats['cpu']['usage'] ?? 0,
                    'model' => $systemStats['cpu']['model'] ?? 'Unknown',
                    'cores' => $systemStats['cpu']['cores'] ?? 0,
                    'load' => $systemStats['cpu']['load'] ?? [0, 0, 0],
                    'frequency' => $systemStats['cpu']['frequency'] ?? 'Unknown',
                    'cache' => $systemStats['cpu']['cache'] ?? 'Unknown'
                ],
                'memory' => [
                    'total' => $systemStats['memory']['total'] ?? '0',
                    'used' => $systemStats['memory']['used'] ?? '0',
                    'free' => $systemStats['memory']['free'] ?? '0',
                    'available' => $systemStats['memory']['available'] ?? '0',
                    'usage_percent' => $systemStats['memory']['usage_percent'] ?? 0
                ],
                'disk' => $systemStats['disk'] ?? [],
                'system' => [
                    'os' => $systemStats['system']['os'] ?? 'Unknown',
                    'kernel' => $systemStats['system']['kernel'] ?? 'Unknown',
                    'uptime' => $systemStats['system']['uptime'] ?? 'Unknown',
                    'hostname' => $systemStats['system']['hostname'] ?? 'Unknown',
                    'domain' => $systemStats['system']['domain'] ?? 'Unknown',
                    'load' => $systemStats['system']['load'] ?? '0.00, 0.00, 0.00',
                    'users' => $systemStats['system']['users'] ?? '0',
                    'date' => $systemStats['system']['date'] ?? date('Y-m-d H:i:s'),
                    'architecture' => $systemStats['system']['architecture'] ?? 'Unknown',
                    'timezone' => $systemStats['system']['timezone'] ?? 'UTC'
                ],
                'network' => [
                    'online' => $systemStats['network']['online'] ?? false,
                    'status' => $systemStats['network']['online'] ? 'online' : 'offline',
                    'active_count' => $systemStats['network']['active_count'] ?? 0,
                    'total_count' => $systemStats['network']['total_count'] ?? 0,
                    'interfaces' => $systemStats['network']['interfaces'] ?? []
                ],
                'process_count' => $systemStats['process_count'] ?? 0
            ];
        } catch (\Exception $e) {
            // Fallback значения в случае ошибки
            $stats = [
                'cpu' => [
                    'usage' => 0,
                    'model' => 'Unknown',
                    'cores' => 0,
                    'load' => [0, 0, 0],
                    'frequency' => 'Unknown',
                    'cache' => 'Unknown'
                ],
                'memory' => [
                    'total' => '0',
                    'used' => '0',
                    'free' => '0',
                    'available' => '0',
                    'usage_percent' => 0
                ],
                'disk' => [],
                'system' => [
                    'os' => 'Unknown',
                    'kernel' => 'Unknown',
                    'uptime' => 'Unknown',
                    'hostname' => 'Unknown',
                    'domain' => 'Unknown',
                    'load' => '0.00, 0.00, 0.00',
                    'users' => '0',
                    'date' => date('Y-m-d H:i:s'),
                    'architecture' => 'Unknown',
                    'timezone' => 'UTC'
                ],
                'network' => [
                    'online' => false,
                    'status' => 'offline',
                    'active_count' => 0,
                    'total_count' => 0,
                    'interfaces' => []
                ],
                'process_count' => 0
            ];
        }

        try {
            // Получаем реальные процессы
            $processes = $processService->getActiveProcesses(10);
        } catch (\Exception $e) {
            $processes = [];
        }

        try {
            // Получаем реальные сервисы
            $services = $serviceService->getServices();
        } catch (\Exception $e) {
            $services = [];
        }

        return $this->render('dashboard', [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'processes' => $processes,
            'services' => $services
        ]);
    }
}
