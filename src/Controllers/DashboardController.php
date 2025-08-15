<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\SystemService;

class DashboardController extends Controller
{
    public function index()
    {
        $systemService = new SystemService();
        $stats = $systemService->getStats();

        return $this->render('dashboard', [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'stats' => $stats
        ]);
    }
}
