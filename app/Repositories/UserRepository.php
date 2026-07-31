<?php

declare(strict_types=1);

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                email,
                role,
                active,
                photo_path,
                photo_updated_at,
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
                role,
                active,
                photo_path,
                photo_updated_at

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

    /**
     * Cria um usuário e retorna o ID gerado.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (
                name,
                email,
                role,
                password,
                active,
                photo_path,
                photo_updated_at,
                created_at,
                updated_at
            )
            VALUES (
                :name,
                :email,
                :role,
                :password,
                :active,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],

            'password' => password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            ),

            'active' => $data['active'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(
        int $id,
        array $data
    ): void {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare("
                UPDATE users

                SET
                    name = :name,
                    email = :email,
                    role = :role,
                    password = :password,
                    active = :active,
                    updated_at = NOW()

                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],

                'password' => password_hash(
                    $data['password'],
                    PASSWORD_DEFAULT
                ),

                'active' => $data['active'],
            ]);

            return;
        }

        $stmt = $this->db->prepare("
            UPDATE users

            SET
                name = :name,
                email = :email,
                role = :role,
                active = :active,
                updated_at = NOW()

            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
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
                'id' => $ignoreId,
            ]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }
    public function activeRecipientIds(string $target = 'ALL'): array
    {
        $target = strtoupper(trim($target));
        $roles = match ($target) {
            'TEACHERS', 'CLASS' => ['TEACHER'],
            'COORDINATION' => ['COORDINATION'],
            'SECRETARY' => ['SECRETARY'],
            'ADMINISTRATION' => ['ADMIN', 'DIRECTION'],
            default => [],
        };

        if ($roles === []) {
            $stmt = $this->db->query("SELECT id FROM users WHERE active = 1 ORDER BY id");
            return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
        }

        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $stmt = $this->db->prepare("SELECT id FROM users WHERE active = 1 AND role IN ({$placeholders}) ORDER BY id");
        $stmt->execute($roles);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }


}