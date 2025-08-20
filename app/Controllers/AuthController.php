<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    public function login()
    {
        helper(['form']);
        echo view('auth/login');
    }

    public function attemptLogin()
    {
        $session = session();
        $userModel = new UserModel();

        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $user = $userModel->where('username', $username)->first();

        if ($user) {
            if (password_verify($password, $user->password)) {
                $ses_data = [
                    'user_id'     => $user->id,
                    'username'    => $user->username,
                    'user_role'   => $user->role,
                    'logged_in'   => TRUE
                ];
                $session->set($ses_data);
                // Redirect all users to admin dashboard
                // Both admin and regular users now go to the same dashboard
                return redirect()->to('/admin/dashboard');
            } else {
                $session->setFlashdata('msg', 'Wrong Password');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('msg', 'User Not Found');
            return redirect()->to('/login');
        }
    }

    public function registerAdmin()
    {
        helper(['form']);
        echo view('auth/register_admin');
    }

    public function attemptRegisterAdmin()
    {
        helper(['form']);
        $rules = [
            'username' => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'passconf' => 'matches[password]'
        ];

        if ($this->validate($rules)) {
            $userModel = new UserModel();
            $data = [
                'username' => $this->request->getVar('username'),
                'password' => $this->request->getVar('password'), // hashing is done in the model
                'role'     => 'admin'

            ]; 
            $userModel->insert($data);
            return redirect()->to('/login');
        } else {
            $data['validation'] = $this->validator;
            echo view('auth/register_admin', $data);
        }
    }

    public function registerUser()
    {
        helper(['form']);
        echo view('auth/register_user');
    }

    public function attemptRegisterUser()
    {
        helper(['form']);
        $rules = [
            'username' => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'passconf' => 'matches[password]'
        ];

        if ($this->validate($rules)) {
            $userModel = new UserModel();
            $data = [
                'username' => $this->request->getVar('username'),
                'password' => $this->request->getVar('password'), // hashing is done in the model
                'role'     => 'user'
            ];
            $userModel->insert($data);
            return redirect()->to('/login');
        } else {
            $data['validation'] = $this->validator;
            echo view('auth/register_user', $data);
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}