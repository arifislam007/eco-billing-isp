<?php

namespace App\Controllers;

class Radius extends BaseController
{
    public function groups()
    {
        $radiusService = new \App\Services\RadiusService();
        $groups = $radiusService->getAllGroups();
        
        $data = [
            'title' => 'RADIUS Groups',
            'groups' => $groups,
        ];
        
        return view('radius/groups', $data);
    }
    
    public function replies()
    {
        $radiusService = new \App\Services\RadiusService();
        $groups = $radiusService->getAllGroups();
        
        $groupAttributes = [];
        foreach ($groups as $group) {
            $groupAttributes[$group['groupname']] = $radiusService->getGroupAttributes($group['groupname']);
        }
        
        $data = [
            'title' => 'RADIUS Replies',
            'groups' => $groups,
            'group_attributes' => $groupAttributes,
        ];
        
        return view('radius/replies', $data);
    }
    
    public function test()
    {
        $data = [
            'title' => 'Test RADIUS',
        ];
        
        return view('radius/test', $data);
    }
    
    public function attemptTest()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        $radiusService = new \App\Services\RadiusService();
        
        if ($radiusService->testAuth($username, $password)) {
            return redirect()->back()->with('success', 'RADIUS authentication successful!');
        }
        
        return redirect()->back()->with('error', 'RADIUS authentication failed!');
    }
}
