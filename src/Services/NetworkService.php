<?php

namespace App\Services;

class NetworkService
{
    /**
     * Получить таблицу маршрутов
     */
    public function getRoutes(): array
    {
        $output = shell_exec('ip route show 2>/dev/null');
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
     * Получить информацию о сетевых интерфейсах
     */
    public function getInterfaces(): array
    {
        $output = shell_exec('ip addr show 2>/dev/null');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $interfaces = [];
        $currentInterface = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Ищем строку с интерфейсом
            if (preg_match('/^\d+:\s+(\w+):/', $line, $matches)) {
                $name = $matches[1];
                if ($name !== 'lo') { // Исключаем loopback
                    $currentInterface = [
                        'name' => $name,
                        'status' => 'down',
                        'ips' => [],
                        'mac' => '',
                        'mtu' => ''
                    ];
                    $interfaces[] = $currentInterface;
                }
            }
            
            // Статус интерфейса
            elseif ($currentInterface && strpos($line, 'state UP') !== false) {
                $currentInterface['status'] = 'up';
            }
            
            // MAC адрес
            elseif ($currentInterface && preg_match('/link\/\w+\s+([a-fA-F0-9:]+)/', $line, $matches)) {
                $currentInterface['mac'] = $matches[1];
            }
            
            // MTU
            elseif ($currentInterface && preg_match('/mtu\s+(\d+)/', $line, $matches)) {
                $currentInterface['mtu'] = $matches[1];
            }
            
            // IP адреса
            elseif ($currentInterface && preg_match('/inet\s+([0-9.]+)/', $line, $matches)) {
                $currentInterface['ips'][] = $matches[1];
            }
            elseif ($currentInterface && preg_match('/inet6\s+([a-fA-F0-9:]+)/', $line, $matches)) {
                $currentInterface['ips'][] = $matches[1];
            }
        }

        return $interfaces;
    }

    /**
     * Получить информацию о DNS
     */
    public function getDnsInfo(): array
    {
        $dns = [];
        
        // Читаем resolv.conf
        if (file_exists('/etc/resolv.conf')) {
            $resolv = file_get_contents('/etc/resolv.conf');
            preg_match_all('/nameserver\s+([0-9.]+)/', $resolv, $matches);
            $dns['nameservers'] = $matches[1] ?? [];
        }

        // Читаем hosts
        if (file_exists('/etc/hosts')) {
            $hosts = file_get_contents('/etc/hosts');
            $dns['hosts_count'] = substr_count($hosts, "\n") - 2; // Исключаем заголовки
        }

        return $dns;
    }

    /**
     * Получить информацию о сетевых соединениях
     */
    public function getConnections(): array
    {
        $output = shell_exec('ss -tuln 2>/dev/null');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $connections = [];

        // Пропускаем заголовок
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $connection = $this->parseConnectionLine($line);
            if ($connection) {
                $connections[] = $connection;
            }
        }

        return $connections;
    }

    /**
     * Парсить строку маршрута
     */
    private function parseRouteLine(string $line): ?array
    {
        // Примеры строк:
        // default via 192.168.1.1 dev eth0
        // 192.168.1.0/24 dev eth0 proto kernel scope link src 192.168.1.100
        // 10.0.0.0/8 via 192.168.1.1 dev eth0

        $route = [
            'destination' => '',
            'gateway' => '',
            'interface' => '',
            'type' => 'unknown',
            'scope' => '',
            'source' => ''
        ];

        // Определяем тип маршрута
        if (strpos($line, 'default') === 0) {
            $route['type'] = 'default';
            $route['destination'] = 'default';
        } elseif (strpos($line, 'link') !== false) {
            $route['type'] = 'local';
        } elseif (strpos($line, 'via') !== false) {
            $route['type'] = 'gateway';
        } else {
            $route['type'] = 'network';
        }

        // Парсим destination
        if (preg_match('/^([0-9.]+(?:\/[0-9]+)?)/', $line, $matches)) {
            $route['destination'] = $matches[1];
        }

        // Парсим gateway
        if (preg_match('/via\s+([0-9.]+)/', $line, $matches)) {
            $route['gateway'] = $matches[1];
        }

        // Парсим interface
        if (preg_match('/dev\s+(\w+)/', $line, $matches)) {
            $route['interface'] = $matches[1];
        }

        // Парсим scope
        if (preg_match('/scope\s+(\w+)/', $line, $matches)) {
            $route['scope'] = $matches[1];
        }

        // Парсим source
        if (preg_match('/src\s+([0-9.]+)/', $line, $matches)) {
            $route['source'] = $matches[1];
        }

        return $route;
    }

    /**
     * Парсить строку соединения
     */
    private function parseConnectionLine(string $line): ?array
    {
        $parts = preg_split('/\s+/', $line);
        if (count($parts) < 5) {
            return null;
        }

        return [
            'protocol' => $parts[0],
            'state' => $parts[1],
            'local_address' => $parts[3],
            'remote_address' => $parts[4]
        ];
    }

    /**
     * Добавить маршрут
     */
    public function addRoute(string $destination, string $gateway, string $interface): array
    {
        $command = "sudo ip route add $destination via $gateway dev $interface 2>&1";
        $output = shell_exec($command);
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Маршрут добавлен" : "Ошибка: $output"
        ];
    }

    /**
     * Удалить маршрут
     */
    public function deleteRoute(string $destination, string $gateway = '', string $interface = ''): array
    {
        $command = "sudo ip route del $destination";
        if ($gateway) {
            $command .= " via $gateway";
        }
        if ($interface) {
            $command .= " dev $interface";
        }
        $command .= " 2>&1";
        
        $output = shell_exec($command);
        $success = empty($output);
        
        return [
            'success' => $success,
            'message' => $success ? "Маршрут удален" : "Ошибка: $output"
        ];
    }

    /**
     * Получить статистику сетевого трафика
     */
    public function getTrafficStats(): array
    {
        $output = shell_exec('cat /proc/net/dev 2>/dev/null');
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $stats = [];

        // Пропускаем заголовки
        for ($i = 2; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $interface = $this->parseTrafficLine($line);
            if ($interface) {
                $stats[] = $interface;
            }
        }

        return $stats;
    }

    /**
     * Парсить строку статистики трафика
     */
    private function parseTrafficLine(string $line): ?array
    {
        // Формат: interface | rx_bytes rx_packets rx_errors rx_dropped | tx_bytes tx_packets tx_errors tx_dropped
        $parts = preg_split('/\s*:\s*/', $line);
        if (count($parts) < 2) {
            return null;
        }

        $interface = trim($parts[0]);
        $data = preg_split('/\s+/', trim($parts[1]));

        if (count($data) < 8) {
            return null;
        }

        return [
            'interface' => $interface,
            'rx_bytes' => (int)$data[0],
            'rx_packets' => (int)$data[1],
            'rx_errors' => (int)$data[2],
            'rx_dropped' => (int)$data[3],
            'tx_bytes' => (int)$data[4],
            'tx_packets' => (int)$data[5],
            'tx_errors' => (int)$data[6],
            'tx_dropped' => (int)$data[7]
        ];
    }

    // ==================== SSH TUNNELS METHODS ====================

    /**
     * Получить список SSH туннелей
     */
    public function getSSHTunnels(): array
    {
        $tunnels = [];
        $configFile = '/etc/ssh-tunnels.conf';
        
        if (file_exists($configFile)) {
            $config = parse_ini_file($configFile, true);
            foreach ($config as $id => $tunnel) {
                $tunnels[] = [
                    'id' => $id,
                    'name' => $tunnel['name'] ?? $id,
                    'host' => $tunnel['host'] ?? '',
                    'port' => $tunnel['port'] ?? 22,
                    'username' => $tunnel['username'] ?? '',
                    'local_port' => $tunnel['local_port'] ?? '',
                    'remote_port' => $tunnel['remote_port'] ?? '',
                    'status' => $this->getSSHTunnelStatus($id),
                    'created' => $tunnel['created'] ?? '',
                    'last_used' => $tunnel['last_used'] ?? ''
                ];
            }
        }
        
        return $tunnels;
    }

    /**
     * Создать SSH туннель
     */
    public function createSSHTunnel(string $name, string $host, int $port, string $username, int $localPort, int $remotePort): array
    {
        try {
            $id = 'tunnel_' . time() . '_' . rand(1000, 9999);
            $configFile = '/etc/ssh-tunnels.conf';
            
            // Создаем конфигурацию туннеля
            $config = [
                'name' => $name,
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'local_port' => $localPort,
                'remote_port' => $remotePort,
                'created' => date('Y-m-d H:i:s'),
                'last_used' => ''
            ];
            
            // Читаем существующую конфигурацию
            $existingConfig = [];
            if (file_exists($configFile)) {
                $existingConfig = parse_ini_file($configFile, true);
            }
            
            // Добавляем новый туннель
            $existingConfig[$id] = $config;
            
            // Записываем конфигурацию
            $this->writeIniFile($configFile, $existingConfig);
            
            return [
                'success' => true,
                'message' => "SSH туннель '$name' создан успешно",
                'data' => ['id' => $id]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка создания SSH туннеля: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Запустить SSH туннель
     */
    public function startSSHTunnel(string $tunnelId): array
    {
        try {
            $configFile = '/etc/ssh-tunnels.conf';
            
            if (!file_exists($configFile)) {
                return ['success' => false, 'message' => 'Файл конфигурации не найден'];
            }
            
            $config = parse_ini_file($configFile, true);
            if (!isset($config[$tunnelId])) {
                return ['success' => false, 'message' => 'Туннель не найден'];
            }
            
            $tunnel = $config[$tunnelId];
            
            // Формируем команду SSH туннеля
            $command = sprintf(
                'ssh -f -N -L %d:localhost:%d %s@%s -p %d',
                $tunnel['local_port'],
                $tunnel['remote_port'],
                $tunnel['username'],
                $tunnel['host'],
                $tunnel['port']
            );
            
            // Запускаем туннель
            $output = shell_exec($command . ' 2>&1');
            $exitCode = $this->getLastExitCode();
            
            if ($exitCode === 0) {
                // Обновляем время последнего использования
                $config[$tunnelId]['last_used'] = date('Y-m-d H:i:s');
                $this->writeIniFile($configFile, $config);
                
                return [
                    'success' => true,
                    'message' => "SSH туннель '{$tunnel['name']}' запущен"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ошибка запуска SSH туннеля: ' . $output
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка запуска SSH туннеля: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Остановить SSH туннель
     */
    public function stopSSHTunnel(string $tunnelId): array
    {
        try {
            $configFile = '/etc/ssh-tunnels.conf';
            
            if (!file_exists($configFile)) {
                return ['success' => false, 'message' => 'Файл конфигурации не найден'];
            }
            
            $config = parse_ini_file($configFile, true);
            if (!isset($config[$tunnelId])) {
                return ['success' => false, 'message' => 'Туннель не найден'];
            }
            
            $tunnel = $config[$tunnelId];
            
            // Ищем и останавливаем процессы SSH туннеля
            $command = sprintf(
                'pkill -f "ssh.*-L %d:localhost:%d.*%s@%s"',
                $tunnel['local_port'],
                $tunnel['remote_port'],
                $tunnel['username'],
                $tunnel['host']
            );
            
            shell_exec($command);
            
            return [
                'success' => true,
                'message' => "SSH туннель '{$tunnel['name']}' остановлен"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка остановки SSH туннеля: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Удалить SSH туннель
     */
    public function deleteSSHTunnel(string $tunnelId): array
    {
        try {
            $configFile = '/etc/ssh-tunnels.conf';
            
            if (!file_exists($configFile)) {
                return ['success' => false, 'message' => 'Файл конфигурации не найден'];
            }
            
            $config = parse_ini_file($configFile, true);
            if (!isset($config[$tunnelId])) {
                return ['success' => false, 'message' => 'Туннель не найден'];
            }
            
            $tunnel = $config[$tunnelId];
            
            // Сначала останавливаем туннель
            $this->stopSSHTunnel($tunnelId);
            
            // Удаляем из конфигурации
            unset($config[$tunnelId]);
            $this->writeIniFile($configFile, $config);
            
            return [
                'success' => true,
                'message' => "SSH туннель '{$tunnel['name']}' удален"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка удаления SSH туннеля: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Получить статус SSH туннеля
     */
    private function getSSHTunnelStatus(string $tunnelId): string
    {
        $configFile = '/etc/ssh-tunnels.conf';
        
        if (!file_exists($configFile)) {
            return 'unknown';
        }
        
        $config = parse_ini_file($configFile, true);
        if (!isset($config[$tunnelId])) {
            return 'unknown';
        }
        
        $tunnel = $config[$tunnelId];
        
        // Проверяем, есть ли активные процессы SSH туннеля
        $command = sprintf(
            'pgrep -f "ssh.*-L %d:localhost:%d.*%s@%s"',
            $tunnel['local_port'],
            $tunnel['remote_port'],
            $tunnel['username'],
            $tunnel['host']
        );
        
        $output = shell_exec($command);
        return $output ? 'running' : 'stopped';
    }

    /**
     * Записать INI файл
     */
    private function writeIniFile(string $filename, array $data): bool
    {
        $content = '';
        
        foreach ($data as $section => $values) {
            $content .= "[$section]\n";
            foreach ($values as $key => $value) {
                $content .= "$key = \"$value\"\n";
            }
            $content .= "\n";
        }
        
        return file_put_contents($filename, $content) !== false;
    }

    /**
     * Получить код последнего выхода
     */
    private function getLastExitCode(): int
    {
        return $this->isWindows() ? 0 : (int)shell_exec('echo $?');
    }

    /**
     * Проверить, является ли система Windows
     */
    private function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    // ==================== PORT FORWARDING METHODS ====================

    /**
     * Получить правила проброса портов
     */
    public function getPortForwardingRules(): array
    {
        $rules = [];
        $configFile = '/etc/port-forwarding.conf';
        
        if (file_exists($configFile)) {
            $config = parse_ini_file($configFile, true);
            foreach ($config as $id => $rule) {
                $rules[] = [
                    'id' => $id,
                    'name' => $rule['name'] ?? $id,
                    'external_port' => $rule['external_port'] ?? '',
                    'internal_port' => $rule['internal_port'] ?? '',
                    'protocol' => $rule['protocol'] ?? 'tcp',
                    'target_ip' => $rule['target_ip'] ?? '127.0.0.1',
                    'status' => $this->getPortForwardingStatus($id),
                    'created' => $rule['created'] ?? '',
                    'last_used' => $rule['last_used'] ?? ''
                ];
            }
        }
        
        return $rules;
    }

    /**
     * Добавить правило проброса портов
     */
    public function addPortForwardingRule(string $name, int $externalPort, int $internalPort, string $protocol = 'tcp', string $targetIp = '127.0.0.1'): array
    {
        try {
            $id = 'rule_' . time() . '_' . rand(1000, 9999);
            $configFile = '/etc/port-forwarding.conf';
            
            // Создаем конфигурацию правила
            $config = [
                'name' => $name,
                'external_port' => $externalPort,
                'internal_port' => $internalPort,
                'protocol' => $protocol,
                'target_ip' => $targetIp,
                'created' => date('Y-m-d H:i:s'),
                'last_used' => ''
            ];
            
            // Читаем существующую конфигурацию
            $existingConfig = [];
            if (file_exists($configFile)) {
                $existingConfig = parse_ini_file($configFile, true);
            }
            
            // Добавляем новое правило
            $existingConfig[$id] = $config;
            
            // Записываем конфигурацию
            $this->writeIniFile($configFile, $existingConfig);
            
            // Применяем правило через iptables
            $this->applyPortForwardingRule($config);
            
            return [
                'success' => true,
                'message' => "Правило проброса портов '$name' добавлено успешно",
                'data' => ['id' => $id]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка добавления правила проброса портов: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Удалить правило проброса портов
     */
    public function deletePortForwardingRule(string $ruleId): array
    {
        try {
            $configFile = '/etc/port-forwarding.conf';
            
            if (!file_exists($configFile)) {
                return ['success' => false, 'message' => 'Файл конфигурации не найден'];
            }
            
            $config = parse_ini_file($configFile, true);
            if (!isset($config[$ruleId])) {
                return ['success' => false, 'message' => 'Правило не найдено'];
            }
            
            $rule = $config[$ruleId];
            
            // Удаляем правило из iptables
            $this->removePortForwardingRule($rule);
            
            // Удаляем из конфигурации
            unset($config[$ruleId]);
            $this->writeIniFile($configFile, $config);
            
            return [
                'success' => true,
                'message' => "Правило проброса портов '{$rule['name']}' удалено"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка удаления правила проброса портов: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Применить правило проброса портов через iptables
     */
    private function applyPortForwardingRule(array $rule): bool
    {
        $externalPort = $rule['external_port'];
        $internalPort = $rule['internal_port'];
        $protocol = $rule['protocol'];
        $targetIp = $rule['target_ip'];
        
        // Команды iptables для проброса портов
        $commands = [
            // Разрешаем входящие соединения
            "iptables -A INPUT -p $protocol --dport $externalPort -j ACCEPT",
            // Перенаправляем трафик
            "iptables -t nat -A PREROUTING -p $protocol --dport $externalPort -j DNAT --to-destination $targetIp:$internalPort",
            // Разрешаем исходящие соединения
            "iptables -A OUTPUT -p $protocol --sport $internalPort -j ACCEPT"
        ];
        
        foreach ($commands as $command) {
            shell_exec($command . ' 2>/dev/null');
        }
        
        return true;
    }

    /**
     * Удалить правило проброса портов из iptables
     */
    private function removePortForwardingRule(array $rule): bool
    {
        $externalPort = $rule['external_port'];
        $internalPort = $rule['internal_port'];
        $protocol = $rule['protocol'];
        $targetIp = $rule['target_ip'];
        
        // Команды iptables для удаления правил
        $commands = [
            // Удаляем входящие соединения
            "iptables -D INPUT -p $protocol --dport $externalPort -j ACCEPT 2>/dev/null",
            // Удаляем перенаправление
            "iptables -t nat -D PREROUTING -p $protocol --dport $externalPort -j DNAT --to-destination $targetIp:$internalPort 2>/dev/null",
            // Удаляем исходящие соединения
            "iptables -D OUTPUT -p $protocol --sport $internalPort -j ACCEPT 2>/dev/null"
        ];
        
        foreach ($commands as $command) {
            shell_exec($command);
        }
        
        return true;
    }

    /**
     * Получить статус правила проброса портов
     */
    private function getPortForwardingStatus(string $ruleId): string
    {
        $configFile = '/etc/port-forwarding.conf';
        
        if (!file_exists($configFile)) {
            return 'unknown';
        }
        
        $config = parse_ini_file($configFile, true);
        if (!isset($config[$ruleId])) {
            return 'unknown';
        }
        
        $rule = $config[$ruleId];
        
        // Проверяем, есть ли правило в iptables
        $command = sprintf(
            'iptables -t nat -L PREROUTING -n | grep "dpt:%d"',
            $rule['external_port']
        );
        
        $output = shell_exec($command);
        return $output ? 'active' : 'inactive';
    }
}
