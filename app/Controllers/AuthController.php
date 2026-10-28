<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;
use App\Services\LoginThrottleService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $service,
        private LoginThrottleService $throttle
    ) {
    }

    public function login(): void
    {
        if (Session::has('user')) {
            Response::redirect(base_url());
        }

        $error = Session::get('login_error') ?? Session::get('csrf_error');

        Session::remove('login_error');
        Session::remove('csrf_error');

        $this->view('pages/auth/login', [
            'title' => 'Login - ' . app_name(),
            'error' => $error,
        ], 'auth');
    }

    public function authenticate(): void
    {
        $email = trim((string) Request::post('email'));
        $password = (string) Request::post('password');
        $origin = Request::clientIp();

        if ($this->throttle->retryAfter($email, $origin) > 0) {
            Session::set('login_error', 'Não foi possível entrar com os dados informados. Tente novamente.');
            Response::redirect(base_url('login'));
        }

        $user = $this->service->attempt($email, $password);

        if (!$user) {
            $this->throttle->recordFailure($email, $origin);
            Session::set('login_error', 'Não foi possível entrar com os dados informados. Tente novamente.');

            Response::redirect(base_url('login'));
        }

        $this->throttle->recordSuccess($email);
        Session::authenticate($user);
        \App\Core\Csrf::rotate();

        Response::redirect(base_url());
    }

    public function logout(): void
    {
        Session::destroy();

        Response::redirect(base_url('login'));
    }
}
