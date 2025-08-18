<?php

namespace App\Services\Network;

use App\Abstracts\BaseService;
use App\Interfaces\NetworkRoutingServiceInterface;

class NetworkRoutingService extends BaseService implements NetworkRoutingServiceInterface
{
    /**
     * Получить таблицу маршрутов
     */
    public function getRoutes(): array
    {
        $output = $this->executeCommand('ip route show');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $routes = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $route = $this->parseRouteLine($line);
            if ($route) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /**
     * Получить статистику маршрутизации
     */
    public function getRoutingStats(): array
    {
        $routes = $this->getRoutes();
        
        $stats = [
            'total_routes' => count($routes),
            'default_routes' => 0,
            'local_routes' => 0,
            'network_routes' => 0,
            'gateway_routes' => 0
        ];

        foreach ($routes as $route) {
            switch ($route['type']) {
                case 'default':
                    $stats['default_routes']++;
                    break;
                case 'local':
                    $stats['local_routes']++;
                    break;
                case 'network':
                    $stats['network_routes']++;
                    break;
                case 'gateway':
                    $stats['gateway_routes']++;
                    break;
            }
        }

        return $stats;
    }

    /**
     * Добавить маршрут
     */
    public function addRoute(string $destination, string $gateway, string $interface): array
    {
        try {
            if (empty($destination) || empty($gateway) || empty($interface)) {
                return ['success' => false, 'message' => 'Все параметры обязательны'];
            }

            $command = "ip route add $destination via $gateway dev $interface";
            $result = $this->safeExecute($command);
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка добавления маршрута: ' . $result['error']];
            }

            return ['success' => true, 'message' => "Маршрут добавлен: $destination via $gateway dev $interface"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Удалить маршрут
     */
    public function deleteRoute(string $destination, string $gateway = '', string $interface = ''): array
    {
        try {
            if (empty($destination)) {
                return ['success' => false, 'message' => 'Назначение обязательно'];
            }

            $command = "ip route del $destination";
            if (!empty($gateway)) {
                $command .= " via $gateway";
            }
            if (!empty($interface)) {
                $command .= " dev $interface";
            }

            $result = $this->safeExecute($command);
            
            if (!$result['success']) {
                return ['success' => false, 'message' => 'Ошибка удаления маршрута: ' . $result['error']];
            }

            return ['success' => true, 'message' => "Маршрут удален: $destination"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Парсить строку маршрута
     */
    protected function parseRouteLine(string $line): ?array
    {
        // Пример строки: "default via 192.168.1.1 dev eth0 proto dhcp metric 100"
        $parts = explode(' ', $line);
        
        if (count($parts) < 3) {
            return null;
        }

        $route = [
            'destination' => $parts[0],
            'type' => 'unknown',
            'gateway' => '',
            'interface' => '',
            'protocol' => '',
            'metric' => ''
        ];

        // Определяем тип маршрута
        if ($parts[0] === 'default') {
            $route['type'] = 'default';
        } elseif (strpos($parts[0], '127.') === 0) {
            $route['type'] = 'local';
        } elseif (strpos($parts[0], '169.254.') === 0) {
            $route['type'] = 'link-local';
        } else {
            $route['type'] = 'network';
        }

        // Парсим остальные части
        for ($i = 1; $i < count($parts); $i++) {
            switch ($parts[$i]) {
                case 'via':
                    if (isset($parts[$i + 1])) {
                        $route['gateway'] = $parts[$i + 1];
                        $route['type'] = 'gateway';
                    }
                    break;
                case 'dev':
                    if (isset($parts[$i + 1])) {
                        $route['interface'] = $parts[$i + 1];
                    }
                    break;
                case 'proto':
                    if (isset($parts[$i + 1])) {
                        $route['protocol'] = $parts[$i + 1];
                    }
                    break;
                case 'metric':
                    if (isset($parts[$i + 1])) {
                        $route['metric'] = $parts[$i + 1];
                    }
                    break;
            }
        }

        return $route;
    }
}
