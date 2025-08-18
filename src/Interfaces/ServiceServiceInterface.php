<?php

namespace App\Interfaces;

interface ServiceServiceInterface
{
    /**
     * Получить список всех сервисов
     */
    public function getAllServices(): array;

    /**
     * Получить статистику сервисов
     */
    public function getStats(): array;

    /**
     * Получить информацию о конкретном сервисе
     */
    public function getServiceInfo(string $serviceName): ?array;

    /**
     * Получить популярные сервисы
     */
    public function getPopularServices(): array;

    /**
     * Получить сервисы
     */
    public function getServices(): array;
}
