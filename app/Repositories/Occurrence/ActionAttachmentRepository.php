<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence;

use App\Repositories\BaseRepository;

final class ActionAttachmentRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $stmt=$this->db->prepare("INSERT INTO occurrence_action_attachments
            (action_id,original_name,stored_name,relative_path,mime_type,extension,size_bytes,uploaded_by,uploaded_by_name,created_at,updated_at)
            VALUES (:action_id,:original_name,:stored_name,:relative_path,:mime_type,:extension,:size_bytes,:uploaded_by,:uploaded_by_name,NOW(),NOW())");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt=$this->db->prepare('SELECT * FROM occurrence_action_attachments WHERE id=:id LIMIT 1');
        $stmt->execute(['id'=>$id]);
        $row=$stmt->fetch();
        return $row?:null;
    }

    public function byAction(int $actionId): array
    {
        $stmt=$this->db->prepare('SELECT * FROM occurrence_action_attachments WHERE action_id=:action_id ORDER BY created_at,id');
        $stmt->execute(['action_id'=>$actionId]);
        return $stmt->fetchAll();
    }

    public function groupedByActions(array $ids): array
    {
        $ids=array_values(array_filter(array_map('intval',$ids),static fn(int $id):bool=>$id>0));
        if($ids===[])return [];
        $marks=implode(',',array_fill(0,count($ids),'?'));
        $stmt=$this->db->prepare("SELECT * FROM occurrence_action_attachments WHERE action_id IN ($marks) ORDER BY created_at,id");
        $stmt->execute($ids);
        $grouped=[];
        foreach($stmt->fetchAll() as $row)$grouped[(int)$row['action_id']][]=$row;
        return $grouped;
    }

    public function delete(int $id): bool
    {
        $stmt=$this->db->prepare('DELETE FROM occurrence_action_attachments WHERE id=:id');
        $stmt->execute(['id'=>$id]);
        return $stmt->rowCount()>0;
    }
}
