<?php

namespace App\Controllers;

class Setting extends BaseController
{
    public function index()
    {
        $settingModel = new \App\Models\SettingModel();
        $settings = $settingModel->getAll();
        
        $data = [
            'title' => 'Settings',
            'settings' => $settings,
        ];
        
        return view('settings/index', $data);
    }
    
    public function updateCompany()
    {
        $settingModel = new \App\Models\SettingModel();
        
        $settings = [
            'company_name' => $this->request->getPost('company_name'),
            'company_address' => $this->request->getPost('company_address'),
            'company_phone' => $this->request->getPost('company_phone'),
            'company_email' => $this->request->getPost('company_email'),
            'company_website' => $this->request->getPost('company_website'),
        ];
        
        foreach ($settings as $key => $value) {
            $settingModel->set($key, $value);
        }
        
        return redirect()->back()->with('success', 'Company settings updated successfully.');
    }
    
    public function updateBilling()
    {
        $settingModel = new \App\Models\SettingModel();
        
        $settings = [
            'tax_percentage' => $this->request->getPost('tax_percentage'),
            'invoice_due_days' => $this->request->getPost('invoice_due_days'),
            'currency' => $this->request->getPost('currency'),
        ];
        
        foreach ($settings as $key => $value) {
            $settingModel->set($key, $value);
        }
        
        return redirect()->back()->with('success', 'Billing settings updated successfully.');
    }
    
    public function updateSmtp()
    {
        $settingModel = new \App\Models\SettingModel();
        
        $settings = [
            'smtp_host' => $this->request->getPost('smtp_host'),
            'smtp_port' => $this->request->getPost('smtp_port'),
            'smtp_user' => $this->request->getPost('smtp_user'),
            'smtp_pass' => $this->request->getPost('smtp_pass'),
            'smtp_from_email' => $this->request->getPost('smtp_from_email'),
            'smtp_from_name' => $this->request->getPost('smtp_from_name'),
        ];
        
        foreach ($settings as $key => $value) {
            $settingModel->set($key, $value);
        }
        
        return redirect()->back()->with('success', 'SMTP settings updated successfully.');
    }
}
