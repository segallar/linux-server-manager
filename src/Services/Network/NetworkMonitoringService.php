<?php

namespace App\Services\Network;

use App\Abstracts\BaseService;
use App\Interfaces\NetworkMonitoringServiceInterface;

class NetworkMonitoringService extends BaseService implements NetworkMonitoringServiceInterface
{
    /**
     * Получить информацию о сетевых интерфейсах
     */
    public function getInterfaces(): array
    {
        $output = $this->executeCommand('ip addr show');
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
            
            if ($currentInterface) {
                // Статус интерфейса
                if (strpos($line, 'state UP') !== false) {
                    $currentInterface['status'] = 'up';
                }
                
                // MAC адрес
                if (preg_match('/link\/ether\s+([a-fA-F0-9:]+)/', $line, $matches)) {
                    $currentInterface['mac'] = $matches[1];
                }
                
                // MTU
                if (preg_match('/mtu\s+(\d+)/', $line, $matches)) {
                    $currentInterface['mtu'] = $matches[1];
                }
                
                // IP адреса
                if (preg_match('/inet\s+([0-9.]+)/', $line, $matches)) {
                    $currentInterface['ips'][] = $matches[1];
                }
            }
        }

        return $interfaces;
    }

    /**
     * Получить информацию о DNS
     */
    public function getDnsInfo(): array
    {
        $dnsInfo = [
            'nameservers' => [],
            'search_domains' => [],
            'resolv_conf' => ''
        ];

        // Читаем /etc/resolv.conf
        if (file_exists('/etc/resolv.conf')) {
            $resolvConf = file_get_contents('/etc/resolv.conf');
            $dnsInfo['resolv_conf'] = $resolvConf;

            $lines = explode("\n", $resolvConf);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) continue;

                if (preg_match('/^nameserver\s+(.+)$/', $line, $matches)) {
                    $dnsInfo['nameservers'][] = $matches[1];
                } elseif (preg_match('/^search\s+(.+)$/', $line, $matches)) {
                    $dnsInfo['search_domains'] = explode(' ', $matches[1]);
                }
            }
        }

        return $dnsInfo;
    }

    /**
     * Получить активные соединения
     */
    public function getConnections(): array
    {
        $output = $this->executeCommand('ss -tuln');
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
     * Получить статистику трафика
     */
    public function getTrafficStats(): array
    {
        $stats = [];

        // Получаем статистику по интерфейсам
        $output = $this->executeCommand('cat /proc/net/dev');
        if ($output) {
            $lines = explode("\n", trim($output));
            
            // Пропускаем заголовки
            for ($i = 2; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) continue;

                $interfaceStats = $this->parseInterfaceStats($line);
                if ($interfaceStats) {
                    $stats[$interfaceStats['name']] = $interfaceStats;
                }
            }
        }

        return $stats;
    }

    /**
     * Парсить строку соединения
     */
    protected function parseConnectionLine(string $line): ?array
    {
        // Пример строки: "tcp    LISTEN 0      128    0.0.0.0:22       0.0.0.0:*"
        $parts = preg_split('/\s+/', $line);
        
        if (count($parts) < 5) {
            return null;
        }

        return [
            'protocol' => $parts[0],
            'state' => $parts[1],
            'recv_q' => $parts[2],
            'send_q' => $parts[3],
            'local_address' => $parts[4],
            'peer_address' => $parts[5] ?? ''
        ];
    }

    /**
     * Парсить статистику интерфейса
     */
    protected function parseInterfaceStats(string $line): ?array
    {
        // Пример строки: "eth0: 1234567 890 1234567 890 0 0 0 0 0 0 0 0 0 0 0 0"
        if (preg_match('/^(\w+):\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $line, $matches)) {
            return [
                'name' => $matches[1],
                'rx_bytes' => (int)$matches[2],
                'rx_packets' => (int)$matches[3],
                'rx_errors' => (int)$matches[4],
                'rx_dropped' => (int)$matches[5],
                'tx_bytes' => (int)$matches[10],
                'tx_packets' => (int)$matches[11],
                'tx_errors' => (int)$matches[12],
                'tx_dropped' => (int)$matches[13]
            ];
        }

        return null;
    }
}
