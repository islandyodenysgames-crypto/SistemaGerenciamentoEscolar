<?php

declare(strict_types=1);

if (($argv[1] ?? '') !== '--run') {
    fwrite(STDERR, "Este teste cria e remove um banco descartável. Execute com --run.\n");
    exit(2);
}

$root = dirname(__DIR__);
require_once $root . '/app/Core/Env.php';
require_once $root . '/app/Database/Connection.php';
require_once $root . '/app/Services/FileSecurityService.php';
require_once $root . '/app/Services/SchoolDataMaintenanceService.php';

use App\Core\Env;
use App\Services\SchoolDataMaintenanceService;

Env::load($root . '/.env');
$host = (string) Env::get('DB_HOST', '127.0.0.1');
$port = (string) Env::get('DB_PORT', '3306');
$user = (string) Env::get('DB_USERNAME', 'root');
$password = (string) Env::get('DB_PASSWORD', '');
$databaseName = 'sge_disposable_' . bin2hex(random_bytes(5));
$temporaryRoot = sys_get_temp_dir() . '/sge-recovery-' . bin2hex(random_bytes(5));
$server = null;

$remove = static function (string $path) use (&$remove): void {
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    if (!is_dir($path)) return;
    foreach (scandir($path) ?: [] as $entry) if ($entry !== '.' && $entry !== '..') $remove($path . '/' . $entry);
    @rmdir($path);
};

try {
    $server = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
    $server->exec("CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db = new PDO("mysql:host={$host};port={$port};dbname={$databaseName};charset=utf8mb4", $user, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
    $db->exec('CREATE TABLE empty_table (id BIGINT PRIMARY KEY AUTO_INCREMENT) ENGINE=InnoDB');
    $db->exec('CREATE TABLE records (id BIGINT PRIMARY KEY AUTO_INCREMENT, value VARCHAR(100) NOT NULL) ENGINE=InnoDB');
    $db->beginTransaction(); $insert=$db->prepare('INSERT INTO records(value) VALUES (?)');
    for($i=0;$i<10000;$i++)$insert->execute(['registro-'.$i]); $db->commit();
    mkdir($temporaryRoot . '/public/uploads/test', 0770, true);
    $largeFile = $temporaryRoot . '/public/uploads/test/large.bin';
    file_put_contents($largeFile, random_bytes(5 * 1024 * 1024)); $originalHash=hash_file('sha256',$largeFile);
    $service = new SchoolDataMaintenanceService($db, $temporaryRoot);
    $backup = $service->createBackup(1, '127.0.0.1', 'integration-test');
    SchoolDataMaintenanceService::inspectArchive($backup);
    $db->exec('DELETE FROM records'); file_put_contents($largeFile, 'alterado');
    $service->restoreBackup(['name'=>basename($backup),'tmp_name'=>$backup,'error'=>UPLOAD_ERR_OK,'size'=>filesize($backup)],1,'127.0.0.1',false);
    if((int)$db->query('SELECT COUNT(*) FROM records')->fetchColumn()!==10000)throw new RuntimeException('A quantidade restaurada diverge.');
    if((string)$db->query('SELECT value FROM records WHERE id=1')->fetchColumn()!=='registro-0')throw new RuntimeException('O conteúdo restaurado diverge.');
    if(!is_file($largeFile)||!hash_equals($originalHash,hash_file('sha256',$largeFile)))throw new RuntimeException('O upload grande não foi restaurado integralmente.');
    echo "Integração MySQL descartável: aprovada (10.000 registros, tabela vazia e arquivo de 5 MiB).\n";
} finally {
    if ($server instanceof PDO && str_starts_with($databaseName, 'sge_disposable_')) $server->exec("DROP DATABASE IF EXISTS `{$databaseName}`");
    $remove($temporaryRoot);
}
