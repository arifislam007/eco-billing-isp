<?php

namespace App\Controllers;

class Router extends BaseController
{
    public function index()
    {
        $routerModel = new \App\Models\RouterModel();
        
        $data = [
            'title' => 'NAS Devices',
            'routers' => $routerModel->findAll(),
        ];
        
        return view('routers/index', $data);
    }
    
    public function create()
    {
        $data = [
            'title' => 'Add Router',
        ];
        
        return view('routers/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'router_name' => 'required|min_length[3]',
            'ip_address' => 'required|valid_ip',
            'router_type' => 'required|in_list[mikrotik,cisco,ubiquiti,other]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $routerModel = new \App\Models\RouterModel();
        
        $data = [
            'router_name' => $this->request->getPost('router_name'),
            'ip_address' => $this->request->getPost('ip_address'),
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
            'port' => $this->request->getPost('port') ?? '8728',
            'router_type' => $this->request->getPost('router_type'),
            'location' => $this->request->getPost('location'),
            'status' => 'active',
        ];
        
        if ($routerModel->insert($data)) {
            return redirect()->to('admin/routers')->with('success', 'Router added successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to add router.');
    }
    
    public function edit($id)
    {
        $routerModel = new \App\Models\RouterModel();
        $router = $routerModel->find($id);
        
        if (!$router) {
            return redirect()->to('admin/routers')->with('error', 'Router not found.');
        }
        
        $data = [
            'title' => 'Edit Router',
            'router' => $router,
        ];
        
        return view('routers/edit', $data);
    }
    
    public function update($id)
    {
        $routerModel = new \App\Models\RouterModel();
        $router = $routerModel->find($id);
        
        if (!$router) {
            return redirect()->to('admin/routers')->with('error', 'Router not found.');
        }
        
        $rules = [
            'router_name' => 'required|min_length[3]',
            'ip_address' => 'required|valid_ip',
            'router_type' => 'required|in_list[mikrotik,cisco,ubiquiti,other]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'router_name' => $this->request->getPost('router_name'),
            'ip_address' => $this->request->getPost('ip_address'),
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
            'port' => $this->request->getPost('port') ?? '8728',
            'router_type' => $this->request->getPost('router_type'),
            'location' => $this->request->getPost('location'),
            'status' => $this->request->getPost('status') ?? 'active',
        ];
        
        if ($routerModel->update($id, $data)) {
            return redirect()->to('admin/routers')->with('success', 'Router updated successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to update router.');
    }
    
    public function delete($id)
    {
        $routerModel = new \App\Models\RouterModel();
        
        if ($routerModel->delete($id)) {
            return redirect()->to('admin/routers')->with('success', 'Router deleted successfully.');
        }
        
        return redirect()->to('admin/routers')->with('error', 'Failed to delete router.');
    }
    
    public function test($id)
    {
        $routerModel = new \App\Models\RouterModel();
        
        if ($routerModel->testConnection($id)) {
            return redirect()->back()->with('success', 'Router connection successful!');
        }
        
        return redirect()->back()->with('error', 'Router connection failed!');
    }
}
