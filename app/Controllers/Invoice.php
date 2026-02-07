<?php

namespace App\Controllers;

class Invoice extends BaseController
{
    public function index()
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        
        $data = [
            'title' => 'Invoices',
            'invoices' => $invoiceModel->getWithDetails(),
        ];
        
        return view('invoices/index', $data);
    }
    
    public function create()
    {
        $customerModel = new \App\Models\CustomerModel();
        $packageModel = new \App\Models\PackageModel();
        
        $data = [
            'title' => 'Create Invoice',
            'customers' => $customerModel->where('status', 'active')->findAll(),
            'packages' => $packageModel->where('status', 'active')->findAll(),
        ];
        
        return view('invoices/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'customer_id' => 'required|numeric',
            'package_id' => 'required|numeric',
            'amount' => 'required|numeric',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $invoiceModel = new \App\Models\InvoiceModel();
        $package = (new \App\Models\PackageModel())->find($this->request->getPost('package_id'));
        
        $taxAmount = $package ? ($package['price'] * $package['tax_percentage'] / 100) : 0;
        $discount = $this->request->getPost('discount') ?? 0;
        $totalAmount = ($package['price'] ?? 0) + $taxAmount - $discount;
        
        $data = [
            'invoice_number' => $invoiceModel->generateInvoiceNumber(),
            'customer_id' => $this->request->getPost('customer_id'),
            'package_id' => $this->request->getPost('package_id'),
            'amount' => $this->request->getPost('amount'),
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'payment_status' => 'unpaid',
            'created_date' => date('Y-m-d'),
            'due_date' => $this->request->getPost('due_date') ?? date('Y-m-d', strtotime('+7 days')),
            'notes' => $this->request->getPost('notes'),
            'created_by' => $this->session->get('user_id'),
        ];
        
        if ($invoiceModel->insert($data)) {
            return redirect()->to('admin/invoices')->with('success', 'Invoice created successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to create invoice.');
    }
    
    public function view($id)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoice = $invoiceModel->getWithDetails($id);
        
        if (!$invoice) {
            return redirect()->to('admin/invoices')->with('error', 'Invoice not found.');
        }
        
        $data = [
            'title' => 'Invoice Details',
            'invoice' => $invoice,
        ];
        
        return view('invoices/view', $data);
    }
    
    public function print($id)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoice = $invoiceModel->getWithDetails($id);
        
        if (!$invoice) {
            return redirect()->to('admin/invoices')->with('error', 'Invoice not found.');
        }
        
        $data = [
            'title' => 'Print Invoice',
            'invoice' => $invoice,
        ];
        
        return view('invoices/print', $data);
    }
    
    public function markAsPaid($id)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoice = $invoiceModel->find($id);
        
        if (!$invoice) {
            return redirect()->to('admin/invoices')->with('error', 'Invoice not found.');
        }
        
        $paymentMethod = $this->request->getPost('payment_method') ?? 'cash';
        
        if ($invoiceModel->markAsPaid($id, $paymentMethod)) {
            // Update customer balance
            (new \App\Models\CustomerModel())->updateBalance($invoice['customer_id'], -$invoice['total_amount']);
            
            // Create payment record
            $paymentModel = new \App\Models\PaymentModel();
            $paymentModel->insert([
                'payment_id' => $paymentModel->generatePaymentId(),
                'invoice_id' => $id,
                'customer_id' => $invoice['customer_id'],
                'amount' => $invoice['total_amount'],
                'payment_method' => $paymentMethod,
                'received_by' => $this->session->get('user_id'),
            ]);
            
            return redirect()->back()->with('success', 'Invoice marked as paid.');
        }
        
        return redirect()->back()->with('error', 'Failed to mark invoice as paid.');
    }
    
    public function delete($id)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        
        if ($invoiceModel->delete($id)) {
            return redirect()->to('admin/invoices')->with('success', 'Invoice deleted successfully.');
        }
        
        return redirect()->to('admin/invoices')->with('error', 'Failed to delete invoice.');
    }
}
