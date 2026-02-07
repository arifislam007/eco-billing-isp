<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'ticket_number', 'customer_id', 'subject', 'priority', 'status',
        'created_by', 'assigned_to'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'ticket_number' => 'required|is_unique[tickets.ticket_number]',
        'subject' => 'required|min_length[5]',
        'priority' => 'required|in_list[low,medium,high,urgent]',
    ];
    
    protected $skipValidation = false;
    
    /**
     * Generate ticket number
     */
    public function generateTicketNumber()
    {
        $prefix = 'TKT-';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return $prefix . $date . '-' . $random;
    }
    
    /**
     * Get tickets with customer info
     */
    public function getWithDetails($id = null)
    {
        $builder = $this->select('tickets.*, customers.full_name, customers.username, customers.phone, users.full_name as assigned_to_name')
            ->join('customers', 'customers.id = tickets.customer_id', 'left')
            ->join('users', 'users.id = tickets.assigned_to', 'left');
        
        if ($id) {
            return $builder->where('tickets.id', $id)->get()->getRowArray();
        }
        
        return $builder->findAll();
    }
    
    /**
     * Get tickets by customer
     */
    public function getByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Get tickets by status
     */
    public function getByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }
    
    /**
     * Get open tickets count
     */
    public function getOpenCount()
    {
        return $this->where('status', 'open')->countAllResults();
    }
    
    /**
     * Update ticket status
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Assign ticket
     */
    public function assign($id, $userId)
    {
        return $this->update($id, ['assigned_to' => $userId, 'status' => 'in_progress']);
    }
}
