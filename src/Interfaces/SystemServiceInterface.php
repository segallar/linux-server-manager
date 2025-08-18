<?php

namespace App\Interfaces;

interface SystemServiceInterface
{
    /**
     * Получить информацию о CPU
     */
    public function getCpuInfo(): array;

    /**
     * Получить информацию о RAM
     */
    public function getMemoryInfo(): array;

    /**
     * Получить информацию о дисках
     */
    public function getDiskInfo(): array;

    /**
     * Получить информацию о сети
     */
    public function getNetworkInfo(): array;

    /**
     * Получить информацию о системе
     */
    public function getSystemInfo(): array;

    /**
     * Получить статистику
     */
    public function getStats(): array;
}
