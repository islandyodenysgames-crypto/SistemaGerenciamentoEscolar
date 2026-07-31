<?php

declare(strict_types=1);

namespace App\Auth;

class Roles
{
    public const ADMIN = 'ADMIN';

    public const DIRECTION = 'DIRECTION';

    public const COORDINATION = 'COORDINATION';

    public const SECRETARY = 'SECRETARY';

    public const TEACHER = 'TEACHER';

    public static function all(): array
    {
        return [
            self::ADMIN => 'Administrador',
            self::DIRECTION => 'Direção',
            self::COORDINATION => 'Coordenação',
            self::SECRETARY => 'Secretaria',
            self::TEACHER => 'Professor',
        ];
    }

    public static function exists(string $role): bool
    {
        return array_key_exists(
            strtoupper($role),
            self::all()
        );
    }

    public static function normalize(
        string $role,
        string $default = self::TEACHER
    ): string {
        $role = strtoupper(trim($role));

        return self::exists($role)
            ? $role
            : $default;
    }

    public static function label(string $role): string
    {
        $role = self::normalize($role);

        return self::all()[$role] ?? 'Professor';
    }

    public static function hasFullAccess(string $role): bool
    {
        return in_array(
            self::normalize($role),
            [
                self::ADMIN,
                self::DIRECTION,
                self::COORDINATION,
            ],
            true
        );
    }
}