<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;
use PDOException;

class SchoolRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function first(): ?array
    {
        try {

            $statement = $this->db->query(
                'SELECT * FROM schools
                 WHERE active = 1
                 ORDER BY id ASC
                 LIMIT 1'
            );

            $school = $statement->fetch(PDO::FETCH_ASSOC);

            return $school ?: null;

        } catch (PDOException) {

            /*
            |--------------------------------------------------------------------------
            | A tabela "schools" pode ainda não existir (primeira execução).
            | Nesse caso retornamos null para que o SchoolService utilize
            | os valores padrão do sistema.
            |--------------------------------------------------------------------------
            */

            return null;

        }
    }

    public function create(array $data): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO schools (
                active,
                name,
                short_name,
                inep_code,
                principal,
                vice_principal,
                address,
                city,
                state,
                zip_code,
                phone,
                email,
                website,
                instagram,
                facebook,
                youtube,
                logo_path,
                primary_color,
                secondary_color,
                created_at,
                updated_at
            ) VALUES (
                1,
                :name,
                :short_name,
                :inep_code,
                :principal,
                :vice_principal,
                :address,
                :city,
                :state,
                :zip_code,
                :phone,
                :email,
                :website,
                :instagram,
                :facebook,
                :youtube,
                :logo_path,
                :primary_color,
                :secondary_color,
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
                inep_code = :inep_code,
                principal = :principal,
                vice_principal = :vice_principal,
                address = :address,
                city = :city,
                state = :state,
                zip_code = :zip_code,
                phone = :phone,
                email = :email,
                website = :website,
                instagram = :instagram,
                facebook = :facebook,
                youtube = :youtube,
                logo_path = :logo_path,
                primary_color = :primary_color,
                secondary_color = :secondary_color,
                updated_at = NOW()
            WHERE id = :id'
        );

        $statement->execute($data);
    }
}