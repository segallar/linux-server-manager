<?php

namespace App\Interfaces;

interface SSHTunnelServiceInterface
{
    /**
     * Получить список SSH туннелей
     */
    public function getSSHTunnels(): array;

    /**
     * Создать SSH туннель
     */
    public function createSSHTunnel(string $name, string $host, int $port, string $username, int $localPort, int $remotePort): array;

    /**
     * Запустить SSH туннель
     */
    public function startSSHTunnel(string $tunnelId): array;

    /**
     * Остановить SSH туннель
     */
    public function stopSSHTunnel(string $tunnelId): array;

    /**
     * Удалить SSH туннель
     */
    public function deleteSSHTunnel(string $tunnelId): array;

    /**
     * Получить соединения SSH туннелей
     */
    public function getSSHTunnelConnections(): array;
}
