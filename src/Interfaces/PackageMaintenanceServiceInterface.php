<?php

namespace App\Interfaces;

interface PackageMaintenanceServiceInterface
{
    /**
     * Очистить кэш пакетов
     */
    public function cleanPackageCache(): array;

    /**
     * Удалить неиспользуемые пакеты
     */
    public function autoremovePackages(): array;

    /**
     * Получить информацию о пакете
     */
    public function getPackageInfo(string $packageName): ?array;

    /**
     * Получить неиспользуемые пакеты
     */
    public function getUnusedPackages(): array;
}
