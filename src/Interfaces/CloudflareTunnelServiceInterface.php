<?php

namespace App\Interfaces;

interface CloudflareTunnelServiceInterface
{
    /**
     * Получить конфигурацию туннеля
     */
    public function getTunnelConfig(string $tunnelId): string;

    /**
     * Создать туннель
     */
    public function createTunnel(string $name, string $url, string $protocol = 'http'): array;

    /**
     * Запустить туннель
     */
    public function startTunnel(string $tunnelId): array;

    /**
     * Остановить туннель
     */
    public function stopTunnel(string $tunnelId): array;

    /**
     * Удалить туннель
     */
    public function deleteTunnel(string $tunnelId): array;
}
