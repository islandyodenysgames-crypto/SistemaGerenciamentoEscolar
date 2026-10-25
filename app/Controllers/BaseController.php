<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Authorization;
use App\Core\Controller;
use App\Core\Response;
use App\Core\Request;
use App\Core\Session;

abstract class BaseController extends Controller
{
    protected function guard(): bool
    {
        if (Session::has('user')) {
            return true;
        }

        if (Request::expectsJson()) {
            http_response_code(401);
            Response::json(['success' => false, 'code' => 'AUTHENTICATION_REQUIRED', 'message' => 'Sua sessão expirou. Entre novamente.']);
        }

        Response::redirect(
            base_url('login')
        );

        return false;
    }

    protected function authorize(
        string $permission,
        ?string $redirectTo = null
    ): bool {
        if (Authorization::can($permission)) {
            return true;
        }

        if (Request::expectsJson()) {
            http_response_code(403);
            Response::json(['success' => false, 'code' => 'ACCESS_DENIED', 'message' => 'Você não possui permissão para realizar esta ação.']);
        }

        Session::set(
            'authorization_error',
            'Você não possui permissão para realizar esta ação.'
        );

        Response::redirect(
            $redirectTo ?? base_url()
        );

        return false;
    }
}
