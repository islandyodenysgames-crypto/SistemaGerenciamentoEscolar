<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SchoolDayRepository extends BaseRepository
{
    public function forYear(int $yearId): array
    {
        $s=$this->db->prepare('SELECT d.*,p.name period_name FROM school_days d LEFT JOIN school_periods p ON p.id=d.school_period_id WHERE d.school_year_id=:id ORDER BY d.school_date');
        $s->execute(['id'=>$yearId]);return $s->fetchAll(PDO::FETCH_ASSOC)?:[];
    }
    public function upsert(array $d): void
    {
        $d['calendar_event_id']=$d['calendar_event_id']??null;
        $s=$this->db->prepare('INSERT INTO school_days (school_year_id,school_period_id,calendar_event_id,school_date,day_type,is_instructional,is_completed,title,notes,created_at,updated_at) VALUES (:school_year_id,:school_period_id,:calendar_event_id,:school_date,:day_type,:is_instructional,:is_completed,:title,:notes,NOW(),NOW()) ON DUPLICATE KEY UPDATE school_period_id=VALUES(school_period_id),calendar_event_id=VALUES(calendar_event_id),day_type=VALUES(day_type),is_instructional=VALUES(is_instructional),title=VALUES(title),notes=VALUES(notes),updated_at=NOW()');
        $s->execute($d);
    }
    public function summary(int $yearId): array
    {
        $s=$this->db->prepare('SELECT COUNT(*) total,SUM(is_instructional=1) planned,SUM(is_instructional=1 AND school_date<=CURDATE()) elapsed,SUM(is_instructional=1 AND school_date>CURDATE()) remaining,SUM(is_completed=1) completed FROM school_days WHERE school_year_id=:id');
        $s->execute(['id'=>$yearId]);return $s->fetch(PDO::FETCH_ASSOC)?:[];
    }
}
