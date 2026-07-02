<?php

declare(strict_types=1);

namespace App\Database;

use App\Core\Config;
use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    sprintf(
                        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                        Config::get('database.host'),
                        Config::get('database.port'),
                        Config::get('database.database')
                    ),
                    Config::get('database.username'),
                    Config::get('database.password'),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                die('Erro ao conectar ao banco: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}