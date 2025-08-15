<?php

namespace App\Services;

class WireGuardService
{
    private string $wgPath = '/usr/bin/wg';
    private string $wgQuickPath = '/usr/bin/wg-quick';

    /**
     * Получить список всех WireGuard интерфейсов
     */
    public function getInterfaces(): array
    {
        $interfaces = [];
        
        // Получаем список интерфейсов
        $output = shell_exec($this->wgPath . ' show interfaces 2>&1');
        
        // Логируем для отладки
        error_log("WireGuard: wg show interfaces output: " . ($output ?: 'NULL'));
        
        if (!$output) {
            error_log("WireGuard: No interfaces found or command failed");
            return $interfaces;
        }

        $interfaceNames = array_filter(explode("\n", trim($output)));
        
        foreach ($interfaceNames as $interfaceName) {
            $interfaceName = trim($interfaceName);
            if (empty($interfaceName)) continue;
            
            $interfaces[] = $this->getInterfaceInfo($interfaceName);
        }
        
        return $interfaces;
    }

    /**
     * Получить информацию об интерфейсе
     */
    public function getInterfaceInfo(string $interfaceName): array
    {
        $info = [
            'name' => $interfaceName,
            'status' => 'unknown',
            'public_key' => '',
            'private_key' => '',
            'address' => '',
            'port' => '',
            'peers' => [],
            'transfer' => [
                'received' => 0,
                'sent' => 0
            ]
        ];

        // Проверяем статус интерфейса
        $status = shell_exec("ip link show $interfaceName 2>/dev/null");
        $info['status'] = $status ? 'up' : 'down';

        // Получаем детальную информацию
        $output = shell_exec($this->wgPath . " show $interfaceName 2>/dev/null");
        if (!$output) {
            return $info;
        }

        $lines = explode("\n", $output);
        $currentPeer = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Публичный ключ интерфейса
            if (preg_match('/^public key:\s+(.+)$/', $line, $matches)) {
                $info['public_key'] = $matches[1];
            }
            
            // Приватный ключ интерфейса
            elseif (preg_match('/^private key:\s+(.+)$/', $line, $matches)) {
                $info['private_key'] = $matches[1];
            }
            
            // Порт
            elseif (preg_match('/^listening port:\s+(\d+)$/', $line, $matches)) {
                $info['port'] = $matches[1];
            }
            
            // Адрес интерфейса
            elseif (preg_match('/^interface:\s+(.+)$/', $line, $matches)) {
                $info['address'] = $matches[1];
            }
            
            // Пиры
            elseif (preg_match('/^peer:\s+(.+)$/', $line, $matches)) {
                if ($currentPeer) {
                    $info['peers'][] = $currentPeer;
                }
                $currentPeer = [
                    'public_key' => $matches[1],
                    'endpoint' => '',
                    'allowed_ips' => [],
                    'latest_handshake' => '',
                    'transfer' => ['received' => 0, 'sent' => 0],
                    'status' => 'inactive'
                ];
            }
            
            // Информация о пире
            elseif ($currentPeer) {
                if (preg_match('/^endpoint:\s+(.+)$/', $line, $matches)) {
                    $currentPeer['endpoint'] = $matches[1];
                }
                elseif (preg_match('/^allowed ips:\s+(.+)$/', $line, $matches)) {
                    $currentPeer['allowed_ips'] = array_map('trim', explode(',', $matches[1]));
                }
                elseif (preg_match('/^latest handshake:\s+(.+)$/', $line, $matches)) {
                    $currentPeer['latest_handshake'] = $matches[1];
                    $currentPeer['status'] = 'active';
                }
                elseif (preg_match('/^transfer:\s+(\d+)\s+B\s+received,\s+(\d+)\s+B\s+sent/', $line, $matches)) {
                    $currentPeer['transfer']['received'] = $this->formatBytes($matches[1]);
                    $currentPeer['transfer']['sent'] = $this->formatBytes($matches[2]);
                }
            }
        }

        // Добавляем последний пир
        if ($currentPeer) {
            $info['peers'][] = $currentPeer;
        }

        return $info;
    }

    /**
     * Получить статистику передачи данных
     */
    public function getTransferStats(string $interfaceName): array
    {
        $stats = ['received' => 0, 'sent' => 0];
        
        $output = shell_exec("cat /sys/class/net/$interfaceName/statistics/rx_bytes 2>/dev/null");
        if ($output) {
            $stats['received'] = $this->formatBytes(trim($output));
        }
        
        $output = shell_exec("cat /sys/class/net/$interfaceName/statistics/tx_bytes 2>/dev/null");
        if ($output) {
            $stats['sent'] = $this->formatBytes(trim($output));
        }
        
        return $stats;
    }

    /**
     * Включить интерфейс
     */
    public function up(string $interfaceName): bool
    {
        $output = shell_exec("sudo $this->wgQuickPath up $interfaceName 2>&1");
        return strpos($output, 'error') === false;
    }

    /**
     * Выключить интерфейс
     */
    public function down(string $interfaceName): bool
    {
        $output = shell_exec("sudo $this->wgQuickPath down $interfaceName 2>&1");
        return strpos($output, 'error') === false;
    }

    /**
     * Перезапустить интерфейс
     */
    public function restart(string $interfaceName): bool
    {
        $this->down($interfaceName);
        sleep(1);
        return $this->up($interfaceName);
    }

    /**
     * Получить конфигурацию интерфейса
     */
    public function getConfig(string $interfaceName): string
    {
        $configPath = "/etc/wireguard/$interfaceName.conf";
        if (file_exists($configPath)) {
            return file_get_contents($configPath);
        }
        return '';
    }

    /**
     * Создать новый интерфейс
     */
    public function createInterface(string $interfaceName, array $config): bool
    {
        $configContent = $this->generateConfig($config);
        $configPath = "/etc/wireguard/$interfaceName.conf";
        
        return file_put_contents($configPath, $configContent) !== false;
    }

    /**
     * Удалить интерфейс
     */
    public function deleteInterface(string $interfaceName): bool
    {
        // Выключаем интерфейс
        $this->down($interfaceName);
        
        // Удаляем конфигурацию
        $configPath = "/etc/wireguard/$interfaceName.conf";
        if (file_exists($configPath)) {
            return unlink($configPath);
        }
        
        return true;
    }

    /**
     * Генерировать конфигурацию
     */
    private function generateConfig(array $config): string
    {
        $content = "[Interface]\n";
        $content .= "PrivateKey = " . $config['private_key'] . "\n";
        $content .= "Address = " . $config['address'] . "\n";
        if (!empty($config['port'])) {
            $content .= "ListenPort = " . $config['port'] . "\n";
        }
        if (!empty($config['dns'])) {
            $content .= "DNS = " . $config['dns'] . "\n";
        }
        if (!empty($config['mtu'])) {
            $content .= "MTU = " . $config['mtu'] . "\n";
        }
        
        // Добавляем пиров
        if (!empty($config['peers'])) {
            foreach ($config['peers'] as $peer) {
                $content .= "\n[Peer]\n";
                $content .= "PublicKey = " . $peer['public_key'] . "\n";
                if (!empty($peer['allowed_ips'])) {
                    $content .= "AllowedIPs = " . implode(', ', $peer['allowed_ips']) . "\n";
                }
                if (!empty($peer['endpoint'])) {
                    $content .= "Endpoint = " . $peer['endpoint'] . "\n";
                }
                if (!empty($peer['persistent_keepalive'])) {
                    $content .= "PersistentKeepalive = " . $peer['persistent_keepalive'] . "\n";
                }
            }
        }
        
        return $content;
    }

    /**
     * Форматировать байты в читаемый вид
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Проверить, установлен ли WireGuard
     */
    public function isInstalled(): bool
    {
        $wgExists = file_exists($this->wgPath);
        $wgQuickExists = file_exists($this->wgQuickPath);
        
        error_log("WireGuard: wg exists: " . ($wgExists ? 'YES' : 'NO'));
        error_log("WireGuard: wg-quick exists: " . ($wgQuickExists ? 'YES' : 'NO'));
        
        return $wgExists && $wgQuickExists;
    }

    /**
     * Получить общую статистику
     */
    public function getStats(): array
    {
        $interfaces = $this->getInterfaces();
        $totalPeers = 0;
        $activePeers = 0;
        $totalTransfer = ['received' => 0, 'sent' => 0];

        foreach ($interfaces as $interface) {
            $totalPeers += count($interface['peers']);
            foreach ($interface['peers'] as $peer) {
                if ($peer['status'] === 'active') {
                    $activePeers++;
                }
            }
        }

        return [
            'total_interfaces' => count($interfaces),
            'active_interfaces' => count(array_filter($interfaces, fn($i) => $i['status'] === 'up')),
            'total_peers' => $totalPeers,
            'active_peers' => $activePeers,
            'total_transfer' => $totalTransfer
        ];
    }
}
