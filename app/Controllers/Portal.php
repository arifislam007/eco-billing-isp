<?php

namespace App\Controllers;

class Portal extends BaseController
{
    public function login()
    {
        if ($this->session->has('customer_id')) {
            return redirect()->to('portal/dashboard');
        }
        
        $data = [
            'title' => 'Customer Login',
        ];
        
        return view('portal/login', $data);
    }
    
    public function authenticate()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        $customerModel = new \App\Models\CustomerModel();
        $customer = $customerModel->getByUsername($username);
        
        if (!$customer) {
            return redirect()->back()->withInput()->with('error', 'Invalid username or password.');
        }
        
        if (!password_verify($password, $customer['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid username or password.');
        }
        
        if ($customer['status'] !== 'active') {
            return redirect()->back()->withInput()->with('error', 'Your account is inactive or expired.');
        }
        
        $this->session->set('customer_id', $customer['id']);
        $this->session->set('customer_username', $customer['username']);
        $this->session->set('customer_name', $customer['full_name']);
        
        return redirect()->to('portal/dashboard');
    }
    
    public function dashboard()
    {
        $customerId = $this->session->get('customer_id');
        
        $customerModel = new \App\Models\CustomerModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $paymentModel = new \App\Models\PaymentModel();
        $ticketModel = new \App\Models\TicketModel();
        
        $customer = $customerModel->find($customerId);
        $customer['package'] = (new \App\Models\PackageModel())->find($customer['package_id']);
        
        $data = [
            'title' => 'Dashboard',
            'customer' => $customer,
            'invoices' => $invoiceModel->getByCustomer($customerId),
            'payments' => $paymentModel->getByCustomer($customerId),
            'tickets' => $ticketModel->getByCustomer($customerId),
        ];
        
        return view('portal/dashboard', $data);
    }
    
    public function invoices()
    {
        $customerId = $this->session->get('customer_id');
        
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoices = $invoiceModel->getByCustomer($customerId);
        
        $data = [
            'title' => 'My Invoices',
            'invoices' => $invoices,
        ];
        
        return view('portal/invoices', $data);
    }
    
    public function tickets()
    {
        $customerId = $this->session->get('customer_id');
        
        $ticketModel = new \App\Models\TicketModel();
        $tickets = $ticketModel->getByCustomer($customerId);
        
        $data = [
            'title' => 'Support Tickets',
            'tickets' => $tickets,
        ];
        
        return view('portal/tickets', $data);
    }
    
    public function storeTicket()
    {
        $customerId = $this->session->get('customer_id');
        
        $rules = [
            'subject' => 'required|min_length[5]',
            'priority' => 'required|in_list[low,medium,high,urgent]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $ticketModel = new \App\Models\TicketModel();
        
        $data = [
            'ticket_number' => $ticketModel->generateTicketNumber(),
            'customer_id' => $customerId,
            'subject' => $this->request->getPost('subject'),
            'priority' => $this->request->getPost('priority'),
            'status' => 'open',
        ];
        
        if ($ticketModel->insert($data)) {
            return redirect()->to('portal/tickets')->with('success', 'Ticket created successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to create ticket.');
    }
    
    public function profile()
    {
        $customerId = $this->session->get('customer_id');
        
        $customerModel = new \App\Models\CustomerModel();
        $customer = $customerModel->find($customerId);
        
        $data = [
            'title' => 'My Profile',
            'customer' => $customer,
        ];
        
        return view('portal/profile', $data);
    }
    
    public function updateProfile()
    {
        $customerId = $this->session->get('customer_id');
        
        $rules = [
            'full_name' => 'required|min_length[2]',
            'phone' => 'required|min_length[7]',
            'email' => 'valid_email',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $customerModel = new \App\Models\CustomerModel();
        
        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
        ];
        
        if ($customerModel->update($customerId, $data)) {
            $this->session->set('customer_name', $data['full_name']);
            return redirect()->back()->with('success', 'Profile updated successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update profile.');
    }
    
    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/portal/login')->with('success', 'You have been logged out successfully.');
    }
}
