<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class AuthService
{
    public function attempt(string $email, string $password): ?array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT id, name, email, password, active
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            'email' => $email,
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        if ((int) $user['active'] !== 1) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        unset($user['password']);

        return $user;
    }
}