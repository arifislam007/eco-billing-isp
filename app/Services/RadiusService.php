<?php

namespace App\Services;

use CodeIgniter\Model;

class RadiusService
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect('radius');
    }
    
    /**
     * Create user in RADIUS database
     */
    public function createUser($username, $password, $group = null)
    {
        // Add to radcheck table
        $this->db->table('radcheck')->insert([
            'username' => $username,
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => $password
        ]);
        
        // Add to usergroup if group specified
        if ($group) {
            $this->addToGroup($username, $group);
        }
        
        return true;
    }
    
    /**
     * Add user to RADIUS group
     */
    public function addToGroup($username, $groupname, $priority = 1)
    {
        $this->db->table('radusergroup')->insert([
            'username' => $username,
            'groupname' => $groupname,
            'priority' => $priority
        ]);
    }
    
    /**
     * Remove user from RADIUS group
     */
    public function removeFromGroup($username, $groupname)
    {
        $this->db->table('radusergroup')
            ->where('username', $username)
            ->where('groupname', $groupname)
            ->delete();
    }
    
    /**
     * Update user password in RADIUS
     */
    public function updatePassword($username, $password)
    {
        $this->db->table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Cleartext-Password')
            ->update(['value' => $password]);
    }
    
    /**
     * Update user group in RADIUS
     */
    public function updateGroup($username, $newGroup)
    {
        // Remove from all groups
        $this->db->table('radusergroup')
            ->where('username', $username)
            ->delete();
        
        // Add to new group
        $this->addToGroup($username, $newGroup);
    }
    
    /**
     * Delete user from RADIUS
     */
    public function deleteUser($username)
    {
        $this->db->table('radcheck')->where('username', $username)->delete();
        $this->db->table('radreply')->where('username', $username)->delete();
        $this->db->table('radusergroup')->where('username', $username)->delete();
    }
    
    /**
     * Sync package to RADIUS group
     */
    public function syncPackageToGroup($package)
    {
        // Remove existing attributes for this group
        $this->db->table('radgroupreply')
            ->where('groupname', $package['radius_group'])
            ->delete();
        
        // Add bandwidth limit (Mikrotik format)
        $this->db->table('radgroupreply')->insert([
            'groupname' => $package['radius_group'],
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => '=',
            'value' => "{$package['download_speed']}M/{$package['upload_speed']}M"
        ]);
        
        // Add interim interval
        $this->db->table('radgroupreply')->insert([
            'groupname' => $package['radius_group'],
            'attribute' => 'Acct-Interim-Interval',
            'op' => '=',
            'value' => '300'
        ]);
        
        // Add session timeout
        $sessionTimeout = $package['valid_days'] * 86400;
        $this->db->table('radgroupreply')->insert([
            'groupname' => $package['radius_group'],
            'attribute' => 'Session-Timeout',
            'op' => '=',
            'value' => $sessionTimeout
        ]);
        
        return true;
    }
    
    /**
     * Get online users from radacct
     */
    public function getOnlineUsers()
    {
        return $this->db->table('radacct')
            ->where('acctstoptime', null)
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get user session info
     */
    public function getUserSession($username)
    {
        return $this->db->table('radacct')
            ->where('username', $username)
            ->where('acctstoptime', null)
            ->get()
            ->getRowArray();
    }
    
    /**
     * Test RADIUS authentication
     */
    public function testAuth($username, $password)
    {
        // Use radtest command
        $secret = 'testing123';
        $radtest = "echo 'User-Name={$username}, Cleartext-Password={$password}' | radclient -s 127.0.0.1 auth {$secret}";
        
        $output = shell_exec($radtest . ' 2>&1');
        
        return strpos($output, 'Access-Accept') !== false;
    }
    
    /**
     * Get RADIUS group attributes
     */
    public function getGroupAttributes($groupname)
    {
        return $this->db->table('radgroupreply')
            ->where('groupname', $groupname)
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get all RADIUS groups
     */
    public function getAllGroups()
    {
        return $this->db->table('radgroupreply')
            ->select('groupname')
            ->distinct()
            ->get()
            ->getResultArray();
    }
}
