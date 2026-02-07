<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    /**
     * Dashboard index
     */
    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'user' => $this->getUser(),
        ];
        
        // Get statistics
        $customerModel = new \App\Models\CustomerModel();
        $packageModel = new \App\Models\PackageModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $paymentModel = new \App\Models\PaymentModel();
        $ticketModel = new \App\Models\TicketModel();
        
        // Customer stats
        $data['total_customers'] = $customerModel->countAll();
        $data['active_customers'] = $customerModel->where('status', 'active')->countAllResults();
        $data['expired_today'] = $customerModel->getExpiredToday()->countAllResults();
        
        // Revenue stats
        $today = date('Y-m-d');
        $data['revenue_today'] = $paymentModel->getPaymentsByDate($today)->get()->getRow('total') ?? 0;
        $data['revenue_month'] = $paymentModel->getPaymentsByMonth()->get()->getRow('total') ?? 0;
        
        // Online users (from RADIUS)
        $radiusDb = \Config\Database::connect('radius');
        $data['online_now'] = $radiusDb->table('radacct')->where('acctstoptime', null)->countAllResults();
        
        // Recent activities
        $logModel = new \App\Models\AccountLogModel();
        $data['recent_activities'] = $logModel->orderBy('created_at', 'DESC')->findAll(10);
        
        // New customers today
        $data['new_customers_today'] = $customerModel->where('DATE(created_at)', $today)->countAllResults();
        
        // Pending tickets
        $data['pending_tickets'] = $ticketModel->where('status', 'open')->countAllResults();
        
        // Monthly sales data for chart
        $data['monthly_sales'] = $this->getMonthlySales();
        $data['payment_methods'] = $this->getPaymentMethodsData();
        
        return view('dashboard/index', $data);
    }
    
    /**
     * Get monthly sales data for chart
     */
    private function getMonthlySales()
    {
        $paymentModel = new \App\Models\PaymentModel();
        
        $months = [];
        $sales = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthName = date('M Y', strtotime("-{$i} months"));
            
            $total = $paymentModel->getPaymentsByMonth($month)->get()->getRow('total') ?? 0;
            
            $months[] = $monthName;
            $sales[] = (float) $total;
        }
        
        return [
            'months' => $months,
            'sales' => $sales,
        ];
    }
    
    /**
     * Get payment methods distribution
     */
    private function getPaymentMethodsData()
    {
        $paymentModel = new \App\Models\PaymentModel();
        
        $methods = $paymentModel->select('payment_method, COUNT(*) as count')
            ->where('DATE(created_at) >=', date('Y-m-d', strtotime('-30 days')))
            ->groupBy('payment_method')
            ->findAll();
        
        $labels = [];
        $data = [];
        $colors = ['#36A2EB', '#FFCE56', '#4BC0C0', '#FF6384', '#9966FF'];
        
        foreach ($methods as $index => $method) {
            $labels[] = ucfirst(str_replace('_', ' ', $method['payment_method']));
            $data[] = $method['count'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($methods)),
        ];
    }
}
