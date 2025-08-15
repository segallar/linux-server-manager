<?php

namespace App\Controllers;

use App\Core\Controller;

class SystemController extends Controller
{
    public function index()
    {
        return $this->render('system', [
            'title' => 'Системная информация',
            'currentPage' => 'system'
        ]);
    }
}
