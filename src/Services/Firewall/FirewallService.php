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
}
