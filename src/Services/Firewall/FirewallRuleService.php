<?php

namespace App\Services\Firewall;

use App\Abstracts\BaseService;
use App\Interfaces\FirewallRuleServiceInterface;
use App\Exceptions\ServiceException;
use App\Exceptions\ValidationException;

class FirewallRuleService extends BaseService implements FirewallRuleServiceInterface
{
    protected string $firewallType;
    
    public function __construct()
    {
        $this->detectFirewallType();
    }
    
    /**
     * Определяет тип файрвола
     */
    protected function detectFirewallType(): void
    {
        $ufwStatus = $this->executeCommand('which ufw');
        $this->firewallType = $ufwStatus ? 'ufw' : 'iptables';
    }
    
    /**
     * Получить правила файрвола
     */
    public function getRules(): array
    {
        $rules = [];
        
        if ($this->firewallType === 'ufw') {
            $rules = $this->getUfwRules();
        } else {
            $rules = $this->getIptablesRules();
        }
        
        return $rules;
    }
    
    /**
     * Добавить правило
     */
    public function addRule(array $rule): array
    {
        // Валидация параметров
        if (empty($rule['action'])) {
            throw new ValidationException("Действие правила обязательно");
        }
        
        if (!in_array($rule['action'], ['ALLOW', 'DENY', 'DROP'])) {
            throw new ValidationException("Неподдерживаемое действие: " . $rule['action']);
        }
        
        if ($this->firewallType === 'ufw') {
            return $this->addUfwRule($rule);
        } else {
            return $this->addIptablesRule($rule);
        }
    }
    
    /**
     * Удалить правило
     */
    public function deleteRule(string $id): array
    {
        if ($this->firewallType === 'ufw') {
            return $this->deleteUfwRule($id);
        } else {
            return $this->deleteIptablesRule($id);
        }
    }
    
    /**
     * Включить файрвол
     */
    public function enable(): array
    {
        if ($this->firewallType === 'ufw') {
            $result = $this->safeExecute('sudo ufw enable');
        } else {
            // Для iptables создаем базовые правила
            $result = $this->safeExecute('sudo iptables -P INPUT DROP && sudo iptables -P FORWARD DROP && sudo iptables -P OUTPUT ACCEPT');
        }
        
        if (!$result['success']) {
            throw new ServiceException("Ошибка включения файрвола: " . $result['error']);
        }
        
        return [
            'success' => true,
            'message' => 'Файрвол успешно включен'
        ];
    }
    
    /**
     * Отключить файрвол
     */
    public function disable(): array
    {
        if ($this->firewallType === 'ufw') {
            $result = $this->safeExecute('sudo ufw disable');
        } else {
            // Для iptables сбрасываем все правила
            $result = $this->safeExecute('sudo iptables -F && sudo iptables -X && sudo iptables -P INPUT ACCEPT && sudo iptables -P FORWARD ACCEPT && sudo iptables -P OUTPUT ACCEPT');
        }
        
        if (!$result['success']) {
            throw new ServiceException("Ошибка отключения файрвола: " . $result['error']);
        }
        
        return [
            'success' => true,
            'message' => 'Файрвол успешно отключен'
        ];
    }
    
    /**
     * Получить правила UFW
     */
    private function getUfwRules(): array
    {
        $output = $this->executeCommand('sudo ufw status numbered');
        if (!$output) {
            return [];
        }
        
        $lines = explode("\n", trim($output));
        $rules = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'Status:') !== false) {
                continue;
            }
            
            $rule = $this->parseUfwRule($line);
            if ($rule) {
                $rules[] = $rule;
            }
        }
        
        return $rules;
    }
    
    /**
     * Получить правила iptables
     */
    private function getIptablesRules(): array
    {
        $output = $this->executeCommand('sudo iptables -L INPUT --line-numbers');
        if (!$output) {
            return [];
        }
        
        $lines = explode("\n", trim($output));
        $rules = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'Chain') !== false || strpos($line, 'target') !== false) {
                continue;
            }
            
            $rule = $this->parseIptablesRule($line);
            if ($rule) {
                $rules[] = $rule;
            }
        }
        
        return $rules;
    }
    
    /**
     * Добавить правило UFW
     */
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
        
        $result = $this->safeExecute($command);
        
        if (!$result['success']) {
            throw new ServiceException("Ошибка добавления правила: " . $result['error']);
        }
        
        return [
            'success' => true,
            'message' => 'Правило успешно добавлено'
        ];
    }
    
    /**
     * Добавить правило iptables
     */
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
        
        $result = $this->safeExecute($command);
        
        if (!$result['success']) {
            throw new ServiceException("Ошибка добавления правила: " . $result['error']);
        }
        
        return [
            'success' => true,
            'message' => 'Правило успешно добавлено'
        ];
    }
    
    /**
     * Удалить правило UFW
     */
    private function deleteUfwRule(string $id): array
    {
        $result = $this->safeExecute("sudo ufw delete {$id}");
        
        if (!$result['success']) {
            throw new ServiceException("Ошибка удаления правила: " . $result['error']);
        }
        
        return [
            'success' => true,
            'message' => 'Правило успешно удалено'
        ];
    }
    
    /**
     * Удалить правило iptables
     */
    private function deleteIptablesRule(string $id): array
    {
        $result = $this->safeExecute("sudo iptables -D INPUT {$id}");
        
        if (!$result['success']) {
            throw new ServiceException("Ошибка удаления правила: " . $result['error']);
        }
        
        return [
            'success' => true,
            'message' => 'Правило успешно удалено'
        ];
    }
    
    /**
     * Парсить правило UFW
     */
    private function parseUfwRule(string $line): ?array
    {
        if (preg_match('/^\s*(\d+)\s+(.+)$/', $line, $matches)) {
            $id = $matches[1];
            $ruleText = trim($matches[2]);
            
            return [
                'id' => $id,
                'action' => $this->parseUfwAction($ruleText),
                'port' => $this->parseUfwPort($ruleText),
                'protocol' => $this->parseUfwProtocol($ruleText),
                'source' => $this->parseUfwSource($ruleText),
                'description' => $this->parseUfwDescription($ruleText),
                'raw' => $ruleText
            ];
        }
        
        return null;
    }
    
    /**
     * Парсить правило iptables
     */
    private function parseIptablesRule(string $line): ?array
    {
        if (preg_match('/^\s*(\d+)\s+(.+)$/', $line, $matches)) {
            $id = $matches[1];
            $ruleText = trim($matches[2]);
            
            return [
                'id' => $id,
                'target' => $this->parseIptablesTarget($ruleText),
                'protocol' => $this->parseIptablesProtocol($ruleText),
                'port' => $this->parseIptablesPort($ruleText),
                'source' => $this->parseIptablesSource($ruleText),
                'raw' => $ruleText
            ];
        }
        
        return null;
    }
    
    /**
     * Парсить действие UFW
     */
    private function parseUfwAction(string $ruleText): string
    {
        if (strpos($ruleText, 'ALLOW') !== false) {
            return 'ALLOW';
        } elseif (strpos($ruleText, 'DENY') !== false) {
            return 'DENY';
        }
        return 'UNKNOWN';
    }
    
    /**
     * Парсить порт UFW
     */
    private function parseUfwPort(string $ruleText): string
    {
        if (preg_match('/(\d+)\/(tcp|udp)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    /**
     * Парсить протокол UFW
     */
    private function parseUfwProtocol(string $ruleText): string
    {
        if (preg_match('/(\d+)\/(tcp|udp)/', $ruleText, $matches)) {
            return $matches[2];
        }
        return 'any';
    }
    
    /**
     * Парсить источник UFW
     */
    private function parseUfwSource(string $ruleText): string
    {
        if (preg_match('/from\s+([^\s]+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    /**
     * Парсить описание UFW
     */
    private function parseUfwDescription(string $ruleText): string
    {
        if (strpos($ruleText, 'OpenSSH') !== false) {
            return 'SSH доступ (порт 22)';
        } elseif (strpos($ruleText, 'Anywhere on wg0') !== false) {
            return 'WireGuard интерфейс';
        } elseif (preg_match('/(\d+)\/(tcp|udp)/', $ruleText, $matches)) {
            $port = (int)$matches[1];
            $protocol = $matches[2];
            return $this->getPortDescription($port, $protocol);
        }
        
        return 'Правило файрвола';
    }
    
    /**
     * Парсить цель iptables
     */
    private function parseIptablesTarget(string $ruleText): string
    {
        $parts = explode(' ', $ruleText);
        return $parts[0] ?? 'UNKNOWN';
    }
    
    /**
     * Парсить протокол iptables
     */
    private function parseIptablesProtocol(string $ruleText): string
    {
        if (preg_match('/\s-p\s+(\w+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    /**
     * Парсить порт iptables
     */
    private function parseIptablesPort(string $ruleText): string
    {
        if (preg_match('/\s--dport\s+(\d+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    /**
     * Парсить источник iptables
     */
    private function parseIptablesSource(string $ruleText): string
    {
        if (preg_match('/\s-s\s+([^\s]+)/', $ruleText, $matches)) {
            return $matches[1];
        }
        return 'any';
    }
    
    /**
     * Получить описание порта
     */
    private function getPortDescription(int $port, string $protocol): string
    {
        $descriptions = [
            21 => 'FTP',
            22 => 'SSH',
            23 => 'Telnet',
            25 => 'SMTP',
            53 => 'DNS',
            80 => 'HTTP',
            110 => 'POP3',
            143 => 'IMAP',
            443 => 'HTTPS',
            3306 => 'MySQL',
            5432 => 'PostgreSQL',
            3389 => 'RDP',
            5900 => 'VNC',
            6379 => 'Redis',
            8080 => 'HTTP Alt',
            8443 => 'HTTPS Alt'
        ];
        
        return $descriptions[$port] ?? "Порт {$port} ({$protocol})";
    }
}
