<?php

declare(strict_types=1);

namespace App\Repositories;

class NoticeRepository extends BaseRepository
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                school_notices.id,
                school_notices.title,
                school_notices.summary,
                school_notices.content,
                school_notices.category,
                school_notices.banner_path,
                school_notices.youtube_url,
                school_notices.featured,
                school_notices.priority,
                school_notices.pinned,
                school_notices.active,
                school_notices.target,
                school_notices.target_class_id,
                school_notices.published_at,
                school_notices.expires_at,
                school_notices.created_by,
                school_notices.created_by_name,
                school_notices.created_by_role,
                school_notices.created_at,
                school_notices.updated_at,

                users.name AS created_by_name,

                school_classes.name AS target_class_name,
                school_classes.year AS target_class_year,
                school_classes.shift AS target_class_shift

            FROM school_notices

            LEFT JOIN users
                ON users.id = school_notices.created_by

            LEFT JOIN school_classes
                ON school_classes.id = school_notices.target_class_id

            ORDER BY
                school_notices.pinned DESC,
                school_notices.published_at DESC,
                school_notices.created_at DESC,
                school_notices.id DESC
        ");

        return $stmt->fetchAll();
    }

    public function activeForDashboard(?int $limit = null): array
    {
        $limitSql = '';
        if ($limit !== null) {
            $limit = max(1, min(500, $limit));
            $limitSql = " LIMIT {$limit}";
        }

        $stmt = $this->db->query("
            SELECT
                school_notices.id,
                school_notices.title,
                school_notices.summary,
                school_notices.content,
                school_notices.category,
                school_notices.banner_path,
                school_notices.youtube_url,
                school_notices.featured,
                school_notices.priority,
                school_notices.pinned,
                school_notices.active,
                school_notices.target,
                school_notices.target_class_id,
                school_notices.published_at,
                school_notices.expires_at,
                school_notices.created_by,
                school_notices.created_by_name,
                school_notices.created_by_role,
                school_notices.created_at,

                school_classes.name AS target_class_name,
                school_classes.year AS target_class_year,
                school_classes.shift AS target_class_shift

            FROM school_notices

            LEFT JOIN users
                ON users.id = school_notices.created_by

            LEFT JOIN school_classes
                ON school_classes.id = school_notices.target_class_id

            WHERE school_notices.active = 1

              AND school_notices.target <> 'TV_PANEL'

              AND (
                    school_notices.published_at IS NULL
                    OR school_notices.published_at <= NOW()
              )

              AND (
                    school_notices.expires_at IS NULL
                    OR school_notices.expires_at >= NOW()
              )

            ORDER BY
                school_notices.pinned DESC,

                CASE school_notices.priority
                    WHEN 'URGENT' THEN 1
                    WHEN 'IMPORTANT' THEN 2
                    ELSE 3
                END ASC,

                school_notices.published_at DESC,
                school_notices.created_at DESC,
                school_notices.id DESC

            {$limitSql}
        ");

        return $stmt->fetchAll();
    }

    public function activeForTarget(string $target, ?int $limit = null): array
    {
        $target = strtoupper(trim($target));
        $limitSql = '';
        if ($limit !== null) {
            $limit = max(1, min(500, $limit));
            $limitSql = " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare("
            SELECT
                school_notices.id,
                school_notices.title,
                school_notices.summary,
                school_notices.content,
                school_notices.category,
                school_notices.banner_path,
                school_notices.youtube_url,
                school_notices.featured,
                school_notices.priority,
                school_notices.pinned,
                school_notices.active,
                school_notices.target,
                school_notices.target_class_id,
                school_notices.published_at,
                school_notices.expires_at,
                school_notices.created_by,
                school_notices.created_by_name,
                school_notices.created_by_role,
                school_notices.created_at,
                school_classes.name AS target_class_name,
                school_classes.year AS target_class_year,
                school_classes.shift AS target_class_shift
            FROM school_notices
            LEFT JOIN users ON users.id = school_notices.created_by
            LEFT JOIN school_classes ON school_classes.id = school_notices.target_class_id
            WHERE school_notices.active = 1
              AND school_notices.target = :target
              AND (school_notices.published_at IS NULL OR school_notices.published_at <= NOW())
              AND (school_notices.expires_at IS NULL OR school_notices.expires_at >= NOW())
            ORDER BY
                school_notices.pinned DESC,
                CASE school_notices.priority
                    WHEN 'URGENT' THEN 1
                    WHEN 'HIGH' THEN 2
                    WHEN 'IMPORTANT' THEN 3
                    ELSE 4
                END ASC,
                school_notices.published_at DESC,
                school_notices.created_at DESC,
                school_notices.id DESC
            {$limitSql}
        ");
        $stmt->execute(['target' => $target]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                school_notices.id,
                school_notices.title,
                school_notices.summary,
                school_notices.content,
                school_notices.category,
                school_notices.banner_path,
                school_notices.youtube_url,
                school_notices.featured,
                school_notices.priority,
                school_notices.pinned,
                school_notices.active,
                school_notices.target,
                school_notices.target_class_id,
                school_notices.published_at,
                school_notices.expires_at,
                school_notices.created_by,
                school_notices.created_by_name,
                school_notices.created_by_role,
                school_notices.created_at,
                school_notices.updated_at,

                school_classes.name AS target_class_name,
                school_classes.year AS target_class_year,
                school_classes.shift AS target_class_shift

            FROM school_notices

            LEFT JOIN users
                ON users.id = school_notices.created_by

            LEFT JOIN school_classes
                ON school_classes.id = school_notices.target_class_id

            WHERE school_notices.id = :id

            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $notice = $stmt->fetch();

        return $notice ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO school_notices (
                title,
                summary,
                content,
                category,
                banner_path,
                youtube_url,
                featured,
                priority,
                pinned,
                active,
                target,
                target_class_id,
                published_at,
                expires_at,
                created_by,
                created_by_name,
                created_by_role,
                created_at,
                updated_at
            )
            VALUES (
                :title,
                :summary,
                :content,
                :category,
                :banner_path,
                :youtube_url,
                :featured,
                :priority,
                :pinned,
                :active,
                :target,
                :target_class_id,
                :published_at,
                :expires_at,
                :created_by,
                :created_by_name,
                :created_by_role,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'title' => $data['title'],
            'summary' => $data['summary'],
            'content' => $data['content'],
            'category' => $data['category'],
            'banner_path' => $data['banner_path'],
            'youtube_url' => $data['youtube_url'],
            'featured' => $data['featured'],
            'priority' => $data['priority'],
            'pinned' => $data['pinned'],
            'active' => $data['active'],
            'target' => $data['target'],
            'target_class_id' => $data['target_class_id'],
            'published_at' => $data['published_at'],
            'expires_at' => $data['expires_at'],
            'created_by'      => $data['created_by'],
            'created_by_name' => $data['created_by_name'],
            'created_by_role' => $data['created_by_role'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(
        int $id,
        array $data
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE school_notices

            SET
                title = :title,
                summary = :summary,
                content = :content,
                category = :category,
                banner_path = :banner_path,
                youtube_url = :youtube_url,
                featured = :featured,
                priority = :priority,
                pinned = :pinned,
                active = :active,
                target = :target,
                target_class_id = :target_class_id,
                published_at = :published_at,
                expires_at = :expires_at,
                updated_at = NOW()

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'content' => $data['content'],
            'category' => $data['category'],
            'banner_path' => $data['banner_path'],
            'youtube_url' => $data['youtube_url'],
            'featured' => $data['featured'],
            'priority' => $data['priority'],
            'pinned' => $data['pinned'],
            'active' => $data['active'],
            'target' => $data['target'],
            'target_class_id' => $data['target_class_id'],
            'published_at' => $data['published_at'],
            'expires_at' => $data['expires_at'],
        ]);
    }

    public function updateActive(
        int $id,
        bool $active
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE school_notices

            SET
                active = :active,
                updated_at = NOW()

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'active' => $active ? 1 : 0,
        ]);
    }

    public function updatePinned(
        int $id,
        bool $pinned
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE school_notices

            SET
                pinned = :pinned,
                updated_at = NOW()

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'pinned' => $pinned ? 1 : 0,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM school_notices
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
        ]);
    }

    public function countActive(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM school_notices

            WHERE active = 1

              AND (
                    published_at IS NULL
                    OR published_at <= NOW()
              )

              AND (
                    expires_at IS NULL
                    OR expires_at >= NOW()
              )
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countScheduled(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM school_notices

            WHERE active = 1
              AND published_at IS NOT NULL
              AND published_at > NOW()
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countExpired(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM school_notices

            WHERE expires_at IS NOT NULL
              AND expires_at < NOW()
        ");

        return (int) $stmt->fetchColumn();
    }

    public function highestActivePriority(): string
    {
        $stmt = $this->db->query("
            SELECT priority
             
            FROM school_notices
             
            WHERE active = 1
             
                 AND (
                         published_at IS NULL
                         OR published_at <= NOW()
                 )
                         
                 AND (
                         expires_at IS NULL
                         OR expires_at >= NOW()
                 )
                         
            ORDER BY
                 FIELD(
                     priority,
                     'URGENT',
                     'IMPORTANT',
                     'INFO'
                     
                )
                     
            LIMIT 1
        ");
        
        $priority = $stmt->fetchColumn();
        
        if ($priority === false) {
            return 'INFO';
        }
        
        return (string) $priority;
    }
}