<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'customer_id', 'username', 'password', 'full_name', 'phone', 'email',
        'address', 'nid_number', 'package_id', 'router_id', 'static_ip',
        'mac_address', 'connection_type', 'status', 'activation_date',
        'expiration_date', 'balance'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'customer_id' => 'required|is_unique[customers.customer_id]',
        'username' => 'required|min_length[3]|is_unique[customers.username]',
        'password' => 'required|min_length[6]',
        'full_name' => 'required|min_length[2]',
        'phone' => 'required|min_length[7]',
        'email' => 'valid_email',
        'connection_type' => 'required|in_list[pppoe,hotspot,static]',
    ];
    
    protected $skipValidation = false;
    
    /**
     * Generate unique customer ID
     */
    public function generateCustomerId()
    {
        $prefix = 'CUST-';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return $prefix . $year . $month . '-' . $random;
    }
    
    /**
     * Hash password before insert
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Get customer with package info
     */
    public function getWithPackage()
    {
        return $this->select('customers.*, packages.package_name, packages.price, packages.download_speed, packages.upload_speed')
            ->join('packages', 'packages.id = customers.package_id', 'left')
            ->findAll();
    }
    
    /**
     * Get customer by username
     */
    public function getByUsername($username)
    {
        return $this->where('username', $username)->first();
    }
    
    /**
     * Get expired customers for today
     */
    public function getExpiredToday()
    {
        return $this->where('DATE(expiration_date)', date('Y-m-d'));
    }
    
    /**
     * Get active customers
     */
    public function getActiveCustomers()
    {
        return $this->where('status', 'active')->findAll();
    }
    
    /**
     * Search customers
     */
    public function search($keyword)
    {
        return $this->like('full_name', $keyword)
            ->orLike('username', $keyword)
            ->orLike('phone', $keyword)
            ->orLike('email', $keyword)
            ->orLike('customer_id', $keyword)
            ->findAll();
    }
    
    /**
     * Get customers by status
     */
    public function getByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }
    
    /**
     * Get customers by package
     */
    public function getByPackage($packageId)
    {
        return $this->where('package_id', $packageId)->findAll();
    }
    
    /**
     * Update customer status
     */
    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Update balance
     */
    public function updateBalance($id, $amount)
    {
        $customer = $this->find($id);
        if ($customer) {
            $newBalance = $customer['balance'] + $amount;
            return $this->update($id, ['balance' => $newBalance]);
        }
        return false;
    }
}
