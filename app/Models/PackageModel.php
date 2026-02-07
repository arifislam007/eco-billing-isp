<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageModel extends Model
{
    protected $table = 'packages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'package_name', 'package_type', 'download_speed', 'upload_speed',
        'bandwidth_limit', 'price', 'tax_percentage', 'radius_group',
        'valid_days', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'package_name' => 'required|min_length[3]',
        'package_type' => 'required|in_list[prepaid,postpaid,hotspot,pppoe]',
        'price' => 'required|numeric',
        'valid_days' => 'required|numeric',
    ];
    
    protected $skipValidation = false;
    
    /**
     * Get active packages
     */
    public function getActivePackages()
    {
        return $this->where('status', 'active')->findAll();
    }
    
    /**
     * Get packages by type
     */
    public function getByType($type)
    {
        return $this->where('package_type', $type)->where('status', 'active')->findAll();
    }
    
    /**
     * Search packages
     */
    public function search($keyword)
    {
        return $this->like('package_name', $keyword)->findAll();
    }
    
    /**
     * Get package with customer count
     */
    public function getWithCustomerCount()
    {
        $packages = $this->findAll();
        
        foreach ($packages as &$package) {
            $customerModel = new \App\Models\CustomerModel();
            $package['customer_count'] = $customerModel->where('package_id', $package['id'])->countAllResults();
        }
        
        return $packages;
    }
}
