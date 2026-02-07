<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Check if user is logged in
        if (!$session->has('user_id')) {
            return redirect()->to('/login')->with('error', 'Please login to access this page.');
        }
        
        // Check if user is active
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($session->get('user_id'));
        
        if (!$user || $user['status'] !== 'active') {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Your account is inactive. Please contact administrator.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing after
    }
}
