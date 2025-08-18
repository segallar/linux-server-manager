<?php

namespace App\Interfaces;

interface NetworkRoutingServiceInterface
{
    /**
     * Получить таблицу маршрутов
     */
    public function getRoutes(): array;

    /**
     * Получить статистику маршрутизации
     */
    public function getRoutingStats(): array;

    /**
     * Добавить маршрут
     */
    public function addRoute(string $destination, string $gateway, string $interface): array;

    /**
     * Удалить маршрут
     */
    public function deleteRoute(string $destination, string $gateway = '', string $interface = ''): array;
}
