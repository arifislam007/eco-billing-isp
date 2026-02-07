<?php

namespace App\Controllers;

class Package extends BaseController
{
    public function index()
    {
        $packageModel = new \App\Models\PackageModel();
        
        $data = [
            'title' => 'Packages',
            'packages' => $packageModel->getWithCustomerCount(),
        ];
        
        return view('packages/index', $data);
    }
    
    public function create()
    {
        $data = [
            'title' => 'Add Package',
        ];
        
        return view('packages/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'package_name' => 'required|min_length[3]',
            'package_type' => 'required|in_list[prepaid,postpaid,hotspot,pppoe]',
            'price' => 'required|numeric',
            'valid_days' => 'required|numeric',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $packageModel = new \App\Models\PackageModel();
        
        $data = [
            'package_name' => $this->request->getPost('package_name'),
            'package_type' => $this->request->getPost('package_type'),
            'download_speed' => $this->request->getPost('download_speed'),
            'upload_speed' => $this->request->getPost('upload_speed'),
            'bandwidth_limit' => $this->request->getPost('bandwidth_limit'),
            'price' => $this->request->getPost('price'),
            'tax_percentage' => $this->request->getPost('tax_percentage') ?? 0,
            'radius_group' => $this->request->getPost('radius_group'),
            'valid_days' => $this->request->getPost('valid_days'),
            'status' => 'active',
        ];
        
        if ($packageModel->insert($data)) {
            return redirect()->to('admin/packages')->with('success', 'Package added successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to add package.');
    }
    
    public function edit($id)
    {
        $packageModel = new \App\Models\PackageModel();
        $package = $packageModel->find($id);
        
        if (!$package) {
            return redirect()->to('admin/packages')->with('error', 'Package not found.');
        }
        
        $data = [
            'title' => 'Edit Package',
            'package' => $package,
        ];
        
        return view('packages/edit', $data);
    }
    
    public function update($id)
    {
        $packageModel = new \App\Models\PackageModel();
        $package = $packageModel->find($id);
        
        if (!$package) {
            return redirect()->to('admin/packages')->with('error', 'Package not found.');
        }
        
        $rules = [
            'package_name' => 'required|min_length[3]',
            'package_type' => 'required|in_list[prepaid,postpaid,hotspot,pppoe]',
            'price' => 'required|numeric',
            'valid_days' => 'required|numeric',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'package_name' => $this->request->getPost('package_name'),
            'package_type' => $this->request->getPost('package_type'),
            'download_speed' => $this->request->getPost('download_speed'),
            'upload_speed' => $this->request->getPost('upload_speed'),
            'bandwidth_limit' => $this->request->getPost('bandwidth_limit'),
            'price' => $this->request->getPost('price'),
            'tax_percentage' => $this->request->getPost('tax_percentage') ?? 0,
            'radius_group' => $this->request->getPost('radius_group'),
            'valid_days' => $this->request->getPost('valid_days'),
            'status' => $this->request->getPost('status') ?? 'active',
        ];
        
        if ($packageModel->update($id, $data)) {
            return redirect()->to('admin/packages')->with('success', 'Package updated successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update package.');
    }
    
    public function delete($id)
    {
        $packageModel = new \App\Models\PackageModel();
        
        if ($packageModel->delete($id)) {
            return redirect()->to('admin/packages')->with('success', 'Package deleted successfully.');
        }
        
        return redirect()->to('admin/packages')->with('error', 'Failed to delete package.');
    }
    
    public function syncRadius($id)
    {
        $packageModel = new \App\Models\PackageModel();
        $package = $packageModel->find($id);
        
        if (!$package || empty($package['radius_group'])) {
            return redirect()->back()->with('error', 'Package not found or no RADIUS group defined.');
        }
        
        $radiusService = new \App\Services\RadiusService();
        
        if ($radiusService->syncPackageToGroup($package)) {
            return redirect()->back()->with('success', 'Package synced to RADIUS successfully.');
        }
        
        return redirect()->back()->with('error', 'Failed to sync package to RADIUS.');
    }
}
