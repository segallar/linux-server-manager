<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\FirewallService;

class FirewallController extends Controller
{
    public function index()
    {
        global $app;
        $cache = $app->cache;
        
        // Пытаемся получить данные из кэша
        $cacheKey = 'firewall_data';
        $cachedData = $cache->get($cacheKey);
        
        if ($cachedData !== null) {
            // Используем кэшированные данные
            $stats = $cachedData['stats'];
            $rules = $cachedData['rules'];
            $fromCache = true;
        } else {
            // Получаем свежие данные
            $firewallService = new FirewallService();
            
            $stats = $firewallService->getStats();
            $rules = $firewallService->getRules();
            
            // Сохраняем в кэш на 2 минуты
            $cache->set($cacheKey, [
                'stats' => $stats,
                'rules' => $rules
            ], 120);
            
            $fromCache = false;
        }

        return $this->render('firewall', [
            'title' => 'Файрвол',
            'currentPage' => 'firewall',
            'stats' => $stats,
            'rules' => $rules,
            'fromCache' => $fromCache
        ]);
    }

    // ==================== API METHODS ====================

    /**
     * API: Получить информацию о файрволе
     */
    public function getFirewallInfo()
    {
        try {
            $firewallService = new FirewallService();
            $info = $firewallService->getFirewallInfo();
            
            return $this->json([
                'success' => true,
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения информации о файрволе: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Получить статистику файрвола
     */
    public function getFirewallStats()
    {
        try {
            $firewallService = new FirewallService();
            $stats = $firewallService->getStats();
            
            return $this->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения статистики файрвола: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Получить список правил
     */
    public function getFirewallRules()
    {
        try {
            $firewallService = new FirewallService();
            $rules = $firewallService->getRules();
            
            return $this->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения правил файрвола: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Добавить правило
     */
    public function addFirewallRule()
    {
        try {
            $data = $this->request->getBody();
            $rule = json_decode($data, true);
            
            if (!$rule) {
                return $this->json([
                    'success' => false,
                    'message' => 'Неверный формат данных'
                ]);
            }
            
            $firewallService = new FirewallService();
            $result = $firewallService->addRule($rule);
            
            // Очищаем кэш после изменения
            global $app;
            $app->cache->delete('firewall_data');
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка добавления правила: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Удалить правило
     */
    public function deleteFirewallRule()
    {
        try {
            $ruleId = $this->request->get('id');
            if (!$ruleId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID правила'
                ]);
            }
            
            $firewallService = new FirewallService();
            $result = $firewallService->deleteRule($ruleId);
            
            // Очищаем кэш после изменения
            global $app;
            $app->cache->delete('firewall_data');
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка удаления правила: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Включить файрвол
     */
    public function enableFirewall()
    {
        try {
            $firewallService = new FirewallService();
            $result = $firewallService->enable();
            
            // Очищаем кэш после изменения
            global $app;
            $app->cache->delete('firewall_data');
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка включения файрвола: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Выключить файрвол
     */
    public function disableFirewall()
    {
        try {
            $firewallService = new FirewallService();
            $result = $firewallService->disable();
            
            // Очищаем кэш после изменения
            global $app;
            $app->cache->delete('firewall_data');
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка выключения файрвола: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Получить логи файрвола
     */
    public function getFirewallLogs()
    {
        try {
            $firewallService = new FirewallService();
            $info = $firewallService->getFirewallInfo();
            
            $logs = [];
            $logFile = $info['type'] === 'ufw' ? '/var/log/ufw.log' : '/var/log/iptables.log';
            
            if (file_exists($logFile)) {
                $output = shell_exec("tail -50 $logFile 2>/dev/null");
                $lines = explode("\n", $output);
                
                foreach ($lines as $line) {
                    if (!empty(trim($line))) {
                        $logs[] = [
                            'timestamp' => substr($line, 0, 19),
                            'message' => substr($line, 20),
                            'type' => $this->parseLogType($line)
                        ];
                    }
                }
            }
            
            return $this->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения логов файрвола: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Парсит тип лога
     */
    private function parseLogType(string $logLine): string
    {
        if (strpos($logLine, 'DROP') !== false) {
            return 'blocked';
        } elseif (strpos($logLine, 'ALLOW') !== false) {
            return 'allowed';
        } elseif (strpos($logLine, 'REJECT') !== false) {
            return 'rejected';
        } else {
            return 'info';
        }
    }
}
