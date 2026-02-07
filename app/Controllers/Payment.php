<?php

namespace App\Controllers;

class Payment extends BaseController
{
    public function index()
    {
        $paymentModel = new \App\Models\PaymentModel();
        
        $data = [
            'title' => 'Payments',
            'payments' => $paymentModel->getWithDetails(),
        ];
        
        return view('payments/index', $data);
    }
    
    public function create()
    {
        $customerModel = new \App\Models\CustomerModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        
        $data = [
            'title' => 'Record Payment',
            'customers' => $customerModel->where('status', 'active')->findAll(),
            'unpaid_invoices' => $invoiceModel->getUnpaid(),
        ];
        
        return view('payments/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'customer_id' => 'required|numeric',
            'amount' => 'required|numeric',
            'payment_method' => 'required|in_list[cash,bank_transfer,mobile_banking,card,other]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $paymentModel = new \App\Models\PaymentModel();
        $invoiceId = $this->request->getPost('invoice_id');
        
        $data = [
            'payment_id' => $paymentModel->generatePaymentId(),
            'customer_id' => $this->request->getPost('customer_id'),
            'invoice_id' => !empty($invoiceId) ? $invoiceId : null,
            'amount' => $this->request->getPost('amount'),
            'payment_method' => $this->request->getPost('payment_method'),
            'transaction_id' => $this->request->getPost('transaction_id'),
            'received_by' => $this->session->get('user_id'),
            'notes' => $this->request->getPost('notes'),
        ];
        
        if ($paymentModel->insert($data)) {
            // Update customer balance
            (new \App\Models\CustomerModel())->updateBalance($data['customer_id'], -$data['amount']);
            
            // If invoice specified, update invoice
            if (!empty($invoiceId)) {
                $invoiceModel = new \App\Models\InvoiceModel();
                $invoice = $invoiceModel->find($invoiceId);
                
                if ($invoice) {
                    $newPaidAmount = $invoice['paid_amount'] + $data['amount'];
                    $paymentStatus = $newPaidAmount >= $invoice['total_amount'] ? 'paid' : 'partial';
                    
                    $invoiceModel->update($invoiceId, [
                        'paid_amount' => $newPaidAmount,
                        'payment_status' => $paymentStatus,
                        'paid_date' => $paymentStatus === 'paid' ? date('Y-m-d') : null,
                        'payment_method' => $data['payment_method'],
                    ]);
                }
            }
            
            return redirect()->to('admin/payments')->with('success', 'Payment recorded successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to record payment.');
    }
    
    public function view($id)
    {
        $paymentModel = new \App\Models\PaymentModel();
        $payment = $paymentModel->getWithDetails($id);
        
        if (!$payment) {
            return redirect()->to('admin/payments')->with('error', 'Payment not found.');
        }
        
        $data = [
            'title' => 'Payment Details',
            'payment' => $payment,
        ];
        
        return view('payments/view', $data);
    }
    
    public function delete($id)
    {
        $paymentModel = new \App\Models\PaymentModel();
        
        if ($paymentModel->delete($id)) {
            return redirect()->to('admin/payments')->with('success', 'Payment deleted successfully.');
        }
        
        return redirect()->to('admin/payments')->with('error', 'Failed to delete payment.');
    }
}
