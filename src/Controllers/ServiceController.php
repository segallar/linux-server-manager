<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ServiceService;

class ServiceController extends Controller
{
    public function index()
    {
        $serviceService = new ServiceService();
        $stats = $serviceService->getStats();
        $popularServices = $serviceService->getPopularServices();

        return $this->render('services', [
            'title' => 'Управление сервисами',
            'currentPage' => 'services',
            'stats' => $stats,
            'services' => $popularServices
        ]);
    }

    public function start()
    {
        $serviceName = $this->request->get('service');
        if (!$serviceName) {
            return $this->json(['success' => false, 'message' => 'Не указан сервис']);
        }

        $serviceService = new ServiceService();
        $result = $serviceService->startService($serviceName);
        
        return $this->json($result);
    }

    public function stop()
    {
        $serviceName = $this->request->get('service');
        if (!$serviceName) {
            return $this->json(['success' => false, 'message' => 'Не указан сервис']);
        }

        $serviceService = new ServiceService();
        $result = $serviceService->stopService($serviceName);
        
        return $this->json($result);
    }

    public function restart()
    {
        $serviceName = $this->request->get('service');
        if (!$serviceName) {
            return $this->json(['success' => false, 'message' => 'Не указан сервис']);
        }

        $serviceService = new ServiceService();
        $result = $serviceService->restartService($serviceName);
        
        return $this->json($result);
    }

    public function enable()
    {
        $serviceName = $this->request->get('service');
        if (!$serviceName) {
            return $this->json(['success' => false, 'message' => 'Не указан сервис']);
        }

        $serviceService = new ServiceService();
        $result = $serviceService->enableService($serviceName);
        
        return $this->json($result);
    }

    public function disable()
    {
        $serviceName = $this->request->get('service');
        if (!$serviceName) {
            return $this->json(['success' => false, 'message' => 'Не указан сервис']);
        }

        $serviceService = new ServiceService();
        $result = $serviceService->disableService($serviceName);
        
        return $this->json($result);
    }
}
