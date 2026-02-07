<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'payment_id', 'invoice_id', 'customer_id', 'amount', 'payment_method',
        'transaction_id', 'received_by', 'notes'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'payment_id' => 'required|is_unique[payments.payment_id]',
        'customer_id' => 'required|numeric',
        'amount' => 'required|numeric',
        'payment_method' => 'required|in_list[cash,bank_transfer,mobile_banking,card,other]',
    ];
    
    protected $skipValidation = false;
    
    /**
     * Generate payment ID
     */
    public function generatePaymentId()
    {
        $prefix = 'PAY-';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return $prefix . $date . '-' . $random;
    }
    
    /**
     * Get payments with customer info
     */
    public function getWithDetails($id = null)
    {
        $builder = $this->select('payments.*, customers.full_name, customers.username, customers.phone, invoices.invoice_number')
            ->join('customers', 'customers.id = payments.customer_id')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'left');
        
        if ($id) {
            return $builder->where('payments.id', $id)->get()->getRowArray();
        }
        
        return $builder->findAll();
    }
    
    /**
     * Get payments by customer
     */
    public function getByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Get payments by date
     */
    public function getPaymentsByDate($date)
    {
        return $this->select('SUM(amount) as total')
            ->where('DATE(created_at)', $date)
            ->get();
    }
    
    /**
     * Get payments by month
     */
    public function getPaymentsByMonth($month = null)
    {
        if (!$month) {
            $month = date('Y-m');
        }
        
        return $this->select('SUM(amount) as total')
            ->where('DATE_FORMAT(created_at, "%Y-%m")', $month)
            ->get();
    }
    
    /**
     * Get payments by date range
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->select('payments.*, customers.full_name, customers.username')
            ->join('customers', 'customers.id = payments.customer_id')
            ->where('DATE(payments.created_at) >=', $startDate)
            ->where('DATE(payments.created_at) <=', $endDate)
            ->orderBy('payments.created_at', 'DESC')
            ->findAll();
    }
    
    /**
     * Calculate total payments
     */
    public function getTotalPayments($startDate = null, $endDate = null)
    {
        $builder = $this->select('SUM(amount) as total');
        
        if ($startDate && $endDate) {
            $builder->where('DATE(created_at) >=', $startDate)
                    ->where('DATE(created_at) <=', $endDate);
        }
        
        return $builder->get()->getRow('total') ?? 0;
    }
}
