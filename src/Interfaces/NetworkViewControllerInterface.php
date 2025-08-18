<?php

namespace App\Interfaces;

interface NetworkViewControllerInterface
{
    /**
     * Главная страница сети
     */
    public function index(): string;

    /**
     * Страница SSH туннелей
     */
    public function ssh(): string;

    /**
     * Страница проброса портов
     */
    public function portForwarding(): string;

    /**
     * Страница WireGuard
     */
    public function wireguard(): string;

    /**
     * Страница Cloudflare
     */
    public function cloudflare(): string;

    /**
     * Страница маршрутизации
     */
    public function routing(): string;
}
