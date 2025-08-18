<?php

namespace App\Services\WireGuard;

use App\Abstracts\BaseService;
use App\Interfaces\WireGuardServiceInterface;

class WireGuardService extends BaseService implements WireGuardServiceInterface
{
    private string $wgPath = '/usr/bin/wg';
    private string $wgQuickPath = '/usr/bin/wg-quick';

    /**
     * Получить список всех WireGuard интерфейсов
     */
    public function getInterfaces(): array
    {
        $interfaces = [];

        // Получаем список интерфейсов с sudo
        $output = $this->executeCommand('sudo ' . $this->wgPath . ' show interfaces');
        if (!$output) {
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
        $status = $this->executeCommand("ip link show $interfaceName");
        $info['status'] = $status ? 'up' : 'down';

        // Получаем детальную информацию с sudo
        $output = $this->executeCommand('sudo ' . $this->wgPath . " show $interfaceName");
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
                    'transfer' => [
                        'received' => 0,
                        'sent' => 0
                    ]
                ];
            }
            
            // Endpoint пира
            elseif ($currentPeer && preg_match('/^endpoint:\s+(.+)$/', $line, $matches)) {
                $currentPeer['endpoint'] = $matches[1];
            }
            
            // Разрешенные IP пира
            elseif ($currentPeer && preg_match('/^allowed ips:\s+(.+)$/', $line, $matches)) {
                $currentPeer['allowed_ips'] = explode(', ', $matches[1]);
            }
            
            // Последнее рукопожатие
            elseif ($currentPeer && preg_match('/^latest handshake:\s+(.+)$/', $line, $matches)) {
                $currentPeer['latest_handshake'] = $matches[1];
            }
            
            // Передача данных пира
            elseif ($currentPeer && preg_match('/^transfer:\s+(\d+)\s+received,\s+(\d+)\s+sent/', $line, $matches)) {
                $currentPeer['transfer']['received'] = (int)$matches[1];
                $currentPeer['transfer']['sent'] = (int)$matches[2];
            }
        }

        // Добавляем последнего пира
        if ($currentPeer) {
            $info['peers'][] = $currentPeer;
        }

        // Получаем общую статистику передачи
        $info['transfer'] = $this->getTransferStats($interfaceName);

        return $info;
    }

    /**
     * Получить статистику передачи данных
     */
    public function getTransferStats(string $interfaceName): array
    {
        $stats = ['received' => 0, 'sent' => 0];

        $output = $this->executeCommand("cat /sys/class/net/$interfaceName/statistics/rx_bytes 2>/dev/null");
        if ($output) {
            $stats['received'] = (int)trim($output);
        }

        $output = $this->executeCommand("cat /sys/class/net/$interfaceName/statistics/tx_bytes 2>/dev/null");
        if ($output) {
            $stats['sent'] = (int)trim($output);
        }

        return $stats;
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
     * Проверить, установлен ли WireGuard
     */
    public function isInstalled(): bool
    {
        return file_exists($this->wgPath) && file_exists($this->wgQuickPath);
    }

    /**
     * Получить статистику
     */
    public function getStats(): array
    {
        $interfaces = $this->getInterfaces();
        
        $stats = [
            'total_interfaces' => count($interfaces),
            'active_interfaces' => 0,
            'total_peers' => 0,
            'active_peers' => 0,
            'total_transfer' => [
                'received' => 0,
                'sent' => 0
            ]
        ];

        foreach ($interfaces as $interface) {
            if ($interface['status'] === 'up') {
                $stats['active_interfaces']++;
            }

            $stats['total_peers'] += count($interface['peers']);
            
            foreach ($interface['peers'] as $peer) {
                if (!empty($peer['latest_handshake']) && $peer['latest_handshake'] !== '0') {
                    $stats['active_peers']++;
                }
            }

            $stats['total_transfer']['received'] += $interface['transfer']['received'];
            $stats['total_transfer']['sent'] += $interface['transfer']['sent'];
        }

        return $stats;
    }

    /**
     * Форматировать время рукопожатия
     */
    protected function formatHandshakeTime($handshakeTime): string
    {
        if (empty($handshakeTime) || $handshakeTime === '0') {
            return 'Никогда';
        }

        $timestamp = strtotime($handshakeTime);
        if (!$timestamp) {
            return 'Неизвестно';
        }

        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'Только что';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "{$minutes} мин назад";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "{$hours} ч назад";
        } else {
            $days = floor($diff / 86400);
            return "{$days} дн назад";
        }
    }

    /**
     * Получить статус пира
     */
    protected function getPeerStatus($handshakeTime): string
    {
        if (empty($handshakeTime) || $handshakeTime === '0') {
            return 'offline';
        }

        $timestamp = strtotime($handshakeTime);
        if (!$timestamp) {
            return 'unknown';
        }

        $diff = time() - $timestamp;
        
        if ($diff < 300) { // 5 минут
            return 'online';
        } elseif ($diff < 3600) { // 1 час
            return 'recent';
        } else {
            return 'offline';
        }
    }
}
