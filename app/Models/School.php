<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class School extends BaseModel
{
    public function first(): ?array
    {
        $statement = $this->db->query(
            'SELECT * FROM schools LIMIT 1'
        );

        $school = $statement->fetch(PDO::FETCH_ASSOC);

        return $school ?: null;
    }

    public function update(array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE schools
             SET
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
                secondary_color = :secondary_color
             WHERE id = :id'
        );

        return $statement->execute($data);
    }

    public function create(array $data): bool
    {
        $statement = $this->db->prepare(
            'INSERT INTO schools
            (
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
                secondary_color
            )
            VALUES
            (
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
                :secondary_color
            )'
        );

        return $statement->execute($data);
    }
}