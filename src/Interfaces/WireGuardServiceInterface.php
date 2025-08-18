<?php

namespace App\Interfaces;

interface WireGuardServiceInterface
{
    /**
     * Получить список всех WireGuard интерфейсов
     */
    public function getInterfaces(): array;

    /**
     * Получить информацию об интерфейсе
     */
    public function getInterfaceInfo(string $interfaceName): array;

    /**
     * Получить статистику передачи данных
     */
    public function getTransferStats(string $interfaceName): array;

    /**
     * Получить конфигурацию интерфейса
     */
    public function getConfig(string $interfaceName): string;

    /**
     * Проверить, установлен ли WireGuard
     */
    public function isInstalled(): bool;

    /**
     * Получить статистику
     */
    public function getStats(): array;
}
