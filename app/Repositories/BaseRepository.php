<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }
}