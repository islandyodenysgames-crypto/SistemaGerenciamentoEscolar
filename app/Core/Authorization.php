<?php

declare(strict_types=1);

namespace App\Core;

use App\Auth\Permissions;
use App\Auth\Roles;

class Authorization
{
    public static function user(): array
    {
        $user = Session::get('user');

        return is_array($user)
            ? $user
            : [];
    }

    public static function role(): ?string
    {
        $user = self::user();

        if (empty($user)) {
            return null;
        }

        return Roles::normalize(
            (string) ($user['role'] ?? Roles::TEACHER)
        );
    }

    public static function can(string $permission): bool
    {
        $role = self::role();

        if ($role === null) {
            return false;
        }

        return Permissions::roleHas(
            $role,
            $permission
        );
    }

    public static function cannot(string $permission): bool
    {
        return !self::can($permission);
    }

    public static function hasRole(string $role): bool
    {
        return self::role() === Roles::normalize($role);
    }

    public static function hasAnyRole(array $roles): bool
    {
        $currentRole = self::role();

        if ($currentRole === null) {
            return false;
        }

        foreach ($roles as $role) {
            if ($currentRole === Roles::normalize((string) $role)) {
                return true;
            }
        }

        return false;
    }

    public static function authorize(
        string $permission,
        ?string $redirectTo = null
    ): void {
        if (self::can($permission)) {
            return;
        }

        Session::set(
            'authorization_error',
            'Você não possui permissão para acessar este recurso.'
        );

        Response::redirect(
            $redirectTo ?? base_url()
        );
    }
}