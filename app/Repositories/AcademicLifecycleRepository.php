<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AcademicLifecycleRepository extends BaseRepository
{
    public function period(int $id): ?array
    {
        $s=$this->db->prepare('SELECT p.*,y.name year_name FROM school_periods p JOIN school_years y ON y.id=p.school_year_id WHERE p.id=:id');
        $s->execute(['id'=>$id]);return $s->fetch(PDO::FETCH_ASSOC)?:null;
    }
    public function metrics(string $start,string $end): array
    {
        $s=$this->db->prepare("SELECT COUNT(DISTINCT a.id) attendance_days,COUNT(ai.id) attendance_records,SUM(ai.status='P') present_count,SUM(ai.status='F') absence_count FROM attendance a LEFT JOIN attendance_items ai ON ai.attendance_id=a.id WHERE a.attendance_date BETWEEN :start AND :end");
        $s->execute(['start'=>$start,'end'=>$end]);$attendance=$s->fetch(PDO::FETCH_ASSOC)?:[];
        $s=$this->db->prepare('SELECT COUNT(*) FROM student_occurrences WHERE occurrence_date BETWEEN :start AND :end');$s->execute(['start'=>$start,'end'=>$end]);
        $attendance['occurrences']=(int)$s->fetchColumn();
        $attendance['frequency_percentage']=(int)($attendance['attendance_records']??0)>0?round(((int)$attendance['present_count']/(int)$attendance['attendance_records'])*100,2):0;
        return $attendance;
    }
    public function snapshot(int $yearId,?int $periodId,string $type,array $metrics,?int $userId): void
    {
        $s=$this->db->prepare('INSERT INTO academic_snapshots (school_year_id,school_period_id,snapshot_type,metrics_json,created_by) VALUES (:year,:period,:type,:metrics,:user)');
        $s->execute(['year'=>$yearId,'period'=>$periodId,'type'=>$type,'metrics'=>json_encode($metrics,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),'user'=>$userId]);
    }
    public function setPeriodStatus(int $id,string $status): void
    {
        $s=$this->db->prepare('UPDATE school_periods SET status=:status,updated_at=NOW() WHERE id=:id');$s->execute(['id'=>$id,'status'=>$status]);
    }
    public function audit(?int $yearId,?int $periodId,string $action,string $description,array $metadata,?int $userId,?string $ip): void
    {
        $s=$this->db->prepare('INSERT INTO academic_audit_log (school_year_id,school_period_id,action,description,metadata_json,user_id,ip_address) VALUES (:year,:period,:action,:description,:metadata,:user,:ip)');
        $s->execute(['year'=>$yearId,'period'=>$periodId,'action'=>$action,'description'=>$description,'metadata'=>$metadata?json_encode($metadata,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR):null,'user'=>$userId,'ip'=>$ip]);
    }
    public function history(): array
    {
        return $this->db->query("SELECT y.id,y.name,y.year,y.status,s.metrics_json,s.created_at snapshot_at FROM school_years y LEFT JOIN academic_snapshots s ON s.id=(SELECT s2.id FROM academic_snapshots s2 WHERE s2.school_year_id=y.id AND s2.snapshot_type='YEAR_CLOSE' ORDER BY s2.id DESC LIMIT 1) ORDER BY y.year DESC")->fetchAll(PDO::FETCH_ASSOC)?:[];
    }
    public function audits(int $limit=100): array
    {
        $limit=max(1,min(500,$limit));return $this->db->query("SELECT l.*,u.name user_name,y.name year_name,p.name period_name FROM academic_audit_log l LEFT JOIN users u ON u.id=l.user_id LEFT JOIN school_years y ON y.id=l.school_year_id LEFT JOIN school_periods p ON p.id=l.school_period_id ORDER BY l.created_at DESC,l.id DESC LIMIT {$limit}")->fetchAll(PDO::FETCH_ASSOC)?:[];
    }
}
