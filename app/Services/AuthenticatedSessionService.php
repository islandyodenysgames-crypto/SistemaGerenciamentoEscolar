<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Session;

final class AuthenticatedSessionService
{
    public function __construct(private AuthService $auth)
    {
    }

    public function revalidateIfDue(): void
    {
        $sessionUser = Session::get('user');
        if (!is_array($sessionUser) || empty($sessionUser['id'])) {
            return;
        }
        $now = time();
        $last = (int) Session::get('_user_revalidated_at', 0);
        if (($now - $last) < max(30, (int) Config::get('session.revalidate_interval', 300))) {
            return;
        }
        $fresh = $this->auth->currentUser((int) $sessionUser['id']);
        if ($fresh === null) {
            Session::remove('user');
            Session::set('login_error', 'Seu acesso foi alterado. Entre novamente.');
            Session::set('_user_revalidated_at', $now);
            return;
        }
        Session::set('user', $fresh);
        Session::set('_user_revalidated_at', $now);
    }
}
