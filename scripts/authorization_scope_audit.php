<?php

declare(strict_types=1);

$root=dirname(__DIR__);$failures=[];
$require=static function(bool $condition,string $message)use(&$failures):void{if(!$condition)$failures[]=$message;};
$source=static fn(string $file):string=>(string)file_get_contents($root.'/'.$file);

$settings=$source('app/Controllers/SettingsController.php');
$reports=$source('app/Controllers/ReportController.php');
$dashboard=$source('app/Controllers/DashboardController.php');
$occurrenceDashboard=$source('app/Controllers/OccurrenceDashboardController.php');
$search=$source('app/Services/SearchService.php');
$favorites=$source('app/Services/FavoriteService.php');
$files=$source('app/Controllers/FileAccessController.php');
$base=$source('app/Controllers/BaseController.php');

$require(str_contains($settings,'extends BaseController')&&str_contains($settings,'Permissions::SETTINGS_MANAGE'),'Configurações gerais não estão protegidas por permissão administrativa.');
$require(str_contains($reports,'Permissions::REPORTS_VIEW'),'Relatórios/exportações não exigem a permissão declarada.');
$require(str_contains($dashboard,'Permissions::DASHBOARD_VIEW'),'Dashboard não exige sua permissão declarada.');
$require(str_contains($occurrenceDashboard,'Permissions::OCCURRENCES_VIEW'),'Dashboard de ocorrências não exige permissão de consulta.');
$require(str_contains($search,'allowedGroups')&&str_contains($search,'student_monitoring_users'),'Busca global não filtra grupos e casos por escopo.');
$require(str_contains($favorites,'isAccessible')&&str_contains($favorites,'student_monitoring_users'),'Favoritos não validam existência e escopo do registro.');
$require(str_contains($files,'withinRecordScope')&&str_contains($files,"http_response_code(404)"),'Downloads privados não ocultam recursos fora do escopo.');
$require(str_contains($base,"http_response_code(401)")&&str_contains($base,"http_response_code(403)"),'Respostas AJAX de autenticação/autorização não estão padronizadas.');

foreach($failures as $failure)fwrite(STDERR,"FALHA: {$failure}\n");
echo 'Auditoria de autorização e escopo: '.count($failures)." erro(s).\n";
exit($failures===[]?0:1);
