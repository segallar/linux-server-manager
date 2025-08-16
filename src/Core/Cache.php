<?php

namespace App\Core;

class Cache
{
    private string $cacheDir;
    private int $defaultTtl;

    public function __construct(string $cacheDir = null, int $defaultTtl = 300)
    {
        $this->cacheDir = $cacheDir ?? dirname(dirname(__DIR__)) . '/cache';
        $this->defaultTtl = $defaultTtl;
        
        // Создаем директорию кэша если не существует
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Получить данные из кэша
     */
    public function get(string $key)
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = file_get_contents($filename);
        if ($data === false) {
            return null;
        }
        
        $cached = json_decode($data, true);
        if (!$cached || !isset($cached['expires']) || !isset($cached['data'])) {
            return null;
        }
        
        // Проверяем срок действия
        if (time() > $cached['expires']) {
            unlink($filename);
            return null;
        }
        
        return $cached['data'];
    }

    /**
     * Сохранить данные в кэш
     */
    public function set(string $key, $data, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $filename = $this->getCacheFilename($key);
        
        $cached = [
            'expires' => time() + $ttl,
            'data' => $data,
            'created' => time()
        ];
        
        $json = json_encode($cached);
        if ($json === false) {
            return false;
        }
        
        return file_put_contents($filename, $json) !== false;
    }

    /**
     * Удалить данные из кэша
     */
    public function delete(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }

    /**
     * Проверить, существует ли ключ в кэше
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Очистить весь кэш
     */
    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }

    /**
     * Получить статистику кэша
     */
    public function getStats(): array
    {
        $files = glob($this->cacheDir . '/*.cache');
        $totalSize = 0;
        $expiredCount = 0;
        $validCount = 0;
        
        foreach ($files as $file) {
            $size = filesize($file);
            $totalSize += $size;
            
            $data = file_get_contents($file);
            $cached = json_decode($data, true);
            
            if ($cached && isset($cached['expires'])) {
                if (time() > $cached['expires']) {
                    $expiredCount++;
                } else {
                    $validCount++;
                }
            }
        }
        
        return [
            'total_files' => count($files),
            'valid_files' => $validCount,
            'expired_files' => $expiredCount,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize)
        ];
    }

    /**
     * Очистить устаревшие записи
     */
    public function cleanup(): int
    {
        $files = glob($this->cacheDir . '/*.cache');
        $deletedCount = 0;
        
        foreach ($files as $file) {
            $data = file_get_contents($file);
            $cached = json_decode($data, true);
            
            if ($cached && isset($cached['expires']) && time() > $cached['expires']) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }

    /**
     * Получить имя файла кэша
     */
    private function getCacheFilename(string $key): string
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }

    /**
     * Форматировать размер в байтах
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
