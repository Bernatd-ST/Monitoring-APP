<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->has('logged_in')) {
            return redirect()->to('/login');
        }
        
        // Get the current URI path
        $uri = $request->getUri()->getPath();
        
        // Get user role from session
        $userRole = session()->get('user_role');
        
        // If user is admin, allow access to everything
        if ($userRole === 'admin') {
            return;
        }
        
        // Define allowed paths for regular users
        $allowedPaths = [
            'admin/dashboard',
            'admin/dashboard-analytics',
            'admin/sales/sales',
            'admin/sales/actual',
            'admin/report',
            'material-shortage'
        ];
        
        // Check if the current path is allowed for regular users
        $isAllowed = false;
        foreach ($allowedPaths as $path) {
            if (strpos($uri, $path) !== false) {
                $isAllowed = true;
                break;
            }
        }
        
        // If path is not allowed for regular users, redirect to dashboard
        if (!$isAllowed) {
            return redirect()->to('/admin/dashboard');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        
    }
}