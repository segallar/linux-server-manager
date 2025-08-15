<?php

namespace App\Controllers;

use App\Core\Controller;

class ProcessController extends Controller
{
    public function index()
    {
        return $this->render('processes', [
            'title' => 'Управление процессами',
            'currentPage' => 'processes'
        ]);
    }
}
