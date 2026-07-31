<?php

declare(strict_types=1);

namespace App\Controllers\Concerns;

use App\Core\Authorization;
use App\Core\Response;
use App\Core\Session;

trait AuthorizesAccess
{
    private function authorizeAccess(
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