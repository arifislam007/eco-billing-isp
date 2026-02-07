<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Customer implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Check if customer is logged in
        if (!$session->has('customer_id')) {
            return redirect()->to('/portal/login')->with('error', 'Please login to access this page.');
        }
        
        // Check if customer is active
        $customerModel = new \App\Models\CustomerModel();
        $customer = $customerModel->find($session->get('customer_id'));
        
        if (!$customer || $customer['status'] !== 'active') {
            $session->destroy();
            return redirect()->to('/portal/login')->with('error', 'Your account is inactive or expired.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after
    }
}
