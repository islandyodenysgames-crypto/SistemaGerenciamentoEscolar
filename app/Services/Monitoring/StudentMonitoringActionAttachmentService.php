<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

use App\Repositories\Monitoring\StudentMonitoringActionAttachmentRepository;
use InvalidArgumentException;
use App\Services\FileSecurityService;

final class StudentMonitoringActionAttachmentService
{
    private const MAX_FILE_SIZE=25*1024*1024;
    private const MAX_FILES=10;
    private const ALLOWED=['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','webp','gif','mp4','webm','mp3','wav','ogg','zip','txt'];

    public function __construct(private StudentMonitoringActionAttachmentRepository $repository){}

    public function find(int $id): ?array { $row=$this->repository->find($id); return $row?$this->decorate($row):null; }
    public function groupedByActions(array $ids): array
    {
        $grouped=$this->repository->groupedByActions($ids);
        foreach($grouped as $id=>$rows)$grouped[$id]=array_map([$this,'decorate'],$rows);
        return $grouped;
    }

    public function uploadMany(int $actionId,array $files,?int $userId,?string $userName): array
    {
        $items=$this->normalize($files);
        if(count($items)>self::MAX_FILES)throw new InvalidArgumentException('Envie no máximo 10 arquivos por vez.');
        $saved=[];
        foreach($items as $file){
            if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)continue;
            $inspected=FileSecurityService::inspectUpload($file,self::ALLOWED,self::MAX_FILE_SIZE);
            $size=$inspected['size_bytes'];$original=$inspected['original_name'];$ext=$inspected['extension'];$tmp=$inspected['tmp_name'];$mime=$inspected['mime_type'];
            $relativeDir='uploads/monitoring-actions/'.date('Y/m').'/'.$actionId;
            $dir=public_path($relativeDir);
            if(!is_dir($dir)&&!mkdir($dir,0775,true)&&!is_dir($dir))throw new InvalidArgumentException('Não foi possível preparar a pasta de anexos.');
            $stored=FileSecurityService::randomName($ext,'anexo');
            if(!move_uploaded_file($tmp,$dir.DIRECTORY_SEPARATOR.$stored))throw new InvalidArgumentException('Não foi possível salvar o arquivo '.$original.'.');
            $saved[]=$this->repository->create(['action_id'=>$actionId,'original_name'=>$original,'stored_name'=>$stored,'relative_path'=>$relativeDir.'/'.$stored,'mime_type'=>$mime,'extension'=>$ext,'size_bytes'=>$size,'uploaded_by'=>$userId,'uploaded_by_name'=>$userName]);
        }
        return $saved;
    }

    public function remove(int $id): bool
    {
        $item=$this->repository->find($id); if(!$item)return false;
        FileSecurityService::deleteWithin(public_path(),(string)$item['relative_path']);
        return $this->repository->delete($id);
    }
    public function removeAll(int $actionId): void
    {
        foreach($this->repository->byAction($actionId) as $item){FileSecurityService::deleteWithin(public_path(),(string)$item['relative_path']);} $this->repository->deleteByAction($actionId);
    }
    private function normalize(array $files): array
    {
        if(!isset($files['name']))return[]; if(!is_array($files['name']))return[$files]; $out=[];
        foreach($files['name'] as $i=>$name)$out[]=['name'=>$name,'type'=>$files['type'][$i]??'','tmp_name'=>$files['tmp_name'][$i]??'','error'=>$files['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$files['size'][$i]??0]; return$out;
    }
    private function decorate(array $item): array
    {
        $mime=strtolower((string)($item['mime_type']??''));$item['url']=base_url((string)$item['relative_path']);$item['is_image']=str_starts_with($mime,'image/');$item['is_video']=str_starts_with($mime,'video/');$item['is_audio']=str_starts_with($mime,'audio/');$item['is_pdf']=$mime==='application/pdf'||strtolower((string)($item['extension']??''))==='pdf';$item['icon']=$item['is_image']?'image':($item['is_video']?'video':($item['is_audio']?'audio-lines':($item['is_pdf']?'file-text':'paperclip')));$bytes=(int)($item['size_bytes']??0);$item['size_label']=$bytes>=1048576?number_format($bytes/1048576,1,',','.').' MB':($bytes>=1024?number_format($bytes/1024,0,',','.').' KB':$bytes.' B');return$item;
    }
}
