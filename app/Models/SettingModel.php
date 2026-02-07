<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['setting_key', 'setting_value'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get setting by key
     */
    public function get($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting['setting_value'] : $default;
    }
    
    /**
     * Set setting
     */
    public function set($key, $value)
    {
        $existing = $this->where('setting_key', $key)->first();
        
        if ($existing) {
            return $this->update($existing['id'], ['setting_value' => $value]);
        }
        
        return $this->insert(['setting_key' => $key, 'setting_value' => $value]);
    }
    
    /**
     * Get all settings as key-value array
     */
    public function getAll()
    {
        $settings = $this->findAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }
    
    /**
     * Delete setting
     */
    public function delete($key)
    {
        return $this->where('setting_key', $key)->delete();
    }
}
