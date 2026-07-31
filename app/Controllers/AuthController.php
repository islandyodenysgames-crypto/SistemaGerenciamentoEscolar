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
    public function __construct(
        private AuthService $service
    ) {
    }

    public function login(): void
    {
        if (Session::has('user')) {
            Response::redirect(base_url());
        }

        $error = Session::get('login_error');

        Session::remove('login_error');

        $this->view('pages/auth/login', [
            'title' => 'Login - ' . app_name(),
            'error' => $error,
        ], 'auth');
    }

    public function authenticate(): void
    {
        $email = trim((string) Request::post('email'));
        $password = (string) Request::post('password');

        $user = $this->service->attempt($email, $password);

        if (!$user) {
            Session::set('login_error', 'E-mail ou senha inválidos.');

            Response::redirect(base_url('login'));
        }

        session_regenerate_id(true);
        Session::set('user', $user);

        Response::redirect(base_url());
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parameters['path'],
                $parameters['domain'],
                $parameters['secure'],
                $parameters['httponly']
            );
        }

        Session::destroy();

        Response::redirect(base_url('login'));
    }
}
