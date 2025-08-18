<?php

namespace App\Services\System;

use App\Abstracts\BaseService;
use App\Interfaces\ProcessServiceInterface;

class ProcessService extends BaseService implements ProcessServiceInterface
{
    /**
     * Получить активные процессы
     */
    public function getActiveProcesses(int $limit = 10): array
    {
        $output = $this->executeCommand("ps aux --sort=-%cpu | head -" . ($limit + 1));
        $lines = explode("\n", trim($output));
        
        $processes = [];
        
        // Пропускаем заголовок
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;
            
            $process = $this->parseProcessLine($line);
            if ($process) {
                $processes[] = $process;
            }
        }
        
        return $processes;
    }

    /**
     * Получить все процессы
     */
    public function getAllProcesses(): array
    {
        $output = $this->executeCommand('ps aux');
        $lines = explode("\n", trim($output));
        
        $processes = [];
        
        // Пропускаем заголовок
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;
            
            $process = $this->parseProcessLine($line);
            if ($process) {
                $processes[] = $process;
            }
        }
        
        return $processes;
    }

    /**
     * Получить статистику процессов
     */
    public function getProcessStats(): array
    {
        $processes = $this->getAllProcesses();
        
        $stats = [
            'total' => count($processes),
            'running' => 0,
            'sleeping' => 0,
            'stopped' => 0,
            'zombie' => 0,
            'unknown' => 0,
            'top_cpu' => [],
            'top_memory' => []
        ];
        
        // Подсчитываем процессы по статусу
        foreach ($processes as $process) {
            switch ($process['state']) {
                case 'R':
                    $stats['running']++;
                    break;
                case 'S':
                case 'D':
                    $stats['sleeping']++;
                    break;
                case 'T':
                    $stats['stopped']++;
                    break;
                case 'Z':
                    $stats['zombie']++;
                    break;
                default:
                    $stats['unknown']++;
                    break;
            }
        }
        
        // Получаем топ процессов по CPU
        $topCpuOutput = $this->executeCommand("ps aux --sort=-%cpu | head -6");
        $topCpuLines = explode("\n", trim($topCpuOutput));
        
        for ($i = 1; $i < count($topCpuLines); $i++) {
            $line = trim($topCpuLines[$i]);
            if (empty($line)) continue;
            
            $process = $this->parseProcessLine($line);
            if ($process) {
                $stats['top_cpu'][] = [
                    'pid' => $process['pid'],
                    'name' => $process['command'],
                    'cpu' => $process['cpu'],
                    'memory' => $process['memory']
                ];
            }
        }
        
        // Получаем топ процессов по памяти
        $topMemoryOutput = $this->executeCommand("ps aux --sort=-%mem | head -6");
        $topMemoryLines = explode("\n", trim($topMemoryOutput));
        
        for ($i = 1; $i < count($topMemoryLines); $i++) {
            $line = trim($topMemoryLines[$i]);
            if (empty($line)) continue;
            
            $process = $this->parseProcessLine($line);
            if ($process) {
                $stats['top_memory'][] = [
                    'pid' => $process['pid'],
                    'name' => $process['command'],
                    'cpu' => $process['cpu'],
                    'memory' => $process['memory']
                ];
            }
        }
        
        return $stats;
    }

    /**
     * Получить количество процессов
     */
    public function getProcessCount(): int
    {
        $output = $this->executeCommand('ps aux | wc -l');
        return (int)trim($output ?: '0') - 1; // Вычитаем заголовок
    }

    /**
     * Парсить строку процесса
     */
    protected function parseProcessLine(string $line): ?array
    {
        // Пример строки: "user 1234 0.5 2.1 123456 7890 ? S 10:30 0:00 /usr/bin/process"
        $parts = preg_split('/\s+/', $line);
        
        if (count($parts) < 11) {
            return null;
        }
        
        return [
            'user' => $parts[0],
            'pid' => (int)$parts[1],
            'cpu' => (float)$parts[2],
            'memory' => (float)$parts[3],
            'vsz' => (int)$parts[4],
            'rss' => (int)$parts[5],
            'tty' => $parts[6],
            'state' => $parts[7],
            'start' => $parts[8],
            'time' => $parts[9],
            'command' => implode(' ', array_slice($parts, 10))
        ];
    }
}
