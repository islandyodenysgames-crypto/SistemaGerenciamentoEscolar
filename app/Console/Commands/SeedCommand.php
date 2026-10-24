<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Database\Connection;
use App\Core\Env;

class SeedCommand
{
    public function handle(): void
    {
        $db = Connection::getInstance();

        $email = trim((string) Env::get('INITIAL_ADMIN_EMAIL', ''));
        $name = trim((string) Env::get('INITIAL_ADMIN_NAME', 'Administrador'));
        $password = (string) Env::get('INITIAL_ADMIN_PASSWORD', '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            throw new \RuntimeException('Defina INITIAL_ADMIN_EMAIL e INITIAL_ADMIN_PASSWORD (mínimo de 12 caracteres) antes de executar o seed.');
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ((int) $stmt->fetchColumn() > 0) {
            echo "Usuário administrador já existe." . PHP_EOL;
            return;
        }

        $stmt = $db->prepare("
            INSERT INTO users (name, email, password, role, active, created_at, updated_at)
            VALUES (:name, :email, :password, :role, :active, NOW(), NOW())
        ");

        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'ADMIN',
            'active' => 1,
        ]);

        echo "Usuário administrador criado com sucesso." . PHP_EOL;
    }
}
