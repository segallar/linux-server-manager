<?php

namespace App\Services\Network;

use App\Abstracts\BaseService;
use App\Interfaces\SSHTunnelServiceInterface;
use App\Exceptions\ServiceException;
use App\Exceptions\ValidationException;

class SSHTunnelService extends BaseService implements SSHTunnelServiceInterface
{
    private string $tunnelsFile = '/tmp/ssh_tunnels.json';

    /**
     * Получить список SSH туннелей
     */
    public function getSSHTunnels(): array
    {
        if (!file_exists($this->tunnelsFile)) {
            return [];
        }

        $content = file_get_contents($this->tunnelsFile);
        if (!$content) {
            return [];
        }

        $tunnels = json_decode($content, true);
        if (!is_array($tunnels)) {
            return [];
        }

        // Добавляем статус для каждого туннеля
        foreach ($tunnels as &$tunnel) {
            $tunnel['status'] = $this->getTunnelStatus($tunnel['id']);
        }

        return $tunnels;
    }

    /**
     * Создать SSH туннель
     */
    public function createSSHTunnel(string $name, string $host, int $port, string $username, int $localPort, int $remotePort): array
    {
        // Валидация параметров
        if (empty($name)) {
            throw new ValidationException("Имя туннеля не может быть пустым");
        }

        if (!$this->validateIpAddress($host) && !filter_var($host, FILTER_VALIDATE_DOMAIN)) {
            throw new ValidationException("Неверный хост: {$host}");
        }

        if (!$this->validatePort($port)) {
            throw new ValidationException("Неверный порт: {$port}");
        }

        if (empty($username)) {
            throw new ValidationException("Имя пользователя не может быть пустым");
        }

        if (!$this->validatePort($localPort)) {
            throw new ValidationException("Неверный локальный порт: {$localPort}");
        }

        if (!$this->validatePort($remotePort)) {
            throw new ValidationException("Неверный удаленный порт: {$remotePort}");
        }

        // Проверяем, что локальный порт не занят
        if ($this->isPortInUse($localPort)) {
            throw new ValidationException("Локальный порт {$localPort} уже используется");
        }

        $tunnelId = uniqid('tunnel_');
        
        $tunnel = [
            'id' => $tunnelId,
            'name' => $name,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'local_port' => $localPort,
            'remote_port' => $remotePort,
            'status' => 'stopped',
            'created_at' => date('Y-m-d H:i:s'),
            'pid' => null
        ];

        $tunnels = $this->getSSHTunnels();
        $tunnels[] = $tunnel;
        
        $this->saveTunnels($tunnels);

        return [
            'success' => true,
            'message' => 'SSH туннель создан успешно',
            'tunnel' => $tunnel
        ];
    }

    /**
     * Запустить SSH туннель
     */
    public function startSSHTunnel(string $tunnelId): array
    {
        $tunnels = $this->getSSHTunnels();
        $tunnelIndex = $this->findTunnelById($tunnels, $tunnelId);
        
        if ($tunnelIndex === -1) {
            throw new ValidationException("Туннель с ID {$tunnelId} не найден");
        }

        $tunnel = $tunnels[$tunnelIndex];
        
        if ($tunnel['status'] === 'running') {
            return [
                'success' => false,
                'message' => 'Туннель уже запущен'
            ];
        }

        // Проверяем, что локальный порт не занят
        if ($this->isPortInUse($tunnel['local_port'])) {
            throw new ValidationException("Локальный порт {$tunnel['local_port']} уже используется");
        }

        $command = sprintf(
            'ssh -f -N -L %d:localhost:%d %s@%s -p %d',
            $tunnel['local_port'],
            $tunnel['remote_port'],
            $tunnel['username'],
            $tunnel['host'],
            $tunnel['port']
        );

        $result = $this->safeExecute($command);
        
        if (!$result['success']) {
            throw new ServiceException("Ошибка запуска SSH туннеля: " . $result['error']);
        }

        // Получаем PID процесса
        $pid = $this->getSSHTunnelPID($tunnel['local_port']);
        
        $tunnels[$tunnelIndex]['status'] = 'running';
        $tunnels[$tunnelIndex]['pid'] = $pid;
        $tunnels[$tunnelIndex]['started_at'] = date('Y-m-d H:i:s');
        
        $this->saveTunnels($tunnels);

        return [
            'success' => true,
            'message' => 'SSH туннель запущен успешно',
            'tunnel' => $tunnels[$tunnelIndex]
        ];
    }

    /**
     * Остановить SSH туннель
     */
    public function stopSSHTunnel(string $tunnelId): array
    {
        $tunnels = $this->getSSHTunnels();
        $tunnelIndex = $this->findTunnelById($tunnels, $tunnelId);
        
        if ($tunnelIndex === -1) {
            throw new ValidationException("Туннель с ID {$tunnelId} не найден");
        }

        $tunnel = $tunnels[$tunnelIndex];
        
        if ($tunnel['status'] !== 'running') {
            return [
                'success' => false,
                'message' => 'Туннель не запущен'
            ];
        }

        // Останавливаем процесс
        if ($tunnel['pid']) {
            $result = $this->safeExecute("kill {$tunnel['pid']}");
            
            if (!$result['success']) {
                $this->logError("Не удалось остановить процесс {$tunnel['pid']}", [
                    'tunnel_id' => $tunnelId,
                    'error' => $result['error']
                ]);
            }
        }

        $tunnels[$tunnelIndex]['status'] = 'stopped';
        $tunnels[$tunnelIndex]['pid'] = null;
        $tunnels[$tunnelIndex]['stopped_at'] = date('Y-m-d H:i:s');
        
        $this->saveTunnels($tunnels);

        return [
            'success' => true,
            'message' => 'SSH туннель остановлен успешно',
            'tunnel' => $tunnels[$tunnelIndex]
        ];
    }

    /**
     * Удалить SSH туннель
     */
    public function deleteSSHTunnel(string $tunnelId): array
    {
        $tunnels = $this->getSSHTunnels();
        $tunnelIndex = $this->findTunnelById($tunnels, $tunnelId);
        
        if ($tunnelIndex === -1) {
            throw new ValidationException("Туннель с ID {$tunnelId} не найден");
        }

        $tunnel = $tunnels[$tunnelIndex];
        
        // Останавливаем туннель, если он запущен
        if ($tunnel['status'] === 'running') {
            $this->stopSSHTunnel($tunnelId);
        }

        // Удаляем туннель из списка
        array_splice($tunnels, $tunnelIndex, 1);
        $this->saveTunnels($tunnels);

        return [
            'success' => true,
            'message' => 'SSH туннель удален успешно'
        ];
    }

    /**
     * Получить соединения SSH туннелей
     */
    public function getSSHTunnelConnections(): array
    {
        $tunnels = $this->getSSHTunnels();
        $connections = [];

        foreach ($tunnels as $tunnel) {
            if ($tunnel['status'] === 'running') {
                $connection = $this->getTunnelConnectionInfo($tunnel);
                if ($connection) {
                    $connections[] = $connection;
                }
            }
        }

        return $connections;
    }

    /**
     * Сохранить туннели в файл
     */
    private function saveTunnels(array $tunnels): void
    {
        $content = json_encode($tunnels, JSON_PRETTY_PRINT);
        file_put_contents($this->tunnelsFile, $content);
    }

    /**
     * Найти туннель по ID
     */
    private function findTunnelById(array $tunnels, string $tunnelId): int
    {
        foreach ($tunnels as $index => $tunnel) {
            if ($tunnel['id'] === $tunnelId) {
                return $index;
            }
        }
        return -1;
    }

    /**
     * Получить статус туннеля
     */
    private function getTunnelStatus(string $tunnelId): string
    {
        $tunnels = $this->getSSHTunnels();
        $tunnelIndex = $this->findTunnelById($tunnels, $tunnelId);
        
        if ($tunnelIndex === -1) {
            return 'not_found';
        }

        $tunnel = $tunnels[$tunnelIndex];
        
        if ($tunnel['status'] === 'running' && $tunnel['pid']) {
            // Проверяем, что процесс все еще работает
            $result = $this->safeExecute("ps -p {$tunnel['pid']} -o pid=");
            if (!$result['success'] || empty($result['output'])) {
                return 'stopped';
            }
        }

        return $tunnel['status'] ?? 'stopped';
    }

    /**
     * Проверить, используется ли порт
     */
    private function isPortInUse(int $port): bool
    {
        $result = $this->safeExecute("ss -tuln | grep :{$port}");
        return $result['success'] && !empty($result['output']);
    }

    /**
     * Получить PID SSH туннеля
     */
    private function getSSHTunnelPID(int $localPort): ?int
    {
        $result = $this->safeExecute("ss -tuln | grep :{$localPort}");
        if (!$result['success'] || empty($result['output'])) {
            return null;
        }

        // Ищем процесс SSH, который слушает на этом порту
        $result = $this->safeExecute("ps aux | grep ssh | grep -v grep");
        if (!$result['success']) {
            return null;
        }

        $lines = explode("\n", $result['output']);
        foreach ($lines as $line) {
            if (strpos($line, "-L {$localPort}:") !== false) {
                $parts = preg_split('/\s+/', trim($line));
                return (int) $parts[1] ?? null;
            }
        }

        return null;
    }

    /**
     * Получить информацию о соединении туннеля
     */
    private function getTunnelConnectionInfo(array $tunnel): ?array
    {
        if ($tunnel['status'] !== 'running') {
            return null;
        }

        return [
            'tunnel_id' => $tunnel['id'],
            'name' => $tunnel['name'],
            'local_port' => $tunnel['local_port'],
            'remote_host' => $tunnel['host'],
            'remote_port' => $tunnel['remote_port'],
            'username' => $tunnel['username'],
            'pid' => $tunnel['pid'],
            'started_at' => $tunnel['started_at'] ?? null,
            'uptime' => $this->calculateUptime($tunnel['started_at'] ?? null)
        ];
    }

    /**
     * Вычислить время работы туннеля
     */
    private function calculateUptime(?string $startedAt): ?string
    {
        if (!$startedAt) {
            return null;
        }

        $start = strtotime($startedAt);
        $now = time();
        $diff = $now - $start;

        $days = floor($diff / 86400);
        $hours = floor(($diff % 86400) / 3600);
        $minutes = floor(($diff % 3600) / 60);

        if ($days > 0) {
            return "{$days}д {$hours}ч {$minutes}м";
        } elseif ($hours > 0) {
            return "{$hours}ч {$minutes}м";
        } else {
            return "{$minutes}м";
        }
    }
}
