<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                email,
                active,
                created_at
            FROM users
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                email,
                active
            FROM users
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (
                name,
                email,
                password,
                active,
                created_at,
                updated_at
            )
            VALUES (
                :name,
                :email,
                :password,
                :active,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'active'   => 1,
        ]);
    }

    public function update(int $id, array $data): void
    {
        if (!empty($data['password'])) {

            $stmt = $this->db->prepare("
                UPDATE users
                SET
                    name = :name,
                    email = :email,
                    password = :password,
                    active = :active,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'id'       => $id,
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => password_hash(
                    $data['password'],
                    PASSWORD_DEFAULT
                ),
                'active'   => $data['active'],
            ]);

            return;
        }

        $stmt = $this->db->prepare("
            UPDATE users
            SET
                name = :name,
                email = :email,
                active = :active,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id'     => $id,
            'name'   => $data['name'],
            'email'  => $data['email'],
            'active' => $data['active'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM users
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
        ]);
    }

    public function emailExists(
        string $email,
        ?int $ignoreId = null
    ): bool {

        if ($ignoreId === null) {

            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM users
                WHERE email = :email
            ");

            $stmt->execute([
                'email' => $email,
            ]);

        } else {

            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM users
                WHERE email = :email
                  AND id <> :id
            ");

            $stmt->execute([
                'email' => $email,
                'id'    => $ignoreId,
            ]);

        }

        return (int) $stmt->fetchColumn() > 0;
    }
}