<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class SearchService
{
    public function search(string $term, int $limit = 8): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return ['students'=>[],'classes'=>[],'occurrences'=>[],'monitoring'=>[],'notices'=>[],'users'=>[]];
        }
        $limit = max(1, min(12, $limit));
        return [
            'students'=>$this->students($term,$limit),
            'classes'=>$this->classes($term,$limit),
            'occurrences'=>$this->occurrences($term,$limit),
            'monitoring'=>$this->monitoring($term,$limit),
            'notices'=>$this->notices($term,$limit),
            'users'=>$this->users($term,$limit),
        ];
    }

    private function rows(string $sql, string $term): array
    {
        $parameters = [];
        $index = 0;
        $searchValue = '%' . $term . '%';

        // O PDO/MySQL com prepares nativos não permite reutilizar o mesmo
        // placeholder nomeado várias vezes na consulta. Cada ocorrência de
        // :term recebe um nome próprio e o mesmo valor de pesquisa.
        $sql = preg_replace_callback(
            '/:term\b/',
            static function () use (&$parameters, &$index, $searchValue): string {
                $placeholder = 'term_' . $index++;
                $parameters[$placeholder] = $searchValue;

                return ':' . $placeholder;
            },
            $sql
        );

        if ($sql === null) {
            return [];
        }

        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }

    private function students(string $term,int $limit): array
    {
        return $this->rows("SELECT s.id,s.name AS title,CONCAT(COALESCE(s.registration,'Sem matrícula'),' · ',COALESCE(c.name,'Sem turma')) AS subtitle,CONCAT('/alunos/perfil?id=',s.id) AS url,'student' AS type
            FROM students s
            LEFT JOIN enrollments e ON e.id=(
                SELECT e2.id
                FROM enrollments e2
                WHERE e2.student_id=s.id AND e2.active=1
                ORDER BY e2.id DESC
                LIMIT 1
            )
            LEFT JOIN school_classes c ON c.id=e.school_class_id
            WHERE s.name LIKE :term OR s.registration LIKE :term
            ORDER BY s.active DESC,s.name
            LIMIT {$limit}",$term);
    }

    private function classes(string $term,int $limit): array
    {
        return $this->rows("SELECT c.id,c.name AS title,CONCAT(COALESCE(c.year,''),' · ',COALESCE(c.shift,'')) AS subtitle,CONCAT('/alunos/turma?id=',c.id) AS url,'class' AS type FROM school_classes c WHERE c.name LIKE :term OR c.shift LIKE :term OR c.year LIKE :term ORDER BY c.active DESC,c.year DESC,c.name LIMIT {$limit}",$term);
    }

    private function occurrences(string $term,int $limit): array
    {
        return $this->rows("SELECT o.id,COALESCE(NULLIF(o.title,''),CONCAT('Ocorrência #',o.id)) AS title,CONCAT(s.name,' · ',DATE_FORMAT(o.occurrence_date,'%d/%m/%Y')) AS subtitle,CONCAT('/ocorrencias/editar?id=',o.id) AS url,'occurrence' AS type FROM student_occurrences o JOIN students s ON s.id=o.student_id WHERE o.title LIKE :term OR o.description LIKE :term OR s.name LIKE :term ORDER BY o.occurrence_date DESC,o.id DESC LIMIT {$limit}",$term);
    }

    private function monitoring(string $term,int $limit): array
    {
        return $this->rows("SELECT m.id,COALESCE(NULLIF(m.case_title,''),CONCAT('Caso #',m.id)) AS title,CONCAT(s.name,' · ',CASE WHEN m.status='ACTIVE' THEN 'Ativo' ELSE 'Concluído' END) AS subtitle,CONCAT('/acompanhamentos?student_id=',m.student_id,'&case_id=',m.id) AS url,'monitoring' AS type FROM student_monitoring m JOIN students s ON s.id=m.student_id WHERE m.case_title LIKE :term OR m.problem_details LIKE :term OR m.reason LIKE :term OR s.name LIKE :term ORDER BY (m.status='ACTIVE') DESC,m.id DESC LIMIT {$limit}",$term);
    }

    private function notices(string $term,int $limit): array
    {
        return $this->rows("SELECT n.id,n.title,CONCAT(CASE n.priority WHEN 'URGENT' THEN 'Urgente' WHEN 'IMPORTANT' THEN 'Importante' ELSE 'Informativo' END,' · Aviso') AS subtitle,CONCAT('/avisos/editar?id=',n.id) AS url,'notice' AS type FROM school_notices n WHERE n.title LIKE :term OR n.content LIKE :term ORDER BY n.active DESC,n.pinned DESC,n.id DESC LIMIT {$limit}",$term);
    }

    private function users(string $term,int $limit): array
    {
        return $this->rows("SELECT u.id,u.name AS title,CONCAT(u.email,' · ',COALESCE(u.role,'Usuário')) AS subtitle,CONCAT('/usuarios/editar?id=',u.id) AS url,'user' AS type FROM users u WHERE u.name LIKE :term OR u.email LIKE :term ORDER BY u.active DESC,u.name LIMIT {$limit}",$term);
    }
}
