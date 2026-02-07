<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'invoice_number', 'customer_id', 'package_id', 'amount', 'tax_amount',
        'discount', 'total_amount', 'payment_status', 'payment_method',
        'paid_amount', 'created_date', 'due_date', 'paid_date', 'notes', 'created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'invoice_number' => 'required|is_unique[invoices.invoice_number]',
        'customer_id' => 'required|numeric',
        'amount' => 'required|numeric',
        'created_date' => 'required|valid_date',
        'due_date' => 'required|valid_date',
    ];
    
    protected $skipValidation = false;
    
    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber()
    {
        $prefix = 'INV-';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return $prefix . $date . '-' . $random;
    }
    
    /**
     * Get invoice with customer and package info
     */
    public function getWithDetails($id = null)
    {
        $builder = $this->select('invoices.*, customers.full_name, customers.username, customers.phone, customers.email, packages.package_name')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->join('packages', 'packages.id = invoices.package_id', 'left');
        
        if ($id) {
            return $builder->where('invoices.id', $id)->get()->getRowArray();
        }
        
        return $builder->findAll();
    }
    
    /**
     * Get invoices by customer
     */
    public function getByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Get unpaid invoices
     */
    public function getUnpaid()
    {
        return $this->where('payment_status', 'unpaid')->findAll();
    }
    
    /**
     * Mark invoice as paid
     */
    public function markAsPaid($id, $paymentMethod = 'cash')
    {
        return $this->update($id, [
            'payment_status' => 'paid',
            'paid_amount' => $this->find($id)['total_amount'],
            'paid_date' => date('Y-m-d'),
            'payment_method' => $paymentMethod,
        ]);
    }
    
    /**
     * Get invoices by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('created_date >=', $startDate)
            ->where('created_date <=', $endDate)
            ->findAll();
    }
    
    /**
     * Calculate total revenue
     */
    public function getTotalRevenue($startDate = null, $endDate = null)
    {
        $builder = $this->select('SUM(total_amount) as total')
            ->where('payment_status', 'paid');
        
        if ($startDate && $endDate) {
            $builder->where('paid_date >=', $startDate)
                    ->where('paid_date <=', $endDate);
        }
        
        return $builder->get()->getRow('total') ?? 0;
    }
}
