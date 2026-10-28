<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use PDO;
use RuntimeException;
use Throwable;
use ZipArchive;

final class SchoolDataMaintenanceService
{
    public const FORMAT = 'sge-school-backup';
    public const VERSION = 2;
    private const MAX_ENTRIES = 20000;
    private const MAX_UNCOMPRESSED = 2147483648;
    private const MAX_RATIO = 200;

    private PDO $db;
    private string $root;
    /** @var resource|null */
    private $maintenanceLock = null;

    public function __construct(?PDO $db = null, ?string $root = null)
    {
        $this->db = $db ?? Connection::getInstance();
        $this->root = $root ?? dirname(__DIR__, 2);
    }

    public function summary(): array
    {
        $counts = [];
        foreach (['students','school_classes','enrollments','attendance','student_occurrences','school_notices'] as $table) {
            $counts[$table] = $this->tableExists($table) ? (int) $this->db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn() : 0;
        }
        return ['counts' => $counts, 'lastBackup' => $this->lastBackupInfo(), 'zipAvailable' => class_exists(ZipArchive::class)];
    }

    public function createBackup(?int $actorId = null, string $ip = 'unknown', string $purpose = 'manual'): string
    {
        return $this->withMaintenanceLock(fn(): string => $this->createBackupUnlocked($actorId, $ip, $purpose));
    }

    private function createBackupUnlocked(?int $actorId, string $ip, string $purpose): string
    {
        $operation = bin2hex(random_bytes(12));
        try {
            $path = $this->buildBackup($operation, $actorId, $purpose);
            $this->audit('BACKUP_CREATED', $operation, $actorId, $ip, 'SUCCESS', ['file' => basename($path), 'purpose' => $purpose, 'size' => filesize($path) ?: 0]);
            return $path;
        } catch (Throwable $e) {
            $this->audit('BACKUP_CREATED', $operation, $actorId, $ip, 'FAILED', ['purpose' => $purpose, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function buildBackup(string $operation, ?int $actorId, string $purpose): string
    {
        $this->requireZip();
        $backupDir = $this->backupDir();
        $workspace = $backupDir . '/.tmp/' . $operation;
        $this->makeDirectory($workspace . '/database');
        $this->makeDirectory($workspace . '/uploads');
        $entries = [];
        $tableInfo = [];
        $schema = $this->schemaDefinition();
        try {
            $this->db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $this->db->beginTransaction();
            foreach ($this->tables() as $table) {
                $relative = 'database/' . rawurlencode($table) . '.ndjson';
                $absolute = $workspace . '/' . $relative;
                $rows = $this->exportTable($table, $absolute);
                $tableInfo[] = ['name' => $table, 'rows' => $rows, 'entry' => $relative];
                $entries[$relative] = $this->fileIntegrity($absolute);
            }
            $this->db->commit();
            $this->stageUploads($this->root . '/public/uploads', $workspace . '/uploads', $entries);
            ksort($entries);
            $schemaHash = hash('sha256', $this->json($schema));
            $manifest = [
                'format' => self::FORMAT, 'version' => self::VERSION, 'operation_id' => $operation,
                'created_at' => date(DATE_ATOM), 'created_by' => $actorId, 'purpose' => $purpose,
                'database' => (string) $this->db->query('SELECT DATABASE()')->fetchColumn(),
                'schema_hash' => $schemaHash, 'schema' => $schema, 'tables' => $tableInfo, 'entries' => $entries,
                'consistency' => ['database' => 'repeatable-read-transaction', 'uploads' => 'staged-after-database-snapshot', 'atomic_database_and_files' => false],
            ];
            file_put_contents($workspace . '/manifest.json', $this->json($manifest), LOCK_EX);
            $filename = 'backup-escolar-' . date('Y-m-d_H-i-s') . '-' . substr($operation, 0, 8) . '.zip';
            $partial = $backupDir . '/.' . $filename . '.partial';
            $final = $backupDir . '/' . $filename;
            $zip = new ZipArchive();
            if ($zip->open($partial, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Não foi possível criar o arquivo temporário do backup.');
            $zip->addFile($workspace . '/manifest.json', 'manifest.json');
            foreach (array_keys($entries) as $relative) $zip->addFile($workspace . '/' . $relative, $relative);
            if (!$zip->close()) throw new RuntimeException('Não foi possível finalizar o arquivo de backup.');
            self::inspectArchive($partial, $schemaHash);
            if (!rename($partial, $final)) throw new RuntimeException('Não foi possível publicar o backup concluído.');
            file_put_contents($backupDir . '/last-backup.json', $this->json(['filename'=>$filename,'created_at'=>$manifest['created_at'],'size'=>filesize($final)?:0,'operation_id'=>$operation]), LOCK_EX);
            return $final;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new RuntimeException('Falha ao gerar backup: ' . $e->getMessage(), 0, $e);
        } finally { $this->removePath($workspace, false); }
    }

    public function restoreBackup(array $file, ?int $actorId = null, string $ip = 'unknown', bool $requireHttpUpload = true): void
    {
        $this->withMaintenanceLock(function () use ($file, $actorId, $ip, $requireHttpUpload): void {
            $this->restoreBackupUnlocked($file, $actorId, $ip, $requireHttpUpload);
        });
    }

    private function restoreBackupUnlocked(array $file, ?int $actorId, string $ip, bool $requireHttpUpload): void
    {
        $inspected = FileSecurityService::inspectUpload($file, ['zip'], 500 * 1024 * 1024, $requireHttpUpload);
        $operation = bin2hex(random_bytes(12));
        $safety = null;
        try {
            self::inspectArchive($inspected['tmp_name'], $this->currentSchemaHash());
            $safety = $this->createBackup($actorId, $ip, 'pre-restore-' . $operation);
            $this->restoreFromPath($inspected['tmp_name'], $operation);
            $this->audit('BACKUP_RESTORED', $operation, $actorId, $ip, 'SUCCESS', ['safety_backup' => basename($safety)]);
        } catch (Throwable $e) {
            $recovered = false;
            if ($safety !== null && is_file($safety)) {
                try { $this->restoreFromPath($safety, $operation . '-rollback'); $recovered = true; } catch (Throwable) {}
            }
            $this->audit('BACKUP_RESTORED', $operation, $actorId, $ip, 'FAILED', ['error'=>$e->getMessage(),'rollback_restored'=>$recovered,'safety_backup'=>$safety?basename($safety):null]);
            throw new RuntimeException($e->getMessage() . ($recovered ? ' O estado anterior foi restaurado automaticamente.' : ' Consulte o backup de segurança e o plano de recuperação.'), 0, $e);
        }
    }

    private function restoreFromPath(string $path, string $operation): void
    {
        $manifest = self::inspectArchive($path, $this->currentSchemaHash());
        $safeOperation = preg_replace('/[^a-z0-9-]/i', '', $operation);
        $stage = $this->backupDir() . '/.tmp/restore-' . $safeOperation;
        $this->makeDirectory($stage);
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new RuntimeException('Não foi possível reabrir o backup validado.');
        try { foreach (array_keys((array) $manifest['entries']) as $entry) $this->extractEntry($zip, (string) $entry, $stage . '/' . $entry); }
        finally { $zip->close(); }
        $uploads = $this->root . '/public/uploads';
        $oldUploads = $this->backupDir() . '/.tmp/uploads-before-' . $safeOperation;
        $databaseCommitted = false;
        try {
            $this->importDatabase($manifest, $stage); $databaseCommitted = true;
            if (is_dir($oldUploads)) $this->removePath($oldUploads);
            if (is_dir($uploads) && !rename($uploads, $oldUploads)) throw new RuntimeException('Não foi possível preservar os uploads atuais.');
            if (!is_dir($stage . '/uploads')) $this->makeDirectory($stage . '/uploads');
            if (!rename($stage . '/uploads', $uploads)) {
                if (is_dir($oldUploads)) @rename($oldUploads, $uploads);
                throw new RuntimeException('Não foi possível ativar os uploads restaurados após o commit do banco.');
            }
            $this->removePath($oldUploads);
        } catch (Throwable $e) {
            throw new RuntimeException(($databaseCommitted ? 'Falha após o commit do banco: ' : 'Falha antes do commit do banco: ') . $e->getMessage(), 0, $e);
        } finally { $this->removePath($stage, false); }
    }

    public static function inspectArchive(string $path, ?string $expectedSchemaHash = null): array
    {
        if (!class_exists(ZipArchive::class)) throw new RuntimeException('A extensão ZIP do PHP não está habilitada.');
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new RuntimeException('O arquivo ZIP está corrompido ou incompleto.');
        try {
            self::validateZipEntries($zip);
            $raw = $zip->getFromName('manifest.json');
            $manifest = $raw === false ? null : json_decode($raw, true);
            if (!is_array($manifest) || ($manifest['format'] ?? '') !== self::FORMAT || (int) ($manifest['version'] ?? 0) !== self::VERSION) throw new RuntimeException('Formato ou versão de backup incompatível.');
            if ($expectedSchemaHash !== null && !hash_equals($expectedSchemaHash, (string) ($manifest['schema_hash'] ?? ''))) throw new RuntimeException('O esquema do backup não é compatível com esta versão do sistema.');
            $entries = $manifest['entries'] ?? null;
            if (!is_array($entries)) throw new RuntimeException('O manifesto de integridade é inválido.');
            $archiveEntries = [];
            for ($i=0;$i<$zip->numFiles;$i++) {
                $archiveName = (string) $zip->getNameIndex($i);
                if ($archiveName !== 'manifest.json' && !str_ends_with($archiveName, '/')) $archiveEntries[] = $archiveName;
            }
            $declaredEntries = array_map('strval', array_keys($entries)); sort($archiveEntries); sort($declaredEntries);
            if ($archiveEntries !== $declaredEntries) throw new RuntimeException('O conteúdo do ZIP não corresponde integralmente ao manifesto.');
            foreach ((array) ($manifest['tables'] ?? []) as $tableInfo) {
                if (!is_array($tableInfo) || !isset($entries[(string) ($tableInfo['entry'] ?? '')])) throw new RuntimeException('O manifesto não contém todos os dados das tabelas.');
            }
            foreach ($entries as $name => $expected) {
                if (!is_array($expected) || !isset($expected['sha256'], $expected['size'])) throw new RuntimeException('O manifesto contém uma entrada inválida.');
                $stat = $zip->statName((string) $name);
                if ($stat === false || (int) ($stat['size'] ?? -1) !== (int) $expected['size']) throw new RuntimeException('O backup está incompleto: ' . $name);
                $stream = $zip->getStream((string) $name); if ($stream === false) throw new RuntimeException('Não foi possível verificar: ' . $name);
                $hash = hash_init('sha256'); hash_update_stream($hash, $stream); fclose($stream);
                if (!hash_equals((string) $expected['sha256'], hash_final($hash))) throw new RuntimeException('Falha de integridade em: ' . $name);
            }
            return $manifest;
        } finally { $zip->close(); }
    }

    private static function validateZipEntries(ZipArchive $zip): void
    {
        if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_ENTRIES) throw new RuntimeException('O backup possui uma quantidade inválida de arquivos.');
        $total = 0;
        for ($i=0;$i<$zip->numFiles;$i++) {
            $stat=$zip->statIndex($i);$name=str_replace('\\','/',(string)($stat['name']??''));
            if($name===''||str_contains($name,"\0")||str_starts_with($name,'/')||preg_match('#(^|/)\.\.(/|$)|^[A-Za-z]:#',$name))throw new RuntimeException('O backup contém caminho inseguro.');
            if($name!=='manifest.json'&&!str_starts_with($name,'database/')&&!str_starts_with($name,'uploads/'))throw new RuntimeException('O backup contém arquivos fora das áreas permitidas.');
            $size=(int)($stat['size']??0);$compressed=max(1,(int)($stat['comp_size']??0));$total+=$size;
            if($total>self::MAX_UNCOMPRESSED||($size>1048576&&$size/$compressed>self::MAX_RATIO))throw new RuntimeException('O backup excede os limites seguros de descompactação.');
            $opsys=0;$attributes=0;if($zip->getExternalAttributesIndex($i,$opsys,$attributes)&&$opsys===ZipArchive::OPSYS_UNIX&&(($attributes>>16)&0170000)===0120000)throw new RuntimeException('O backup não pode conter links simbólicos.');
        }
    }

    public function clearSchoolData(array $options, ?int $actorId = null, string $ip = 'unknown'): array
    {
        return $this->withMaintenanceLock(fn(): array => $this->clearSchoolDataUnlocked($options, $actorId, $ip));
    }

    private function clearSchoolDataUnlocked(array $options, ?int $actorId, string $ip): array
    {
        $operation=bin2hex(random_bytes(12));$safety=$this->createBackup($actorId,$ip,'pre-cleanup-'.$operation);
        $keepStudents=!empty($options['keep_students']);$keepClasses=$keepStudents||!empty($options['keep_classes']);
        $keepStudentPhotos=$keepStudents&&!empty($options['keep_student_photos']);$keepClassPhotos=$keepClasses&&!empty($options['keep_class_photos']);
        $preserve=['migrations','users','schools','school_settings','system_settings','system_setting_revisions'];if($keepClasses)$preserve[]='school_classes';if($keepStudents)array_push($preserve,'students','enrollments');
        $cleared=[];$this->db->exec('SET FOREIGN_KEY_CHECKS=0');
        try{$this->db->beginTransaction();foreach($this->tables() as $table){if(in_array($table,$preserve,true))continue;$this->db->exec("DELETE FROM `{$table}`");$cleared[]=$table;}$this->db->commit();}
        catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();$this->audit('DATA_CLEANUP',$operation,$actorId,$ip,'FAILED',['error'=>$e->getMessage(),'safety_backup'=>basename($safety)]);throw new RuntimeException('Não foi possível limpar os dados: '.$e->getMessage(),0,$e);}finally{$this->db->exec('SET FOREIGN_KEY_CHECKS=1');}
        try {
            $keepDirs=['schools','users'];if($keepStudentPhotos)$keepDirs[]='students';if($keepClassPhotos)$keepDirs[]='classes';$this->clearDirectory($this->root.'/public/uploads',$keepDirs);
            if($keepStudents&&!$keepStudentPhotos&&$this->tableExists('students'))$this->nullExistingColumn('students',['photo_path','photo','image_path']);if($keepClasses&&!$keepClassPhotos&&$this->tableExists('school_classes'))$this->nullExistingColumn('school_classes',['photo_path','photo','image_path']);
        } catch (Throwable $e) {
            $recovered=false;try{$this->restoreFromPath($safety,$operation.'-cleanup-rollback');$recovered=true;}catch(Throwable){}
            $this->audit('DATA_CLEANUP',$operation,$actorId,$ip,'FAILED',['error'=>$e->getMessage(),'rollback_restored'=>$recovered,'safety_backup'=>basename($safety)]);
            throw new RuntimeException('A limpeza falhou após o commit. '.($recovered?'O estado anterior foi restaurado.':'Restaure manualmente o backup de segurança '.basename($safety).'.'),0,$e);
        }
        $result=['cleared_tables'=>count($cleared),'kept_students'=>$keepStudents,'kept_classes'=>$keepClasses,'kept_student_photos'=>$keepStudentPhotos,'kept_class_photos'=>$keepClassPhotos,'safety_backup'=>basename($safety)];$this->audit('DATA_CLEANUP',$operation,$actorId,$ip,'SUCCESS',$result);return $result;
    }

    private function exportTable(string $table,string $path):int{$out=fopen($path,'wb');if($out===false)throw new RuntimeException('Não foi possível preparar a exportação da tabela '.$table);$count=0;try{$statement=$this->db->query("SELECT * FROM `{$table}`");while(($row=$statement->fetch(PDO::FETCH_ASSOC))!==false){fwrite($out,$this->json($row)."\n");$count++;}$statement->closeCursor();}finally{fclose($out);}return $count;}
    private function importDatabase(array $manifest,string $stage):void{$current=array_flip($this->tables());$this->db->exec('SET FOREIGN_KEY_CHECKS=0');try{$this->db->beginTransaction();foreach(array_keys($current) as $table)$this->db->exec("DELETE FROM `{$table}`");foreach((array)$manifest['tables'] as $info){$table=(string)($info['name']??'');$entry=(string)($info['entry']??'');if(!isset($current[$table])||$entry==='')throw new RuntimeException('Tabela incompatível no backup: '.$table);$handle=fopen($stage.'/'.$entry,'rb');if($handle===false)throw new RuntimeException('Dados ausentes para '.$table);$statement=null;$columns=[];try{while(($line=fgets($handle))!==false){$row=json_decode($line,true,512,JSON_THROW_ON_ERROR);if(!is_array($row))throw new RuntimeException('Linha inválida na tabela '.$table);if($statement===null){$columns=array_keys($row);$quoted=implode(',',array_map(fn($c)=>'`'.str_replace('`','',(string)$c).'`',$columns));$statement=$this->db->prepare("INSERT INTO `{$table}` ({$quoted}) VALUES (".implode(',',array_fill(0,count($columns),'?')).')');}$statement->execute(array_map(fn($c)=>$row[$c]??null,$columns));}}finally{fclose($handle);}}$this->db->commit();}catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}finally{$this->db->exec('SET FOREIGN_KEY_CHECKS=1');}}
    private function schemaDefinition():array{return $this->db->query("SELECT TABLE_NAME,COLUMN_NAME,ORDINAL_POSITION,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC)?:[];}
    private function currentSchemaHash():string{return hash('sha256',$this->json($this->schemaDefinition()));}
    private function tables():array{return array_map('strval',$this->db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN));}
    private function tableExists(string $table):bool{$s=$this->db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$s->execute([$table]);return(int)$s->fetchColumn()>0;}
    private function stageUploads(string $source,string $target,array &$entries):void{if(!is_dir($source))return;$iterator=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source,\FilesystemIterator::SKIP_DOTS));foreach($iterator as $file){if(!$file->isFile()||$file->isLink())continue;$relative=str_replace('\\','/',substr($file->getPathname(),strlen($source)+1));$entry='uploads/'.$relative;$destination=$target.'/'.$relative;$this->makeDirectory(dirname($destination));if(!copy($file->getPathname(),$destination))throw new RuntimeException('Não foi possível copiar um upload para o snapshot.');$entries[$entry]=$this->fileIntegrity($destination);}}
    private function extractEntry(ZipArchive $zip,string $entry,string $target):void{$this->makeDirectory(dirname($target));$in=$zip->getStream($entry);$out=fopen($target,'wb');if($in===false||$out===false){if(is_resource($in))fclose($in);if(is_resource($out))fclose($out);throw new RuntimeException('Falha ao preparar a extração de '.$entry);}$copied=stream_copy_to_stream($in,$out);fclose($in);fclose($out);if($copied===false)throw new RuntimeException('Falha durante a extração de '.$entry);}
    private function fileIntegrity(string $path):array{return['sha256'=>hash_file('sha256',$path),'size'=>filesize($path)?:0];}
    private function backupDir():string{$dir=$this->root.'/storage/backups';$this->makeDirectory($dir.'/.tmp');return$dir;}
    private function makeDirectory(string $dir):void{if(!is_dir($dir)&&!mkdir($dir,0770,true)&&!is_dir($dir))throw new RuntimeException('Não foi possível preparar o diretório temporário.');}
    private function requireZip():void{if(!class_exists(ZipArchive::class))throw new RuntimeException('A extensão ZIP do PHP não está habilitada.');}
    private function withMaintenanceLock(callable $operation):mixed{if(is_resource($this->maintenanceLock))return$operation();$lockPath=$this->backupDir().'/maintenance.lock';$handle=fopen($lockPath,'c+');if($handle===false||!flock($handle,LOCK_EX)){if(is_resource($handle))fclose($handle);throw new RuntimeException('Não foi possível obter o bloqueio exclusivo de manutenção.');}$this->maintenanceLock=$handle;try{return$operation();}finally{flock($handle,LOCK_UN);fclose($handle);$this->maintenanceLock=null;}}
    private function clearDirectory(string $directory,array $keep):void{if(!is_dir($directory))return;foreach(scandir($directory)?:[]as$entry){if($entry==='.'||$entry==='..'||in_array($entry,$keep,true))continue;$this->removePath($directory.'/'.$entry);}}
    private function removePath(string $path,bool $throw=true):void{if(is_file($path)||is_link($path)){if(!@unlink($path)&&is_file($path)&&$throw)throw new RuntimeException('Não foi possível remover arquivo temporário.');return;}if(!is_dir($path))return;foreach(scandir($path)?:[]as$entry){if($entry==='.'||$entry==='..')continue;$this->removePath($path.'/'.$entry,$throw);}if(!@rmdir($path)&&is_dir($path)&&$throw)throw new RuntimeException('Não foi possível remover diretório temporário.');}
    private function nullExistingColumn(string $table,array $candidates):void{foreach($candidates as$column){$s=$this->db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');$s->execute([$table,$column]);if((int)$s->fetchColumn()>0){$this->db->exec("UPDATE `{$table}` SET `{$column}`=NULL");return;}}}
    private function lastBackupInfo():?array{$file=$this->root.'/storage/backups/last-backup.json';if(!is_file($file))return null;$data=json_decode((string)file_get_contents($file),true);return is_array($data)?$data:null;}
    private function audit(string $action,string $operation,?int $actor,string $ip,string $status,array $details):void{$dir=$this->root.'/storage/logs';if(!is_dir($dir))@mkdir($dir,0770,true);$record=['at'=>date(DATE_ATOM),'action'=>$action,'operation_id'=>$operation,'actor_id'=>$actor,'ip'=>$ip,'status'=>$status,'details'=>$details];@file_put_contents($dir.'/data-maintenance.ndjson',$this->json($record)."\n",FILE_APPEND|LOCK_EX);}
    private function json(array $data):string{return json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);}
}
