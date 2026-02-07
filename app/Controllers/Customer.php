<?php

namespace App\Controllers;

class Customer extends BaseController
{
    protected $validation;
    
    public function __construct()
    {
        $this->validation = \Config\Services::validation();
    }
    
    /**
     * List all customers
     */
    public function index()
    {
        $customerModel = new \App\Models\CustomerModel();
        $packageModel = new \App\Models\PackageModel();
        
        $data = [
            'title' => 'Customers',
            'customers' => $customerModel->getWithPackage(),
            'packages' => $packageModel->where('status', 'active')->findAll(),
        ];
        
        return view('customers/index', $data);
    }
    
    /**
     * Show create customer form
     */
    public function create()
    {
        $packageModel = new \App\Models\PackageModel();
        $routerModel = new \App\Models\RouterModel();
        
        $data = [
            'title' => 'Add Customer',
            'packages' => $packageModel->where('status', 'active')->findAll(),
            'routers' => $routerModel->where('status', 'active')->findAll(),
        ];
        
        return view('customers/create', $data);
    }
    
    /**
     * Store new customer
     */
    public function store()
    {
        $rules = [
            'username' => 'required|min_length[3]|is_unique[customers.username]',
            'password' => 'required|min_length[6]',
            'full_name' => 'required|min_length[2]',
            'phone' => 'required|min_length[7]',
            'email' => 'valid_email',
            'package_id' => 'required|numeric',
            'connection_type' => 'required|in_list[pppoe,hotspot,static]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $customerModel = new \App\Models\CustomerModel();
        $radiusService = new \App\Services\RadiusService();
        
        $data = [
            'customer_id' => $customerModel->generateCustomerId(),
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
            'nid_number' => $this->request->getPost('nid_number'),
            'package_id' => $this->request->getPost('package_id'),
            'router_id' => $this->request->getPost('router_id'),
            'static_ip' => $this->request->getPost('static_ip'),
            'mac_address' => $this->request->getPost('mac_address'),
            'connection_type' => $this->request->getPost('connection_type'),
            'status' => 'inactive',
            'activation_date' => date('Y-m-d'),
            'expiration_date' => date('Y-m-d', strtotime('+30 days')),
        ];
        
        // Check if package exists and get RADIUS group
        $package = (new \App\Models\PackageModel())->find($data['package_id']);
        
        if ($customerModel->insert($data)) {
            // Add to RADIUS database
            if ($package && !empty($package['radius_group'])) {
                $radiusService->createUser($data['username'], $this->request->getPost('password'), $package['radius_group']);
            } else {
                $radiusService->createUser($data['username'], $this->request->getPost('password'));
            }
            
            // Log activity
            $logModel = new \App\Models\AccountLogModel();
            $logModel->log($this->session->get('user_id'), 'create_customer', 'Created customer: ' . $data['username'], $this->request->getIPAddress());
            
            return redirect()->to('admin/customers')->with('success', 'Customer added successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to add customer.');
    }
    
    /**
     * Show edit customer form
     */
    public function edit($id)
    {
        $customerModel = new \App\Models\CustomerModel();
        $packageModel = new \App\Models\PackageModel();
        $routerModel = new \App\Models\RouterModel();
        
        $customer = $customerModel->find($id);
        if (!$customer) {
            return redirect()->to('admin/customers')->with('error', 'Customer not found.');
        }
        
        $data = [
            'title' => 'Edit Customer',
            'customer' => $customer,
            'packages' => $packageModel->where('status', 'active')->findAll(),
            'routers' => $routerModel->where('status', 'active')->findAll(),
        ];
        
        return view('customers/edit', $data);
    }
    
    /**
     * Update customer
     */
    public function update($id)
    {
        $customerModel = new \App\Models\CustomerModel();
        $customer = $customerModel->find($id);
        
        if (!$customer) {
            return redirect()->to('admin/customers')->with('error', 'Customer not found.');
        }
        
        $rules = [
            'full_name' => 'required|min_length[2]',
            'phone' => 'required|min_length[7]',
            'email' => 'valid_email',
            'package_id' => 'required|numeric',
            'connection_type' => 'required|in_list[pppoe,hotspot,static]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
            'nid_number' => $this->request->getPost('nid_number'),
            'package_id' => $this->request->getPost('package_id'),
            'router_id' => $this->request->getPost('router_id'),
            'static_ip' => $this->request->getPost('static_ip'),
            'mac_address' => $this->request->getPost('mac_address'),
            'connection_type' => $this->request->getPost('connection_type'),
        ];
        
        // Update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            
            // Update RADIUS password
            $radiusService = new \App\Services\RadiusService();
            $radiusService->updatePassword($customer['username'], $password);
        }
        
        if ($customerModel->update($id, $data)) {
            // Update RADIUS group if package changed
            $package = (new \App\Models\PackageModel())->find($data['package_id']);
            if ($package && !empty($package['radius_group'])) {
                $radiusService = new \App\Services\RadiusService();
                $radiusService->updateGroup($customer['username'], $package['radius_group']);
            }
            
            // Log activity
            $logModel = new \App\Models\AccountLogModel();
            $logModel->log($this->session->get('user_id'), 'update_customer', 'Updated customer: ' . $customer['username'], $this->request->getIPAddress());
            
            return redirect()->to('admin/customers')->with('success', 'Customer updated successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update customer.');
    }
    
    /**
     * View customer details
     */
    public function view($id)
    {
        $customerModel = new \App\Models\CustomerModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $paymentModel = new \App\Models\PaymentModel();
        
        $customer = $customerModel->find($id);
        if (!$customer) {
            return redirect()->to('admin/customers')->with('error', 'Customer not found.');
        }
        
        // Get package info
        $customer['package'] = (new \App\Models\PackageModel())->find($customer['package_id']);
        
        // Get recent invoices
        $customer['invoices'] = $invoiceModel->where('customer_id', $id)->orderBy('created_at', 'DESC')->findAll(5);
        
        // Get recent payments
        $customer['payments'] = $paymentModel->where('customer_id', $id)->orderBy('created_at', 'DESC')->findAll(5);
        
        // Get activity log
        $logModel = new \App\Models\AccountLogModel();
        $customer['logs'] = $logModel->where('customer_id', $id)->orderBy('created_at', 'DESC')->findAll(10);
        
        $data = [
            'title' => 'Customer Details',
            'customer' => $customer,
        ];
        
        return view('customers/view', $data);
    }
    
    /**
     * Delete customer
     */
    public function delete($id)
    {
        $customerModel = new \App\Models\CustomerModel();
        $customer = $customerModel->find($id);
        
        if (!$customer) {
            return redirect()->to('admin/customers')->with('error', 'Customer not found.');
        }
        
        // Remove from RADIUS
        $radiusService = new \App\Services\RadiusService();
        $radiusService->deleteUser($customer['username']);
        
        if ($customerModel->delete($id)) {
            // Log activity
            $logModel = new \App\Models\AccountLogModel();
            $logModel->log($this->session->get('user_id'), 'delete_customer', 'Deleted customer: ' . $customer['username'], $this->request->getIPAddress());
            
            return redirect()->to('admin/customers')->with('success', 'Customer deleted successfully.');
        }
        
        return redirect()->to('admin/customers')->with('error', 'Failed to delete customer.');
    }
    
    /**
     * Update customer status
     */
    public function updateStatus($id)
    {
        $customerModel = new \App\Models\CustomerModel();
        $customer = $customerModel->find($id);
        
        if (!$customer) {
            return redirect()->to('admin/customers')->with('error', 'Customer not found.');
        }
        
        $status = $this->request->getPost('status');
        
        if ($customerModel->updateStatus($id, $status)) {
            // Log activity
            $logModel = new \App\Models\AccountLogModel();
            $logModel->log($this->session->get('user_id'), 'update_status', 'Changed status to: ' . $status . ' for: ' . $customer['username'], $this->request->getIPAddress());
            
            return redirect()->back()->with('success', 'Status updated successfully.');
        }
        
        return redirect()->back()->with('error', 'Failed to update status.');
    }
    
    /**
     * Export customers to CSV
     */
    public function export()
    {
        $customerModel = new \App\Models\CustomerModel();
        $customers = $customerModel->getWithPackage();
        
        $filename = 'customers_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header row
        fputcsv($output, ['Customer ID', 'Username', 'Full Name', 'Phone', 'Email', 'Package', 'Price', 'Status', 'Activation Date', 'Expiration Date']);
        
        // Data rows
        foreach ($customers as $customer) {
            fputcsv($output, [
                $customer['customer_id'],
                $customer['username'],
                $customer['full_name'],
                $customer['phone'],
                $customer['email'],
                $customer['package_name'] ?? 'N/A',
                $customer['price'] ?? '0.00',
                $customer['status'],
                $customer['activation_date'],
                $customer['expiration_date'],
            ]);
        }
        
        fclose($output);
        exit;
    }
}
