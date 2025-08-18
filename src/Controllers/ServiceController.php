<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\Service\ServiceService;

class ServiceController extends Controller
{
    public function index()
    {
        global $app;
        $cache = $app->cache;
        
        // Пытаемся получить данные из кэша
        $cacheKey = 'services_data';
        $cachedData = $cache->get($cacheKey);
        
        if ($cachedData !== null) {
            // Используем кэшированные данные
            $stats = $cachedData['stats'];
            $popularServices = $cachedData['services'];
            $fromCache = true;
        } else {
            // Получаем свежие данные
            $serviceService = new ServiceService();
            $stats = $serviceService->getStats();
            $popularServices = $serviceService->getPopularServices();
            
            // Сохраняем в кэш на 2 минуты (сервисы могут часто меняться)
            $cache->set($cacheKey, [
                'stats' => $stats,
                'services' => $popularServices
            ], 120);
            
            $fromCache = false;
        }

        return $this->render('services', [
            'title' => 'Управление сервисами',
            'currentPage' => 'services',
            'stats' => $stats,
            'services' => $popularServices,
            'fromCache' => $fromCache
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
