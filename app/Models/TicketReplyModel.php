<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketReplyModel extends Model
{
    protected $table = 'ticket_replies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['ticket_id', 'user_id', 'message'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'ticket_id' => 'required|numeric',
        'user_id' => 'required|numeric',
        'message' => 'required|min_length[5]',
    ];
    
    protected $skipValidation = false;
    
    /**
     * Get replies by ticket
     */
    public function getByTicket($ticketId)
    {
        return $this->select('ticket_replies.*, users.full_name')
            ->join('users', 'users.id = ticket_replies.user_id')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
