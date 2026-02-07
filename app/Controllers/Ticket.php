<?php

namespace App\Controllers;

class Ticket extends BaseController
{
    public function index()
    {
        $ticketModel = new \App\Models\TicketModel();
        
        $data = [
            'title' => 'Support Tickets',
            'tickets' => $ticketModel->getWithDetails(),
        ];
        
        return view('tickets/index', $data);
    }
    
    public function create()
    {
        $customerModel = new \App\Models\CustomerModel();
        
        $data = [
            'title' => 'Create Ticket',
            'customers' => $customerModel->where('status', 'active')->findAll(),
        ];
        
        return view('tickets/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'customer_id' => 'required|numeric',
            'subject' => 'required|min_length[5]',
            'priority' => 'required|in_list[low,medium,high,urgent]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $ticketModel = new \App\Models\TicketModel();
        
        $data = [
            'ticket_number' => $ticketModel->generateTicketNumber(),
            'customer_id' => $this->request->getPost('customer_id'),
            'subject' => $this->request->getPost('subject'),
            'priority' => $this->request->getPost('priority'),
            'status' => 'open',
            'created_by' => $this->session->get('user_id'),
        ];
        
        if ($ticketModel->insert($data)) {
            return redirect()->to('admin/tickets')->with('success', 'Ticket created successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to create ticket.');
    }
    
    public function view($id)
    {
        $ticketModel = new \App\Models\TicketModel();
        $ticket = $ticketModel->getWithDetails($id);
        
        if (!$ticket) {
            return redirect()->to('admin/tickets')->with('error', 'Ticket not found.');
        }
        
        $ticketReplyModel = new \App\Models\TicketReplyModel();
        $ticket['replies'] = $ticketReplyModel->getByTicket($id);
        
        $data = [
            'title' => 'Ticket Details',
            'ticket' => $ticket,
        ];
        
        return view('tickets/view', $data);
    }
    
    public function reply($id)
    {
        $ticketModel = new \App\Models\TicketModel();
        $ticket = $ticketModel->find($id);
        
        if (!$ticket) {
            return redirect()->to('admin/tickets')->with('error', 'Ticket not found.');
        }
        
        $rules = [
            'message' => 'required|min_length[5]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $ticketReplyModel = new \App\Models\TicketReplyModel();
        
        $data = [
            'ticket_id' => $id,
            'user_id' => $this->session->get('user_id'),
            'message' => $this->request->getPost('message'),
        ];
        
        if ($ticketReplyModel->insert($data)) {
            // Update ticket status to in_progress if it was open
            if ($ticket['status'] === 'open') {
                $ticketModel->update($id, ['status' => 'in_progress']);
            }
            
            return redirect()->back()->with('success', 'Reply added successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to add reply.');
    }
    
    public function updateStatus($id)
    {
        $ticketModel = new \App\Models\TicketModel();
        
        $status = $this->request->getPost('status');
        
        if ($ticketModel->updateStatus($id, $status)) {
            return redirect()->back()->with('success', 'Status updated successfully.');
        }
        
        return redirect()->back()->with('error', 'Failed to update status.');
    }
}
