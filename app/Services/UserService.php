<?php

declare(strict_types=1);

namespace App\Services;

use App\Auth\Roles;
use App\Repositories\UserRepository;
use InvalidArgumentException;

class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {
    }

    public function all(): array
    {
        $users = $this->repository->all();

        foreach ($users as &$user) {
            $role = Roles::normalize(
                (string) (
                    $user['role']
                    ?? Roles::TEACHER
                )
            );

            $user['role'] = $role;
            $user['role_label'] = Roles::label(
                $role
            );

            $user['id'] = (int) (
                $user['id'] ?? 0
            );

            $user['active'] = (int) (
                $user['active'] ?? 0
            );
        }

        unset($user);

        return $users;
    }

    public function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $user = $this->repository->find($id);

        if (!$user) {
            return null;
        }

        $role = Roles::normalize(
            (string) (
                $user['role']
                ?? Roles::TEACHER
            )
        );

        $user['id'] = (int) (
            $user['id'] ?? 0
        );

        $user['active'] = (int) (
            $user['active'] ?? 0
        );

        $user['role'] = $role;
        $user['role_label'] = Roles::label(
            $role
        );

        return $user;
    }

    /**
     * Cria um usuário e retorna o ID gerado.
     */
    public function create(array $data): int
    {
        $normalized = $this->normalizeData(
            $data,
            true
        );

        if (
            $this->repository->emailExists(
                $normalized['email']
            )
        ) {
            throw new InvalidArgumentException(
                'Já existe um usuário com esse e-mail.'
            );
        }

        $userId = $this->repository->create(
            $normalized
        );

        if ($userId <= 0) {
            throw new InvalidArgumentException(
                'Não foi possível criar o usuário.'
            );
        }

        return $userId;
    }

    public function update(
        int $id,
        array $data
    ): void {
        if ($id <= 0 || !$this->repository->find($id)) {
            throw new InvalidArgumentException(
                'Usuário não encontrado.'
            );
        }

        $normalized = $this->normalizeData(
            $data,
            false
        );

        if (
            $this->repository->emailExists(
                $normalized['email'],
                $id
            )
        ) {
            throw new InvalidArgumentException(
                'Já existe outro usuário com esse e-mail.'
            );
        }

        $this->repository->update(
            $id,
            $normalized
        );
    }

    public function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return $this->repository->delete($id);
    }

    public function emailExists(
        string $email,
        ?int $ignoreId = null
    ): bool {
        return $this->repository->emailExists(
            strtolower(trim($email)),
            $ignoreId
        );
    }

    public function roles(): array
    {
        return Roles::all();
    }

    private function normalizeData(
        array $data,
        bool $creating
    ): array {
        $name = trim(
            preg_replace(
                '/\s+/u',
                ' ',
                (string) ($data['name'] ?? '')
            ) ?? ''
        );

        $email = strtolower(
            trim(
                (string) ($data['email'] ?? '')
            )
        );

        $password = (string) (
            $data['password'] ?? ''
        );

        $role = Roles::normalize(
            (string) ($data['role'] ?? ''),
            Roles::TEACHER
        );

        $active = array_key_exists(
            'active',
            $data
        )
            ? (
                (int) $data['active'] === 1
                    ? 1
                    : 0
            )
            : 1;

        if ($name === '') {
            throw new InvalidArgumentException(
                'Informe o nome do usuário.'
            );
        }

        if (mb_strlen($name) > 120) {
            throw new InvalidArgumentException(
                'O nome deve possuir no máximo 120 caracteres.'
            );
        }

        if (
            $email === ''
            || !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new InvalidArgumentException(
                'Informe um e-mail válido.'
            );
        }

        if (mb_strlen($email) > 190) {
            throw new InvalidArgumentException(
                'O e-mail deve possuir no máximo 190 caracteres.'
            );
        }

        if ($creating && $password === '') {
            throw new InvalidArgumentException(
                'Informe uma senha para o usuário.'
            );
        }

        if (
            $password !== ''
            && strlen($password) < 6
        ) {
            throw new InvalidArgumentException(
                'A senha deve possuir pelo menos 6 caracteres.'
            );
        }

        return [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'active' => $active,
        ];
    }
}