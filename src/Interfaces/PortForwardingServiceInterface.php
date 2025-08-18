<?php

namespace App\Interfaces;

interface PortForwardingServiceInterface
{
    /**
     * Получить правила перенаправления портов
     */
    public function getPortForwardingRules(): array;

    /**
     * Добавить правило перенаправления портов
     */
    public function addPortForwardingRule(string $name, int $externalPort, int $internalPort, string $protocol = 'tcp', string $targetIp = '127.0.0.1'): array;

    /**
     * Удалить правило перенаправления портов
     */
    public function deletePortForwardingRule(string $ruleId): array;

    /**
     * Получить предупреждения безопасности для перенаправления портов
     */
    public function getPortForwardingSecurityWarnings(): array;
}
