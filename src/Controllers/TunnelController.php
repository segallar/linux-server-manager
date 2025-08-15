<?php

namespace App\Controllers;

class TunnelController
{
    public function ssh()
    {
        $currentPage = 'tunnels-ssh';
        $title = 'SSH туннели - Linux Server Manager';
        
        ob_start();
        include __DIR__ . '/../../templates/tunnels/ssh.php';
        $content = ob_get_clean();
        
        include __DIR__ . '/../../templates/layout.php';
    }
    
    public function portForwarding()
    {
        $currentPage = 'tunnels-port-forwarding';
        $title = 'Проброс портов - Linux Server Manager';
        
        ob_start();
        include __DIR__ . '/../../templates/tunnels/port-forwarding.php';
        $content = ob_get_clean();
        
        include __DIR__ . '/../../templates/layout.php';
    }
    
    public function wireguard()
    {
        $currentPage = 'tunnels-wireguard';
        $title = 'WireGuard - Linux Server Manager';
        
        ob_start();
        include __DIR__ . '/../../templates/tunnels/wireguard.php';
        $content = ob_get_clean();
        
        include __DIR__ . '/../../templates/layout.php';
    }
    
    public function cloudflare()
    {
        $currentPage = 'tunnels-cloudflare';
        $title = 'Cloudflare - Linux Server Manager';
        
        ob_start();
        include __DIR__ . '/../../templates/tunnels/cloudflare.php';
        $content = ob_get_clean();
        
        include __DIR__ . '/../../templates/layout.php';
    }
}
