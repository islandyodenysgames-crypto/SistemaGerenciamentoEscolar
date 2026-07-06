<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

class SchoolRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function first(): ?array
    {
        $statement = $this->db->query(
            'SELECT * FROM schools WHERE active = 1 ORDER BY id ASC LIMIT 1'
        );

        $school = $statement->fetch(PDO::FETCH_ASSOC);

        return $school ?: null;
    }

    public function create(array $data): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO schools (
                name,
                short_name,
                city,
                state,
                address,
                principal,
                phone,
                email,
                website,
                logo_path,
                primary_color,
                secondary_color,
                active,
                created_at,
                updated_at
            ) VALUES (
                :name,
                :short_name,
                :city,
                :state,
                :address,
                :principal,
                :phone,
                :email,
                :website,
                :logo_path,
                :primary_color,
                :secondary_color,
                1,
                NOW(),
                NOW()
            )'
        );

        $statement->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;

        $statement = $this->db->prepare(
            'UPDATE schools SET
                name = :name,
                short_name = :short_name,
                city = :city,
                state = :state,
                address = :address,
                principal = :principal,
                phone = :phone,
                email = :email,
                website = :website,
                logo_path = :logo_path,
                primary_color = :primary_color,
                secondary_color = :secondary_color,
                updated_at = NOW()
            WHERE id = :id'
        );

        $statement->execute($data);
    }
}