<?php

declare(strict_types=1);

namespace App\Database;

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
                        DB_HOST,
                        DB_PORT,
                        DB_NAME
                    ),

                    DB_USER,

                    DB_PASS,

                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]

                );

            } catch (PDOException $e) {

                die($e->getMessage());

            }
        }

        return self::$instance;
    }
}