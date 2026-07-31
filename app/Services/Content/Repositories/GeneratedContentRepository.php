<?php

declare(strict_types=1);

namespace App\Services\Content\Repositories;

use App\Database\Connection;
use PDO;

final class GeneratedContentRepository
{
    public function findUsable(string $type, string $date): ?array
    {
        $stmt = Connection::getInstance()->prepare("SELECT * FROM institutional_generated_contents WHERE content_type=:type AND reference_date=:date AND status IN ('published','approved') ORDER BY id DESC LIMIT 1");
        $stmt->execute(['type'=>$type,'date'=>$date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findAny(string $type, string $date): ?array
    {
        $stmt = Connection::getInstance()->prepare('SELECT * FROM institutional_generated_contents WHERE content_type=:type AND reference_date=:date ORDER BY id DESC LIMIT 1');
        $stmt->execute(['type'=>$type,'date'=>$date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = Connection::getInstance()->prepare("INSERT INTO institutional_generated_contents (content_type,reference_date,style,text_content,data_snapshot,source_provider,status,is_automatic,published_at,created_at,updated_at) VALUES (:content_type,:reference_date,:style,:text_content,:data_snapshot,:source_provider,:status,1,:published_at,NOW(),NOW())");
        $stmt->execute($data);
        return (int)Connection::getInstance()->lastInsertId();
    }

    public function paginate(?string $status = null, int $limit = 100): array
    {
        $limit = max(1,min(300,$limit));
        $sql = 'SELECT c.*,u.name approved_by_name FROM institutional_generated_contents c LEFT JOIN users u ON u.id=c.approved_by';
        $params=[];
        if ($status !== null && $status !== '') { $sql .= ' WHERE c.status=:status'; $params['status']=$status; }
        $sql .= " ORDER BY c.reference_date DESC,c.id DESC LIMIT {$limit}";
        $stmt=Connection::getInstance()->prepare($sql);$stmt->execute($params);return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id,string $status,?int $userId,?string $text=null): void
    {
        $allowed=['pending','approved','published','discarded'];
        if(!in_array($status,$allowed,true)) throw new \InvalidArgumentException('Status inválido.');
        $sql="UPDATE institutional_generated_contents SET status=:status, approved_by=:user, approved_at=".($status==='discarded'?'NULL':'NOW()').", published_at=".($status==='published'||$status==='approved'?'NOW()':'NULL');
        $params=['status'=>$status,'user'=>$userId,'id'=>$id];
        if($text!==null){$sql.=', text_content=:text';$params['text']=$text;}
        $sql.=' WHERE id=:id';
        $stmt=Connection::getInstance()->prepare($sql);$stmt->execute($params);
    }

    public function clearFrom(string $date): void
    {
        $stmt=Connection::getInstance()->prepare('DELETE FROM institutional_generated_contents WHERE reference_date>=:date');$stmt->execute(['date'=>$date]);
    }
}
