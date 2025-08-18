<?php

namespace App\Controllers\Network;

use App\Core\Controller;
use App\Services\Network\PortForwardingService;

class PortForwardingApiController extends Controller
{
    private PortForwardingService $portForwardingService;

    public function __construct()
    {
        $this->portForwardingService = new PortForwardingService();
    }

    /**
     * API: Получить правила проброса портов
     */
    public function getPortForwardingRules()
    {
        try {
            $rules = $this->portForwardingService->getPortForwardingRules();
            
            return $this->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения правил проброса портов: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Добавить правило проброса портов
     */
    public function addPortForwardingRule()
    {
        try {
            $name = $this->request->post('name');
            $externalPort = $this->request->post('external_port');
            $internalPort = $this->request->post('internal_port');
            $protocol = $this->request->post('protocol', 'tcp');
            $targetIp = $this->request->post('target_ip', '127.0.0.1');
            
            if (!$name || !$externalPort || !$internalPort) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не все обязательные параметры указаны'
                ]);
            }

            $result = $this->portForwardingService->addPortForwardingRule(
                $name, 
                (int)$externalPort, 
                (int)$internalPort, 
                $protocol, 
                $targetIp
            );
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['rule'] ?? null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка добавления правила проброса портов: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Удалить правило проброса портов
     */
    public function deletePortForwardingRule()
    {
        try {
            $ruleId = $this->request->get('id');
            if (!$ruleId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID правила'
                ]);
            }

            $result = $this->portForwardingService->deletePortForwardingRule($ruleId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка удаления правила проброса портов: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Активировать правило
     */
    public function activateRule()
    {
        try {
            $ruleId = $this->request->post('rule_id');
            if (!$ruleId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID правила'
                ]);
            }

            $result = $this->portForwardingService->activateRule($ruleId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['rule'] ?? null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка активации правила: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Деактивировать правило
     */
    public function deactivateRule()
    {
        try {
            $ruleId = $this->request->post('rule_id');
            if (!$ruleId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Не указан ID правила'
                ]);
            }

            $result = $this->portForwardingService->deactivateRule($ruleId);
            
            return $this->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['rule'] ?? null
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка деактивации правила: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Получить предупреждения безопасности
     */
    public function getSecurityWarnings()
    {
        try {
            $warnings = $this->portForwardingService->getPortForwardingSecurityWarnings();
            
            return $this->json([
                'success' => true,
                'data' => $warnings
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения предупреждений безопасности: ' . $e->getMessage()
            ]);
        }
    }
}
