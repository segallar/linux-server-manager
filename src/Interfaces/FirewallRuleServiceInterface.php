<?php

namespace App\Interfaces;

interface FirewallRuleServiceInterface
{
    /**
     * Получить правила файрвола
     */
    public function getRules(): array;

    /**
     * Добавить правило
     */
    public function addRule(array $rule): array;

    /**
     * Удалить правило
     */
    public function deleteRule(string $id): array;

    /**
     * Включить файрвол
     */
    public function enable(): array;

    /**
     * Отключить файрвол
     */
    public function disable(): array;
}
