<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\Repositories\Occurrence\ActionAttachmentRepository;
use InvalidArgumentException;

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
            if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK)throw new InvalidArgumentException('Um dos anexos não pôde ser enviado.');
            $size=(int)($file['size']??0);
            if($size<=0||$size>self::MAX_FILE_SIZE)throw new InvalidArgumentException('Cada anexo deve possuir no máximo 25 MB.');
            $original=trim(basename((string)($file['name']??'arquivo')));
            $ext=strtolower(pathinfo($original,PATHINFO_EXTENSION));
            if(!in_array($ext,self::ALLOWED,true))throw new InvalidArgumentException('Formato de anexo não permitido: '.($ext?:'sem extensão').'.');
            $tmp=(string)($file['tmp_name']??'');
            if($tmp===''||!is_uploaded_file($tmp))throw new InvalidArgumentException('Arquivo temporário inválido.');
            $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($tmp)?:'application/octet-stream';
            $relativeDir='uploads/occurrence-actions/'.date('Y/m').'/'.$actionId;
            $dir=public_path($relativeDir);
            if(!is_dir($dir)&&!mkdir($dir,0775,true)&&!is_dir($dir))throw new InvalidArgumentException('Não foi possível preparar a pasta dos anexos.');
            $stored=bin2hex(random_bytes(16)).'.'.$ext;
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

    public function remove(int $id): bool
    {
        $item=$this->repository->find($id);
        if(!$item)return false;
        $path=public_path((string)$item['relative_path']);
        if(is_file($path))@unlink($path);
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
