<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function login(): void
    {
        $this->view('auth/login', [
            'title' => 'Login - ' . app_name(),
            'error' => Session::get('login_error'),
        ], 'auth');

        Session::remove('login_error');
    }

    public function authenticate(): void
    {
        $email = trim((string) Request::post('email'));
        $password = (string) Request::post('password');

        $service = new AuthService();

        $user = $service->attempt($email, $password);

        if (!$user) {
            Session::set('login_error', 'E-mail ou senha inválidos.');

            Response::redirect(base_url('login'));
        }

        Session::set('user', $user);

        Response::redirect(base_url());
    }

    public function logout(): void
    {
        Session::remove('user');

        Response::redirect(base_url('login'));
    }
}