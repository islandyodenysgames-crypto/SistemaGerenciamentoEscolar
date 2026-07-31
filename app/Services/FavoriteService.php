<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class FavoriteService
{
    private const TYPES = ['student', 'class', 'monitoring'];

    public function toggle(int $userId, string $type, int $id): bool
    {
        $type = strtolower(trim($type));
        if ($userId <= 0 || $id <= 0 || !in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException('Favorito inválido.');
        }

        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT id FROM user_favorites WHERE user_id=:user_id AND favorite_type=:type AND favorite_id=:favorite_id LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'type' => $type, 'favorite_id' => $id]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $delete = $db->prepare('DELETE FROM user_favorites WHERE id=:id');
            $delete->execute(['id' => (int) $existing]);
            return false;
        }

        $insert = $db->prepare('INSERT INTO user_favorites (user_id,favorite_type,favorite_id) VALUES (:user_id,:type,:favorite_id)');
        $insert->execute(['user_id' => $userId, 'type' => $type, 'favorite_id' => $id]);
        return true;
    }

    public function isFavorite(int $userId, string $type, int $id): bool
    {
        $type = strtolower(trim($type));
        if ($userId <= 0 || $id <= 0 || !in_array($type, self::TYPES, true)) {
            return false;
        }

        $stmt = Connection::getInstance()->prepare(
            'SELECT 1 FROM user_favorites WHERE user_id=:user_id AND favorite_type=:type AND favorite_id=:favorite_id LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'favorite_id' => $id,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function allForUser(int $userId): array
    {
        if ($userId <= 0) return [];
        $db = Connection::getInstance();
        $sql = "SELECT f.id AS favorite_record_id,f.favorite_type,f.favorite_id,f.created_at,
            CASE f.favorite_type
                WHEN 'student' THEN s.name
                WHEN 'class' THEN c.name
                WHEN 'monitoring' THEN COALESCE(NULLIF(m.case_title,''), CONCAT('Caso #',m.id))
            END AS title,
            CASE f.favorite_type
                WHEN 'student' THEN CONCAT('/alunos/perfil?id=',s.id)
                WHEN 'class' THEN CONCAT('/alunos/turma?id=',c.id)
                WHEN 'monitoring' THEN CONCAT('/acompanhamentos?student_id=',m.student_id,'&case_id=',m.id)
            END AS url,
            CASE f.favorite_type
                WHEN 'student' THEN COALESCE(c2.name,'Sem turma')
                WHEN 'class' THEN CONCAT(COALESCE(c.year,''),' · ',COALESCE(c.shift,''))
                WHEN 'monitoring' THEN COALESCE(s2.name,'Aluno')
            END AS subtitle
        FROM user_favorites f
        LEFT JOIN students s ON f.favorite_type='student' AND s.id=f.favorite_id
        LEFT JOIN enrollments e ON e.id=(
            SELECT e2.id
            FROM enrollments e2
            WHERE e2.student_id=s.id AND e2.active=1
            ORDER BY e2.id DESC
            LIMIT 1
        )
        LEFT JOIN school_classes c2 ON e.school_class_id=c2.id
        LEFT JOIN school_classes c ON f.favorite_type='class' AND c.id=f.favorite_id
        LEFT JOIN student_monitoring m ON f.favorite_type='monitoring' AND m.id=f.favorite_id
        LEFT JOIN students s2 ON m.student_id=s2.id
        WHERE f.user_id=:user_id
        HAVING title IS NOT NULL
        ORDER BY f.created_at DESC
        LIMIT 12";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function keysForUser(int $userId): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT favorite_type,favorite_id FROM user_favorites WHERE user_id=:user_id');
        $stmt->execute(['user_id' => $userId]);
        $keys=[];
        foreach ($stmt->fetchAll() as $row) $keys[$row['favorite_type'].':'.$row['favorite_id']] = true;
        return $keys;
    }
}
