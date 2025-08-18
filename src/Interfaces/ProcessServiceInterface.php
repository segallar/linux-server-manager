<?php

namespace App\Interfaces;

interface ProcessServiceInterface
{
    /**
     * Получить активные процессы
     */
    public function getActiveProcesses(int $limit = 10): array;

    /**
     * Получить все процессы
     */
    public function getAllProcesses(): array;

    /**
     * Получить статистику процессов
     */
    public function getProcessStats(): array;

    /**
     * Получить количество процессов
     */
    public function getProcessCount(): int;
}
