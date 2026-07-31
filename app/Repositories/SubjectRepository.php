<?php

declare(strict_types=1);

namespace App\Repositories;

use PDOException;

class SubjectRepository extends BaseRepository
{
    /**
     * Retorna todas as disciplinas.
     */
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                subjects.id,
                subjects.name,
                subjects.code,
                subjects.description,
                subjects.active,
                subjects.created_at,
                subjects.updated_at,

                COUNT(
                    DISTINCT user_subjects.user_id
                ) AS total_users,

                COUNT(
                    DISTINCT student_occurrences.id
                ) AS total_occurrences

            FROM subjects

            LEFT JOIN user_subjects
                ON user_subjects.subject_id =
                    subjects.id

            LEFT JOIN student_occurrences
                ON student_occurrences.subject_id =
                    subjects.id

            GROUP BY
                subjects.id,
                subjects.name,
                subjects.code,
                subjects.description,
                subjects.active,
                subjects.created_at,
                subjects.updated_at

            ORDER BY
                subjects.active DESC,

                CASE
                    WHEN subjects.code = 'GERAL'
                    THEN 0
                    ELSE 1
                END ASC,

                subjects.name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Retorna apenas disciplinas ativas.
     */
    public function active(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                code,
                description,
                active,
                created_at,
                updated_at

            FROM subjects

            WHERE active = 1

            ORDER BY
                CASE
                    WHEN code = 'GERAL'
                    THEN 0
                    ELSE 1
                END ASC,

                name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * Localiza uma disciplina pelo ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                subjects.id,
                subjects.name,
                subjects.code,
                subjects.description,
                subjects.active,
                subjects.created_at,
                subjects.updated_at,

                COUNT(
                    DISTINCT user_subjects.user_id
                ) AS total_users,

                COUNT(
                    DISTINCT student_occurrences.id
                ) AS total_occurrences

            FROM subjects

            LEFT JOIN user_subjects
                ON user_subjects.subject_id =
                    subjects.id

            LEFT JOIN student_occurrences
                ON student_occurrences.subject_id =
                    subjects.id

            WHERE subjects.id = :id

            GROUP BY
                subjects.id,
                subjects.name,
                subjects.code,
                subjects.description,
                subjects.active,
                subjects.created_at,
                subjects.updated_at

            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $subject = $stmt->fetch();

        return $subject ?: null;
    }

    /**
     * Localiza uma disciplina pelo código.
     */
    public function findByCode(
        string $code
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                code,
                description,
                active,
                created_at,
                updated_at

            FROM subjects

            WHERE code = :code

            LIMIT 1
        ");

        $stmt->execute([
            'code' => $code,
        ]);

        $subject = $stmt->fetch();

        return $subject ?: null;
    }

    /**
     * Localiza a disciplina padrão
     * Geral da Escola.
     */
    public function general(): ?array
    {
        return $this->findByCode(
            'GERAL'
        );
    }

    /**
     * Verifica se já existe disciplina
     * com o mesmo nome.
     */
    public function nameExists(
        string $name,
        ?int $ignoreId = null
    ): bool {
        $sql = "
            SELECT COUNT(*)

            FROM subjects

            WHERE LOWER(TRIM(name)) =
                LOWER(TRIM(:name))
        ";

        $params = [
            'name' => $name,
        ];

        if (
            $ignoreId !== null
            && $ignoreId > 0
        ) {
            $sql .= "
                AND id <> :ignore_id
            ";

            $params['ignore_id'] =
                $ignoreId;
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica se já existe disciplina
     * com o mesmo código.
     */
    public function codeExists(
        string $code,
        ?int $ignoreId = null
    ): bool {
        $sql = "
            SELECT COUNT(*)

            FROM subjects

            WHERE UPPER(TRIM(code)) =
                UPPER(TRIM(:code))
        ";

        $params = [
            'code' => $code,
        ];

        if (
            $ignoreId !== null
            && $ignoreId > 0
        ) {
            $sql .= "
                AND id <> :ignore_id
            ";

            $params['ignore_id'] =
                $ignoreId;
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Cria uma disciplina.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO subjects (
                name,
                code,
                description,
                active,
                created_at,
                updated_at
            )
            VALUES (
                :name,
                :code,
                :description,
                :active,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'name' =>
                $data['name'],

            'code' =>
                $data['code'],

            'description' =>
                $data['description'] ?? null,

            'active' =>
                $data['active'] ?? 1,
        ]);

        return (int) $this->db
            ->lastInsertId();
    }

    /**
     * Atualiza uma disciplina.
     */
    public function update(
        int $id,
        array $data
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE subjects

            SET
                name = :name,
                code = :code,
                description = :description,
                active = :active,
                updated_at = NOW()

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,

            'name' =>
                $data['name'],

            'code' =>
                $data['code'],

            'description' =>
                $data['description'] ?? null,

            'active' =>
                $data['active'] ?? 1,
        ]);
    }

    /**
     * Ativa ou inativa uma disciplina.
     */
    public function updateActive(
        int $id,
        bool $active
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE subjects

            SET
                active = :active,
                updated_at = NOW()

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,

            'active' =>
                $active ? 1 : 0,
        ]);
    }

    /**
     * Quantidade de usuários vinculados.
     */
    public function countUsers(
        int $subjectId
    ): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)

            FROM user_subjects

            WHERE subject_id = :subject_id
        ");

        $stmt->execute([
            'subject_id' => $subjectId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Quantidade de ocorrências vinculadas.
     */
    public function countOccurrences(
        int $subjectId
    ): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE subject_id = :subject_id
        ");

        $stmt->execute([
            'subject_id' => $subjectId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Verifica se a disciplina possui vínculos
     * que impeçam sua exclusão.
     */
    public function hasRelations(
        int $subjectId
    ): bool {
        return $this->countUsers($subjectId) > 0
            || $this->countOccurrences(
                $subjectId
            ) > 0;
    }

    /**
     * Exclui uma disciplina.
     *
     * O Service deve verificar os vínculos antes.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM subjects

            WHERE id = :id
        ");

        try {
            return $stmt->execute([
                'id' => $id,
            ]);
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Retorna as disciplinas vinculadas
     * a determinado usuário.
     */
    public function byUser(
        int $userId,
        bool $onlyActive = false
    ): array {
        $sql = "
            SELECT
                subjects.id,
                subjects.name,
                subjects.code,
                subjects.description,
                subjects.active,
                user_subjects.created_at
                    AS linked_at

            FROM user_subjects

            INNER JOIN subjects
                ON subjects.id =
                    user_subjects.subject_id

            WHERE user_subjects.user_id =
                :user_id
        ";

        if ($onlyActive) {
            $sql .= "
                AND subjects.active = 1
            ";
        }

        $sql .= "
            ORDER BY subjects.name ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Retorna apenas os IDs das disciplinas
     * vinculadas ao usuário.
     */
    public function idsByUser(
        int $userId
    ): array {
        $stmt = $this->db->prepare("
            SELECT subject_id

            FROM user_subjects

            WHERE user_id = :user_id

            ORDER BY subject_id ASC
        ");

        $stmt->execute([
            'user_id' => $userId,
        ]);

        $ids = [];

        foreach ($stmt->fetchAll() as $row) {
            $subjectId = (int) (
                $row['subject_id'] ?? 0
            );

            if ($subjectId > 0) {
                $ids[] = $subjectId;
            }
        }

        return $ids;
    }

    /**
     * Remove todos os vínculos de disciplina
     * do usuário.
     */
    public function detachAllFromUser(
        int $userId
    ): bool {
        $stmt = $this->db->prepare("
            DELETE FROM user_subjects

            WHERE user_id = :user_id
        ");

        return $stmt->execute([
            'user_id' => $userId,
        ]);
    }

    /**
     * Vincula uma disciplina ao usuário.
     */
    public function attachToUser(
        int $userId,
        int $subjectId
    ): bool {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO user_subjects (
                user_id,
                subject_id,
                created_at,
                updated_at
            )
            VALUES (
                :user_id,
                :subject_id,
                NOW(),
                NOW()
            )
        ");

        return $stmt->execute([
            'user_id' => $userId,
            'subject_id' => $subjectId,
        ]);
    }

    /**
     * Substitui todos os vínculos de disciplinas
     * de determinado usuário.
     *
     * A transação deve ser controlada pelo Service.
     */
    public function syncUserSubjects(
        int $userId,
        array $subjectIds
    ): void {
        $this->detachAllFromUser(
            $userId
        );

        foreach ($subjectIds as $subjectId) {
            $subjectId = (int) $subjectId;

            if ($subjectId <= 0) {
                continue;
            }

            $this->attachToUser(
                $userId,
                $subjectId
            );
        }
    }

    /**
     * Retorna os usuários vinculados
     * à disciplina.
     */
    public function usersBySubject(
        int $subjectId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                users.id,
                users.name,
                users.email,
                users.role,
                users.active,

                user_subjects.created_at
                    AS linked_at

            FROM user_subjects

            INNER JOIN users
                ON users.id =
                    user_subjects.user_id

            WHERE user_subjects.subject_id =
                :subject_id

            ORDER BY users.name ASC
        ");

        $stmt->execute([
            'subject_id' => $subjectId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Ranking de disciplinas por quantidade
     * de ocorrências.
     */
    public function rankingByOccurrences(
        int $limit = 10
    ): array {
        $limit = max(
            1,
            min(50, $limit)
        );

        $stmt = $this->db->query("
            SELECT
                subjects.id,
                subjects.name,
                subjects.code,
                subjects.active,

                COUNT(
                    student_occurrences.id
                ) AS total_occurrences,

                SUM(
                    CASE
                        WHEN student_occurrences.status =
                            'OPEN'
                        THEN 1
                        ELSE 0
                    END
                ) AS open_occurrences,

                SUM(
                    CASE
                        WHEN student_occurrences.severity =
                            'CRITICAL'
                        THEN 1
                        ELSE 0
                    END
                ) AS critical_occurrences

            FROM subjects

            LEFT JOIN student_occurrences
                ON student_occurrences.subject_id =
                    subjects.id

            GROUP BY
                subjects.id,
                subjects.name,
                subjects.code,
                subjects.active

            HAVING total_occurrences > 0

            ORDER BY
                total_occurrences DESC,
                critical_occurrences DESC,
                open_occurrences DESC,
                subjects.name ASC

            LIMIT {$limit}
        ");

        return $stmt->fetchAll();
    }
}