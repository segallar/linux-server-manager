<?php

namespace App\Services\Firewall;

use App\Abstracts\BaseService;
use App\Interfaces\FirewallServiceInterface;

class FirewallService extends BaseService implements FirewallServiceInterface
{
    protected string $firewallType;
    protected string $configPath;
    
    public function __construct()
    {
        $this->detectFirewallType();
    }
    
    /**
     * Определяет тип файрвола (iptables или ufw)
     */
    protected function detectFirewallType(): void
    {
        // Проверяем, установлен ли ufw
        $ufwStatus = $this->executeCommand('which ufw');
        if ($ufwStatus) {
            $this->firewallType = 'ufw';
            $this->configPath = '/etc/ufw/';
        } else {
            $this->firewallType = 'iptables';
            $this->configPath = '/etc/iptables/';
        }
    }
    
    /**
     * Получает информацию о файрволе
     */
    public function getFirewallInfo(): array
    {
        $info = [
            'type' => $this->firewallType,
            'status' => $this->getStatus(),
            'rules_count' => $this->getRulesCount(),
            'default_policy' => $this->getDefaultPolicy(),
            'active_connections' => $this->getActiveConnections(),
            'blocked_attempts' => $this->getBlockedAttempts(),
            'last_activity' => $this->getLastActivity()
        ];
        
        return $info;
    }
    
    /**
     * Получает статус файрвола
     */
    public function getStatus(): string
    {
        if ($this->firewallType === 'ufw') {
            $output = $this->executeCommand('sudo ufw status');
            
            if (strpos($output, 'Status: active') !== false) {
                return 'active';
            } elseif (strpos($output, 'Status: inactive') !== false) {
                return 'inactive';
            } else {
                // Альтернативный способ проверки
                $output2 = $this->executeCommand('sudo ufw status verbose');
                
                if (strpos($output2, 'Status: active') !== false) {
                    return 'active';
                } elseif (strpos($output2, 'Status: inactive') !== false) {
                    return 'inactive';
                }
            }
        } else {
            // Для iptables проверяем наличие правил
            $output = $this->executeCommand('sudo iptables -L | wc -l');
            if ((int)$output > 3) { // Больше заголовков
                return 'active';
            }
        }
        
        return 'inactive';
    }
    
    /**
     * Получает количество правил
     */
    public function getRulesCount(): array
    {
        $counts = [
            'input' => 0,
            'output' => 0,
            'forward' => 0
        ];
        
        if ($this->firewallType === 'ufw') {
            $output = $this->executeCommand('sudo ufw status numbered');
            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                if (strpos($line, 'ALLOW') !== false) {
                    $counts['input']++;
                }
            }
        } else {
            // Для iptables
            $inputRules = $this->executeCommand('sudo iptables -L INPUT | wc -l');
            $outputRules = $this->executeCommand('sudo iptables -L OUTPUT | wc -l');
            $forwardRules = $this->executeCommand('sudo iptables -L FORWARD | wc -l');
            
            $counts['input'] = max(0, (int)$inputRules - 2); // Вычитаем заголовки
            $counts['output'] = max(0, (int)$outputRules - 2);
            $counts['forward'] = max(0, (int)$forwardRules - 2);
        }
        
        return $counts;
    }
    
    /**
     * Получает политику по умолчанию
     */
    public function getDefaultPolicy(): array
    {
        $policy = [
            'input' => 'ACCEPT',
            'output' => 'ACCEPT',
            'forward' => 'ACCEPT'
        ];
        
        if ($this->firewallType === 'ufw') {
            $output = $this->executeCommand('sudo ufw status verbose');
            
            if (strpos($output, 'Default: deny (incoming)') !== false) {
                $policy['input'] = 'DENY';
            }
            if (strpos($output, 'Default: deny (outgoing)') !== false) {
                $policy['output'] = 'DENY';
            }
        } else {
            // Для iptables
            $inputPolicy = $this->executeCommand('sudo iptables -L INPUT | head -1');
            $outputPolicy = $this->executeCommand('sudo iptables -L OUTPUT | head -1');
            $forwardPolicy = $this->executeCommand('sudo iptables -L FORWARD | head -1');
            
            if (strpos($inputPolicy, 'DROP') !== false) {
                $policy['input'] = 'DROP';
            }
            if (strpos($outputPolicy, 'DROP') !== false) {
                $policy['output'] = 'DROP';
            }
            if (strpos($forwardPolicy, 'DROP') !== false) {
                $policy['forward'] = 'DROP';
            }
        }
        
        return $policy;
    }
    
    /**
     * Получает активные соединения
     */
    public function getActiveConnections(): int
    {
        $output = $this->executeCommand('ss -tuln | wc -l');
        return max(0, (int)$output - 1); // Вычитаем заголовок
    }
    
    /**
     * Получает заблокированные попытки
     */
    public function getBlockedAttempts(): int
    {
        // Проверяем логи файрвола
        $output = $this->executeCommand('grep -c "UFW BLOCK\|iptables.*DROP" /var/log/syslog 2>/dev/null');
        return (int)$output;
    }
    
    /**
     * Получает последнюю активность
     */
    public function getLastActivity(): string
    {
        // Проверяем последние записи в логах
        $output = $this->executeCommand('grep "UFW\|iptables" /var/log/syslog | tail -1 | cut -d" " -f1-3 2>/dev/null');
        
        if ($output) {
            return trim($output);
        }
        
        return 'UNKNOWN';
    }
    
    /**
     * Получает статистику
     */
    public function getStats(): array
    {
        return [
            'status' => $this->getStatus(),
            'rules_count' => $this->getRulesCount(),
            'active_connections' => $this->getActiveConnections(),
            'blocked_attempts' => $this->getBlockedAttempts(),
            'last_activity' => $this->getLastActivity()
        ];
    }
    
    /**
     * Проверяет права доступа
     */
    public function checkPermissions(): array
    {
        $permissions = [
            'sudo' => false,
            'ufw' => false,
            'iptables' => false,
            'log_access' => false
        ];
        
        // Проверяем sudo права
        $sudoTest = $this->executeCommand('sudo -n true 2>/dev/null');
        $permissions['sudo'] = $sudoTest !== null;
        
        // Проверяем доступ к ufw
        if ($this->firewallType === 'ufw') {
            $ufwTest = $this->executeCommand('sudo ufw status 2>/dev/null');
            $permissions['ufw'] = $ufwTest !== null;
        }
        
        // Проверяем доступ к iptables
        $iptablesTest = $this->executeCommand('sudo iptables -L 2>/dev/null');
        $permissions['iptables'] = $iptablesTest !== null;
        
        // Проверяем доступ к логам
        $logTest = $this->executeCommand('tail -1 /var/log/syslog 2>/dev/null');
        $permissions['log_access'] = $logTest !== null;
        
        return $permissions;
    }
    
    /**
     * Получает детальную информацию о файрволе
     */
    public function getDetailedFirewallInfo(): array
    {
        $info = $this->getFirewallInfo();
        $info['permissions'] = $this->checkPermissions();
        $info['config_path'] = $this->configPath;
        $info['detected_at'] = date('Y-m-d H:i:s');
        
        return $info;
    }

    /**
     * Получает список правил файрвола
     */
    public function getRules(): array
    {
        $rules = [];
        
        if ($this->firewallType === 'ufw') {
            $output = $this->executeCommand('sudo ufw status numbered');
            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
                    $ruleNumber = $matches[1];
                    $ruleText = $matches[2];
                    
                    $rules[] = [
                        'id' => $ruleNumber,
                        'rule' => $ruleText,
                        'type' => $this->parseRuleType($ruleText),
                        'action' => $this->parseRuleAction($ruleText),
                        'source' => $this->parseRuleSource($ruleText),
                        'destination' => $this->parseRuleDestination($ruleText),
                        'port' => $this->parseRulePort($ruleText)
                    ];
                }
            }
        } else {
            // Для iptables
            $output = $this->executeCommand('sudo iptables -L --line-numbers');
            $lines = explode("\n", $output);
            $currentChain = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                if (preg_match('/^Chain\s+(\w+)/', $line, $matches)) {
                    $currentChain = $matches[1];
                } elseif (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
                    $ruleNumber = $matches[1];
                    $ruleText = $matches[2];
                    
                    $rules[] = [
                        'id' => $ruleNumber,
                        'chain' => $currentChain,
                        'rule' => $ruleText,
                        'type' => $this->parseIptablesRuleType($ruleText),
                        'action' => $this->parseIptablesRuleAction($ruleText),
                        'source' => $this->parseIptablesRuleSource($ruleText),
                        'destination' => $this->parseIptablesRuleDestination($ruleText)
                    ];
                }
            }
        }
        
        return $rules;
    }

    /**
     * Добавляет правило в файрвол
     */
    public function addRule(array $ruleData): array
    {
        try {
            $action = $ruleData['action'] ?? 'ALLOW';
            $port = $ruleData['port'] ?? '';
            $source = $ruleData['source'] ?? '';
            $protocol = $ruleData['protocol'] ?? 'tcp';
            
            if ($this->firewallType === 'ufw') {
                $command = "sudo ufw $action";
                
                if ($port) {
                    $command .= " $port";
                }
                
                if ($source) {
                    $command .= " from $source";
                }
                
                if ($protocol && $protocol !== 'tcp') {
                    $command .= "/$protocol";
                }
                
                $result = $this->executeCommand($command);
                
                if ($result !== null) {
                    return [
                        'success' => true,
                        'message' => 'Правило успешно добавлено'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ошибка добавления правила'
                    ];
                }
            } else {
                // Для iptables
                $chain = strtoupper($action) === 'ALLOW' ? 'ACCEPT' : 'DROP';
                $command = "sudo iptables -A INPUT";
                
                if ($source) {
                    $command .= " -s $source";
                }
                
                if ($port) {
                    $command .= " -p $protocol --dport $port";
                }
                
                $command .= " -j $chain";
                
                $result = $this->executeCommand($command);
                
                if ($result !== null) {
                    return [
                        'success' => true,
                        'message' => 'Правило успешно добавлено'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ошибка добавления правила'
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка добавления правила: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Удаляет правило из файрвола
     */
    public function deleteRule(string $ruleId): array
    {
        try {
            if ($this->firewallType === 'ufw') {
                $command = "sudo ufw delete $ruleId";
                $result = $this->executeCommand($command);
                
                if ($result !== null) {
                    return [
                        'success' => true,
                        'message' => 'Правило успешно удалено'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ошибка удаления правила'
                    ];
                }
            } else {
                // Для iptables
                $command = "sudo iptables -D INPUT $ruleId";
                $result = $this->executeCommand($command);
                
                if ($result !== null) {
                    return [
                        'success' => true,
                        'message' => 'Правило успешно удалено'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ошибка удаления правила'
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка удаления правила: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Включает файрвол
     */
    public function enable(): array
    {
        try {
            if ($this->firewallType === 'ufw') {
                $result = $this->executeCommand('sudo ufw enable');
                
                if ($result !== null) {
                    return [
                        'success' => true,
                        'message' => 'Файрвол UFW успешно включен'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ошибка включения файрвола'
                    ];
                }
            } else {
                // Для iptables создаем базовые правила
                $this->executeCommand('sudo iptables -P INPUT DROP');
                $this->executeCommand('sudo iptables -P FORWARD DROP');
                $this->executeCommand('sudo iptables -P OUTPUT ACCEPT');
                $this->executeCommand('sudo iptables -A INPUT -i lo -j ACCEPT');
                $this->executeCommand('sudo iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT');
                
                return [
                    'success' => true,
                    'message' => 'Файрвол iptables успешно включен'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка включения файрвола: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Выключает файрвол
     */
    public function disable(): array
    {
        try {
            if ($this->firewallType === 'ufw') {
                $result = $this->executeCommand('sudo ufw disable');
                
                if ($result !== null) {
                    return [
                        'success' => true,
                        'message' => 'Файрвол UFW успешно выключен'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ошибка выключения файрвола'
                    ];
                }
            } else {
                // Для iptables сбрасываем все правила
                $this->executeCommand('sudo iptables -F');
                $this->executeCommand('sudo iptables -P INPUT ACCEPT');
                $this->executeCommand('sudo iptables -P FORWARD ACCEPT');
                $this->executeCommand('sudo iptables -P OUTPUT ACCEPT');
                
                return [
                    'success' => true,
                    'message' => 'Файрвол iptables успешно выключен'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка выключения файрвола: ' . $e->getMessage()
            ];
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * Парсит тип правила UFW
     */
    private function parseRuleType(string $ruleText): string
    {
        if (strpos($ruleText, 'ALLOW') !== false) {
            return 'allow';
        } elseif (strpos($ruleText, 'DENY') !== false) {
            return 'deny';
        } else {
            return 'unknown';
        }
    }

    /**
     * Парсит действие правила UFW
     */
    private function parseRuleAction(string $ruleText): string
    {
        if (strpos($ruleText, 'ALLOW') !== false) {
            return 'ALLOW';
        } elseif (strpos($ruleText, 'DENY') !== false) {
            return 'DENY';
        } else {
            return 'UNKNOWN';
        }
    }

    /**
     * Парсит источник правила UFW
     */
    private function parseRuleSource(string $ruleText): string
    {
        if (preg_match('/from\s+([^\s]+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }

    /**
     * Парсит назначение правила UFW
     */
    private function parseRuleDestination(string $ruleText): string
    {
        if (preg_match('/to\s+([^\s]+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }

    /**
     * Парсит порт правила UFW
     */
    private function parseRulePort(string $ruleText): string
    {
        if (preg_match('/(\d+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Парсит тип правила iptables
     */
    private function parseIptablesRuleType(string $ruleText): string
    {
        if (strpos($ruleText, 'ACCEPT') !== false) {
            return 'accept';
        } elseif (strpos($ruleText, 'DROP') !== false) {
            return 'drop';
        } else {
            return 'unknown';
        }
    }

    /**
     * Парсит действие правила iptables
     */
    private function parseIptablesRuleAction(string $ruleText): string
    {
        if (strpos($ruleText, 'ACCEPT') !== false) {
            return 'ACCEPT';
        } elseif (strpos($ruleText, 'DROP') !== false) {
            return 'DROP';
        } else {
            return 'UNKNOWN';
        }
    }

    /**
     * Парсит источник правила iptables
     */
    private function parseIptablesRuleSource(string $ruleText): string
    {
        if (preg_match('/\s+([0-9.]+)\s+anywhere/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }

    /**
     * Парсит назначение правила iptables
     */
    private function parseIptablesRuleDestination(string $ruleText): string
    {
        if (preg_match('/anywhere\s+([0-9.]+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
}
