<?php

namespace App\Interfaces;

interface FirewallServiceInterface
{
    /**
     * Получить информацию о файрволе
     */
    public function getFirewallInfo(): array;

    /**
     * Получить статус файрвола
     */
    public function getStatus(): string;

    /**
     * Получить количество правил
     */
    public function getRulesCount(): array;

    /**
     * Получить политику по умолчанию
     */
    public function getDefaultPolicy(): array;

    /**
     * Получить активные соединения
     */
    public function getActiveConnections(): int;

    /**
     * Получить заблокированные попытки
     */
    public function getBlockedAttempts(): int;

    /**
     * Получить последнюю активность
     */
    public function getLastActivity(): string;

    /**
     * Получить статистику
     */
    public function getStats(): array;

    /**
     * Проверить права доступа
     */
    public function checkPermissions(): array;

    /**
     * Получить детальную информацию о файрволе
     */
    public function getDetailedFirewallInfo(): array;
}
