<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 */
abstract class BaseController extends Controller
{
    protected $session;
    protected $db;
    
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Initialize session
        $this->session = \Config\Services::session();
        
        // Initialize database
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Check if user is logged in
     */
    protected function isLoggedIn()
    {
        return $this->session->has('user_id');
    }
    
    /**
     * Get current user data
     */
    protected function getUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $userModel = new \App\Models\UserModel();
        return $userModel->find($this->session->get('user_id'));
    }
    
    /**
     * Check if user has role
     */
    protected function hasRole($role)
    {
        $user = $this->getUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Check if user is admin
     */
    protected function isAdmin()
    {
        return $this->hasRole('admin');
    }
    
    /**
     * Set flash message
     */
    protected function setMessage($type, $message)
    {
        $this->session->setFlashdata($type, $message);
    }
    
    /**
     * Set flash success message
     */
    protected function setSuccess($message)
    {
        $this->setMessage('success', $message);
    }
    
    /**
     * Set flash error message
     */
    protected function setError($message)
    {
        $this->setMessage('error', $message);
    }
}
