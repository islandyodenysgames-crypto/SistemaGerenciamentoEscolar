<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Database\Connection;

class SeedCommand
{
    public function handle(): void
    {
        $db = Connection::getInstance();

        $email = 'admin@sistema.local';

        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ((int) $stmt->fetchColumn() > 0) {
            echo "Usuário administrador já existe." . PHP_EOL;
            return;
        }

        $stmt = $db->prepare("
            INSERT INTO users (name, email, password, active, created_at, updated_at)
            VALUES (:name, :email, :password, :active, NOW(), NOW())
        ");

        $stmt->execute([
            'name' => 'Administrador',
            'email' => $email,
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'active' => 1,
        ]);

        echo "Usuário administrador criado com sucesso." . PHP_EOL;
    }
}