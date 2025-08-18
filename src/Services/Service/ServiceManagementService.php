<?php

namespace App\Services\Service;

use App\Abstracts\BaseService;
use App\Interfaces\ServiceManagementServiceInterface;

class ServiceManagementService extends BaseService implements ServiceManagementServiceInterface
{
    /**
     * Запустить сервис
     */
    public function startService(string $serviceName): array
    {
        try {
            if (empty($serviceName)) {
                return ['success' => false, 'message' => 'Имя сервиса не указано'];
            }

            $result = $this->safeExecute("systemctl start $serviceName");
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка запуска сервиса: ' . $result['error']];
            }

            return ['success' => true, 'message' => "Сервис '$serviceName' запущен"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Остановить сервис
     */
    public function stopService(string $serviceName): array
    {
        try {
            if (empty($serviceName)) {
                return ['success' => false, 'message' => 'Имя сервиса не указано'];
            }

            $result = $this->safeExecute("systemctl stop $serviceName");
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка остановки сервиса: ' . $result['error']];
            }

            return ['success' => true, 'message' => "Сервис '$serviceName' остановлен"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Перезапустить сервис
     */
    public function restartService(string $serviceName): array
    {
        try {
            if (empty($serviceName)) {
                return ['success' => false, 'message' => 'Имя сервиса не указано'];
            }

            $result = $this->safeExecute("systemctl restart $serviceName");
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка перезапуска сервиса: ' . $result['error']];
            }

            return ['success' => true, 'message' => "Сервис '$serviceName' перезапущен"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Включить сервис
     */
    public function enableService(string $serviceName): array
    {
        try {
            if (empty($serviceName)) {
                return ['success' => false, 'message' => 'Имя сервиса не указано'];
            }

            $result = $this->safeExecute("systemctl enable $serviceName");
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка включения сервиса: ' . $result['error']];
            }

            return ['success' => true, 'message' => "Сервис '$serviceName' включен"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Отключить сервис
     */
    public function disableService(string $serviceName): array
    {
        try {
            if (empty($serviceName)) {
                return ['success' => false, 'message' => 'Имя сервиса не указано'];
            }

            $result = $this->safeExecute("systemctl disable $serviceName");
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка отключения сервиса: ' . $result['error']];
            }

            return ['success' => true, 'message' => "Сервис '$serviceName' отключен"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }
}
