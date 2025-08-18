<?php

namespace App\Interfaces;

interface CloudflareServiceInterface
{
    /**
     * Проверить, установлен ли cloudflared
     */
    public function isInstalled(): bool;

    /**
     * Проверить, авторизован ли cloudflared
     */
    public function isAuthenticated(): bool;

    /**
     * Получить список всех туннелей
     */
    public function getTunnels(): array;

    /**
     * Получить статистику
     */
    public function getStats(): array;
}
