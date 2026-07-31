<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SchoolPeriodRepository extends BaseRepository
{
    public function forYear(int $yearId): array
    {
        $stmt=$this->db->prepare('SELECT * FROM school_periods WHERE school_year_id=:year_id ORDER BY order_number,id');
        $stmt->execute(['year_id'=>$yearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $stmt=$this->db->prepare('SELECT * FROM school_periods WHERE id=:id LIMIT 1');
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function overlaps(int $yearId,string $start,string $end,?int $ignore=null): bool
    {
        $sql='SELECT COUNT(*) FROM school_periods WHERE school_year_id=:year_id AND start_date<=:end_date AND end_date>=:start_date';
        $params=['year_id'=>$yearId,'start_date'=>$start,'end_date'=>$end];
        if($ignore!==null){$sql.=' AND id<>:id';$params['id']=$ignore;}
        $stmt=$this->db->prepare($sql);$stmt->execute($params);
        return (int)$stmt->fetchColumn()>0;
    }

    public function save(?int $id,array $data): int
    {
        if($id===null){
            $stmt=$this->db->prepare('INSERT INTO school_periods (school_year_id,name,short_name,type,order_number,start_date,end_date,color,description,status,created_at,updated_at) VALUES (:school_year_id,:name,:short_name,:type,:order_number,:start_date,:end_date,:color,:description,:status,NOW(),NOW())');
            $stmt->execute($data);return (int)$this->db->lastInsertId();
        }
        $data['id']=$id;
        $stmt=$this->db->prepare('UPDATE school_periods SET name=:name,short_name=:short_name,type=:type,order_number=:order_number,start_date=:start_date,end_date=:end_date,color=:color,description=:description,status=:status,updated_at=NOW() WHERE id=:id AND school_year_id=:school_year_id');
        $stmt->execute($data);return $id;
    }

    public function delete(int $id): void
    {
        $stmt=$this->db->prepare("DELETE FROM school_periods WHERE id=:id AND status IN ('OPEN','REOPENED')");
        $stmt->execute(['id'=>$id]);
        if($stmt->rowCount()!==1) throw new \InvalidArgumentException('Somente períodos abertos podem ser excluídos.');
    }

    public function current(int $yearId,?string $date=null): ?array
    {
        $stmt=$this->db->prepare('SELECT * FROM school_periods WHERE school_year_id=:year_id AND :reference BETWEEN start_date AND end_date ORDER BY order_number LIMIT 1');
        $stmt->execute(['year_id'=>$yearId,'reference'=>$date ?: date('Y-m-d')]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
