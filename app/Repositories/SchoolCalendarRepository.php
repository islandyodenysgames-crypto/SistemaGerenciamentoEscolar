<?php

declare(strict_types=1);

namespace App\Repositories;

class SchoolCalendarRepository extends BaseRepository
{
    public function all(): array
    {
        $stmt = $this->db->query("SELECT e.*, u.name AS created_by_name FROM school_calendar_events e LEFT JOIN users u ON u.id=e.created_by ORDER BY e.start_date ASC, e.start_time ASC, e.id ASC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM school_calendar_events WHERE id=:id LIMIT 1');
        $stmt->execute(['id'=>$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function forRange(string $start, string $end): array
    {
        $stmt = $this->db->prepare("SELECT * FROM school_calendar_events WHERE active=1 AND start_date <= :end_date AND COALESCE(end_date,start_date) >= :start_date ORDER BY start_date ASC, start_time ASC, featured DESC");
        $stmt->execute(['start_date'=>$start,'end_date'=>$end]);
        return $stmt->fetchAll();
    }

    public function upcoming(int $limit=6): array
    {
        $limit=max(1,min(20,$limit));
        $stmt=$this->db->query("SELECT * FROM school_calendar_events WHERE active=1 AND COALESCE(end_date,start_date) >= CURDATE() ORDER BY featured DESC, start_date ASC, start_time ASC LIMIT {$limit}");
        return $stmt->fetchAll();
    }

    public function create(array $d): int
    {
        $stmt=$this->db->prepare("INSERT INTO school_calendar_events (title,description,type,start_date,end_date,start_time,end_time,location,all_day,featured,active,created_by,school_year_id,school_period_id,affects_school_day,school_day_type,created_at,updated_at) VALUES (:title,:description,:type,:start_date,:end_date,:start_time,:end_time,:location,:all_day,:featured,:active,:created_by,:school_year_id,:school_period_id,:affects_school_day,:school_day_type,NOW(),NOW())");
        $stmt->execute($d);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id,array $d): bool
    {
        $d['id']=$id;
        $stmt=$this->db->prepare("UPDATE school_calendar_events SET title=:title,description=:description,type=:type,start_date=:start_date,end_date=:end_date,start_time=:start_time,end_time=:end_time,location=:location,all_day=:all_day,featured=:featured,active=:active,school_year_id=:school_year_id,school_period_id=:school_period_id,affects_school_day=:affects_school_day,school_day_type=:school_day_type,updated_at=NOW() WHERE id=:id");
        return $stmt->execute($d);
    }

    public function delete(int $id): bool
    {
        $stmt=$this->db->prepare('DELETE FROM school_calendar_events WHERE id=:id');
        return $stmt->execute(['id'=>$id]);
    }
}
