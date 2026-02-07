<?php

namespace App\Controllers;

class Report extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Reports',
        ];
        
        return view('reports/index', $data);
    }
    
    public function revenue()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $paymentModel = new \App\Models\PaymentModel();
        $payments = $paymentModel->getByDateRange($startDate, $endDate);
        
        $totalRevenue = 0;
        $paymentMethods = [];
        
        foreach ($payments as $payment) {
            $totalRevenue += $payment['amount'];
            $method = $payment['payment_method'];
            if (!isset($paymentMethods[$method])) {
                $paymentMethods[$method] = 0;
            }
            $paymentMethods[$method] += $payment['amount'];
        }
        
        $data = [
            'title' => 'Revenue Report',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_revenue' => $totalRevenue,
            'payments' => $payments,
            'payment_methods' => $paymentMethods,
        ];
        
        return view('reports/revenue', $data);
    }
    
    public function customers()
    {
        $customerModel = new \App\Models\CustomerModel();
        $customers = $customerModel->getWithPackage();
        
        $statusCounts = [
            'active' => 0,
            'inactive' => 0,
            'expired' => 0,
            'suspended' => 0,
        ];
        
        foreach ($customers as $customer) {
            $status = $customer['status'];
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }
        
        $data = [
            'title' => 'Customer Report',
            'customers' => $customers,
            'status_counts' => $statusCounts,
            'total_customers' => count($customers),
        ];
        
        return view('reports/customers', $data);
    }
    
    public function payments()
    {
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $paymentModel = new \App\Models\PaymentModel();
        $payments = $paymentModel->getByDateRange($startDate, $endDate);
        
        $data = [
            'title' => 'Payment Report',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payments' => $payments,
        ];
        
        return view('reports/payments', $data);
    }
    
    public function online()
    {
        $radiusService = new \App\Services\RadiusService();
        $onlineUsers = $radiusService->getOnlineUsers();
        
        $data = [
            'title' => 'Online Users',
            'online_users' => $onlineUsers,
            'total_online' => count($onlineUsers),
        ];
        
        return view('reports/online', $data);
    }
}
