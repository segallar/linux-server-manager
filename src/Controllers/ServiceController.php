<?php

namespace App\Controllers;

use App\Core\Controller;

class ServiceController extends Controller
{
    public function index()
    {
        return $this->render('services', [
            'title' => 'Управление сервисами',
            'currentPage' => 'services'
        ]);
    }
}
