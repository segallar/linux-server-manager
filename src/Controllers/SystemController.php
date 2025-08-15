<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\SystemService;

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
}
