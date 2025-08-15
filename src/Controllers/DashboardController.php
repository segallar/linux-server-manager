<?php

namespace App\Controllers;

class DashboardController
{
    public function index()
    {
        // Получаем содержимое шаблона
        ob_start();
        include __DIR__ . '/../../templates/dashboard.php';
        $content = ob_get_clean();
        
        // Подключаем основной шаблон
        include __DIR__ . '/../../templates/layout.php';
    }
}
