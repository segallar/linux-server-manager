<?php

namespace App\Services\WireGuard;

use App\Abstracts\BaseService;
use App\Interfaces\WireGuardManagementServiceInterface;

class WireGuardManagementService extends BaseService implements WireGuardManagementServiceInterface
{
    private string $wgQuickPath = '/usr/bin/wg-quick';

    /**
     * Поднять интерфейс
     */
    public function up(string $interfaceName): bool
    {
        try {
            if (empty($interfaceName)) {
                return false;
            }

            $result = $this->safeExecute("sudo $this->wgQuickPath up $interfaceName");
            return $result['success'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Опустить интерфейс
     */
    public function down(string $interfaceName): bool
    {
        try {
            if (empty($interfaceName)) {
                return false;
            }

            $result = $this->safeExecute("sudo $this->wgQuickPath down $interfaceName");
            return $result['success'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Перезапустить интерфейс
     */
    public function restart(string $interfaceName): bool
    {
        try {
            if (empty($interfaceName)) {
                return false;
            }

            // Сначала опускаем интерфейс
            $this->down($interfaceName);
            
            // Затем поднимаем
            return $this->up($interfaceName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Создать интерфейс
     */
    public function createInterface(string $interfaceName, array $config): bool
    {
        try {
            if (empty($interfaceName)) {
                return false;
            }

            $configPath = "/etc/wireguard/$interfaceName.conf";
            
            // Создаем директорию, если не существует
            $configDir = dirname($configPath);
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }

            // Формируем конфигурацию
            $configContent = $this->buildConfig($config);
            
            // Записываем конфигурацию
            if (file_put_contents($configPath, $configContent) === false) {
                return false;
            }

            // Устанавливаем правильные права доступа
            chmod($configPath, 0600);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Удалить интерфейс
     */
    public function deleteInterface(string $interfaceName): bool
    {
        try {
            if (empty($interfaceName)) {
                return false;
            }

            // Сначала опускаем интерфейс
            $this->down($interfaceName);

            // Удаляем конфигурационный файл
            $configPath = "/etc/wireguard/$interfaceName.conf";
            if (file_exists($configPath)) {
                unlink($configPath);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Создать конфигурацию интерфейса
     */
    protected function buildConfig(array $config): string
    {
        $content = "[Interface]\n";
        
        if (!empty($config['private_key'])) {
            $content .= "PrivateKey = {$config['private_key']}\n";
        }
        
        if (!empty($config['address'])) {
            $content .= "Address = {$config['address']}\n";
        }
        
        if (!empty($config['port'])) {
            $content .= "ListenPort = {$config['port']}\n";
        }
        
        if (!empty($config['dns'])) {
            $content .= "DNS = {$config['dns']}\n";
        }
        
        if (!empty($config['mtu'])) {
            $content .= "MTU = {$config['mtu']}\n";
        }
        
        if (!empty($config['table'])) {
            $content .= "Table = {$config['table']}\n";
        }
        
        if (!empty($config['pre_up'])) {
            $content .= "PreUp = {$config['pre_up']}\n";
        }
        
        if (!empty($config['post_up'])) {
            $content .= "PostUp = {$config['post_up']}\n";
        }
        
        if (!empty($config['pre_down'])) {
            $content .= "PreDown = {$config['pre_down']}\n";
        }
        
        if (!empty($config['post_down'])) {
            $content .= "PostDown = {$config['post_down']}\n";
        }
        
        // Добавляем пиров
        if (!empty($config['peers'])) {
            foreach ($config['peers'] as $peer) {
                $content .= "\n[Peer]\n";
                
                if (!empty($peer['public_key'])) {
                    $content .= "PublicKey = {$peer['public_key']}\n";
                }
                
                if (!empty($peer['endpoint'])) {
                    $content .= "Endpoint = {$peer['endpoint']}\n";
                }
                
                if (!empty($peer['allowed_ips'])) {
                    $content .= "AllowedIPs = " . implode(', ', $peer['allowed_ips']) . "\n";
                }
                
                if (!empty($peer['persistent_keepalive'])) {
                    $content .= "PersistentKeepalive = {$peer['persistent_keepalive']}\n";
                }
                
                if (!empty($peer['preshared_key'])) {
                    $content .= "PresharedKey = {$peer['preshared_key']}\n";
                }
            }
        }
        
        return $content;
    }
}
