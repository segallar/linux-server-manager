<?php

namespace App\Interfaces;

interface ServiceManagementServiceInterface
{
    /**
     * Запустить сервис
     */
    public function startService(string $serviceName): array;

    /**
     * Остановить сервис
     */
    public function stopService(string $serviceName): array;

    /**
     * Перезапустить сервис
     */
    public function restartService(string $serviceName): array;

    /**
     * Включить сервис
     */
    public function enableService(string $serviceName): array;

    /**
     * Отключить сервис
     */
    public function disableService(string $serviceName): array;
}
