<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Authorization;
use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;

abstract class BaseController extends Controller
{
    protected function guard(): bool
    {
        if (Session::has('user')) {
            return true;
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