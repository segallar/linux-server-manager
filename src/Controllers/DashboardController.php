<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Используем тестовые данные в правильном формате
        $stats = [
            'cpu' => [
                'usage' => 25,
                'model' => 'Intel(R) Core(TM) i7-8700K CPU @ 3.70GHz',
                'cores' => 8,
                'load' => [1.2, 1.5, 1.8]
            ],
            'memory' => [
                'total' => '16GB',
                'used' => '8GB',
                'free' => '8GB',
                'usage_percent' => 50
            ],
            'disk' => [
                'total' => '500GB',
                'used' => '375GB',
                'free' => '125GB',
                'usage_percent' => 75
            ],
            'system' => [
                'os' => 'Ubuntu 22.04.3 LTS',
                'kernel' => '5.15.0-88-generic',
                'uptime' => '2 дня, 5 часов, 30 минут',
                'hostname' => 'sirocco.romansegalla.online',
                'load' => '1.2, 1.5, 1.8',
                'users' => 3,
                'date' => date('Y-m-d H:i:s')
            ],
            'network' => [
                'interfaces' => [
                    [
                        'name' => 'eth0',
                        'status' => 'up',
                        'ips' => ['192.168.1.100', '10.0.0.50']
                    ],
                    [
                        'name' => 'lo',
                        'status' => 'up',
                        'ips' => ['127.0.0.1']
                    ]
                ]
            ]
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
            ],
            [
                'pid' => 9012,
                'name' => 'sshd',
                'cpu' => 0.5,
                'memory' => 8.3,
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
            ],
            [
                'name' => 'ssh',
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
