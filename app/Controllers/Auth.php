<?php

namespace App\Controllers;

class Auth extends BaseController
{
    protected $validation;
    
    public function __construct()
    {
        $this->validation = \Config\Services::validation();
    }
    
    /**
     * Login page
     */
    public function login()
    {
        if ($this->isLoggedIn()) {
            return redirect()->to('admin/dashboard');
        }
        
        $data = [
            'title' => 'Login',
        ];
        
        return view('auth/login', $data);
    }
    
    /**
     * Attempt login
     */
    public function attemptLogin()
    {
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[6]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('username', $username)->first();
        
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Invalid username or password.');
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid username or password.');
        }
        
        // Check if user is active
        if ($user['status'] !== 'active') {
            return redirect()->back()->withInput()->with('error', 'Your account is inactive. Please contact administrator.');
        }
        
        // Set session
        $this->session->set('user_id', $user['id']);
        $this->session->set('username', $user['username']);
        $this->session->set('full_name', $user['full_name']);
        $this->session->set('role', $user['role']);
        
        // Log activity
        $logModel = new \App\Models\AccountLogModel();
        $logModel->log($user['id'], 'login', 'User logged in', $this->request->getIPAddress());
        
        return redirect()->to('admin/dashboard')->with('success', 'Welcome back, ' . $user['full_name'] . '!');
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        // Log activity
        if ($this->isLoggedIn()) {
            $logModel = new \App\Models\AccountLogModel();
            $logModel->log($this->session->get('user_id'), 'logout', 'User logged out', $this->request->getIPAddress());
        }
        
        $this->session->destroy();
        
        return redirect()->to('/login')->with('success', 'You have been logged out successfully.');
    }
}
