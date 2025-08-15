<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return $this->render('dashboard', [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard'
        ]);
    }
}
