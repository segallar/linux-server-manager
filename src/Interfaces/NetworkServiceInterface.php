<?php

namespace App\Interfaces;

interface NetworkServiceInterface
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
     * Получить информацию о сетевых интерфейсах
     */
    public function getInterfaces(): array;

    /**
     * Получить информацию о DNS
     */
    public function getDnsInfo(): array;

    /**
     * Получить активные соединения
     */
    public function getConnections(): array;

    /**
     * Получить статистику трафика
     */
    public function getTrafficStats(): array;
}
