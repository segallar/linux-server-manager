<?php

namespace App\Services\Cloudflare;

use App\Abstracts\BaseService;
use App\Interfaces\CloudflareTunnelServiceInterface;
use App\Exceptions\ServiceException;
use App\Exceptions\ValidationException;

class CloudflareTunnelService extends BaseService implements CloudflareTunnelServiceInterface
{
    protected string $cloudflaredPath = '/usr/local/bin/cloudflared';

    /**
     * Получить конфигурацию туннеля
     */
    public function getTunnelConfig(string $tunnelId): string
    {
        if (!$this->isInstalled()) {
            throw new ServiceException('Cloudflared не установлен');
        }

        $cloudflaredPath = $this->getCloudflaredPath();
        $output = $this->executeCommand("$cloudflaredPath tunnel config show $tunnelId");
        
        if (!$output) {
            throw new ServiceException('Не удалось получить конфигурацию туннеля');
        }

        return $output;
    }

    /**
     * Создать туннель
     */
    public function createTunnel(string $name, string $url, string $protocol = 'http'): array
    {
        try {
            if (!$this->isInstalled()) {
                return ['success' => false, 'message' => 'Cloudflared не установлен'];
            }

            if (!$this->isAuthenticated()) {
                return ['success' => false, 'message' => 'Cloudflared не авторизован'];
            }

            if (empty($name)) {
                throw new ValidationException('Имя туннеля обязательно');
            }

            if (empty($url)) {
                throw new ValidationException('URL обязателен');
            }

            if (!in_array($protocol, ['http', 'https'])) {
                throw new ValidationException('Неподдерживаемый протокол');
            }

            $cloudflaredPath = $this->getCloudflaredPath();
            
            // Создаем туннель
            $command = "$cloudflaredPath tunnel create $name 2>&1";
            $output = $this->executeCommand($command);
            
            if (strpos($output, 'Created tunnel') === false) {
                return [
                    'success' => false,
                    'message' => 'Ошибка создания туннеля: ' . $output
                ];
            }

            // Извлекаем ID туннеля
            if (preg_match('/Created tunnel ([a-f0-9-]+)/', $output, $matches)) {
                $tunnelId = $matches[1];
            } else {
                return [
                    'success' => false,
                    'message' => 'Не удалось получить ID туннеля'
                ];
            }

            // Создаем конфигурацию
            $configResult = $this->createTunnelConfig($tunnelId, $name, $url, $protocol);
            if (!$configResult['success']) {
                return $configResult;
            }

            return [
                'success' => true,
                'message' => "Туннель '$name' создан успешно",
                'tunnel_id' => $tunnelId,
                'tunnel_name' => $name
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка создания туннеля: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Запустить туннель
     */
    public function startTunnel(string $tunnelId): array
    {
        try {
            if (!$this->isInstalled()) {
                return ['success' => false, 'message' => 'Cloudflared не установлен'];
            }

            // Проверяем, существует ли туннель
            $tunnels = $this->getTunnels();
            $tunnelExists = false;
            $tunnelName = '';
            
            foreach ($tunnels as $tunnel) {
                if ($tunnel['id'] === $tunnelId) {
                    $tunnelExists = true;
                    $tunnelName = $tunnel['name'];
                    break;
                }
            }
            
            if (!$tunnelExists) {
                return ['success' => false, 'message' => 'Туннель не найден'];
            }

            // Проверяем, не запущен ли уже туннель
            if ($this->isTunnelRunning($tunnelId)) {
                return [
                    'success' => false,
                    'message' => 'Туннель уже запущен'
                ];
            }

            $cloudflaredPath = $this->getCloudflaredPath();
            $configFile = "/etc/cloudflared/$tunnelId.yml";
            
            // Запускаем туннель в фоновом режиме
            $command = "nohup $cloudflaredPath tunnel run --config $configFile > /var/log/cloudflared-$tunnelId.log 2>&1 &";
            $this->executeCommand($command);
            
            // Ждем немного и проверяем, запустился ли туннель
            sleep(2);
            
            if ($this->isTunnelRunning($tunnelId)) {
                return [
                    'success' => true,
                    'message' => "Туннель '$tunnelName' запущен"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ошибка запуска туннеля'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка запуска туннеля: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Остановить туннель
     */
    public function stopTunnel(string $tunnelId): array
    {
        try {
            if (!$this->isInstalled()) {
                return ['success' => false, 'message' => 'Cloudflared не установлен'];
            }

            // Проверяем, существует ли туннель
            $tunnels = $this->getTunnels();
            $tunnelExists = false;
            $tunnelName = '';
            
            foreach ($tunnels as $tunnel) {
                if ($tunnel['id'] === $tunnelId) {
                    $tunnelExists = true;
                    $tunnelName = $tunnel['name'];
                    break;
                }
            }
            
            if (!$tunnelExists) {
                return ['success' => false, 'message' => 'Туннель не найден'];
            }

            // Останавливаем туннель
            $command = "pkill -f 'cloudflared.*tunnel.*run.*$tunnelId'";
            $this->executeCommand($command);
            
            // Ждем немного и проверяем, остановился ли туннель
            sleep(2);
            
            if (!$this->isTunnelRunning($tunnelId)) {
                return [
                    'success' => true,
                    'message' => "Туннель '$tunnelName' остановлен"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ошибка остановки туннеля'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка остановки туннеля: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Удалить туннель
     */
    public function deleteTunnel(string $tunnelId): array
    {
        try {
            if (!$this->isInstalled()) {
                return ['success' => false, 'message' => 'Cloudflared не установлен'];
            }

            // Проверяем, существует ли туннель
            $tunnels = $this->getTunnels();
            $tunnelExists = false;
            $tunnelName = '';
            
            foreach ($tunnels as $tunnel) {
                if ($tunnel['id'] === $tunnelId) {
                    $tunnelExists = true;
                    $tunnelName = $tunnel['name'];
                    break;
                }
            }
            
            if (!$tunnelExists) {
                return ['success' => false, 'message' => 'Туннель не найден'];
            }

            $cloudflaredPath = $this->getCloudflaredPath();
            
            // Сначала останавливаем туннель
            $this->stopTunnel($tunnelId);
            
            // Удаляем туннель
            $command = "$cloudflaredPath tunnel delete $tunnelId";
            $output = $this->executeCommand($command);
            
            if (strpos($output, 'Deleted tunnel') !== false || strpos($output, 'tunnel deleted') !== false) {
                // Удаляем конфигурационные файлы
                $configFile = "/etc/cloudflared/$tunnelId.yml";
                $credentialsFile = "/etc/cloudflared/$tunnelId.json";
                
                if (file_exists($configFile)) {
                    unlink($configFile);
                }
                if (file_exists($credentialsFile)) {
                    unlink($credentialsFile);
                }
                
                return [
                    'success' => true,
                    'message' => "Туннель '$tunnelName' удален"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ошибка удаления туннеля: ' . $output
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка удаления туннеля: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Проверить, установлен ли cloudflared
     */
    protected function isInstalled(): bool
    {
        $cloudflaredExists = file_exists($this->cloudflaredPath);
        $whichOutput = $this->executeCommand('which cloudflared');
        
        return $cloudflaredExists || !empty($whichOutput);
    }

    /**
     * Проверить, авторизован ли cloudflared
     */
    protected function isAuthenticated(): bool
    {
        $certPaths = [
            '/root/.cloudflared/cert.pem',
            '/home/www-data/.cloudflared/cert.pem',
            '/var/www/.cloudflared/cert.pem',
            '/etc/cloudflared/cert.pem'
        ];
        
        $certExists = false;
        foreach ($certPaths as $path) {
            if (file_exists($path)) {
                $certExists = true;
                break;
            }
        }
        
        if (!$certExists) {
            return false;
        }
        
        $cloudflaredPath = $this->getCloudflaredPath();
        $output = $this->executeCommand("$cloudflaredPath tunnel list");
        
        return strpos($output, 'originCertPath=') === false && strpos($output, 'Cannot determine default origin certificate path') === false;
    }

    /**
     * Получить путь к cloudflared
     */
    protected function getCloudflaredPath(): string
    {
        $whichOutput = $this->executeCommand('which cloudflared');
        if (!empty($whichOutput)) {
            return trim($whichOutput);
        }
        
        if (file_exists($this->cloudflaredPath)) {
            return $this->cloudflaredPath;
        }
        
        return 'cloudflared';
    }

    /**
     * Получить список туннелей
     */
    protected function getTunnels(): array
    {
        $tunnels = [];

        if (!$this->isInstalled() || !$this->isAuthenticated()) {
            return $tunnels;
        }

        $cloudflaredPath = $this->getCloudflaredPath();
        $output = $this->executeCommand("$cloudflaredPath tunnel list");
        
        if (!$output) {
            return $tunnels;
        }

        $lines = explode("\n", trim($output));
        $headerFound = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (strpos($line, 'ID') !== false && strpos($line, 'NAME') !== false) {
                $headerFound = true;
                continue;
            }

            if (!$headerFound) {
                continue;
            }

            $tunnel = $this->parseTunnelLine($line);
            if ($tunnel) {
                $tunnels[] = $tunnel;
            }
        }

        return $tunnels;
    }

    /**
     * Парсить строку туннеля
     */
    protected function parseTunnelLine(string $line): ?array
    {
        $parts = preg_split('/\s+/', $line);
        
        if (count($parts) < 4) {
            return null;
        }

        return [
            'id' => $parts[0],
            'name' => $parts[1],
            'status' => $parts[2],
            'created' => $parts[3]
        ];
    }

    /**
     * Создать конфигурацию туннеля
     */
    protected function createTunnelConfig(string $tunnelId, string $name, string $url, string $protocol): array
    {
        try {
            $configDir = "/etc/cloudflared";
            $configFile = "$configDir/$tunnelId.yml";
            
            // Создаем директорию если не существует
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }
            
            // Создаем конфигурацию
            $config = [
                'tunnel' => $tunnelId,
                'credentials-file' => "/etc/cloudflared/$tunnelId.json",
                'ingress' => [
                    [
                        'hostname' => "$name.your-domain.com",
                        'service' => "$protocol://$url"
                    ],
                    [
                        'service' => 'http_status:404'
                    ]
                ]
            ];
            
            $yaml = yaml_emit($config);
            if (file_put_contents($configFile, $yaml) === false) {
                return ['success' => false, 'message' => 'Ошибка создания конфигурации'];
            }
            
            return ['success' => true, 'message' => 'Конфигурация создана'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ошибка создания конфигурации: ' . $e->getMessage()];
        }
    }

    /**
     * Проверить, запущен ли туннель
     */
    protected function isTunnelRunning(string $tunnelId): bool
    {
        $command = "pgrep -f 'cloudflared.*tunnel.*run.*$tunnelId'";
        $output = $this->executeCommand($command);
        return !empty($output);
    }
}
