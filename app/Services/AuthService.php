<?php

declare(strict_types=1);

namespace App\Services;

use App\Auth\Roles;
use App\Database\Connection;
use PDO;

class AuthService
{
    public function __construct(private mixed $database = null)
    {
    }

    public function attempt(
        string $email,
        string $password
    ): ?array {
        $db = $this->database ?? Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                id,
                name,
                email,
                role,
                password,
                active,
                photo_path,
                photo_updated_at

            FROM users

            WHERE email = :email

            LIMIT 1
        ");

        $stmt->execute([
            'email' => trim($email),
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        if ((int) ($user['active'] ?? 0) !== 1) {
            return null;
        }

        if (!password_verify(
            $password,
            (string) ($user['password'] ?? '')
        )) {
            return null;
        }

        unset($user['password']);

        $user['id'] = (int) ($user['id'] ?? 0);

        $user['active'] = (int) (
            $user['active'] ?? 0
        );

        $user['role'] = Roles::normalize(
            (string) ($user['role'] ?? Roles::TEACHER)
        );

        $user['role_label'] = Roles::label(
            $user['role']
        );

        return $user;
    }

    public function currentUser(int $id): ?array
    {
        $db = $this->database ?? Connection::getInstance();
        $stmt = $db->prepare('SELECT id, name, email, role, active, photo_path, photo_updated_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || (int) ($user['active'] ?? 0) !== 1) {
            return null;
        }
        $user['id'] = (int) $user['id'];
        $user['active'] = 1;
        $user['role'] = Roles::normalize((string) ($user['role'] ?? Roles::TEACHER));
        $user['role_label'] = Roles::label($user['role']);
        return $user;
    }
}
