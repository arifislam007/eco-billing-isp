<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountLogModel extends Model
{
    protected $table = 'account_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['customer_id', 'action', 'description', 'ip_address'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Log an activity
     */
    public function log($customerId, $action, $description = null, $ipAddress = null)
    {
        return $this->insert([
            'customer_id' => $customerId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ipAddress,
        ]);
    }
    
    /**
     * Get logs by customer
     */
    public function getByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Get logs by action
     */
    public function getByAction($action)
    {
        return $this->where('action', $action)->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Get recent logs
     */
    public function getRecent($limit = 50)
    {
        return $this->orderBy('created_at', 'DESC')->findAll($limit);
    }
}
