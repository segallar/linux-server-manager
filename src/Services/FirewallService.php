<?php

namespace App\Services;

class FirewallService
{
    private string $firewallType;
    private string $configPath;
    
    public function __construct()
    {
        $this->detectFirewallType();
    }
    
    /**
     * Определяет тип файрвола (iptables или ufw)
     */
    private function detectFirewallType(): void
    {
        // Проверяем, установлен ли ufw
        $ufwStatus = shell_exec('which ufw 2>/dev/null');
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
            // Выполняем команду с полным путем и проверяем результат
            $output = shell_exec('sudo ufw status 2>/dev/null');
            
            // Логируем для отладки
            error_log("UFW Status Output: " . $output);
            
            if (strpos($output, 'Status: active') !== false) {
                return 'active';
            } elseif (strpos($output, 'Status: inactive') !== false) {
                return 'inactive';
            } else {
                // Если не можем определить, пробуем альтернативный способ
                $output2 = shell_exec('sudo ufw status verbose 2>/dev/null');
                error_log("UFW Status Verbose Output: " . $output2);
                
                if (strpos($output2, 'Status: active') !== false) {
                    return 'active';
                } elseif (strpos($output2, 'Status: inactive') !== false) {
                    return 'inactive';
                }
            }
        } else {
            // Для iptables проверяем наличие правил
            $output = shell_exec('sudo iptables -L 2>/dev/null | wc -l');
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
            $output = shell_exec('sudo ufw status numbered 2>/dev/null');
            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                if (strpos($line, 'ALLOW IN') !== false) {
                    $counts['input']++;
                } elseif (strpos($line, 'ALLOW OUT') !== false) {
                    $counts['output']++;
                }
            }
        } else {
            // Для iptables
            $input = shell_exec('sudo iptables -L INPUT 2>/dev/null | wc -l');
            $output = shell_exec('sudo iptables -L OUTPUT 2>/dev/null | wc -l');
            $forward = shell_exec('sudo iptables -L FORWARD 2>/dev/null | wc -l');
            
            $counts['input'] = max(0, (int)$input - 2); // Вычитаем заголовки
            $counts['output'] = max(0, (int)$output - 2);
            $counts['forward'] = max(0, (int)$forward - 2);
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
            $output = shell_exec('sudo ufw status verbose 2>/dev/null');
            if (preg_match('/Default:\s+(\w+)\s+\(incoming\)/', $output, $matches)) {
                $policy['input'] = strtoupper($matches[1]);
            }
            if (preg_match('/Default:\s+(\w+)\s+\(outgoing\)/', $output, $matches)) {
                $policy['output'] = strtoupper($matches[1]);
            }
        } else {
            // Для iptables
            $input = shell_exec('sudo iptables -L INPUT --line-numbers 2>/dev/null | tail -1');
            $output = shell_exec('sudo iptables -L OUTPUT --line-numbers 2>/dev/null | tail -1');
            $forward = shell_exec('sudo iptables -L FORWARD --line-numbers 2>/dev/null | tail -1');
            
            if (preg_match('/\s+(\w+)\s*$/', $input, $matches)) {
                $policy['input'] = strtoupper($matches[1]);
            }
            if (preg_match('/\s+(\w+)\s*$/', $output, $matches)) {
                $policy['output'] = strtoupper($matches[1]);
            }
            if (preg_match('/\s+(\w+)\s*$/', $forward, $matches)) {
                $policy['forward'] = strtoupper($matches[1]);
            }
        }
        
        return $policy;
    }
    
    /**
     * Получает активные соединения
     */
    public function getActiveConnections(): int
    {
        $output = shell_exec('netstat -an 2>/dev/null | grep ESTABLISHED | wc -l');
        return (int)$output;
    }
    
    /**
     * Получает количество заблокированных попыток
     */
    public function getBlockedAttempts(): int
    {
        // Пытаемся получить из логов
        $output = shell_exec('grep -c "DROP" /var/log/ufw.log 2>/dev/null || echo "0"');
        return (int)$output;
    }
    
    /**
     * Получает время последней активности
     */
    public function getLastActivity(): string
    {
        $logFile = $this->firewallType === 'ufw' ? '/var/log/ufw.log' : '/var/log/iptables.log';
        
        if (file_exists($logFile)) {
            $output = shell_exec("tail -1 $logFile 2>/dev/null | cut -d' ' -f1-3");
            return $output ? trim($output) : 'Неизвестно';
        }
        
        return 'Неизвестно';
    }
    
    /**
     * Получает список правил
     */
    public function getRules(): array
    {
        $rules = [];
        
        if ($this->firewallType === 'ufw') {
            $output = shell_exec('sudo ufw status numbered 2>/dev/null');
            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                // Ищем строки с правилами в формате [ID] Rule
                if (preg_match('/^\[(\d+)\]\s+(.+)$/', $line, $matches)) {
                    $ruleId = $matches[1];
                    $ruleText = $matches[2];
                    
                    // Пропускаем заголовки
                    if (strpos($ruleText, 'To') !== false || strpos($ruleText, '--') !== false) {
                        continue;
                    }
                    
                    $rules[] = [
                        'id' => $ruleId,
                        'action' => $this->parseUfwAction($ruleText),
                        'protocol' => $this->parseUfwProtocol($ruleText),
                        'port' => $this->parseUfwPort($ruleText),
                        'source' => $this->parseUfwSource($ruleText),
                        'description' => $this->parseUfwDescription($ruleText)
                    ];
                }
            }
        } else {
            // Для iptables
            $output = shell_exec('sudo iptables -L --line-numbers 2>/dev/null');
            $lines = explode("\n", $output);
            $currentChain = '';
            
            foreach ($lines as $line) {
                if (preg_match('/^Chain\s+(\w+)/', $line, $matches)) {
                    $currentChain = $matches[1];
                } elseif (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {
                    $ruleText = $matches[2];
                    $rules[] = [
                        'id' => $matches[1],
                        'chain' => $currentChain,
                        'action' => $this->parseIptablesAction($ruleText),
                        'protocol' => $this->parseIptablesProtocol($ruleText),
                        'port' => $this->parseIptablesPort($ruleText),
                        'source' => $this->parseIptablesSource($ruleText),
                        'target' => $this->parseIptablesTarget($ruleText)
                    ];
                }
            }
        }
        
        return $rules;
    }
    
    /**
     * Добавляет новое правило
     */
    public function addRule(array $rule): array
    {
        try {
            if ($this->firewallType === 'ufw') {
                return $this->addUfwRule($rule);
            } else {
                return $this->addIptablesRule($rule);
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка добавления правила: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Удаляет правило
     */
    public function deleteRule(string $id): array
    {
        try {
            if ($this->firewallType === 'ufw') {
                $command = "echo 'y' | sudo ufw delete $id 2>&1";
            } else {
                $command = "sudo iptables -D INPUT $id 2>&1";
            }
            
            $output = shell_exec($command);
            
            if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
                return [
                    'success' => false,
                    'message' => 'Ошибка удаления правила: ' . $output
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Правило успешно удалено'
            ];
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
                $command = "echo 'y' | sudo ufw enable 2>&1";
            } else {
                $command = "sudo iptables -P INPUT DROP && sudo iptables -P FORWARD DROP && sudo iptables -A INPUT -i lo -j ACCEPT && sudo iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT 2>&1";
            }
            
            $output = shell_exec($command);
            
            if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
                return [
                    'success' => false,
                    'message' => 'Ошибка включения файрвола: ' . $output
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Файрвол успешно включен'
            ];
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
                $command = "echo 'y' | sudo ufw disable 2>&1";
            } else {
                $command = "sudo iptables -F && sudo iptables -P INPUT ACCEPT && sudo iptables -P FORWARD ACCEPT && sudo iptables -P OUTPUT ACCEPT 2>&1";
            }
            
            $output = shell_exec($command);
            
            if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
                return [
                    'success' => false,
                    'message' => 'Ошибка выключения файрвола: ' . $output
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Файрвол успешно выключен'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка выключения файрвола: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Получает статистику файрвола
     */
    public function getStats(): array
    {
        $stats = [
            'type' => $this->firewallType,
            'status' => $this->getStatus(),
            'rules_count' => $this->getRulesCount(),
            'total_rules' => array_sum($this->getRulesCount()),
            'active_connections' => $this->getActiveConnections(),
            'blocked_attempts' => $this->getBlockedAttempts(),
            'last_activity' => $this->getLastActivity(),
            'default_policy' => $this->getDefaultPolicy()
        ];
        
        return $stats;
    }
    
    /**
     * Проверяет права доступа к файрволу
     */
    public function checkPermissions(): array
    {
        $result = [
            'can_read' => false,
            'can_write' => false,
            'can_execute' => false,
            'error' => null
        ];
        
        try {
            // Проверяем возможность чтения статуса
            $testOutput = shell_exec('sudo ufw status 2>&1');
            if ($testOutput && strpos($testOutput, 'Status:') !== false) {
                $result['can_read'] = true;
            } else {
                $result['error'] = 'Не удается прочитать статус UFW: ' . $testOutput;
            }
            
            // Проверяем возможность выполнения команд
            $testOutput = shell_exec('sudo ufw status verbose 2>&1');
            if ($testOutput && strpos($testOutput, 'Status:') !== false) {
                $result['can_execute'] = true;
            }
            
            // Проверяем возможность записи (попытка добавить тестовое правило)
            // Это безопасная проверка, так как мы не сохраняем правило
            $testOutput = shell_exec('sudo ufw --dry-run allow 99999 2>&1');
            if ($testOutput && strpos($testOutput, 'Skipping') !== false) {
                $result['can_write'] = true;
            }
            
        } catch (\Exception $e) {
            $result['error'] = 'Ошибка проверки прав: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Получает детальную информацию о файрволе с проверкой прав
     */
    public function getDetailedFirewallInfo(): array
    {
        $info = $this->getFirewallInfo();
        $permissions = $this->checkPermissions();
        
        return [
            'info' => $info,
            'permissions' => $permissions,
            'debug' => [
                'firewall_type' => $this->firewallType,
                'config_path' => $this->configPath,
                'raw_status' => shell_exec('sudo ufw status 2>&1'),
                'raw_verbose' => shell_exec('sudo ufw status verbose 2>&1')
            ]
        ];
    }
    
    // Вспомогательные методы для парсинга правил UFW
    private function parseUfwAction(string $ruleText): string
    {
        if (strpos($ruleText, 'ALLOW') !== false) return 'ALLOW';
        if (strpos($ruleText, 'DENY') !== false) return 'DENY';
        if (strpos($ruleText, 'REJECT') !== false) return 'REJECT';
        return 'UNKNOWN';
    }
    
    private function parseUfwProtocol(string $ruleText): string
    {
        if (strpos($ruleText, '/tcp') !== false) return 'tcp';
        if (strpos($ruleText, '/udp') !== false) return 'udp';
        if (strpos($ruleText, '/icmp') !== false) return 'icmp';
        return 'any';
    }
    
    private function parseUfwPort(string $ruleText): string
    {
        // Ищем порт в формате "80/tcp", "443", "8080" и т.д.
        if (preg_match('/(\d+)\/(tcp|udp|icmp)/', $ruleText, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^(\d+)\s/', $ruleText, $matches)) {
            return $matches[1];
        }
        // Проверяем специальные случаи
        if (strpos($ruleText, 'OpenSSH') !== false) return '22';
        if (strpos($ruleText, 'Anywhere on wg0') !== false) return 'any';
        return 'any';
    }
    
    private function parseUfwSource(string $ruleText): string
    {
        // Ищем источник в конце строки
        if (preg_match('/\s+(Anywhere.*?)$/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    /**
     * Получает полное описание порта с сервисом
     */
    private function getPortDescription(int $port, string $protocol = 'tcp'): string
    {
        $service = $this->getPortServiceInfo($port, $protocol);
        $protocolUpper = strtoupper($protocol);
        
        return "Порт $port ($protocolUpper) - $service";
    }
    
    /**
     * Получает полное описание порта с сервисом
     */
    private function getPortServiceInfo(int $port, string $protocol = 'tcp'): string
    {
        $services = [
            // Стандартные порты
            20 => 'FTP Data',
            21 => 'FTP Control',
            22 => 'SSH',
            23 => 'Telnet',
            25 => 'SMTP',
            53 => 'DNS',
            67 => 'DHCP Server',
            68 => 'DHCP Client',
            69 => 'TFTP',
            80 => 'HTTP',
            81 => 'HTTP Alternative',
            110 => 'POP3',
            123 => 'NTP',
            137 => 'NetBIOS Name Service',
            138 => 'NetBIOS Datagram',
            139 => 'NetBIOS Session',
            143 => 'IMAP',
            161 => 'SNMP',
            162 => 'SNMP Trap',
            389 => 'LDAP',
            443 => 'HTTPS',
            445 => 'SMB/CIFS',
            465 => 'SMTPS',
            514 => 'Syslog',
            515 => 'LPR/LPD',
            520 => 'RIP',
            567 => 'AMQP (RabbitMQ)',
            587 => 'SMTP Submission',
            631 => 'IPP (CUPS)',
            993 => 'IMAPS',
            995 => 'POP3S',
            1433 => 'MSSQL',
            1521 => 'Oracle DB',
            1723 => 'PPTP VPN',
            3306 => 'MySQL',
            3389 => 'RDP',
            5432 => 'PostgreSQL',
            5900 => 'VNC',
            6379 => 'Redis',
            7844 => 'Custom Service',
            8080 => 'HTTP Alternative',
            8443 => 'HTTPS Alternative',
            9000 => 'Web Services',
            9090 => 'Web Services',
            27017 => 'MongoDB',
            
            // UDP порты
            67 => 'DHCP Server',
            68 => 'DHCP Client',
            69 => 'TFTP',
            123 => 'NTP',
            137 => 'NetBIOS Name Service',
            138 => 'NetBIOS Datagram',
            161 => 'SNMP',
            162 => 'SNMP Trap',
            514 => 'Syslog',
            520 => 'RIP',
            1194 => 'OpenVPN',
            1701 => 'L2TP',
            500 => 'ISAKMP/IKE',
            4500 => 'IPSec NAT-T',
            3478 => 'STUN/TURN',
            5349 => 'STUN/TURN TLS',
            10000 => 'Webmin',
            10001 => 'Webmin SSL',
        ];
        
        return $services[$port] ?? 'Неизвестный сервис';
    }
    
    // Вспомогательные методы для парсинга правил iptables
    private function parseIptablesAction(string $ruleText): string
    {
        if (preg_match('/\s+(\w+)\s*$/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'UNKNOWN';
    }
    
    private function parseIptablesProtocol(string $ruleText): string
    {
        if (preg_match('/-p\s+(\w+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    private function parseIptablesPort(string $ruleText): string
    {
        if (preg_match('/--dport\s+(\d+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    private function parseIptablesSource(string $ruleText): string
    {
        if (preg_match('/-s\s+([^\s]+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    private function parseIptablesTarget(string $ruleText): string
    {
        if (preg_match('/-j\s+(\w+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'UNKNOWN';
    }
    
    // Методы для добавления правил
    private function addUfwRule(array $rule): array
    {
        $command = "sudo ufw ";
        
        if ($rule['action'] === 'ALLOW') {
            $command .= "allow ";
        } elseif ($rule['action'] === 'DENY') {
            $command .= "deny ";
        } else {
            return ['success' => false, 'message' => 'Неподдерживаемое действие'];
        }
        
        if (!empty($rule['port']) && $rule['port'] !== 'any') {
            $command .= $rule['port'];
        }
        
        if (!empty($rule['protocol']) && $rule['protocol'] !== 'any') {
            $command .= "/" . $rule['protocol'];
        }
        
        if (!empty($rule['source']) && $rule['source'] !== 'any') {
            $command .= " from " . $rule['source'];
        }
        
        if (!empty($rule['description'])) {
            $command .= " comment '" . $rule['description'] . "'";
        }
        
        $command .= " 2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
            return ['success' => false, 'message' => 'Ошибка добавления правила: ' . $output];
        }
        
        return ['success' => true, 'message' => 'Правило успешно добавлено'];
    }
    
    private function addIptablesRule(array $rule): array
    {
        $command = "sudo iptables -A INPUT ";
        
        if (!empty($rule['protocol']) && $rule['protocol'] !== 'any') {
            $command .= "-p " . $rule['protocol'] . " ";
        }
        
        if (!empty($rule['port']) && $rule['port'] !== 'any') {
            $command .= "--dport " . $rule['port'] . " ";
        }
        
        if (!empty($rule['source']) && $rule['source'] !== 'any') {
            $command .= "-s " . $rule['source'] . " ";
        }
        
        if (!empty($rule['target'])) {
            $command .= "-j " . $rule['target'] . " ";
        }
        
        $command .= "2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'error') !== false || strpos($output, 'Error') !== false) {
            return ['success' => false, 'message' => 'Ошибка добавления правила: ' . $output];
        }
        
        return ['success' => true, 'message' => 'Правило успешно добавлено'];
    }

    private function parseUfwDescription(string $ruleText): string
    {
        // Специальные случаи
        if (strpos($ruleText, 'OpenSSH') !== false) {
            $description = 'SSH доступ (порт 22)';
        } elseif (strpos($ruleText, 'Anywhere on wg0') !== false) {
            $description = 'WireGuard интерфейс';
        } elseif (preg_match('/(\d+)\/(tcp|udp)/', $ruleText, $matches)) {
            $port = (int)$matches[1];
            $protocol = $matches[2];
            $description = $this->getPortDescription($port, $protocol);
        } elseif (preg_match('/^(\d+)\s/', $ruleText, $matches)) {
            $port = (int)$matches[1];
            $description = $this->getPortDescription($port, 'tcp');
        } else {
            $description = 'Правило файрвола';
        }
        
        // Добавляем IPv6 индикатор
        if (strpos($ruleText, '(v6)') !== false) {
            $description .= ' (IPv6)';
        }
        
        return $description;
    }
}
