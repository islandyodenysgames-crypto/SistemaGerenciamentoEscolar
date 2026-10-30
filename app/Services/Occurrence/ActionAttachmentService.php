<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\Repositories\Occurrence\ActionAttachmentRepository;
use InvalidArgumentException;
use App\Services\FileSecurityService;

final class ActionAttachmentService
{
    private const MAX_FILE_SIZE=25*1024*1024;
    private const MAX_FILES=10;
    private const ALLOWED=['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];

    public function __construct(private ActionAttachmentRepository $repository){}

    public function uploadMany(int $actionId,array $files,?int $userId,?string $userName): array
    {
        $normalized=$this->normalize($files);
        if(count($normalized)>self::MAX_FILES)throw new InvalidArgumentException('Envie no máximo 10 arquivos por providência.');
        $saved=[];
        foreach($normalized as $file){
            if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)continue;
            $inspected=FileSecurityService::inspectUpload($file,self::ALLOWED,self::MAX_FILE_SIZE);
            $size=$inspected['size_bytes'];$original=$inspected['original_name'];$ext=$inspected['extension'];$tmp=$inspected['tmp_name'];$mime=$inspected['mime_type'];
            $relativeDir='uploads/occurrence-actions/'.date('Y/m').'/'.$actionId;
            $dir=public_path($relativeDir);
            if(!is_dir($dir)&&!mkdir($dir,0775,true)&&!is_dir($dir))throw new InvalidArgumentException('Não foi possível preparar a pasta dos anexos.');
            $stored=FileSecurityService::randomName($ext,'anexo');
            if(!move_uploaded_file($tmp,$dir.DIRECTORY_SEPARATOR.$stored))throw new InvalidArgumentException('Não foi possível salvar o anexo '.$original.'.');
            $saved[]=$this->repository->create(['action_id'=>$actionId,'original_name'=>$original,'stored_name'=>$stored,'relative_path'=>$relativeDir.'/'.$stored,'mime_type'=>$mime,'extension'=>$ext,'size_bytes'=>$size,'uploaded_by'=>$userId,'uploaded_by_name'=>$userName]);
        }
        return $saved;
    }

    public function groupedByActions(array $ids): array
    {
        $grouped=$this->repository->groupedByActions($ids);
        foreach($grouped as $id=>$items)$grouped[$id]=array_map([$this,'decorate'],$items);
        return $grouped;
    }

    public function byAction(int $actionId): array
    {
        if ($actionId <= 0) return [];
        return array_map([$this, 'decorate'], $this->repository->byAction($actionId));
    }

    public function find(int $id): ?array
    {
        $item = $this->repository->find($id);
        return $item ? $this->decorate($item) : null;
    }

    public function remove(int $id): bool
    {
        $item=$this->repository->find($id);
        if(!$item)return false;
        FileSecurityService::deleteWithin(public_path(),(string)$item['relative_path']);
        return $this->repository->delete($id);
    }

    private function normalize(array $files): array
    {
        if(!isset($files['name']))return [];
        if(!is_array($files['name']))return [$files];
        $result=[];
        foreach($files['name'] as $i=>$name)$result[]=['name'=>$name,'type'=>$files['type'][$i]??'','tmp_name'=>$files['tmp_name'][$i]??'','error'=>$files['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$files['size'][$i]??0];
        return $result;
    }

    private function decorate(array $item): array
    {
        $item['url']=base_url((string)$item['relative_path']);
        $bytes=(int)($item['size_bytes']??0);
        $item['size_label']=$bytes>=1048576?number_format($bytes/1048576,1,',','.').' MB':($bytes>=1024?number_format($bytes/1024,0,',','.').' KB':$bytes.' B');
        return $item;
    }
}
