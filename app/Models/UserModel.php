<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['username', 'password', 'full_name', 'email', 'phone', 'role', 'status'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
        'password' => 'required|min_length[6]',
        'full_name' => 'required|min_length[2]',
        'email' => 'valid_email',
        'role' => 'required|in_list[admin,manager,support]',
    ];
    
    protected $validationMessages = [
        'username' => [
            'is_unique' => 'This username is already taken.',
        ],
    ];
    
    protected $skipValidation = false;
    
    /**
     * Hash password before insert/update
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Get user by username
     */
    public function getUserByUsername($username)
    {
        return $this->where('username', $username)->first();
    }
    
    /**
     * Get all active users
     */
    public function getActiveUsers()
    {
        return $this->where('status', 'active')->findAll();
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)->findAll();
    }
}
