<?php

namespace App\Abstracts;

abstract class BaseService
{
    /**
     * Выполнить команду в shell
     */
    protected function executeCommand(string $command): ?string
    {
        return shell_exec($command . ' 2>/dev/null');
    }

    /**
     * Проверить, что команда выполнена успешно
     */
    protected function isCommandSuccessful(string $command): bool
    {
        $output = $this->executeCommand($command);
        return $output !== null && $output !== '';
    }

    /**
     * Валидировать IP адрес
     */
    protected function validateIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Валидировать порт
     */
    protected function validatePort(int $port): bool
    {
        return $port >= 1 && $port <= 65535;
    }

    /**
     * Валидировать имя интерфейса
     */
    protected function validateInterfaceName(string $name): bool
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $name) === 1;
    }

    /**
     * Логировать ошибку
     */
    protected function logError(string $message, array $context = []): void
    {
        error_log(sprintf('[%s] %s: %s', 
            date('Y-m-d H:i:s'), 
            $message, 
            json_encode($context)
        ));
    }

    /**
     * Безопасно выполнить команду с проверкой результата
     */
    protected function safeExecute(string $command): array
    {
        try {
            $output = $this->executeCommand($command);
            
            if ($output === null) {
                return [
                    'success' => false,
                    'error' => 'Команда не выполнена',
                    'output' => null
                ];
            }

            return [
                'success' => true,
                'error' => null,
                'output' => trim($output)
            ];
        } catch (\Exception $e) {
            $this->logError('Ошибка выполнения команды: ' . $e->getMessage(), [
                'command' => $command
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'output' => null
            ];
        }
    }
}
