<?php

namespace App\Services\Network;

use App\Abstracts\BaseService;
use App\Interfaces\PortForwardingServiceInterface;
use App\Exceptions\ServiceException;
use App\Exceptions\ValidationException;

class PortForwardingService extends BaseService implements PortForwardingServiceInterface
{
    private string $rulesFile = '/tmp/port_forwarding_rules.json';

    /**
     * Получить правила перенаправления портов
     */
    public function getPortForwardingRules(): array
    {
        if (!file_exists($this->rulesFile)) {
            return [];
        }

        $content = file_get_contents($this->rulesFile);
        if (!$content) {
            return [];
        }

        $rules = json_decode($content, true);
        if (!is_array($rules)) {
            return [];
        }

        // Добавляем статус для каждого правила
        foreach ($rules as &$rule) {
            $rule['status'] = $this->getRuleStatus($rule['id']);
        }

        return $rules;
    }

    /**
     * Добавить правило перенаправления портов
     */
    public function addPortForwardingRule(string $name, int $externalPort, int $internalPort, string $protocol = 'tcp', string $targetIp = '127.0.0.1'): array
    {
        // Валидация параметров
        if (empty($name)) {
            throw new ValidationException("Имя правила не может быть пустым");
        }

        if (!$this->validatePort($externalPort)) {
            throw new ValidationException("Неверный внешний порт: {$externalPort}");
        }

        if (!$this->validatePort($internalPort)) {
            throw new ValidationException("Неверный внутренний порт: {$internalPort}");
        }

        if (!in_array($protocol, ['tcp', 'udp'])) {
            throw new ValidationException("Неверный протокол: {$protocol}. Допустимые значения: tcp, udp");
        }

        if (!$this->validateIpAddress($targetIp)) {
            throw new ValidationException("Неверный IP адрес назначения: {$targetIp}");
        }

        // Проверяем, что внешний порт не занят
        if ($this->isPortInUse($externalPort)) {
            throw new ValidationException("Внешний порт {$externalPort} уже используется");
        }

        // Проверяем уникальность имени
        $rules = $this->getPortForwardingRules();
        foreach ($rules as $rule) {
            if ($rule['name'] === $name) {
                throw new ValidationException("Правило с именем '{$name}' уже существует");
            }
        }

        $ruleId = uniqid('rule_');
        
        $rule = [
            'id' => $ruleId,
            'name' => $name,
            'external_port' => $externalPort,
            'internal_port' => $internalPort,
            'protocol' => $protocol,
            'target_ip' => $targetIp,
            'status' => 'inactive',
            'created_at' => date('Y-m-d H:i:s'),
            'last_used' => null,
            'connection_count' => 0
        ];

        $rules[] = $rule;
        $this->saveRules($rules);

        return [
            'success' => true,
            'message' => 'Правило перенаправления портов создано успешно',
            'rule' => $rule
        ];
    }

    /**
     * Удалить правило перенаправления портов
     */
    public function deletePortForwardingRule(string $ruleId): array
    {
        $rules = $this->getPortForwardingRules();
        $ruleIndex = $this->findRuleById($rules, $ruleId);
        
        if ($ruleIndex === -1) {
            throw new ValidationException("Правило с ID {$ruleId} не найдено");
        }

        $rule = $rules[$ruleIndex];
        
        // Удаляем правило из iptables, если оно активно
        if ($rule['status'] === 'active') {
            $this->removeIptablesRule($rule);
        }

        // Удаляем правило из списка
        array_splice($rules, $ruleIndex, 1);
        $this->saveRules($rules);

        return [
            'success' => true,
            'message' => 'Правило перенаправления портов удалено успешно'
        ];
    }

    /**
     * Получить предупреждения безопасности для перенаправления портов
     */
    public function getPortForwardingSecurityWarnings(): array
    {
        try {
            $rules = $this->getPortForwardingRules();
            $warnings = [];

            foreach ($rules as $rule) {
                $externalPort = $rule['external_port'];
                $status = $this->getRuleStatus($rule['id']);

                // Проверяем опасные порты
                $dangerousPorts = [
                    21 => 'FTP',
                    22 => 'SSH',
                    23 => 'Telnet',
                    25 => 'SMTP',
                    53 => 'DNS',
                    80 => 'HTTP',
                    110 => 'POP3',
                    143 => 'IMAP',
                    3306 => 'MySQL',
                    5432 => 'PostgreSQL',
                    3389 => 'RDP',
                    5900 => 'VNC',
                    6379 => 'Redis',
                    8080 => 'HTTP Alt',
                    8443 => 'HTTPS Alt'
                ];

                if (isset($dangerousPorts[$externalPort])) {
                    $service = $dangerousPorts[$externalPort];
                    $risk = $this->getServiceRisk($service);
                    $recommendation = $this->getSecurityRecommendation($service, $risk);

                    $warnings[] = [
                        'type' => 'dangerous_port',
                        'title' => "Открыт порт {$externalPort} ({$service})",
                        'description' => "Порт {$externalPort} используется для {$service}",
                        'risk' => $risk,
                        'rule_id' => $rule['id'],
                        'rule_name' => $rule['name'],
                        'external_port' => $externalPort,
                        'internal_ip' => $rule['target_ip'],
                        'status' => $status,
                        'recommendation' => $recommendation
                    ];
                }

                // Проверяем HTTP без HTTPS
                if ($externalPort == 80 && $status === 'active') {
                    $hasHttps = false;
                    foreach ($rules as $otherRule) {
                        if ($otherRule['external_port'] == 443 && $this->getRuleStatus($otherRule['id']) === 'active') {
                            $hasHttps = true;
                            break;
                        }
                    }
                    
                    if (!$hasHttps) {
                        $warnings[] = [
                            'type' => 'http_without_https',
                            'title' => 'HTTP без HTTPS',
                            'description' => 'Порт 80 открыт, но порт 443 не настроен',
                            'risk' => 'medium',
                            'rule_id' => $rule['id'],
                            'rule_name' => $rule['name'],
                            'external_port' => $externalPort,
                            'internal_ip' => $rule['target_ip'],
                            'status' => $status,
                            'recommendation' => 'Настройте HTTPS (порт 443) для безопасного соединения'
                        ];
                    }
                }
                
                // Проверяем неиспользуемые правила
                if ($status === 'inactive') {
                    $warnings[] = [
                        'type' => 'inactive_rule',
                        'title' => "Правило '{$rule['name']}' неактивно",
                        'description' => "Порт {$externalPort} не используется",
                        'risk' => 'low',
                        'rule_id' => $rule['id'],
                        'rule_name' => $rule['name'],
                        'external_port' => $externalPort,
                        'internal_ip' => $rule['target_ip'],
                        'status' => $status,
                        'recommendation' => 'Удалите неиспользуемое правило или активируйте его'
                    ];
                }
            }
            
            // Проверяем общие проблемы безопасности
            $activeRules = array_filter($rules, function($rule) {
                return $this->getRuleStatus($rule['id']) === 'active';
            });
            
            if (count($activeRules) > 10) {
                $warnings[] = [
                    'type' => 'too_many_rules',
                    'title' => 'Слишком много открытых портов',
                    'description' => 'Открыто ' . count($activeRules) . ' портов',
                    'risk' => 'medium',
                    'rule_id' => null,
                    'rule_name' => null,
                    'external_port' => null,
                    'internal_ip' => null,
                    'status' => null,
                    'recommendation' => 'Рассмотрите возможность закрытия неиспользуемых портов'
                ];
            }
            
            // Сортируем по уровню риска
            $riskOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            usort($warnings, function($a, $b) use ($riskOrder) {
                return $riskOrder[$b['risk']] - $riskOrder[$a['risk']];
            });
            
            return $warnings;
        } catch (\Exception $e) {
            $this->logError('Ошибка получения предупреждений безопасности: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Активировать правило
     */
    public function activateRule(string $ruleId): array
    {
        $rules = $this->getPortForwardingRules();
        $ruleIndex = $this->findRuleById($rules, $ruleId);
        
        if ($ruleIndex === -1) {
            throw new ValidationException("Правило с ID {$ruleId} не найдено");
        }

        $rule = $rules[$ruleIndex];
        
        if ($rule['status'] === 'active') {
            return [
                'success' => false,
                'message' => 'Правило уже активно'
            ];
        }

        // Добавляем правило в iptables
        $result = $this->addIptablesRule($rule);
        
        if (!$result['success']) {
            throw new ServiceException("Ошибка активации правила: " . $result['error']);
        }

        $rules[$ruleIndex]['status'] = 'active';
        $this->saveRules($rules);

        return [
            'success' => true,
            'message' => 'Правило активировано успешно',
            'rule' => $rules[$ruleIndex]
        ];
    }

    /**
     * Деактивировать правило
     */
    public function deactivateRule(string $ruleId): array
    {
        $rules = $this->getPortForwardingRules();
        $ruleIndex = $this->findRuleById($rules, $ruleId);
        
        if ($ruleIndex === -1) {
            throw new ValidationException("Правило с ID {$ruleId} не найдено");
        }

        $rule = $rules[$ruleIndex];
        
        if ($rule['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'Правило не активно'
            ];
        }

        // Удаляем правило из iptables
        $result = $this->removeIptablesRule($rule);
        
        if (!$result['success']) {
            $this->logError("Ошибка деактивации правила: " . $result['error'], [
                'rule_id' => $ruleId
            ]);
        }

        $rules[$ruleIndex]['status'] = 'inactive';
        $this->saveRules($rules);

        return [
            'success' => true,
            'message' => 'Правило деактивировано успешно',
            'rule' => $rules[$ruleIndex]
        ];
    }

    /**
     * Сохранить правила в файл
     */
    private function saveRules(array $rules): void
    {
        $content = json_encode($rules, JSON_PRETTY_PRINT);
        file_put_contents($this->rulesFile, $content);
    }

    /**
     * Найти правило по ID
     */
    private function findRuleById(array $rules, string $ruleId): int
    {
        foreach ($rules as $index => $rule) {
            if ($rule['id'] === $ruleId) {
                return $index;
            }
        }
        return -1;
    }

    /**
     * Получить статус правила
     */
    private function getRuleStatus(string $ruleId): string
    {
        $rules = $this->getPortForwardingRules();
        $ruleIndex = $this->findRuleById($rules, $ruleId);
        
        if ($ruleIndex === -1) {
            return 'not_found';
        }

        $rule = $rules[$ruleIndex];
        
        // Проверяем, есть ли правило в iptables
        if ($rule['status'] === 'active') {
            $result = $this->checkIptablesRule($rule);
            if (!$result) {
                return 'inactive';
            }
        }

        return $rule['status'] ?? 'inactive';
    }

    /**
     * Проверить, используется ли порт
     */
    private function isPortInUse(int $port): bool
    {
        $result = $this->safeExecute("ss -tuln | grep :{$port}");
        return $result['success'] && !empty($result['output']);
    }

    /**
     * Добавить правило в iptables
     */
    private function addIptablesRule(array $rule): array
    {
        $command = sprintf(
            'iptables -t nat -A PREROUTING -p %s --dport %d -j DNAT --to-destination %s:%d',
            $rule['protocol'],
            $rule['external_port'],
            $rule['target_ip'],
            $rule['internal_port']
        );

        return $this->safeExecute($command);
    }

    /**
     * Удалить правило из iptables
     */
    private function removeIptablesRule(array $rule): array
    {
        $command = sprintf(
            'iptables -t nat -D PREROUTING -p %s --dport %d -j DNAT --to-destination %s:%d',
            $rule['protocol'],
            $rule['external_port'],
            $rule['target_ip'],
            $rule['internal_port']
        );

        return $this->safeExecute($command);
    }

    /**
     * Проверить наличие правила в iptables
     */
    private function checkIptablesRule(array $rule): bool
    {
        $command = sprintf(
            'iptables -t nat -C PREROUTING -p %s --dport %d -j DNAT --to-destination %s:%d 2>/dev/null',
            $rule['protocol'],
            $rule['external_port'],
            $rule['target_ip'],
            $rule['internal_port']
        );

        $result = $this->safeExecute($command);
        return $result['success'];
    }

    /**
     * Получить уровень риска сервиса
     */
    private function getServiceRisk(string $service): string
    {
        $highRisk = ['Telnet', 'FTP', 'VNC'];
        $mediumRisk = ['HTTP', 'SMTP', 'POP3', 'IMAP', 'MySQL', 'PostgreSQL', 'Redis'];
        
        if (in_array($service, $highRisk)) {
            return 'high';
        } elseif (in_array($service, $mediumRisk)) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Получить рекомендации по безопасности
     */
    private function getSecurityRecommendation(string $service, string $risk): string
    {
        $recommendations = [
            'FTP' => 'Используйте SFTP вместо FTP',
            'SSH' => 'Настройте ключевую аутентификацию и ограничьте доступ',
            'Telnet' => 'Замените на SSH',
            'SMTP' => 'Настройте аутентификацию и шифрование',
            'DNS' => 'Настройте DNSSEC',
            'HTTP' => 'Перенаправьте на HTTPS',
            'POP3' => 'Используйте POP3S или IMAPS',
            'IMAP' => 'Используйте IMAPS',
            'MySQL' => 'Ограничьте доступ по IP и используйте сильные пароли',
            'PostgreSQL' => 'Ограничьте доступ по IP и используйте SSL',
            'RDP' => 'Используйте VPN или ограничьте доступ по IP',
            'VNC' => 'Используйте SSH туннель для VNC',
            'Redis' => 'Настройте аутентификацию и ограничьте доступ',
            'HTTP Alt' => 'Перенаправьте на HTTPS',
            'HTTPS Alt' => 'Проверьте SSL сертификат'
        ];
        
        return $recommendations[$service] ?? 'Проверьте настройки безопасности';
    }
}
