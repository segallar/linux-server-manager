<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Используем тестовые данные вместо SystemService
        $stats = [
            'cpu_usage' => 25,
            'memory_usage' => 50,
            'disk_usage' => 75,
            'uptime' => '2 дня'
        ];

        $processes = [
            [
                'pid' => 1234,
                'name' => 'nginx',
                'cpu' => 2.5,
                'memory' => 15.2,
                'status' => 'running'
            ],
            [
                'pid' => 5678,
                'name' => 'php-fpm',
                'cpu' => 1.8,
                'memory' => 25.1,
                'status' => 'running'
            ]
        ];

        $services = [
            [
                'name' => 'nginx',
                'status' => 'active',
                'enabled' => true
            ],
            [
                'name' => 'php8.3-fpm',
                'status' => 'active',
                'enabled' => true
            ]
        ];

        return $this->render('dashboard', [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'processes' => $processes,
            'services' => $services
        ]);
    }
}
