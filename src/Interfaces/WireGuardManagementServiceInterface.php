<?php

namespace App\Interfaces;

interface WireGuardManagementServiceInterface
{
    /**
     * Поднять интерфейс
     */
    public function up(string $interfaceName): bool;

    /**
     * Опустить интерфейс
     */
    public function down(string $interfaceName): bool;

    /**
     * Перезапустить интерфейс
     */
    public function restart(string $interfaceName): bool;

    /**
     * Создать интерфейс
     */
    public function createInterface(string $interfaceName, array $config): bool;

    /**
     * Удалить интерфейс
     */
    public function deleteInterface(string $interfaceName): bool;
}
