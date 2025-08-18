<?php

namespace App\Services;

class ProcessService
{
    /**
     * Получить информацию о процессе по PID
     */
    public function getProcessInfo(int $pid): ?array
    {
        $output = shell_exec("ps -p $pid -o pid,ppid,user,%cpu,%mem,vsz,rss,tty,stat,start,time,comm,args --no-headers 2>/dev/null");
        
        if (empty($output)) {
            return null;
        }
        
        $parts = preg_split('/\s+/', trim($output), 13);
        if (count($parts) < 13) {
            return null;
        }
        
        return [
            'pid' => $parts[0],
            'ppid' => $parts[1],
            'user' => $parts[2],
            'cpu' => $parts[3],
            'mem' => $parts[4],
            'vsz' => $this->formatBytes((int)$parts[5] * 1024),
            'rss' => $this->formatBytes((int)$parts[6] * 1024),
            'tty' => $parts[7],
            'stat' => $parts[8],
            'start' => $parts[9],
            'time' => $parts[10],
            'command' => $parts[11],
            'args' => $parts[12] ?? ''
        ];
    }

    /**
     * Завершить процесс
     */
    public function killProcess(int $pid, string $signal = 'TERM'): array
    {
        $signal = strtoupper($signal);
        $validSignals = ['TERM', 'KILL', 'HUP', 'INT', 'QUIT'];
        
        if (!in_array($signal, $validSignals)) {
            return ['success' => false, 'message' => 'Неверный сигнал'];
        }
        
        // Проверяем, существует ли процесс
        if (!$this->processExists($pid)) {
            return ['success' => false, 'message' => 'Процесс не найден'];
        }
        
        $output = shell_exec("kill -$signal $pid 2>&1");
        $exitCode = $this->getLastExitCode();
        
        if ($exitCode === 0) {
            return ['success' => true, 'message' => "Процесс $pid завершен сигналом $signal"];
        } else {
            return ['success' => false, 'message' => "Ошибка при завершении процесса: $output"];
        }
    }

    /**
     * Изменить приоритет процесса
     */
    public function setProcessPriority(int $pid, int $priority): array
    {
        // Проверяем, существует ли процесс
        if (!$this->processExists($pid)) {
            return ['success' => false, 'message' => 'Процесс не найден'];
        }
        
        // Приоритет должен быть от -20 до 19
        if ($priority < -20 || $priority > 19) {
            return ['success' => false, 'message' => 'Приоритет должен быть от -20 до 19'];
        }
        
        $output = shell_exec("renice $priority -p $pid 2>&1");
        $exitCode = $this->getLastExitCode();
        
        if ($exitCode === 0) {
            return ['success' => true, 'message' => "Приоритет процесса $pid изменен на $priority"];
        } else {
            return ['success' => false, 'message' => "Ошибка при изменении приоритета: $output"];
        }
    }

    /**
     * Запустить новый процесс
     */
    public function startProcess(string $command, string $user = 'root', int $priority = 0): array
    {
        // Проверяем команду
        if (empty($command)) {
            return ['success' => false, 'message' => 'Команда не может быть пустой'];
        }
        
        // Экранируем команду
        $escapedCommand = escapeshellcmd($command);
        
        // Формируем команду запуска
        $runCommand = "nohup $escapedCommand > /dev/null 2>&1 & echo \$!";
        
        if ($user !== 'root') {
            $runCommand = "sudo -u $user $runCommand";
        }
        
        $pid = shell_exec($runCommand);
        $exitCode = $this->getLastExitCode();
        
        if ($exitCode === 0 && !empty($pid)) {
            $pid = (int)trim($pid);
            
            // Устанавливаем приоритет, если он не 0
            if ($priority !== 0) {
                $this->setProcessPriority($pid, $priority);
            }
            
            return [
                'success' => true, 
                'message' => "Процесс '$command' запущен с PID $pid",
                'pid' => $pid
            ];
        } else {
            return ['success' => false, 'message' => 'Ошибка при запуске процесса'];
        }
    }

    /**
     * Получить дерево процессов
     */
    public function getProcessTree(): array
    {
        $output = shell_exec("ps -eo pid,ppid,comm --forest 2>/dev/null");
        $lines = explode("\n", trim($output));
        
        $tree = [];
        $processes = [];
        
        // Парсим процессы
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            // Убираем символы дерева
            $cleanLine = preg_replace('/^[├│└─\s]+/', '', $line);
            $parts = preg_split('/\s+/', trim($cleanLine), 3);
            
            if (count($parts) >= 3) {
                $processes[] = [
                    'pid' => (int)$parts[0],
                    'ppid' => (int)$parts[1],
                    'command' => $parts[2]
                ];
            }
        }
        
        // Строим дерево
        foreach ($processes as $process) {
            $tree[$process['pid']] = [
                'pid' => $process['pid'],
                'ppid' => $process['ppid'],
                'command' => $process['command'],
                'children' => []
            ];
        }
        
        // Добавляем детей
        foreach ($tree as $pid => $process) {
            if ($process['ppid'] !== 0 && isset($tree[$process['ppid']])) {
                $tree[$process['ppid']]['children'][] = $process;
            }
        }
        
        // Возвращаем только корневые процессы
        return array_filter($tree, function($process) {
            return $process['ppid'] === 0;
        });
    }

    /**
     * Получить топ процессов по использованию ресурсов
     */
    public function getTopProcesses(int $limit = 10): array
    {
        $output = shell_exec("ps aux --sort=-%cpu | head -" . ($limit + 1) . " 2>/dev/null");
        $lines = explode("\n", trim($output));
        
        $processes = [];
        
        // Пропускаем заголовок
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;
            
            $parts = preg_split('/\s+/', $line, 11);
            if (count($parts) < 11) continue;
            
            $processes[] = [
                'pid' => $parts[1],
                'user' => $parts[0],
                'cpu' => $parts[2],
                'mem' => $parts[3],
                'vsz' => $this->formatBytes((int)$parts[4] * 1024),
                'rss' => $this->formatBytes((int)$parts[5] * 1024),
                'tty' => $parts[6],
                'stat' => $parts[7],
                'start' => $parts[8],
                'time' => $parts[9],
                'command' => $parts[10]
            ];
        }
        
        return $processes;
    }

    /**
     * Получить процессы по пользователю
     */
    public function getProcessesByUser(string $user): array
    {
        $output = shell_exec("ps -u $user -o pid,ppid,user,%cpu,%mem,stat,time,comm --no-headers 2>/dev/null");
        $lines = explode("\n", trim($output));
        
        $processes = [];
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $parts = preg_split('/\s+/', trim($line), 8);
            if (count($parts) >= 8) {
                $processes[] = [
                    'pid' => $parts[0],
                    'ppid' => $parts[1],
                    'user' => $parts[2],
                    'cpu' => $parts[3],
                    'mem' => $parts[4],
                    'stat' => $parts[5],
                    'time' => $parts[6],
                    'command' => $parts[7]
                ];
            }
        }
        
        return $processes;
    }

    /**
     * Проверить, существует ли процесс
     */
    private function processExists(int $pid): bool
    {
        $output = shell_exec("ps -p $pid --no-headers 2>/dev/null");
        return !empty($output);
    }

    /**
     * Получить код выхода последней команды
     */
    private function getLastExitCode(): int
    {
        return (int)shell_exec('echo $?');
    }

    /**
     * Форматировать байты
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
