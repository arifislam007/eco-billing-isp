<?php

namespace App\Models;

use CodeIgniter\Model;

class RouterModel extends Model
{
    protected $table = 'routers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'router_name', 'ip_address', 'username', 'password', 'port',
        'api_token', 'router_type', 'location', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'router_name' => 'required|min_length[3]',
        'ip_address' => 'required|valid_ip',
        'router_type' => 'required|in_list[mikrotik,cisco,ubiquiti,other]',
    ];
    
    protected $skipValidation = false;
    
    /**
     * Get active routers
     */
    public function getActiveRouters()
    {
        return $this->where('status', 'active')->findAll();
    }
    
    /**
     * Get routers by type
     */
    public function getByType($type)
    {
        return $this->where('router_type', $type)->where('status', 'active')->findAll();
    }
    
    /**
     * Test router connection
     */
    public function testConnection($id)
    {
        $router = $this->find($id);
        
        if (!$router) {
            return false;
        }
        
        // For MikroTik routers, try API connection
        if ($router['router_type'] === 'mikrotik') {
            return $this->testMikrotikConnection($router);
        }
        
        // For other types, just ping
        return $this->ping($router['ip_address']);
    }
    
    /**
     * Test MikroTik API connection
     */
    private function testMikrotikConnection($router)
    {
        // Try to connect using socket
        $socket = @fsockopen($router['ip_address'], $router['port'], $errno, $errstr, 5);
        
        if ($socket) {
            fclose($socket);
            return true;
        }
        
        return false;
    }
    
    /**
     * Ping IP address
     */
    private function ping($ip)
    {
        $ping = exec("ping -n 2 {$ip}", $output, $status);
        return $status === 0;
    }
}
