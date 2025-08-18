<?php

namespace App\Interfaces;

interface PackageServiceInterface
{
    /**
     * Получить список доступных обновлений
     */
    public function getUpgradablePackages(): array;

    /**
     * Получить статистику пакетов
     */
    public function getPackageStats(): array;

    /**
     * Обновить список пакетов
     */
    public function updatePackageList(): array;

    /**
     * Обновить все пакеты
     */
    public function upgradeAllPackages(): array;

    /**
     * Обновить конкретный пакет
     */
    public function upgradePackage(string $packageName): array;
}
